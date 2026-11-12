<?php

namespace Tests\Feature;

use App\Models\Laporan;
use App\Models\User;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Portal Satwa Liar');
        $response->assertSee('Masuk ke Sistem');
    }

    public function test_user_can_authenticate_with_juanda123(): void
    {
        // Test primary department accounts
        foreach (['admin', 'amc', 'pk', 'safety', 'security', 'admin.utama'] as $username) {
            $response = $this->post('/login', [
                'username' => $username,
                'password' => 'juanda123',
            ]);
            $expectedRedirect = ($username === 'admin' || $username === 'admin.utama') ? '/admin' : '/dashboard';
            $response->assertRedirect($expectedRedirect);
            $this->assertAuthenticated();
            $this->post('/logout');
        }
    }

    public function test_admin_can_login_and_access_admin_dashboard(): void
    {
        $admin = User::where('role', 'admin')->first();
        if (! $admin) {
            $this->markTestSkipped('No admin user found in database');
        }

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Logbook Sisi Udara');
    }

    public function test_employee_cannot_access_admin_dashboard(): void
    {
        $pegawai = User::where('role', 'pegawai')->first();
        if (! $pegawai) {
            $this->markTestSkipped('No pegawai user found in database');
        }

        $response = $this->actingAs($pegawai)->get('/admin');
        $response->assertRedirect('/dashboard');
    }

    public function test_employee_can_access_form_pengaduan(): void
    {
        $pegawai = User::where('role', 'pegawai')->first();
        if (! $pegawai) {
            $this->markTestSkipped('No pegawai user found in database');
        }

        $response = $this->actingAs($pegawai)->get('/laporan/create');
        $response->assertStatus(200);
        $response->assertSee('Form Pengaduan Satwa Liar');
        $response->assertSee('Bagian 2 — Pengamatan Satwa & Foto Burung', false);
        $response->assertSee('inputKameraBelakang');
        $response->assertSee('selectJenisBurung');
        $response->assertSee('inputJumlahBurung');
        $response->assertSee('selectGridBurung');
    }

    public function test_admin_can_access_statistik(): void
    {
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get('/admin/statistik');
        $response->assertStatus(200);
        $response->assertSee('Peta Sebaran Grid');
        $response->assertSee('Denah Sektoral Grid Sisi Udara — Bandara Juanda');
        // Ensure "Citra Satelit Murni" tab is removed
        $response->assertDontSee('Citra Satelit Murni');
        // Ensure emojis are replaced with Material Symbols
        $response->assertDontSee('🐾');
        $response->assertDontSee('✈️');
        $response->assertDontSee('🔤');
        $response->assertDontSee('🚨');
        $response->assertSee('pets');
        $response->assertSee('flight');
        $response->assertSee('grid_4x4');
        $response->assertSee('alt_route');
    }

    public function test_admin_can_filter_statistik_by_kategori_and_zona(): void
    {
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get('/admin/statistik?kategori=burung&zona=runway');
        $response->assertStatus(200);
        $response->assertSee('Peta Sebaran Grid');
        $response->assertSee('quickFilterCard');
        $response->assertSee('filterRealtime');
    }

    public function test_admin_can_access_manajemen(): void
    {
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get('/admin/manajemen');
        $response->assertStatus(200);
        $response->assertSee('Katalog Master Satwa Liar');

        $responseUser = $this->actingAs($admin)->get('/admin/manajemen-user');
        $responseUser->assertStatus(200);
        $responseUser->assertSee('Manajemen User');

        $responseFields = $this->actingAs($admin)->get('/admin/manajemen-user?tab=fields');
        $responseFields->assertStatus(200);
        $responseFields->assertSee('Pengaturan Field Form Pelaporan');
    }

    public function test_admin_can_download_excel(): void
    {
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get('/admin/export-excel');
        $response->assertStatus(200);

        $downloadResponse = $this->actingAs($admin)->get(route('admin.export.excel.download'));
        $downloadResponse->assertStatus(200);
        $downloadResponse->assertHeader('content-type', 'application/vnd.ms-excel; charset=utf-8');
    }

    public function test_admin_can_view_report_print(): void
    {
        $admin = User::where('role', 'admin')->first();
        $laporan = Laporan::first();
        if ($laporan) {
            $response = $this->actingAs($admin)->get("/admin/laporan/{$laporan->id}/cetak");
            $response->assertStatus(200);
            $response->assertSee('Laporan Pengamatan Satwa Liar');
        }
    }

    public function test_admin_can_access_statistik_with_reordered_cards_and_pagination(): void
    {
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get('/admin/statistik');
        $response->assertStatus(200);
        $response->assertSee('Filter Cepat Kategori Satwa &amp; Zona', false);
        $response->assertSee('Peringkat 5 Grid Hotspot Teratas');
        $response->assertSee('Daftar Log Koordinat Grid Aktif &amp; Pemantauan Intervensi', false);
        $response->assertSee('tablePaginationBar');
        $response->assertSee('btnAuditNext');
        $response->assertSee('btnAuditPrev');
        $response->assertSee('auditPageNumbers');
    }
}
