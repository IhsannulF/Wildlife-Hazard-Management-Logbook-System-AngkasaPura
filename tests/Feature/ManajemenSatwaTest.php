<?php

namespace Tests\Feature;

use App\Models\JenisSatwa;
use App\Models\User;
use Tests\TestCase;

class ManajemenSatwaTest extends TestCase
{
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/admin/manajemen');
        $response->assertRedirect('/login');
    }

    public function test_employee_cannot_access_manajemen(): void
    {
        $pegawai = User::where('role', 'pegawai')->first();
        if (! $pegawai) {
            $this->markTestSkipped('Pegawai user not found.');
        }

        $response = $this->actingAs($pegawai)->get('/admin/manajemen');
        $response->assertRedirect('/dashboard');
    }

    public function test_admin_can_access_manajemen_satwa(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get('/admin/manajemen');
        $response->assertStatus(200);
        $response->assertSee('Katalog Master Satwa Liar Sisi Udara');
        $response->assertSee('Total Spesies Terdaftar');
        $response->assertSee('Spesies Risiko Kritis');
        $response->assertSee('Matriks Sebaran Spasial Runway');
        $response->assertSee('speciesDetailModal');
        $response->assertSee('addSpeciesModal');
        $response->assertSee('editSpeciesModal');
    }

    public function test_accessing_tab_users_or_fields_redirects_to_admin_users(): void
    {
        $admin = User::where('role', 'admin')->first();

        $responseUsers = $this->actingAs($admin)->get('/admin/manajemen?tab=users');
        $responseUsers->assertRedirect('/admin/manajemen-user?tab=users');

        $responseFields = $this->actingAs($admin)->get('/admin/manajemen?tab=fields');
        $responseFields->assertRedirect('/admin/manajemen-user?tab=fields');
    }

    public function test_admin_can_store_new_satwa(): void
    {
        $admin = User::where('role', 'admin')->first();
        $namaUnik = 'Satwa Uji '.time();

        $response = $this->actingAs($admin)->post('/admin/manajemen/satwa', [
            'nama' => $namaUnik,
            'kategori' => 'burung',
            'tingkat_risiko' => 'sedang',
            'grid_hotspot' => 'K-10',
            'deskripsi' => 'Deskripsi pengujian otomatis.',
            'sop_pengusiran' => 'Sirene AMC',
        ]);

        $response->assertRedirect('/admin/manajemen?tab=satwa');
        $response->assertSessionHas('sukses');

        $satwa = JenisSatwa::where('nama', $namaUnik)->first();
        $this->assertNotNull($satwa);
        $this->assertEquals('burung', $satwa->kategori);
        $this->assertEquals('sedang', $satwa->tingkat_risiko);

        // Clean up test record
        $satwa->delete();
    }

    public function test_admin_can_update_satwa(): void
    {
        $admin = User::where('role', 'admin')->first();
        $satwa = JenisSatwa::create([
            'nama' => 'Satwa Update Test '.time(),
            'kategori' => 'reptil',
            'tingkat_risiko' => 'kritis',
            'grid_hotspot' => 'D-9',
            'deskripsi' => 'Sebelum diubah.',
        ]);

        $response = $this->actingAs($admin)->post("/admin/manajemen/satwa/{$satwa->id}", [
            'nama' => 'Satwa Updated '.time(),
            'kategori' => 'reptil',
            'tingkat_risiko' => 'rendah',
            'grid_hotspot' => 'D-9',
            'deskripsi' => 'Setelah diubah.',
        ]);

        $response->assertRedirect('/admin/manajemen?tab=satwa');
        $response->assertSessionHas('sukses');

        $updated = $satwa->fresh();
        $this->assertEquals('rendah', $updated->tingkat_risiko);
        $this->assertEquals('Setelah diubah.', $updated->deskripsi);

        // Clean up
        $updated->delete();
    }

    public function test_admin_can_delete_satwa(): void
    {
        $admin = User::where('role', 'admin')->first();
        $satwa = JenisSatwa::create([
            'nama' => 'Satwa Delete Test '.time(),
            'kategori' => 'mamalia',
            'tingkat_risiko' => 'sedang',
        ]);

        $response = $this->actingAs($admin)->post("/admin/manajemen/satwa/{$satwa->id}/delete");
        $response->assertRedirect('/admin/manajemen?tab=satwa');
        $response->assertSessionHas('sukses');

        $this->assertNull(JenisSatwa::find($satwa->id));
    }
}
