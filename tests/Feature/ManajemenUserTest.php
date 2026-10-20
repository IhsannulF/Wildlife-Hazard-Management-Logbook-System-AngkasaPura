<?php

namespace Tests\Feature;

use App\Models\FormField;
use App\Models\User;
use Tests\TestCase;

class ManajemenUserTest extends TestCase
{
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/admin/manajemen-user');
        $response->assertRedirect('/login');
    }

    public function test_employee_cannot_access_manajemen_user(): void
    {
        $pegawai = User::where('role', 'pegawai')->first();
        if (! $pegawai) {
            $this->markTestSkipped('Pegawai user not found.');
        }

        $response = $this->actingAs($pegawai)->get('/admin/manajemen-user');
        $response->assertRedirect('/dashboard');
    }

    public function test_admin_can_access_tab_users(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get('/admin/manajemen-user?tab=users');
        $response->assertStatus(200);
        $response->assertSee('Manajemen User &amp; Konfigurasi Sistem', false);
        $response->assertSee('Total Akun Petugas');
        $response->assertSee('Daftar Akun Petugas &amp; Hak Akses', false);
        $response->assertSee('modalAddUser');
        $response->assertSee('modalEditUser');
    }

    public function test_admin_can_access_tab_fields(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get('/admin/manajemen-user?tab=fields');
        $response->assertStatus(200);
        $response->assertSee('Pengaturan Field Form Pelaporan Dinamis');
        $response->assertSee('modalAddField');
        $response->assertSee('Field Form Pelaporan');
    }

    public function test_admin_can_store_and_delete_user(): void
    {
        $admin = User::where('role', 'admin')->first();
        $uniqueUsername = 'petugas.'.time();

        $response = $this->actingAs($admin)->post('/admin/manajemen/users', [
            'namalengkap' => 'Petugas Lapangan Test',
            'username' => $uniqueUsername,
            'password' => 'secret123',
            'role' => 'pegawai',
            'jabatan' => 'AMC Officer',
        ]);

        $response->assertRedirect('/admin/manajemen-user?tab=users');
        $response->assertSessionHas('sukses');

        $user = User::where('username', $uniqueUsername)->first();
        $this->assertNotNull($user);
        $this->assertEquals('AMC Officer', $user->jabatan);

        // Delete user
        $responseDelete = $this->actingAs($admin)->post("/admin/manajemen/users/{$user->id}/delete");
        $responseDelete->assertRedirect('/admin/manajemen-user?tab=users');
        $this->assertNull(User::find($user->id));
    }

    public function test_admin_can_toggle_user_status(): void
    {
        $admin = User::where('role', 'admin')->first();
        $user = User::where('id', '!=', $admin->id)->first();

        if (! $user) {
            $this->markTestSkipped('Secondary user not found.');
        }

        $initialStatus = $user->aktif;
        $response = $this->actingAs($admin)->post("/admin/manajemen/users/{$user->id}/toggle");
        $response->assertRedirect('/admin/manajemen-user?tab=users');

        $this->assertEquals(! $initialStatus, $user->fresh()->aktif);

        // Revert status back
        $this->actingAs($admin)->post("/admin/manajemen/users/{$user->id}/toggle");
    }

    public function test_admin_can_toggle_form_field(): void
    {
        $admin = User::where('role', 'admin')->first();
        $field = FormField::first();

        if (! $field) {
            $this->markTestSkipped('FormField record not found.');
        }

        $initialStatus = $field->aktif;
        $response = $this->actingAs($admin)->post("/admin/manajemen/fields/{$field->id}/toggle");
        $response->assertRedirect('/admin/manajemen-user?tab=fields');

        $this->assertEquals(! $initialStatus, $field->fresh()->aktif);

        // Revert status back
        $this->actingAs($admin)->post("/admin/manajemen/fields/{$field->id}/toggle");
    }
}
