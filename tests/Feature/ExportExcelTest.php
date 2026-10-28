<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ExportExcelTest extends TestCase
{
    protected function getAdminUser(): User
    {
        return User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_access_export_configuration_page(): void
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin)->get(route('admin.export.excel'));

        $response->assertStatus(200);
        $response->assertSee('Pusat Konfigurasi & Ekspor Laporan Excel', false);
        $response->assertSee('Standar Baku Ditjen Hubud (DKPPU 16 Kolom)', false);
        $response->assertSee('Rekapitulasi Operasional Ringkas', false);
    }

    public function test_admin_can_query_live_count_endpoint(): void
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin)->get(route('admin.export.excel.count'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'total_laporan',
            'total_ekor',
            'total_satwa_rows',
        ]);
        $response->assertJson([
            'status' => 'success',
        ]);
    }

    public function test_admin_can_download_excel_with_custom_columns(): void
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin)->post(route('admin.export.excel.download'), [
            'preset_choice' => 'dkppu',
            'mode_periode' => 'all',
            'mode_baris' => 'per_satwa',
            'include_summary' => '1',
            'file_format' => 'xls',
            'columns' => [
                'no',
                'tanggal',
                'kondisi_cuaca',
                'unit_kerja',
                'grid_lokasi',
                'nama_satwa',
                'nama_latin',
                'jumlah_ekor',
                'aktivitas_satwa',
                'status_penanganan',
            ],
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=utf-8');
        $this->assertStringContainsString('REKAPITULASI LOGBOOK PENGAMATAN', $response->getContent());
    }

    public function test_admin_can_download_csv_format(): void
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin)->post(route('admin.export.excel.download'), [
            'mode_periode' => 'all',
            'file_format' => 'csv',
            'columns' => [
                'no',
                'tanggal',
                'nama_petugas',
                'unit_kerja',
                'nama_satwa',
            ],
        ]);

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('Content-Type'), 'text/csv'));
        $content = $response->streamedContent();
        $this->assertStringContainsString('Petugas Pelapor', $content);
    }
}
