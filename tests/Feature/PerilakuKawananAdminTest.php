<?php

namespace Tests\Feature;

use App\Models\DetailSatwa;
use App\Models\Laporan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * PRD v2.1 FR-0.2: perilaku, arah terbang, dan ketinggian kawanan terlihat oleh admin.
 */
class PerilakuKawananAdminTest extends TestCase
{
    use DatabaseTransactions;

    public function test_ringkasan_kosong_bila_petugas_tidak_mengisi(): void
    {
        $this->assertNull((new DetailSatwa)->flockBehaviorSummary());
    }

    public function test_modal_detail_admin_memuat_perilaku_kawanan(): void
    {
        $laporan = $this->createLaporanWithFlock();

        $response = $this->actingAs(User::factory()->admin()->create())
            ->getJson(route('admin.laporan.detail', $laporan->id));

        $response->assertOk();
        $response->assertJsonPath('satwa.0.perilaku_kawanan', 'Mencari makan · arah Timur Laut · ketinggian 50–200 ft');
    }

    public function test_berita_acara_menampilkan_perilaku_kawanan(): void
    {
        $laporan = $this->createLaporanWithFlock();

        $response = $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.laporan.cetak', $laporan->id));

        $response->assertOk();
        $response->assertSee('Mencari makan · arah Timur Laut · ketinggian 50–200 ft');
    }

    public function test_ekspor_memakai_perilaku_terstruktur_dan_jam_pengamatan(): void
    {
        $laporan = $this->createLaporanWithFlock();

        $response = $this->actingAs(User::factory()->admin()->create())->post(route('admin.export.excel.download'), [
            'mode_periode' => 'all',
            'mode_baris' => 'per_satwa',
            'include_summary' => '0',
            'file_format' => 'csv',
            'columns' => ['id_laporan', 'waktu', 'aktivitas_satwa'],
        ]);

        $response->assertOk();
        $csv = $response->streamedContent();
        $this->assertStringContainsString('#'.$laporan->id, $csv);
        $this->assertStringContainsString('06:15 WIB', $csv);
        $this->assertStringContainsString('Mencari makan · arah Timur Laut · ketinggian 50–200 ft', $csv);
    }

    private function createLaporanWithFlock(): Laporan
    {
        $laporan = Laporan::create([
            'user_id' => User::factory()->create()->id,
            'tanggal' => '2019-03-10',
            'jam' => '06:15',
            'nama_petugas' => 'Petugas Uji',
            'unit_kerja' => 'Apron Movement Control (AMC)',
            'area_inspeksi' => 'Runway',
            'kondisi_cuaca' => 'Cerah',
            'aktivitas_satwa' => 'Catatan bebas lama',
            'status' => 'belum',
        ]);

        DetailSatwa::create([
            'laporan_id' => $laporan->id,
            'nama_satwa' => 'Burung Kuntul',
            'jumlah' => 6,
            'grid' => 'K-10',
            'perilaku' => 'mencari_makan',
            'arah_terbang' => 'TL',
            'ketinggian' => '50-200ft',
        ]);

        return $laporan;
    }
}
