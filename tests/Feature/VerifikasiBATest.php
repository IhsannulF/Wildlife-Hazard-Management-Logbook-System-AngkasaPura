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
}
