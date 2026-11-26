<?php

namespace Tests\Feature;

use App\Models\FormField;
use App\Models\Laporan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Admin mengatur isian form inspeksi; form pegawai dan validasi server mengikuti pengaturan itu.
 */
class FormInspeksiAdminTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_membuka_halaman_pengaturan_form_inspeksi(): void
    {
        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.form-inspeksi.index'));

        $response->assertOk();
        $response->assertSee('Pengaturan Form Inspeksi');
        $response->assertSee('Kondisi Ditemukan di Area Sisi Udara');
    }

    public function test_pegawai_tidak_bisa_membuka_pengaturan(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'pegawai']))
            ->get(route('admin.form-inspeksi.index'))
            ->assertRedirect(route('user.dashboard'));
    }

    public function test_pengaturan_disimpan_dan_dipakai_form_pegawai(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            // "+" (bukan spread) agar kunci = id field tetap terjaga.
            ->put(route('admin.form-inspeksi.update'), ['fields' => $this->fieldPayload('aktivitas_satwa', ['aktif' => 0])
                + $this->fieldPayload('ciri_ukuran', ['wajib' => 1, 'label' => 'Ciri Fisik Satwa'])
                + $this->fieldPayload('tanggal', ['aktif' => 0, 'wajib' => 0]),
            ])
            ->assertSessionHasNoErrors();

        $this->assertFalse($this->field('aktivitas_satwa')->aktif);
        $this->assertTrue($this->field('ciri_ukuran')->wajib);
        $this->assertTrue($this->field('tanggal')->aktif, 'Isian inti tetap aktif');
        $this->assertTrue($this->field('tanggal')->wajib, 'Isian inti tetap wajib');

        $form = $this->actingAs(User::factory()->create(['role' => 'pegawai']))->get(route('laporan.create'));

        $form->assertOk();
        $form->assertDontSee('name="aktivitas_satwa"', false);
        $form->assertSee('Ciri Fisik Satwa');
        $this->assertMatchesRegularExpression('/name="ciri_ukuran"[^>]*required/', $form->getContent());
    }

    public function test_isian_tambahan_tampil_divalidasi_dan_tersimpan(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.form-inspeksi.store'), [
                'label' => 'Kendaraan Patroli',
                'tipe' => 'dropdown',
                'wajib' => 1,
                'opsi' => "Patroli 1\nPatroli 2",
            ])
            ->assertSessionHasNoErrors();
        $field = FormField::where('label', 'Kendaraan Patroli')->sole();

        $pegawai = User::factory()->create(['role' => 'pegawai']);
        $this->actingAs($pegawai)->get(route('laporan.create'))
            ->assertSee('Isian Tambahan')
            ->assertSee('Patroli 2');

        $this->actingAs($pegawai)->post(route('laporan.store'), $this->laporanPayload([$field->nama_field => 'Patroli 9']))
            ->assertSessionHasErrors($field->nama_field);

        $this->actingAs($pegawai)->post(route('laporan.store'), $this->laporanPayload([$field->nama_field => 'Patroli 2']))
            ->assertSessionHasNoErrors();
        $this->assertSame('Patroli 2', Laporan::where('user_id', $pegawai->id)->latest('id')->sole()->extra_data[$field->nama_field]);
    }

    public function test_isian_bawaan_tidak_bisa_dihapus_tetapi_tambahan_bisa(): void
    {
        $admin = User::factory()->admin()->create();
        $custom = FormField::create(['nama_field' => 'catatan_uji', 'tipe' => 'text', 'label' => 'Catatan Uji', 'wajib' => false, 'aktif' => true, 'urutan' => 99]);

        $this->actingAs($admin)->delete(route('admin.form-inspeksi.destroy', $this->field('kondisi_apron')))->assertSessionHas('error');
        $this->actingAs($admin)->delete(route('admin.form-inspeksi.destroy', $custom))->assertSessionHas('success');

        $this->assertNotNull(FormField::where('nama_field', 'kondisi_apron')->first());
        $this->assertNull(FormField::find($custom->id));
    }

    private function field(string $name): FormField
    {
        return FormField::where('nama_field', $name)->sole();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<int, array<string, mixed>>
     */
    private function fieldPayload(string $name, array $overrides): array
    {
        $field = $this->field($name);

        return [$field->id => array_merge([
            'label' => $field->label,
            'placeholder' => $field->placeholder,
            'urutan' => $field->urutan,
            'wajib' => (int) $field->wajib,
            'aktif' => (int) $field->aktif,
        ], $overrides)];
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function laporanPayload(array $extra): array
    {
        return array_merge([
            'tanggal' => '2026-11-20',
            'jam' => '06:15',
            'nama_petugas' => 'Petugas AMC Lapangan',
            'unit_kerja' => 'Apron Movement Control (AMC)',
            'area_inspeksi' => 'Runway',
            'grid_lokasi' => 'K-10',
            'kondisi_cuaca' => 'Cerah',
            'tanda_tangan' => 'data:image/png;base64,sample_signature_data_for_test_1234567890',
            'satwa' => [['nama' => 'Burung Kuntul', 'lokasi' => [['grid' => 'K-10', 'jumlah' => 3]]]],
            'kondisi_apron' => 'Hidup',
            'tindak_lanjut' => 'Telah ditangani',
            'detail_pengusiran' => 'Sirene patroli',
        ], $extra);
    }
}
