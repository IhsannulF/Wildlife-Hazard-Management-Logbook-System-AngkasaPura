<?php

namespace Tests\Feature;

use App\Models\Laporan;
use App\Models\User;
use Tests\TestCase;

class TanggapanDanPrioritasRisikoTest extends TestCase
{
    protected function getAdminUser(): User
    {
        return User::where('role', 'admin')->first()
            ?? User::factory()->create(['role' => 'admin', 'username' => 'admin_safety']);
    }

    protected function getPegawaiUser(string $username = 'amc'): User
    {
        return User::where('username', $username)->first()
            ?? User::where('role', 'pegawai')->first()
            ?? User::factory()->create(['role' => 'pegawai', 'username' => $username]);
    }

    protected function getOtherPegawaiUser(): User
    {
        $primary = $this->getPegawaiUser();

        return User::where('role', 'pegawai')->where('id', '!=', $primary->id)->first()
            ?? User::factory()->create(['role' => 'pegawai', 'username' => 'pegawai_lain']);
    }

    protected function createTestLaporan(array $attributes = []): Laporan
    {
        return Laporan::create(array_merge([
            'user_id' => $this->getPegawaiUser()->id,
            'nama_petugas' => 'Petugas Lapangan',
            'tanggal' => now()->format('Y-m-d'),
            'jam' => '08:00:00',
            'kondisi_cuaca' => 'Cerah Berawan',
            'unit_kerja' => 'Apron Movement Control (AMC)',
            'area_inspeksi' => 'Runway 07L/25R',
            'grid_lokasi' => 'K-10',
            'status' => 'belum',
            'risk_score' => 4,
            'risk_level' => 'sedang',
        ], $attributes));
    }

    public function test_laporan_baru_otomatis_menghitung_risk_score_dan_level_icao(): void
    {
        $pegawai = $this->getPegawaiUser();

        $payload = [
            'tanggal' => now()->format('Y-m-d'),
            'jam' => '08:30:00',
            'nama_petugas' => 'Petugas AMC Lapangan',
            'unit_kerja' => 'Apron Movement Control (AMC)',
            'area_inspeksi' => 'Runway 07L/25R Bahu Landasan',
            'grid_lokasi' => 'K-10',
            'kondisi_cuaca' => 'Cerah Berawan',
            'tanda_tangan' => 'data:image/png;base64,sample_signature_data_for_test_1234567890',
            'satwa' => [
                [
                    'nama' => 'Burung Cangak Abu',
                    'lokasi' => [
                        [
                            'grid' => 'K-10',
                            'jumlah' => 8, // Kawanan besar di Runway Hotspot -> P=3, S=4 -> Skor 12 (Kritis)
                        ],
                    ],
                ],
            ],
            'ciri_ukuran' => 'Burung bentang sayap lebar terpantau di runway strip',
            'kondisi_apron' => 'Kering',
            'aktivitas_satwa' => 'Mencari makan dekat threshold',
            'tindak_lanjut' => 'Pengusiran aktif',
            'detail_pengusiran' => 'Diusir dengan sirene mobil patroli',
        ];

        $response = $this->actingAs($pegawai)->post(route('laporan.store'), $payload);
        $response->assertRedirect(route('user.dashboard'));

        $this->assertDatabaseHas('laporan', [
            'user_id' => $pegawai->id,
            'grid_lokasi' => 'K-10',
            'risk_score' => 12,
            'risk_level' => 'kritis',
        ]);
    }

    public function test_antrean_verifikasi_admin_mendahulukan_laporan_kritis_dan_tinggi(): void
    {
        $admin = $this->getAdminUser();
        $pegawai = $this->getPegawaiUser();

        // Buat 1 laporan rendah dan 1 laporan kritis (keduanya status belum)
        $lapRendah = $this->createTestLaporan([
            'user_id' => $pegawai->id,
            'nama_petugas' => 'Test Petugas',
            'tanggal' => now()->subDay()->format('Y-m-d'),
            'status' => 'belum',
            'risk_score' => 2,
            'risk_level' => 'rendah',
            'area_inspeksi' => 'Perimeter Selatan',
            'grid_lokasi' => 'A-1',
        ]);

        $lapKritis = $this->createTestLaporan([
            'user_id' => $pegawai->id,
            'nama_petugas' => 'Test Petugas',
            'tanggal' => now()->subDay()->format('Y-m-d'),
            'status' => 'belum',
            'risk_score' => 12,
            'risk_level' => 'kritis',
            'area_inspeksi' => 'Runway 07L',
            'grid_lokasi' => 'K-10',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.verifikasi'));
        $response->assertStatus(200);

        $viewLaporans = $response->viewData('laporans');
        $this->assertNotNull($viewLaporans);

        // Cari index kedua laporan di antrean
        $indexKritis = null;
        $indexRendah = null;
        foreach ($viewLaporans as $idx => $lap) {
            if ($lap->id === $lapKritis->id) {
                $indexKritis = $idx;
            }
            if ($lap->id === $lapRendah->id) {
                $indexRendah = $idx;
            }
        }

        if ($indexKritis !== null && $indexRendah !== null) {
            $this->assertLessThan($indexRendah, $indexKritis, 'Laporan kritis harus berada di antrean lebih atas dibanding laporan rendah');
        }

        // Cleanup
        $lapRendah->delete();
        $lapKritis->delete();
    }

    public function test_admin_dapat_mengirim_instruksi_dinas(): void
    {
        $admin = $this->getAdminUser();
        $pegawai = $this->getPegawaiUser();

        $laporan = $this->createTestLaporan([
            'user_id' => $pegawai->id,
            'nama_petugas' => 'Petugas Lapangan',
            'tanggal' => now()->format('Y-m-d'),
            'status' => 'belum',
            'risk_score' => 8,
            'risk_level' => 'tinggi',
            'area_inspeksi' => 'Runway 07L',
        ]);

        $response = $this->actingAs($admin)
            ->postJson(route('laporan.tanggapan.store', $laporan->id), [
                'kategori_tanggapan' => 'instruksi',
                'isi' => 'Lakukan pengusiran lanjutan dengan flare gun 2 butir dan periksa perimeter timur.',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'tanggapan' => [
                    'kategori_tanggapan' => 'instruksi',
                    'isi' => 'Lakukan pengusiran lanjutan dengan flare gun 2 butir dan periksa perimeter timur.',
                ],
            ]);

        $this->assertDatabaseHas('tanggapan', [
            'laporan_id' => $laporan->id,
            'user_id' => $admin->id,
            'kategori_tanggapan' => 'instruksi',
        ]);

        $laporan->delete();
    }

    public function test_kategori_penutupan_otomatis_mengubah_status_laporan_menjadi_sudah(): void
    {
        $admin = $this->getAdminUser();
        $pegawai = $this->getPegawaiUser();

        $laporan = $this->createTestLaporan([
            'user_id' => $pegawai->id,
            'nama_petugas' => 'Petugas Lapangan',
            'tanggal' => now()->format('Y-m-d'),
            'status' => 'belum',
            'risk_score' => 4,
            'risk_level' => 'sedang',
        ]);

        $response = $this->actingAs($admin)
            ->postJson(route('laporan.tanggapan.store', $laporan->id), [
                'kategori_tanggapan' => 'penutupan',
                'isi' => 'Tindakan pengusiran telah diverifikasi sesuai SOP. Laporan ditutup resmi.',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'laporan_status' => 'sudah',
            ]);

        $laporan->refresh();
        $this->assertEquals('sudah', $laporan->status);

        $laporan->delete();
    }

    public function test_pegawai_dapat_membalas_tanggapan_pada_laporannya_sendiri(): void
    {
        $pegawai = $this->getPegawaiUser();

        $laporan = $this->createTestLaporan([
            'user_id' => $pegawai->id,
            'nama_petugas' => 'Petugas Lapangan',
            'tanggal' => now()->format('Y-m-d'),
            'status' => 'belum',
            'risk_score' => 6,
            'risk_level' => 'sedang',
        ]);

        $response = $this->actingAs($pegawai)
            ->postJson(route('laporan.tanggapan.store', $laporan->id), [
                'isi' => 'Siap, pengusiran lanjutan telah dilaksanakan dan perimeter dipastikan steril.',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'tanggapan' => [
                    'kategori_tanggapan' => 'klarifikasi',
                    'isi' => 'Siap, pengusiran lanjutan telah dilaksanakan dan perimeter dipastikan steril.',
                ],
            ]);

        $this->assertDatabaseHas('tanggapan', [
            'laporan_id' => $laporan->id,
            'user_id' => $pegawai->id,
            'isi' => 'Siap, pengusiran lanjutan telah dilaksanakan dan perimeter dipastikan steril.',
        ]);

        $laporan->delete();
    }

    public function test_pegawai_tidak_dapat_membalas_laporan_milik_pegawai_lain(): void
    {
        $pegawai1 = $this->getPegawaiUser();
        $pegawai2 = $this->getOtherPegawaiUser();

        $laporan = $this->createTestLaporan([
            'user_id' => $pegawai1->id,
            'nama_petugas' => 'Petugas 1',
            'tanggal' => now()->format('Y-m-d'),
            'status' => 'belum',
        ]);

        // Pegawai 2 mencoba menanggapi laporan pegawai 1
        $response = $this->actingAs($pegawai2)
            ->postJson(route('laporan.tanggapan.store', $laporan->id), [
                'isi' => 'Mencoba menanggapi laporan orang lain.',
            ]);

        $response->assertStatus(403);

        $laporan->delete();
    }
}
