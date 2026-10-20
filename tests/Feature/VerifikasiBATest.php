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
}
