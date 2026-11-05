<?php

namespace Tests\Feature;

use App\Models\Laporan;
use App\Models\User;
use Tests\TestCase;

class VerifikasiBATest extends TestCase
{
    public function test_guest_cannot_access_verifikasi(): void
    {
        $response = $this->get('/admin/verifikasi');
        $response->assertRedirect('/login');

        $responseTab = $this->get('/admin?tab=verifikasi');
        $responseTab->assertRedirect('/login');
    }

    public function test_employee_cannot_access_verifikasi(): void
    {
        $pegawai = User::where('role', 'pegawai')->first();
        if (! $pegawai) {
            $this->markTestSkipped('Pegawai user not found.');
        }

        $response = $this->actingAs($pegawai)->get('/admin/verifikasi');
        $response->assertRedirect('/dashboard');
    }

    public function test_admin_can_access_verifikasi_via_dedicated_route(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get('/admin/verifikasi');
        $response->assertStatus(200);
        $response->assertSee('Verifikasi Berita Acara (BA)');
        $response->assertSee('Antrean Verifikasi Berita Acara (BA) Sisi Udara');
        $response->assertSee('Semua Status');
        $response->assertSee('Belum Ditangani');
        $response->assertSee('Sudah Ditangani');
        $response->assertSee('signaturePad');
        $response->assertSee('miniGridContainer');
    }

    public function test_admin_can_access_verifikasi_via_query_tab(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get('/admin?tab=verifikasi');
        $response->assertStatus(200);
        $response->assertSee('Verifikasi Berita Acara (BA)');
        $response->assertSee('Antrean Verifikasi Berita Acara (BA) Sisi Udara');
        $response->assertSee('Semua Status');
    }

    public function test_admin_can_filter_verifikasi_by_status(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get('/admin/verifikasi?status=belum');
        $response->assertStatus(200);
        $response->assertSee('Belum Ditangani');
    }

    public function test_admin_can_search_verifikasi(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get('/admin/verifikasi?search=K-10');
        $response->assertStatus(200);
        $response->assertSee('K-10');
        $response->assertSee('id="searchInput"', false);
    }

    public function test_admin_sees_filter_elements_in_single_row(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get('/admin/verifikasi');
        $response->assertStatus(200);
        $response->assertDontSee('Filter Grid Cepat:');
        $response->assertSee('Unit Kerja:');
        $response->assertSee('filterUnit');
        $response->assertSee('Area Sisi Udara:');
        $response->assertSee('filterArea');
        $response->assertSee('datePickerTrigger');
        $response->assertSee('Reset Filter');
    }

    public function test_admin_can_get_laporan_detail_json(): void
    {
        $admin = User::where('role', 'admin')->first();
        $laporan = Laporan::first();

        if (! $laporan) {
            $this->markTestSkipped('Laporan record not found.');
        }

        $response = $this->actingAs($admin)->getJson("/admin/laporan/{$laporan->id}/detail");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'satwa',
        ]);
    }

    public function test_admin_can_update_status_via_ajax(): void
    {
        $admin = User::where('role', 'admin')->first();
        $laporan = Laporan::first();

        if (! $laporan) {
            $this->markTestSkipped('Laporan record not found.');
        }

        $response = $this->actingAs($admin)->postJson("/admin/laporan/{$laporan->id}/update", [
            'status_validasi' => 'sudah',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('sudah', $laporan->fresh()->status);
    }

    public function test_admin_can_access_all_verifikasi_sub_routes(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        $subRoutes = [
            '/admin/verifikasi/autocrat' => 'Laporan Autocrat',
            '/admin/verifikasi/patroli' => 'Log Sesi Patroli Sisi Udara',
            '/admin/verifikasi/detail-temuan' => 'Detail Temuan Individu Satwa Liar',
            '/admin/verifikasi/backup-temuan' => 'Back Up Detail Temuan Satwa Liar',
            '/admin/verifikasi/master-titik' => 'Master Titik &amp; Zona Pengamatan Sisi Udara (Juanda)',
            '/admin/verifikasi/master-jenis' => 'Master Jenis Satwa Liar (Airside Wildlife Species)',
            '/admin/verifikasi/master-preview' => 'Master Preview Galeri Visual Dokumentasi Satwa',
        ];

        foreach ($subRoutes as $uri => $expectedTitle) {
            $response = $this->actingAs($admin)->get($uri);
            $response->assertStatus(200);
            $response->assertSee($expectedTitle, false);
            $response->assertSee('Verifikasi Berita Acara (BA)');
        }
    }

    public function test_admin_can_filter_verifikasi_by_date_range(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get('/admin/verifikasi?start_date=2026-06-01&end_date=2026-06-30');
        $response->assertStatus(200);
        $response->assertSee('datePickerTrigger');
        $response->assertSee('filterStartDate');
        $response->assertSee('filterEndDate');
        $response->assertSee('value="2026-06-01"', false);
        $response->assertSee('value="2026-06-30"', false);
        $response->assertSee('01 Jun 2026 - 30 Jun 2026');
    }

    public function test_admin_can_filter_verifikasi_by_single_date(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get('/admin/verifikasi?start_date=2026-06-24&end_date=2026-06-24');
        $response->assertStatus(200);
        $response->assertSee('datePickerTrigger');
        $response->assertSee('value="2026-06-24"', false);
        $response->assertSee('24 Jun 2026');
    }

    public function test_admin_can_set_custom_per_page_on_verifikasi(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        // Test per_page = 20
        $response20 = $this->actingAs($admin)->get('/admin/verifikasi?per_page=20');
        $response20->assertStatus(200);
        $response20->assertSee('id="perPageTop"', false);
        $response20->assertSee('id="perPageBottom"', false);
        $response20->assertSee('<option value="20" selected>20 data</option>', false);
        $this->assertEquals(20, $response20->viewData('perPage'));
        $this->assertEquals(20, $response20->viewData('laporans')->perPage());

        // Test per_page = 50
        $response50 = $this->actingAs($admin)->get('/admin/verifikasi?per_page=50');
        $response50->assertStatus(200);
        $response50->assertSee('<option value="50" selected>50 data</option>', false);
        $this->assertEquals(50, $response50->viewData('perPage'));
        $this->assertEquals(50, $response50->viewData('laporans')->perPage());
    }

    public function test_admin_per_page_falls_back_to_default_when_invalid(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        $responseInvalid = $this->actingAs($admin)->get('/admin/verifikasi?per_page=999');
        $responseInvalid->assertStatus(200);
        $responseInvalid->assertSee('<option value="10" selected>10 data</option>', false);
        $this->assertEquals(10, $responseInvalid->viewData('perPage'));
        $this->assertEquals(10, $responseInvalid->viewData('laporans')->perPage());
    }

    public function test_admin_per_page_persists_in_session(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        // Set per_page=50 and store in session
        $this->actingAs($admin)->get('/admin/verifikasi?per_page=50')
            ->assertSessionHas('verifikasi_per_page', 50);

        // Subsequent access without query param should remember session
        $subsequentResponse = $this->actingAs($admin)->get('/admin/verifikasi');
        $subsequentResponse->assertStatus(200);
        $this->assertEquals(50, $subsequentResponse->viewData('perPage'));
        $this->assertEquals(50, $subsequentResponse->viewData('laporans')->perPage());
    }

    public function test_admin_can_set_custom_per_page_on_all_sub_features(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        // Sub 1: Patroli
        $resPatroli = $this->actingAs($admin)->get('/admin/verifikasi/patroli?per_page=20');
        $resPatroli->assertStatus(200);
        $resPatroli->assertSee('id="perPagePatroliTop"', false);
        $resPatroli->assertSee('id="perPagePatroliBottom"', false);
        $this->assertEquals(20, $resPatroli->viewData('perPage'));

        // Sub 2: Detail Temuan
        $resDetail = $this->actingAs($admin)->get('/admin/verifikasi/detail-temuan?per_page=20');
        $resDetail->assertStatus(200);
        $resDetail->assertSee('id="perPageDetailTop"', false);
        $resDetail->assertSee('id="perPageDetailBottom"', false);
        $this->assertEquals(20, $resDetail->viewData('detailTemuanList')->perPage());

        // Sub 3: Back Up Detail Temuan
        $resBackup = $this->actingAs($admin)->get('/admin/verifikasi/backup-temuan?per_page=20');
        $resBackup->assertStatus(200);
        $resBackup->assertSee('id="perPageBackupTop"', false);
        $resBackup->assertSee('id="perPageBackupBottom"', false);
        $this->assertEquals(20, $resBackup->viewData('backupTemuanList')->perPage());

        // Sub 4: Master Titik
        $resTitik = $this->actingAs($admin)->get('/admin/verifikasi/master-titik?per_page=20');
        $resTitik->assertStatus(200);
        $resTitik->assertSee('id="perPageTitikTop"', false);
        $resTitik->assertSee('id="perPageTitikBottom"', false);
        $this->assertEquals(20, $resTitik->viewData('masterTitikList')->perPage());

        // Sub 5: Master Jenis
        $resJenis = $this->actingAs($admin)->get('/admin/verifikasi/master-jenis?per_page=20');
        $resJenis->assertStatus(200);
        $resJenis->assertSee('id="perPageJenisTop"', false);
        $resJenis->assertSee('id="perPageJenisBottom"', false);
        $this->assertEquals(20, $resJenis->viewData('masterJenisList')->perPage());

        // Sub 6: Master Preview
        $resPreview = $this->actingAs($admin)->get('/admin/verifikasi/master-preview?per_page=20');
        $resPreview->assertStatus(200);
        $resPreview->assertSee('id="perPagePreviewTop"', false);
        $resPreview->assertSee('id="perPagePreviewBottom"', false);
        $this->assertEquals(20, $resPreview->viewData('previewList')->perPage());
    }
}
