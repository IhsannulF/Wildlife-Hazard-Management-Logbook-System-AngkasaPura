<?php

namespace Tests\Feature;

use App\Models\DetailSatwa;
use App\Models\GridCell;
use App\Models\Laporan;
use App\Models\Pengaturan;
use App\Models\TindakanPengusiran;
use App\Models\User;
use App\Services\IcaoRiskMatrixService;
use App\Services\Risk\GridRiskService;
use App\Support\AirportGridGeometry;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * PRD v2.1 F1: atribut grid, skor zonasi S_grid, 4 kelas warna, ambang dapat diubah admin.
 */
class ZonasiRisikoGridTest extends TestCase
{
    use DatabaseTransactions;

    private const TANGGAL = '2019-03-10';

    public function test_atribut_grid_mengikuti_posisi_runway_di_gridmap(): void
    {
        $runway = AirportGridGeometry::cell('G', 10);
        $approach = AirportGridGeometry::cell('G', 2);
        $far = AirportGridGeometry::cell('A', 15);

        $this->assertSame('runway', $runway['zona']);
        $this->assertLessThan(100, $runway['jarak_runway_m']);
        $this->assertFalse($runway['di_approach']);
        $this->assertTrue($approach['di_approach']);
        $this->assertSame('10', $approach['runway_terdekat']);
        $this->assertSame('28', AirportGridGeometry::cell('G', 28)['runway_terdekat']);
        $this->assertSame('perimeter', $far['zona']);
        $this->assertGreaterThan(300, $far['jarak_runway_m']);
        $this->assertSame(348, GridCell::count());
    }

    /**
     * @return array<string, array{0: string, 1: float}>
     */
    public static function speciesWeights(): array
    {
        return [
            'besar berkelompok' => ['Burung Kuntul Kerbau', 2.0],
            'sedang' => ['Burung Pecuk Padi', 1.5],
            'kecil' => ['Burung Layang-layang Api', 1.0],
        ];
    }

    #[DataProvider('speciesWeights')]
    public function test_bobot_spesies_mengikuti_ukuran_burung(string $species, float $weight): void
    {
        $this->assertSame($weight, GridRiskService::speciesWeight($species));
    }

    public function test_skor_zonasi_hanya_menghitung_burung_dan_memakai_bobot_jarak(): void
    {
        $this->createObservation('G-10', 'Burung Kuntul Kerbau', 6);
        $this->createObservation('G-10', 'Biawak Air', 3);
        $this->createObservation('A-15', 'Burung Layang-layang Api', 2);

        $scores = $this->scoresForTestDate();

        $this->assertSame(30.7, $scores['G-10']['skor']);
        $this->assertSame(1, $scores['G-10']['pengamatan']);
        $this->assertSame(GridRiskService::CLASS_VERY_HIGH, $scores['G-10']['kelas']);
        $this->assertSame(1.1, $scores['A-15']['skor']);
        $this->assertSame(GridRiskService::CLASS_LOW, $scores['A-15']['kelas']);
    }

    public function test_bobot_jarak_makin_besar_mendekati_runway(): void
    {
        $weights = array_map(
            fn (string $grid): float => GridRiskService::distanceWeight(GridCell::where('grid', $grid)->sole()),
            ['G-10', 'F-10', 'D-10', 'A-10']
        );

        $this->assertSame($weights, array_values(array_unique($weights)));
        $this->assertSame($weights, collect($weights)->sortDesc()->values()->all());
        $this->assertSame(3.0, GridRiskService::distanceWeight(GridCell::where('grid', 'G-3')->sole()));
    }

    public function test_heatmap_menyebar_ke_tetangga_dan_lebih_merah_dekat_runway(): void
    {
        $this->createObservation('G-10', 'Burung Kuntul Kerbau', 6);
        $this->createObservation('A-20', 'Burung Kuntul Kerbau', 6);

        $heatmap = app(GridRiskService::class)->heatmap($this->scoresForTestDate());

        $this->assertCount(348, $heatmap);
        $this->assertGreaterThan($heatmap['A-20']['indeks'], $heatmap['G-10']['indeks']);
        $this->assertGreaterThan(0, $heatmap['F-10']['indeks']);
        $this->assertLessThan($heatmap['G-10']['indeks'], $heatmap['F-10']['indeks']);
        $this->assertSame(0.0, $heatmap['L-1']['indeks']);
        $this->assertSame(GridRiskService::CLASS_LOW, $heatmap['L-1']['kelas']);
    }

    public function test_halaman_statistik_memakai_skor_zonasi_tanpa_data_hardcode(): void
    {
        $this->createObservation('G-10', 'Burung Kuntul Kerbau', 6);

        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.statistik', [
            'time_filter' => 'year',
            'tahun' => 2019,
            'selected_grid' => 'G-10',
        ]));

        $response->assertOk();
        $response->assertSee('Sangat Tinggi (S = 30,7)');
        $response->assertSee('Sangat Tinggi (S ≥ 30)');
        $response->assertDontSee('Kanal Drainase Perimeter Selatan Terminal 2');
        $response->assertDontSee('Rawa Buffer Barat &amp; Runway 10 Approach', false);
    }

    public function test_panel_detail_memakai_jam_puncak_dari_data_tanpa_teks_karangan(): void
    {
        $this->createObservation('G-10', 'Burung Kuntul Kerbau', 6);

        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.statistik', [
            'time_filter' => 'year',
            'tahun' => 2019,
            'selected_grid' => 'G-10',
        ]));

        $response->assertSee('Jam puncak: 06:00–07:00 WIB');
        $response->assertDontSee('Runway 25R');
        $response->assertDontSee('38 Kejadian');
    }

    public function test_peta_form_pegawai_memakai_data_asli_tanpa_baseline(): void
    {
        $kemarin = now(config('whms_risk.zona_waktu'))->subDay()->toDateString();
        $this->createObservation('L-1', 'Burung Kuntul Kerbau', 7, $kemarin);

        $response = $this->actingAs(User::factory()->create(['role' => 'pegawai']))->get(route('laporan.create'));

        $response->assertOk();
        $response->assertViewHas('gridAgg', fn (array $grids): bool => ($grids['L-1']['total_satwa'] ?? 0) >= 7);
        $response->assertDontSee('Kanal Drainase Perimeter Selatan Terminal 2');
    }

    public function test_hotspot_icao_mengikuti_kelas_zonasi_tinggi(): void
    {
        $kemarin = now(config('whms_risk.zona_waktu'))->subDay()->toDateString();
        $this->createObservation('G-3', 'Burung Kuntul Kerbau', 40, $kemarin);
        $icao = app(IcaoRiskMatrixService::class);

        $this->assertContains('G-3', $icao->hotspotGrids());
        $this->assertSame(2, $icao->calculateProbability(1, 'G-3'));
        $this->assertSame(1, $icao->calculateProbability(1, 'L-29'));
    }

    public function test_heatmap_disembunyikan_untuk_kategori_non_burung(): void
    {
        $this->createObservation('G-10', 'Burung Kuntul Kerbau', 6);

        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.statistik', [
            'time_filter' => 'year', 'tahun' => 2019, 'kategori' => 'reptil',
        ]));

        $response->assertViewHas('heatmapZonasi', []);
        $response->assertSee('Heatmap disembunyikan');
    }

    public function test_heatmap_hanya_sel_zona_terpilih_dan_filter_burung_memakai_deteksi_lengkap(): void
    {
        $this->createObservation('G-10', 'Burung Kuntul Kerbau', 6);
        $this->createObservation('E-10', 'Cabak Kota', 4);

        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.statistik', [
            'time_filter' => 'year', 'tahun' => 2019, 'kategori' => 'burung', 'zona' => 'runway',
        ]));

        $response->assertViewHas('heatmapZonasi', function (array $heatmap): bool {
            $zones = GridCell::whereIn('grid', array_keys($heatmap))->distinct()->pluck('zona')->all();

            return $zones === ['runway'] && $heatmap['G-10']['indeks'] > 0;
        });

        $this->actingAs(User::factory()->admin()->create())->get(route('admin.statistik', [
            'time_filter' => 'year', 'tahun' => 2019, 'kategori' => 'burung',
        ]))->assertViewHas('gridAgg', fn (array $grids): bool => isset($grids['E-10']));
    }

    public function test_filter_mamalia_tidak_menangkap_burung_bernama_mirip(): void
    {
        $this->createObservation('G-10', 'Burung Elang Tikus', 2);
        $this->createObservation('E-10', 'Kerak Kerbau', 5);
        $this->createObservation('K-5', 'Kera Ekor Panjang', 3);

        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.statistik', [
            'time_filter' => 'year', 'tahun' => 2019, 'kategori' => 'mamalia',
        ]));

        $response->assertViewHas('gridAgg', fn (array $grids): bool => array_keys($grids) === ['K-5']);
    }

    public function test_kartu_dashboard_menghitung_respon_dan_ba_tervalidasi_dari_data(): void
    {
        TindakanPengusiran::query()->delete();
        $laporan = Laporan::create([
            'user_id' => User::factory()->create()->id, 'tanggal' => self::TANGGAL, 'jam' => '06:30',
            'nama_petugas' => 'Petugas Uji', 'unit_kerja' => 'AMC', 'area_inspeksi' => 'Runway',
            'kondisi_cuaca' => 'Cerah', 'grid_lokasi' => 'G-10', 'status' => 'sudah',
        ]);
        TindakanPengusiran::factory()->create(['laporan_id' => $laporan->id, 'waktu' => self::TANGGAL.' 06:45:00', 'grid' => 'G-10']);
        TindakanPengusiran::factory()->create(['laporan_id' => $laporan->id, 'waktu' => self::TANGGAL.' 07:30:00', 'grid' => 'G-10']);

        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.dashboard'));

        $response->assertViewHas('rerataResponMenit', 15.0);
        $response->assertSee('15,0 Menit');
        $response->assertDontSee('4.2 Menit');
        $response->assertDontSee('100% Dispersal BA Valid');
    }

    public function test_admin_mengubah_ambang_dan_kelas_ikut_berubah(): void
    {
        $this->createObservation('G-10', 'Burung Kuntul Kerbau', 6);

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.statistik.ambang-zonasi'), ['sedang' => 10, 'tinggi' => 40, 'sangat_tinggi' => 80])
            ->assertSessionHasNoErrors();

        $this->assertEquals(['sedang' => 10, 'tinggi' => 40, 'sangat_tinggi' => 80], Pengaturan::value(Pengaturan::AMBANG_ZONASI));
        $this->assertSame(GridRiskService::CLASS_MEDIUM, $this->scoresForTestDate()['G-10']['kelas']);
    }

    public function test_ambang_harus_naik_berurutan(): void
    {
        $response = $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.statistik.ambang-zonasi'), ['sedang' => 9, 'tinggi' => 9, 'sangat_tinggi' => 5]);

        $response->assertSessionHasErrors(['tinggi', 'sangat_tinggi']);
        $this->assertNull(Pengaturan::value(Pengaturan::AMBANG_ZONASI));
    }

    public function test_pegawai_tidak_bisa_mengubah_ambang(): void
    {
        $pegawai = User::factory()->create(['role' => 'pegawai']);

        $this->actingAs($pegawai)
            ->put(route('admin.statistik.ambang-zonasi'), ['sedang' => 1, 'tinggi' => 2, 'sangat_tinggi' => 3])
            ->assertRedirect(route('user.dashboard'));

        $this->assertNull(Pengaturan::value(Pengaturan::AMBANG_ZONASI));
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function scoresForTestDate(): array
    {
        $date = CarbonImmutable::parse(self::TANGGAL);

        return app(GridRiskService::class)->scores($date, $date);
    }

    private function createObservation(string $grid, string $species, int $count, string $tanggal = self::TANGGAL): void
    {
        $laporan = Laporan::create([
            'user_id' => User::factory()->create()->id,
            'tanggal' => $tanggal,
            'jam' => '06:30',
            'nama_petugas' => 'Petugas Uji',
            'unit_kerja' => 'Apron Movement Control (AMC)',
            'area_inspeksi' => 'Runway',
            'kondisi_cuaca' => 'Cerah',
            'grid_lokasi' => $grid,
            'status' => 'belum',
        ]);

        DetailSatwa::create([
            'laporan_id' => $laporan->id,
            'nama_satwa' => $species,
            'jumlah' => $count,
            'grid' => $grid,
        ]);
    }
}
