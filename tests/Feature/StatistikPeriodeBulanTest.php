<?php

namespace Tests\Feature;

use App\Models\DetailSatwa;
use App\Models\Laporan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Peta grid statistik dapat difilter per rentang bulan pilihan pengguna (1 bulan, 2 bulan, dst.).
 */
class StatistikPeriodeBulanTest extends TestCase
{
    use DatabaseTransactions;

    public function test_rentang_satu_bulan_hanya_menampilkan_temuan_bulan_itu(): void
    {
        $this->createObservation('2019-03-10', 'G-10', 6);
        $this->createObservation('2019-05-10', 'A-20', 4);

        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.statistik', [
            'time_filter' => 'custom',
            'dari' => '2019-03',
            'sampai' => '2019-03',
        ]));

        $response->assertOk();
        $response->assertViewHas('periodeLabel', 'Mar 2019 (1 bulan)');
        $response->assertViewHas('gridAgg', fn (array $grids): bool => isset($grids['G-10']) && ! isset($grids['A-20']));
        $response->assertViewHas('heatmapZonasi', fn (array $heatmap): bool => $heatmap['G-10']['indeks'] > 0 && $heatmap['A-20']['indeks'] === 0.0);
    }

    public function test_rentang_beberapa_bulan_dan_urutan_terbalik_ditukar(): void
    {
        $this->createObservation('2019-03-10', 'G-10', 6);
        $this->createObservation('2019-05-10', 'A-20', 4);

        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.statistik', [
            'time_filter' => 'custom',
            'dari' => '2019-05',
            'sampai' => '2019-03',
        ]));

        $response->assertViewHas('periodeLabel', 'Mar 2019 – Mei 2019 (3 bulan)');
        $response->assertViewHas('gridAgg', fn (array $grids): bool => isset($grids['G-10'], $grids['A-20']));
    }

    public function test_filter_kategori_tetap_membawa_periode_pilihan(): void
    {
        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.statistik', [
            'time_filter' => 'custom',
            'dari' => '2019-03',
            'sampai' => '2019-04',
        ]));

        $response->assertSee(route('admin.statistik', ['time_filter' => 'custom', 'dari' => '2019-03', 'sampai' => '2019-04', 'kategori' => 'burung', 'zona' => 'all']));
    }

    public function test_format_bulan_tidak_valid_ditolak(): void
    {
        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.statistik', [
            'time_filter' => 'custom',
            'dari' => '03-2019',
        ]));

        $response->assertSessionHasErrors('dari');
    }

    private function createObservation(string $date, string $grid, int $count): void
    {
        $laporan = Laporan::create([
            'user_id' => User::factory()->create()->id,
            'tanggal' => $date,
            'jam' => '06:30',
            'nama_petugas' => 'Petugas Uji',
            'unit_kerja' => 'Apron Movement Control (AMC)',
            'area_inspeksi' => 'Runway',
            'kondisi_cuaca' => 'Cerah',
            'grid_lokasi' => $grid,
            'status' => 'belum',
        ]);

        DetailSatwa::create(['laporan_id' => $laporan->id, 'nama_satwa' => 'Burung Kuntul Kerbau', 'jumlah' => $count, 'grid' => $grid]);
    }
}
