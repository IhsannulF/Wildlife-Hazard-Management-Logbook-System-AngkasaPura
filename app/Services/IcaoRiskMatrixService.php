<?php

namespace App\Services;

class IcaoRiskMatrixService
{
    /**
     * Grid hotspot berfrekuensi tinggi di Bandara Juanda berdasarkan data historis
     */
    public const HOTSPOT_GRIDS = [
        'K-10', 'D-9', 'E-12', 'G-18', 'H-8', 'F-5',
        'A-2', 'B-4', 'L-10', 'C-6', 'J-11', 'E-18',
        'K-09', 'K-11', 'D-10', 'E-7',
    ];

    /**
     * Hitung matriks risiko ICAO berdasarkan data satwa, zona, dan grid
     *
     * @param  array<int, array{nama?: string, nama_satwa?: string, jumlah?: int|string}>  $satwaList
     * @return array{score: int, level: string, probability: int, severity: int, zone_score: int, species_score: int, total_satwa: int}
     */
    public function calculate(array $satwaList, ?string $areaInspeksi = null, ?string $gridLokasi = null): array
    {
        $totalSatwa = 0;
        $speciesNames = [];

        foreach ($satwaList as $s) {
            $nama = (string) ($s['nama'] ?? $s['nama_satwa'] ?? '');
            if ($nama !== '') {
                $speciesNames[] = $nama;
            }

            $jml = (int) ($s['jumlah'] ?? 0);
            $totalSatwa += max(0, $jml);
        }

        // Jika tidak ada jumlah eksplisit tetapi ada nama satwa, asumsikan minimal 1 ekor
        if ($totalSatwa === 0 && ! empty($speciesNames)) {
            $totalSatwa = 1;
        }

        $probability = $this->calculateProbability($totalSatwa, $gridLokasi);
        $severityData = $this->calculateSeverity($speciesNames, $areaInspeksi);
        $severity = $severityData['severity'];

        $rawScore = $probability * $severity;
        $score = max(1, min(12, $rawScore));
        $level = $this->determineLevel($score);

        return [
            'score' => $score,
            'level' => $level,
            'probability' => $probability,
            'severity' => $severity,
            'zone_score' => $severityData['zone_score'],
            'species_score' => $severityData['species_score'],
            'total_satwa' => $totalSatwa,
        ];
    }

    /**
     * Hitung probabilitas (P: 1 - 3 atau 1 - 4 dengan hotspot)
     */
    public function calculateProbability(int $totalEkor, ?string $gridLokasi = null): int
    {
        // Nilai dasar kuantitas
        if ($totalEkor <= 1) {
            $baseP = 1; // Soliter
        } elseif ($totalEkor <= 5) {
            $baseP = 2; // Kelompok kecil
        } else {
            $baseP = 3; // Kawanan besar / flocking
        }

        // Penyesuaian frekuensi area / hotspot
        $isHotspot = false;
        if (! empty($gridLokasi)) {
            $normalizedGrid = strtoupper(trim(str_replace('=', '-', $gridLokasi)));
            $isHotspot = in_array($normalizedGrid, self::HOTSPOT_GRIDS, true);
        }

        if ($isHotspot && $baseP < 3) {
            $baseP += 1;
        }

        return max(1, min(4, $baseP));
    }

    /**
     * Hitung tingkat keparahan / severity (S: 1 - 4)
     *
     * @param  array<int, string>  $speciesNames
     * @return array{severity: int, zone_score: int, species_score: int}
     */
    public function calculateSeverity(array $speciesNames, ?string $areaInspeksi = null): array
    {
        $zoneScore = $this->getZoneScore($areaInspeksi);
        $speciesScore = $this->getSpeciesScore($speciesNames);

        // Matriks Keparahan ICAO (Kombinasi Zona & Bobot Satwa)
        $severity = match ($zoneScore) {
            3 => match ($speciesScore) { // Runway Strip (Airside Critical)
                3 => 4, // Spesies Tinggi di Runway = Kritis Maksimal
                2 => 3, // Spesies Sedang di Runway = Keparahan Tinggi
                default => 2, // Spesies Kecil di Runway = Keparahan Sedang
            },
            2 => match ($speciesScore) { // Taxiway / Apron (Movement Area)
                3 => 3,
                2 => 2,
                default => 1,
            },
            default => match ($speciesScore) { // Perimeter / Drainage / Buffer
                3 => 2,
                default => 1,
            },
        };

        return [
            'severity' => max(1, min(4, $severity)),
            'zone_score' => $zoneScore,
            'species_score' => $speciesScore,
        ];
    }

    /**
     * Skor Zona Pergerakan Pesawat
     * Runway Strip = 3, Taxiway / Apron = 2, Perimeter = 1
     */
    public function getZoneScore(?string $area): int
    {
        if (empty($area)) {
            return 2; // Default movement area
        }

        $areaLower = strtolower($area);

        if (
            str_contains($areaLower, 'runway') ||
            str_contains($areaLower, 'landasan') ||
            str_contains($areaLower, '07l') ||
            str_contains($areaLower, '25r') ||
            str_contains($areaLower, 'threshold') ||
            str_contains($areaLower, 'touchdown')
        ) {
            return 3;
        }

        if (
            str_contains($areaLower, 'taxiway') ||
            str_contains($areaLower, 'apron') ||
            str_contains($areaLower, 'taxilane') ||
            str_contains($areaLower, 'tarmac') ||
            str_contains($areaLower, 'stand') ||
            str_contains($areaLower, 'gse')
        ) {
            return 2;
        }

        return 1; // Perimeter, drainase, buffer, pagar, saluran
    }

    /**
     * Skor Bobot Spesies Satwa
     * Burung besar / flocking / mamalia & reptil besar = 3
     * Biawak sedang / burung sedang / mamalia sedang = 2
     * Burung kecil soliter = 1
     *
     * @param  array<int, string>  $speciesNames
     */
    public function getSpeciesScore(array $speciesNames): int
    {
        if (empty($speciesNames)) {
            return 2; // Default sedang
        }

        $maxScore = 1;

        foreach ($speciesNames as $name) {
            $n = strtolower($name);

            // Level Tinggi (Skor 3)
            if (
                str_contains($n, 'cangak') ||
                str_contains($n, 'elang') ||
                str_contains($n, 'kuntul') ||
                str_contains($n, 'blekok') ||
                str_contains($n, 'pecuk') ||
                str_contains($n, 'anjing') ||
                str_contains($n, 'kera') ||
                str_contains($n, 'monyet') ||
                str_contains($n, 'ular') ||
                str_contains($n, 'sanca') ||
                str_contains($n, 'piton')
            ) {
                return 3; // Langsung kembalikan skor tertinggi jika ditemukan
            }

            // Burung & satwa kecil soliter (Skor 1)
            if (
                str_contains($n, 'gereja') ||
                str_contains($n, 'walet') ||
                str_contains($n, 'layang') ||
                str_contains($n, 'pipit') ||
                str_contains($n, 'emprit') ||
                str_contains($n, 'prenjak') ||
                str_contains($n, 'katak') ||
                str_contains($n, 'kodok')
            ) {
                $maxScore = max($maxScore, 1);

                continue;
            }

            // Level Sedang (Skor 2)
            if (
                str_contains($n, 'biawak') ||
                str_contains($n, 'kucing') ||
                str_contains($n, 'garangan') ||
                str_contains($n, 'musang') ||
                str_contains($n, 'tikus') ||
                str_contains($n, 'kerak') ||
                str_contains($n, 'myna') ||
                str_contains($n, 'cabak') ||
                str_contains($n, 'burung')
            ) {
                $maxScore = max($maxScore, 2);
            }
        }

        return $maxScore;
    }

    /**
     * Kategorisasi Level Risiko ICAO
     * Rendah (1-3), Sedang (4-6), Tinggi (7-9), Kritis (>9)
     */
    public function determineLevel(int $score): string
    {
        if ($score <= 3) {
            return 'rendah';
        }
        if ($score <= 6) {
            return 'sedang';
        }
        if ($score <= 9) {
            return 'tinggi';
        }

        return 'kritis';
    }

    /**
     * Badge CSS untuk Admin Table & Dashboard
     */
    public function getBadgeClass(string $level): string
    {
        return match (strtolower($level)) {
            'kritis' => 'bg-rose-600 text-white font-extrabold ring-2 ring-rose-300 animate-pulse',
            'tinggi' => 'bg-amber-600 text-white font-bold ring-1 ring-amber-300',
            'sedang' => 'bg-amber-100 text-amber-800 border border-amber-300 font-semibold',
            default => 'bg-emerald-100 text-emerald-800 border border-emerald-300 font-semibold',
        };
    }

    /**
     * Label representasi teks resmi ICAO
     */
    public function getLabel(string $level): string
    {
        return match (strtolower($level)) {
            'kritis' => 'Kritis (Critical Hazard)',
            'tinggi' => 'Tinggi (High Risk)',
            'sedang' => 'Sedang (Medium Risk)',
            default => 'Rendah (Low Risk)',
        };
    }
}
