<?php

namespace Tests\Feature;

use App\Models\DetailSatwa;
use App\Models\Laporan;
use App\Models\User;
use Tests\TestCase;
use ZipArchive;

class ExportExcelTest extends TestCase
{
    /** @var array<int, int> */
    private array $createdLaporanIds = [];

    protected function tearDown(): void
    {
        if (! empty($this->createdLaporanIds)) {
            DetailSatwa::whereIn('laporan_id', $this->createdLaporanIds)->delete();
            Laporan::whereIn('id', $this->createdLaporanIds)->delete();
        }

        parent::tearDown();
    }

    protected function getAdminUser(): User
    {
        return User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_access_export_configuration_page(): void
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin)->get(route('admin.export.excel'));

        $response->assertStatus(200);
        $response->assertSee('Pusat Konfigurasi');
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
            'file_format' => 'xlsx',
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
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('.xlsx"', (string) $response->headers->get('Content-Disposition'));

        $parts = $this->extractXlsxParts((string) $response->getContent());
        $this->assertStringContainsString('REKAPITULASI LOGBOOK PENGAMATAN', $parts['sheet1']);
        $this->assertNotFalse(simplexml_load_string($parts['sheet1']));
        $this->assertNotFalse(simplexml_load_string($parts['styles']));
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

    public function test_excel_export_outputs_dynamic_colgroup_widths_for_headers_and_content(): void
    {
        $admin = $this->getAdminUser();

        $laporan = Laporan::create([
            'user_id' => $admin->id,
            'nama_petugas' => 'Petugas Uji Lebar Kolom',
            'tanggal' => '2026-11-12',
            'jam' => '10:30:00',
            'kondisi_cuaca' => 'Cerah',
            'unit_kerja' => 'Apron Movement Control (AMC)',
            'area_inspeksi' => 'Runway 10/28',
            'grid_lokasi' => 'A-01',
            'gps_lat' => -7.3798,
            'gps_lng' => 112.7871,
            'risk_score' => 6,
            'risk_level' => 'sedang',
            'tanda_tangan' => null,
            'ciri_ukuran' => 'Sedang',
            'kondisi_apron' => 'Kering',
            'aktivitas_satwa' => 'Bertengger dan melintas di sekitar area taxiway charlie dekat runway',
            'tindak_lanjut' => 'Pengusiran aktif',
            'detail_pengusiran' => 'Sirene kendaraan AMC dan 2 butir petasan akustik untuk membubarkan kawanan burung',
            'status' => 'sudah',
        ]);
        $this->createdLaporanIds[] = $laporan->id;

        DetailSatwa::create([
            'laporan_id' => $laporan->id,
            'nama_satwa' => 'Burung Kuntul Perak Sayap Panjang',
            'jumlah' => 3,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.export.excel.download'), [
            'mode_periode' => 'range',
            'start_date' => '2026-11-12',
            'end_date' => '2026-11-12',
            'nama_satwa' => 'Burung Kuntul Perak Sayap Panjang',
            'mode_baris' => 'per_satwa',
            'include_summary' => '1',
            'file_format' => 'xlsx',
            'columns' => [
                'no',
                'nama_satwa',
                'jumlah_ekor',
                'aktivitas_satwa',
                'metode_pengusiran',
            ],
        ]);

        $response->assertStatus(200);
        $parts = $this->extractXlsxParts((string) $response->getContent());
        $sheetXml = $parts['sheet1'];

        $this->assertStringContainsString('<cols>', $sheetXml);
        $this->assertStringContainsString('customWidth="1"', $sheetXml);
        $this->assertStringContainsString('customHeight="1"', $sheetXml);
        $this->assertStringContainsString('Burung Kuntul Perak Sayap Panjang', $sheetXml);

        preg_match_all('/<col min="\d+" max="\d+" width="([\d.]+)" customWidth="1"\/>/', $sheetXml, $matches);
        $this->assertCount(5, $matches[1]);

        $widths = array_map('floatval', $matches[1]);
        // 'no' harus tetap ringkas (< 15 char width) karena tidak tertimpa metadata merge
        $this->assertLessThan(15.0, $widths[0]);
        // 'nama_satwa' harus melebar mengikuti isi 'Burung Kuntul Perak Sayap Panjang'
        $this->assertGreaterThan(36.0, $widths[1]);
        // 'aktivitas_satwa' & 'metode_pengusiran' (> 47 karakter) dibatasi ke max cap 56.0 char dengan wrapText
        $this->assertSame(56.0, $widths[3]);
        $this->assertSame(56.0, $widths[4]);
        $this->assertStringContainsString('wrapText="1"', $parts['styles']);
    }

    /**
     * @return array{sheet1: string, styles: string}
     */
    private function extractXlsxParts(string $binary): array
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'test_xlsx_');
        $this->assertNotFalse($tempPath);
        file_put_contents($tempPath, $binary);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($tempPath) === true);

        $sheet1 = $zip->getFromName('xl/worksheets/sheet1.xml');
        $styles = $zip->getFromName('xl/styles.xml');
        $contentTypes = $zip->getFromName('[Content_Types].xml');
        $workbook = $zip->getFromName('xl/workbook.xml');

        $zip->close();
        @unlink($tempPath);

        $this->assertIsString($sheet1);
        $this->assertIsString($styles);
        $this->assertIsString($contentTypes);
        $this->assertIsString($workbook);

        return [
            'sheet1' => $sheet1,
            'styles' => $styles,
        ];
    }
}
