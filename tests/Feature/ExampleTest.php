<?php

namespace Tests\Feature;

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
        $response = $this->post('/login', [
            'username' => 'admin.utama',
            'password' => 'juanda123',
        ]);
        $response->assertRedirect('/admin');
        $this->assertAuthenticated();
    }

    public function test_admin_can_login_and_access_admin_dashboard(): void
    {
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $this->markTestSkipped('No admin user found in database');
        }

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Data Master Laporan Satwa Liar');
    }

    public function test_employee_cannot_access_admin_dashboard(): void
    {
        $pegawai = User::where('role', 'pegawai')->first();
        if (!$pegawai) {
            $this->markTestSkipped('No pegawai user found in database');
        }

        $response = $this->actingAs($pegawai)->get('/admin');
        $response->assertRedirect('/dashboard');
    }

    public function test_employee_can_access_form_pengaduan(): void
    {
        $pegawai = User::where('role', 'pegawai')->first();
        if (!$pegawai) {
            $this->markTestSkipped('No pegawai user found in database');
        }

        $response = $this->actingAs($pegawai)->get('/laporan/create');
        $response->assertStatus(200);
        $response->assertSee('Form Pengaduan Satwa Liar');
    }

    public function test_admin_can_access_statistik(): void
    {
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get('/admin/statistik');
        $response->assertStatus(200);
        $response->assertSee('Grafik Fluktuasi Satwa Bulanan');
    }

    public function test_admin_can_access_manajemen(): void
    {
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get('/admin/manajemen');
        $response->assertStatus(200);
        $response->assertSee('Pengaturan Field Form Pelaporan');
        $response->assertSee('Master Jenis Satwa');
    }

    public function test_admin_can_download_excel(): void
    {
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get('/admin/export-excel');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.ms-excel');
    }

    public function test_admin_can_view_report_print(): void
    {
        $admin = User::where('role', 'admin')->first();
        $laporan = \App\Models\Laporan::first();
        if ($laporan) {
            $response = $this->actingAs($admin)->get("/admin/laporan/{$laporan->id}/cetak");
            $response->assertStatus(200);
            $response->assertSee('Laporan Pengamatan Satwa Liar');
        }
    }
}
