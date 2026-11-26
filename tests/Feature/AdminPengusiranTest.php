<?php

namespace Tests\Feature;

use App\Models\MasterAlatDispersal;
use App\Models\TindakanPengusiran;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminPengusiranTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_melihat_log_pengusiran_dan_master_alat(): void
    {
        $admin = User::factory()->admin()->create();
        $alat = $this->createAlat('Laser Uji Admin');
        TindakanPengusiran::factory()->create(['alat_id' => $alat->id, 'grid' => 'F-21', 'keterangan' => 'Log uji admin']);

        $response = $this->actingAs($admin)->get(route('admin.ai.pengusiran.index'));

        $response->assertOk();
        $response->assertSee('Log uji admin');
        $response->assertSee('F-21');
        $response->assertSee('Laser Uji Admin');
    }

    public function test_filter_grid_hanya_menampilkan_log_grid_tersebut(): void
    {
        $admin = User::factory()->admin()->create();
        TindakanPengusiran::factory()->create(['grid' => 'J-27', 'keterangan' => 'Log grid J27']);
        TindakanPengusiran::factory()->create(['grid' => 'J-28', 'keterangan' => 'Log grid J28']);

        $response = $this->actingAs($admin)->get(route('admin.ai.pengusiran.index', ['grid' => 'j-27']));

        $response->assertOk();
        $response->assertSee('Log grid J27');
        $response->assertDontSee('Log grid J28');
    }

    public function test_pegawai_tidak_bisa_membuka_halaman_admin_pengusiran(): void
    {
        $pegawai = User::factory()->create();

        $response = $this->actingAs($pegawai)->get(route('admin.ai.pengusiran.index'));

        $response->assertRedirect(route('user.dashboard'));
    }

    public function test_admin_menambah_alat_baru(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.ai.pengusiran.alat.store'), [
            'nama_alat' => 'Drone Pengusir Uji',
            'kategori' => 'Akustik',
            'satuan' => 'Menit',
            'stok_tersedia' => 4,
        ]);

        $response->assertRedirect(route('admin.ai.pengusiran.index'));
        $this->assertDatabaseHas('master_alat_dispersal', [
            'nama_alat' => 'Drone Pengusir Uji',
            'kategori' => 'Akustik',
            'satuan' => 'Menit',
            'stok_tersedia' => 4,
            'aktif' => true,
        ]);
    }

    public function test_nama_alat_ganda_dan_kategori_tidak_dikenal_ditolak(): void
    {
        $admin = User::factory()->admin()->create();
        $this->createAlat('Alat Kembar Uji');

        $response = $this->actingAs($admin)->post(route('admin.ai.pengusiran.alat.store'), [
            'nama_alat' => 'Alat Kembar Uji',
            'kategori' => 'Sihir',
            'satuan' => 'Kali',
        ]);

        $response->assertSessionHasErrors(['nama_alat', 'kategori']);
    }

    public function test_admin_mengubah_alat_tanpa_bentrok_dengan_namanya_sendiri(): void
    {
        $admin = User::factory()->admin()->create();
        $alat = $this->createAlat('Sirene Lama Uji');

        $response = $this->actingAs($admin)->put(route('admin.ai.pengusiran.alat.update', $alat->id), [
            'nama_alat' => 'Sirene Lama Uji',
            'kategori' => 'Akustik',
            'satuan' => 'Dentuman',
            'stok_tersedia' => 10,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame('Dentuman', $alat->fresh()->satuan);
        $this->assertSame(10, $alat->fresh()->stok_tersedia);
    }

    public function test_admin_menonaktifkan_alat(): void
    {
        $admin = User::factory()->admin()->create();
        $alat = $this->createAlat('Alat Toggle Uji');

        $this->actingAs($admin)->post(route('admin.ai.pengusiran.alat.toggle', $alat->id));

        $this->assertFalse($alat->fresh()->aktif);
    }

    public function test_alat_yang_sudah_dipakai_tidak_bisa_dihapus(): void
    {
        $admin = User::factory()->admin()->create();
        $alat = $this->createAlat('Alat Terpakai Uji');
        TindakanPengusiran::factory()->create(['alat_id' => $alat->id]);

        $response = $this->actingAs($admin)->delete(route('admin.ai.pengusiran.alat.destroy', $alat->id));

        $response->assertSessionHas('error');
        $this->assertModelExists($alat);
    }

    public function test_alat_yang_belum_dipakai_bisa_dihapus(): void
    {
        $admin = User::factory()->admin()->create();
        $alat = $this->createAlat('Alat Belum Dipakai Uji');

        $response = $this->actingAs($admin)->delete(route('admin.ai.pengusiran.alat.destroy', $alat->id));

        $response->assertSessionHas('success');
        $this->assertModelMissing($alat);
    }

    public function test_pengusiran_menerima_waktu_wib_saat_ini_walau_lebih_maju_dari_utc(): void
    {
        $this->travelTo('2026-11-25 01:00:00');
        $pegawai = User::factory()->create();

        $response = $this->actingAs($pegawai)->post(route('pengusiran.store'), [
            'alat_id' => $this->createAlat('Alat WIB Uji')->id,
            'waktu' => '2026-11-25T07:30',
            'grid' => 'K-10',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tindakan_pengusiran', ['user_id' => $pegawai->id, 'waktu' => '2026-11-25 07:30:00']);
    }

    private function createAlat(string $nama): MasterAlatDispersal
    {
        return MasterAlatDispersal::create(['nama_alat' => $nama, 'kategori' => 'Optik', 'satuan' => 'Kali']);
    }
}
