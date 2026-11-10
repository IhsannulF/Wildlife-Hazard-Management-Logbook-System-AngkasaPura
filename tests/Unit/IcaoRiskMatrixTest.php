<?php

namespace Tests\Unit;

use App\Services\IcaoRiskMatrixService;
use PHPUnit\Framework\TestCase;

class IcaoRiskMatrixTest extends TestCase
{
    private IcaoRiskMatrixService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new IcaoRiskMatrixService;
    }

    public function test_kalkulasi_probabilitas_soliter_dan_kawanan(): void
    {
        // Soliter (<= 1 ekor) non-hotspot
        $p1 = $this->service->calculateProbability(1, 'Z-99');
        $this->assertEquals(1, $p1);

        // Kelompok kecil (2 - 5 ekor) non-hotspot
        $p2 = $this->service->calculateProbability(3, 'Z-99');
        $this->assertEquals(2, $p2);

        $p2Max = $this->service->calculateProbability(5, 'Z-99');
        $this->assertEquals(2, $p2Max);

        // Kawanan besar (> 5 ekor)
        $p3 = $this->service->calculateProbability(12, 'Z-99');
        $this->assertEquals(3, $p3);
    }

    public function test_faktor_frekuensi_hotspot_meningkatkan_probabilitas(): void
    {
        // Grid K-10 adalah hotspot utama
        $pHotspot1 = $this->service->calculateProbability(1, 'K-10');
        // Base 1 + 1 (hotspot) = 2
        $this->assertEquals(2, $pHotspot1);

        $pHotspot2 = $this->service->calculateProbability(4, 'D-9');
        // Base 2 + 1 (hotspot) = 3
        $this->assertEquals(3, $pHotspot2);
    }

    public function test_kalkulasi_skor_zona_pergerakan(): void
    {
        // Runway strip = 3
        $this->assertEquals(3, $this->service->getZoneScore('Runway 07L/25R Bahu'));
        $this->assertEquals(3, $this->service->getZoneScore('Touchdown Zone Runway 10'));

        // Taxiway / Apron = 2
        $this->assertEquals(2, $this->service->getZoneScore('Taxiway Echo & Stand Apron'));
        $this->assertEquals(2, $this->service->getZoneScore('Apron Terminal 1'));

        // Perimeter / Drainase = 1
        $this->assertEquals(1, $this->service->getZoneScore('Perimeter Selatan'));
        $this->assertEquals(1, $this->service->getZoneScore('Kanal Drainase Terbuka'));
    }

    public function test_kalkulasi_skor_bobot_spesies(): void
    {
        // Spesies Tinggi (3)
        $this->assertEquals(3, $this->service->getSpeciesScore(['Burung Cangak Abu']));
        $this->assertEquals(3, $this->service->getSpeciesScore(['Burung Kuntul Kerbau']));
        $this->assertEquals(3, $this->service->getSpeciesScore(['Anjing Liar']));
        $this->assertEquals(3, $this->service->getSpeciesScore(['Ular Sanca Kembang']));

        // Spesies Sedang (2)
        $this->assertEquals(2, $this->service->getSpeciesScore(['Biawak Air Asia']));
        $this->assertEquals(2, $this->service->getSpeciesScore(['Kucing Liar']));

        // Spesies Rendah (1)
        $this->assertEquals(1, $this->service->getSpeciesScore(['Burung Gereja']));
    }

    public function test_kalkulasi_tingkat_keparahan_severity_kombinasi_zona_dan_spesies(): void
    {
        // Runway (3) + Spesies Tinggi (3) -> S = 4 (Kritis)
        $s1 = $this->service->calculateSeverity(['Burung Cangak Abu'], 'Runway 07L');
        $this->assertEquals(4, $s1['severity']);
        $this->assertEquals(3, $s1['zone_score']);
        $this->assertEquals(3, $s1['species_score']);

        // Runway (3) + Spesies Sedang (2) -> S = 3
        $s2 = $this->service->calculateSeverity(['Biawak Air'], 'Runway Strip 10');
        $this->assertEquals(3, $s2['severity']);

        // Taxiway (2) + Spesies Tinggi (3) -> S = 3
        $s3 = $this->service->calculateSeverity(['Anjing Liar'], 'Taxiway Alpha');
        $this->assertEquals(3, $s3['severity']);

        // Taxiway (2) + Spesies Sedang (2) -> S = 2
        $s4 = $this->service->calculateSeverity(['Biawak'], 'Apron B');
        $this->assertEquals(2, $s4['severity']);

        // Perimeter (1) + Spesies Rendah (1) -> S = 1
        $s5 = $this->service->calculateSeverity(['Burung Gereja'], 'Perimeter Pagar');
        $this->assertEquals(1, $s5['severity']);
    }

    public function test_kalkulasi_matriks_risiko_skor_dan_kategori(): void
    {
        // Kasus 1: Kritis (> 9)
        // Kawanan Cangak Abu (8 ekor) di Runway 07L: P=3, S=4 -> Skor = 12 (Kritis)
        $resCritical = $this->service->calculate(
            [['nama' => 'Burung Cangak Abu', 'jumlah' => 8]],
            'Runway 07L/25R',
            'K-10'
        );
        $this->assertEquals(12, $resCritical['score']);
        $this->assertEquals('kritis', $resCritical['level']);

        // Kasus 2: Tinggi (7 - 9)
        // Soliter Cangak Abu (1 ekor) di Hotspot K-10 Runway: P=2, S=4 -> Skor = 8 (Tinggi)
        $resHigh = $this->service->calculate(
            [['nama' => 'Burung Cangak Abu', 'jumlah' => 1]],
            'Runway 07L',
            'K-10'
        );
        $this->assertEquals(8, $resHigh['score']);
        $this->assertEquals('tinggi', $resHigh['level']);

        // Kasus 3: Sedang (4 - 6)
        // Biawak Air (2 ekor) di Taxiway: P=2, S=2 -> Skor = 4 (Sedang)
        $resMedium = $this->service->calculate(
            [['nama' => 'Biawak Air', 'jumlah' => 2]],
            'Taxiway Alpha',
            'Z-1'
        );
        $this->assertEquals(4, $resMedium['score']);
        $this->assertEquals('sedang', $resMedium['level']);

        // Kasus 4: Rendah (1 - 3)
        // Burung Gereja (1 ekor) di Perimeter: P=1, S=1 -> Skor = 1 (Rendah)
        $resLow = $this->service->calculate(
            [['nama' => 'Burung Gereja', 'jumlah' => 1]],
            'Perimeter Pagar Luar',
            'Z-2'
        );
        $this->assertEquals(1, $resLow['score']);
        $this->assertEquals('rendah', $resLow['level']);
    }

    public function test_threshold_level_determinations(): void
    {
        $this->assertEquals('rendah', $this->service->determineLevel(1));
        $this->assertEquals('rendah', $this->service->determineLevel(3));
        $this->assertEquals('sedang', $this->service->determineLevel(4));
        $this->assertEquals('sedang', $this->service->determineLevel(6));
        $this->assertEquals('tinggi', $this->service->determineLevel(7));
        $this->assertEquals('tinggi', $this->service->determineLevel(9));
        $this->assertEquals('kritis', $this->service->determineLevel(10));
        $this->assertEquals('kritis', $this->service->determineLevel(12));
    }
}
