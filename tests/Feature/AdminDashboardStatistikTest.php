<?php

namespace Tests\Feature;

use App\Models\DetailSatwa;
use App\Models\Laporan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminDashboardStatistikTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dashboard_statistics_are_computed_from_reports_of_selected_year(): void
    {
        $admin = User::factory()->admin()->create();
        $this->createReportWithAnimals('2019-03-10', '07:15:00', [
            ['nama_satwa' => 'Burung Kuntul', 'jumlah' => 5, 'grid' => 'K-10'],
            ['nama_satwa' => 'Biawak Air', 'jumlah' => 3, 'grid' => 'K-10'],
        ]);
        $this->createReportWithAnimals('2019-03-12', null, [
            ['nama_satwa' => 'Burung Cangak Abu', 'jumlah' => 2, 'grid' => 'D-9'],
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard', ['tahun' => 2019]));

        $response->assertOk();
        $response->assertViewHas('statistikTahun', 2019);
        $statistik = $response->viewData('statistik');
        $this->assertSame(
            ['observasi' => 2, 'burung' => 7, 'jenis' => 2, 'kejadian' => 0, 'grid_risiko_tinggi' => 0],
            $statistik['kpi']
        );
        $this->assertSame(7, $statistik['tren']['burung'][2]);
        $this->assertSame(2, $statistik['tren']['observasi'][2]);
        $this->assertSame(
            [
                ['nama' => 'Burung Kuntul', 'jumlah' => 5, 'persen' => 71.4],
                ['nama' => 'Burung Cangak Abu', 'jumlah' => 2, 'persen' => 28.6],
            ],
            $statistik['top_jenis']
        );
        $this->assertSame(5, $statistik['jam']['burung'][7]);
        $this->assertSame(2, $statistik['jam']['tanpa_jam']);
        $response->assertSee('Statistik &amp; Infografis Populasi Burung', false);
        $response->assertSee('id="whmsStatistikData"', false);
    }

    public function test_grid_activity_only_counts_the_last_six_months_of_a_past_year(): void
    {
        $admin = User::factory()->admin()->create();
        $this->createReportWithAnimals('2019-03-01', '08:00:00', [
            ['nama_satwa' => 'Burung Kuntul', 'jumlah' => 400, 'grid' => 'K-10'],
        ]);
        $this->createReportWithAnimals('2019-11-20', '08:00:00', [
            ['nama_satwa' => 'Burung Kuntul', 'jumlah' => 60, 'grid' => 'K-10'],
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard', ['tahun' => 2019]));

        $grid = $response->viewData('statistik')['grid'];
        $this->assertSame('2019-07-01', $grid['mulai']);
        $this->assertSame('2019-12-31', $grid['akhir']);
        $this->assertSame(['rendah' => 0, 'sedang' => 1, 'tinggi' => 0, 'sangat_tinggi' => 0], $grid['distribusi']);
        $this->assertSame([['grid' => 'K-10', 'jumlah' => 60, 'kelas' => 'sedang']], $grid['teratas']);
    }

    public function test_year_without_reports_falls_back_to_latest_year_with_data(): void
    {
        $admin = User::factory()->admin()->create();
        $this->createReportWithAnimals('2019-05-05', '09:00:00', [
            ['nama_satwa' => 'Burung Kuntul', 'jumlah' => 1, 'grid' => 'K-10'],
        ]);
        $latestYear = (int) substr((string) Laporan::query()->max('tanggal'), 0, 4);

        $response = $this->actingAs($admin)->get(route('admin.dashboard', ['tahun' => 1990]));

        $response->assertOk();
        $response->assertViewHas('statistikTahun', $latestYear);
    }

    /**
     * @param  list<array{nama_satwa: string, jumlah: int, grid: string}>  $animals
     */
    private function createReportWithAnimals(string $date, ?string $time, array $animals): Laporan
    {
        $laporan = Laporan::create([
            'user_id' => User::factory()->create()->id,
            'nama_petugas' => 'Petugas Uji',
            'tanggal' => $date,
            'jam' => $time,
            'kondisi_cuaca' => 'Cerah',
            'unit_kerja' => 'AMC',
            'area_inspeksi' => 'Runway',
            'grid_lokasi' => $animals[0]['grid'],
        ]);

        foreach ($animals as $animal) {
            DetailSatwa::create(['laporan_id' => $laporan->id, ...$animal]);
        }

        return $laporan;
    }
}
