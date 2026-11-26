<?php

namespace App\Services\Risk;

use App\Http\Controllers\Admin\StatistikController;
use App\Models\DetailSatwa;
use App\Models\GridCell;
use App\Models\Pengaturan;
use App\Support\GridCode;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Skor zonasi risiko per grid (PRD v2.1 §7.1, spec.md §9.3):
 * S = N̄ × W_spesies × W_jarak × W_attractor, N̄ = rata-rata ekor burung per pengamatan di grid.
 * Hanya burung yang dihitung karena skor ini mengukur bahaya bird strike.
 */
class GridRiskService
{
    public const CLASS_LOW = 'rendah';

    public const CLASS_MEDIUM = 'sedang';

    public const CLASS_HIGH = 'tinggi';

    public const CLASS_VERY_HIGH = 'sangat_tinggi';

    /**
     * @var array<string, string>
     */
    public const CLASS_LABELS = [
        self::CLASS_LOW => 'Rendah',
        self::CLASS_MEDIUM => 'Sedang',
        self::CLASS_HIGH => 'Tinggi',
        self::CLASS_VERY_HIGH => 'Sangat Tinggi',
    ];

    /**
     * Skor tiap grid yang punya pengamatan burung dalam rentang waktu (tanggal laporan, inklusif).
     *
     * @return array<string, array{grid: string, pengamatan: int, total_ekor: int, rata_ekor: float, w_spesies: float, w_jarak: float, w_attractor: float, skor: float, kelas: string}>
     */
    public function scores(?CarbonInterface $from = null, ?CarbonInterface $to = null): array
    {
        $cells = GridCell::query()->get()->keyBy('grid');

        return $this->birdObservations($from, $to)
            ->groupBy('grid')
            ->filter(fn (Collection $rows, string $grid): bool => $cells->has($grid))
            ->map(fn (Collection $rows, string $grid): array => $this->scoreGrid($grid, $rows, $cells->get($grid)))
            ->sortByDesc('skor')
            ->all();
    }

    /**
     * @return array{sedang: float, tinggi: float, sangat_tinggi: float}
     */
    public function thresholds(): array
    {
        $stored = Pengaturan::value(Pengaturan::AMBANG_ZONASI, []);
        $defaults = config('whms_risk.zonasi.ambang');

        return [
            'sedang' => (float) ($stored['sedang'] ?? $defaults['sedang']),
            'tinggi' => (float) ($stored['tinggi'] ?? $defaults['tinggi']),
            'sangat_tinggi' => (float) ($stored['sangat_tinggi'] ?? $defaults['sangat_tinggi']),
        ];
    }

    /**
     * @param  array{sedang: float, tinggi: float, sangat_tinggi: float}|null  $thresholds
     */
    public function classify(float $score, ?array $thresholds = null): string
    {
        $thresholds ??= $this->thresholds();

        return match (true) {
            $score >= $thresholds['sangat_tinggi'] => self::CLASS_VERY_HIGH,
            $score >= $thresholds['tinggi'] => self::CLASS_HIGH,
            $score >= $thresholds['sedang'] => self::CLASS_MEDIUM,
            default => self::CLASS_LOW,
        };
    }

    public static function speciesWeight(?string $speciesName): float
    {
        $name = strtolower((string) $speciesName);

        foreach (config('whms_risk.zonasi.w_spesies') as $size) {
            foreach ($size['kata_kunci'] as $keyword) {
                if (str_contains($name, $keyword)) {
                    return (float) $size['bobot'];
                }
            }
        }

        return (float) config('whms_risk.zonasi.w_spesies.kecil.bobot');
    }

    public static function distanceWeight(GridCell $cell): float
    {
        if ($cell->di_approach) {
            return (float) config('whms_risk.zonasi.w_jarak_approach');
        }

        $weight = config('whms_risk.zonasi.w_jarak');

        return round($weight['min'] + ($weight['maks'] - $weight['min']) * exp(-$cell->jarak_runway_m / $weight['skala_m']), 3);
    }

    /**
     * Nilai heatmap untuk semua sel (tampilan peta): aktivitas burung (N̄ × W_spesies) tiap grid
     * disebar ke sel tetangga dengan kernel Gauss (diambil pengaruh terbesar), lalu dikali W_jarak dan W_attractor sel tujuan.
     *
     * @param  array<string, array{rata_ekor: float, w_spesies: float}>  $scores  hasil scores()
     * @return array<string, array{indeks: float, kelas: string}>
     */
    public function heatmap(array $scores): array
    {
        $kernel = config('whms_risk.zonasi.heatmap');
        $thresholds = $this->thresholds();
        $attractorWeight = (float) config('whms_risk.zonasi.w_attractor');

        $sources = [];
        foreach ($scores as $grid => $score) {
            $cell = GridCode::toCell($grid);
            if ($cell !== null) {
                $sources[] = [...$cell, 'aktivitas' => $score['rata_ekor'] * $score['w_spesies']];
            }
        }

        return GridCell::query()->get()->mapWithKeys(function (GridCell $cell) use ($sources, $kernel, $thresholds, $attractorWeight): array {
            $target = GridCode::toCell($cell->grid);
            $activity = 0.0;

            foreach ($sources as $source) {
                $rowDistance = $source['row'] - $target['row'];
                $columnDistance = $source['col'] - $target['col'];
                if (abs($rowDistance) > $kernel['radius_sel'] || abs($columnDistance) > $kernel['radius_sel']) {
                    continue;
                }

                // Ambil pengaruh terbesar (bukan jumlah) agar kumpulan grid berdekatan tidak membengkakkan nilai.
                $activity = max($activity, $source['aktivitas'] * exp(-($rowDistance ** 2 + $columnDistance ** 2) / (2 * $kernel['sigma_sel'] ** 2)));
            }

            $index = $activity * self::distanceWeight($cell) * $attractorWeight;

            return [$cell->grid => ['indeks' => round($index, 1), 'kelas' => $this->classify($index, $thresholds)]];
        })->all();
    }

    /**
     * @param  Collection<int, array{grid: string, nama: string, jumlah: int}>  $rows
     * @return array{grid: string, pengamatan: int, total_ekor: int, rata_ekor: float, w_spesies: float, w_jarak: float, w_attractor: float, skor: float, kelas: string}
     */
    private function scoreGrid(string $grid, Collection $rows, GridCell $cell): array
    {
        $totalBirds = (int) $rows->sum('jumlah');
        $observations = $rows->count();
        $speciesWeight = $totalBirds > 0
            ? $rows->sum(fn (array $row): float => $row['jumlah'] * self::speciesWeight($row['nama'])) / $totalBirds
            : 1.0;
        $distanceWeight = self::distanceWeight($cell);
        $attractorWeight = (float) config('whms_risk.zonasi.w_attractor');
        $averageBirds = $totalBirds / $observations;
        $score = $averageBirds * $speciesWeight * $distanceWeight * $attractorWeight;

        return [
            'grid' => $grid,
            'pengamatan' => $observations,
            'total_ekor' => $totalBirds,
            'rata_ekor' => round($averageBirds, 2),
            'w_spesies' => round($speciesWeight, 2),
            'w_jarak' => $distanceWeight,
            'w_attractor' => $attractorWeight,
            'skor' => round($score, 1),
            'kelas' => $this->classify($score),
        ];
    }

    /**
     * @return Collection<int, array{grid: string, nama: string, jumlah: int}>
     */
    private function birdObservations(?CarbonInterface $from, ?CarbonInterface $to): Collection
    {
        return DetailSatwa::query()
            ->join('laporan', 'laporan.id', '=', 'detail_satwa.laporan_id')
            ->when($from, fn ($query) => $query->whereDate('laporan.tanggal', '>=', $from->toDateString()))
            ->when($to, fn ($query) => $query->whereDate('laporan.tanggal', '<=', $to->toDateString()))
            ->where('detail_satwa.jumlah', '>', 0)
            ->get(['detail_satwa.nama_satwa', 'detail_satwa.jumlah', 'detail_satwa.grid', 'laporan.grid_lokasi'])
            ->filter(fn (DetailSatwa $row): bool => StatistikController::isBirdSpecies($row->nama_satwa))
            ->map(fn (DetailSatwa $row): array => [
                'grid' => (string) GridCode::normalize($row->grid ?: $row->getAttribute('grid_lokasi')),
                'nama' => (string) $row->nama_satwa,
                'jumlah' => (int) $row->jumlah,
            ])
            ->values();
    }
}
