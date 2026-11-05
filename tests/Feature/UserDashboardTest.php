<?php

namespace Tests\Feature;

use App\Models\DetailSatwa;
use App\Models\Laporan;
use App\Models\Tanggapan;
use App\Models\User;
use Tests\TestCase;

class UserDashboardTest extends TestCase
{
    protected function getPegawaiUser(string $username = 'amc'): User
    {
        return User::where('username', $username)->first()
            ?? User::where('role', 'pegawai')->first()
            ?? User::factory()->create(['role' => 'pegawai', 'username' => $username]);
    }

    protected function getSecondPegawaiUser(): User
    {
        return User::where('username', 'pk')->first()
            ?? User::where('role', 'pegawai')->where('id', '!=', $this->getPegawaiUser()->id)->first()
            ?? User::factory()->create(['role' => 'pegawai', 'username' => 'petugas_kedua']);
    }

    public function test_guest_is_redirected_from_user_dashboard(): void
    {
        $response = $this->get(route('user.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_pegawai_can_access_dashboard_with_all_view_data(): void
    {
        $pegawai = $this->getPegawaiUser();

        $response = $this->actingAs($pegawai)->get(route('user.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('user.dashboard');
        $response->assertViewHasAll([
            'user',
            'totalLaporanSaya',
            'menungguValidasi',
            'sudahDitangani',
            'laporans',
        ]);
        $response->assertSee('Dashboard Logbook Pegawai', false);
        $response->assertSee('Total Laporan Saya', false);
        $response->assertSee('Menunggu Verifikasi', false);
        $response->assertSee('Selesai / Terverifikasi', false);
    }

    public function test_user_dashboard_only_shows_reports_belonging_to_logged_in_user(): void
    {
        $userA = $this->getPegawaiUser('amc');
        $userB = $this->getSecondPegawaiUser();

        // Buat laporan untuk User A
        $laporanA = Laporan::create([
            'user_id' => $userA->id,
            'nama_petugas' => 'Petugas AMC A',
            'tanggal' => now()->toDateString(),
            'kondisi_cuaca' => 'Cerah',
            'unit_kerja' => 'Apron Movement Control',
            'area_inspeksi' => 'Runway 10/28',
            'grid_lokasi' => 'K-10',
            'status' => 'belum',
        ]);

        // Buat laporan untuk User B
        $laporanB = Laporan::create([
            'user_id' => $userB->id,
            'nama_petugas' => 'Petugas PK B',
            'tanggal' => now()->toDateString(),
            'kondisi_cuaca' => 'Hujan',
            'unit_kerja' => 'ARFF',
            'area_inspeksi' => 'Perimeter Barat',
            'grid_lokasi' => 'D-02',
            'status' => 'belum',
        ]);

        // Akses sebagai User A
        $responseA = $this->actingAs($userA)->get(route('user.dashboard'));
        $responseA->assertStatus(200);
        $responseA->assertSee($laporanA->no_laporan, false);
        $responseA->assertDontSee($laporanB->no_laporan, false);

        // Akses sebagai User B
        $responseB = $this->actingAs($userB)->get(route('user.dashboard'));
        $responseB->assertStatus(200);
        $responseB->assertSee($laporanB->no_laporan, false);
        $responseB->assertDontSee($laporanA->no_laporan, false);
    }

    public function test_user_dashboard_calculates_personal_metrics_correctly(): void
    {
        $user = $this->getPegawaiUser('amc');

        $initialTotal = Laporan::where('user_id', $user->id)->count();
        $initialBelum = Laporan::where('user_id', $user->id)->where(fn ($q) => $q->where('status', 'belum')->orWhereNull('status'))->count();
        $initialSudah = Laporan::where('user_id', $user->id)->where('status', 'sudah')->count();

        // Tambah 1 laporan 'belum' dan 1 laporan 'sudah'
        Laporan::create([
            'user_id' => $user->id,
            'nama_petugas' => 'Petugas AMC',
            'tanggal' => now()->toDateString(),
            'kondisi_cuaca' => 'Cerah',
            'unit_kerja' => 'AMC',
            'area_inspeksi' => 'Apron',
            'status' => 'belum',
        ]);

        Laporan::create([
            'user_id' => $user->id,
            'nama_petugas' => 'Petugas AMC',
            'tanggal' => now()->toDateString(),
            'kondisi_cuaca' => 'Berawan',
            'unit_kerja' => 'AMC',
            'area_inspeksi' => 'Taxiway',
            'status' => 'sudah',
        ]);

        $response = $this->actingAs($user)->get(route('user.dashboard'));
        $response->assertStatus(200);

        $response->assertViewHas('totalLaporanSaya', $initialTotal + 2);
        $response->assertViewHas('menungguValidasi', $initialBelum + 1);
        $response->assertViewHas('sudahDitangani', $initialSudah + 1);
    }

    public function test_user_dashboard_can_filter_by_status(): void
    {
        $user = $this->getPegawaiUser('amc');

        $lapBelum = Laporan::create([
            'user_id' => $user->id,
            'nama_petugas' => 'Petugas AMC',
            'tanggal' => now()->toDateString(),
            'kondisi_cuaca' => 'Cerah',
            'unit_kerja' => 'AMC',
            'area_inspeksi' => 'Runway',
            'status' => 'belum',
        ]);

        $lapSudah = Laporan::create([
            'user_id' => $user->id,
            'nama_petugas' => 'Petugas AMC',
            'tanggal' => now()->toDateString(),
            'kondisi_cuaca' => 'Cerah',
            'unit_kerja' => 'AMC',
            'area_inspeksi' => 'Apron',
            'status' => 'sudah',
        ]);

        // Filter status=belum
        $responseBelum = $this->actingAs($user)->get(route('user.dashboard', ['status' => 'belum']));
        $responseBelum->assertStatus(200);
        $responseBelum->assertSee($lapBelum->no_laporan, false);
        $responseBelum->assertDontSee($lapSudah->no_laporan, false);

        // Filter status=sudah
        $responseSudah = $this->actingAs($user)->get(route('user.dashboard', ['status' => 'sudah']));
        $responseSudah->assertStatus(200);
        $responseSudah->assertSee($lapSudah->no_laporan, false);
        $responseSudah->assertDontSee($lapBelum->no_laporan, false);
    }

    public function test_user_can_view_own_report_detail_json(): void
    {
        $user = $this->getPegawaiUser('amc');

        $laporan = Laporan::create([
            'user_id' => $user->id,
            'nama_petugas' => 'Petugas AMC Test',
            'tanggal' => now()->toDateString(),
            'kondisi_cuaca' => 'Cerah',
            'unit_kerja' => 'AMC',
            'area_inspeksi' => 'Runway 10',
            'grid_lokasi' => 'K-10',
            'status' => 'belum',
        ]);

        DetailSatwa::create([
            'laporan_id' => $laporan->id,
            'nama_satwa' => 'Burung Blekok Sawah',
            'jumlah' => 4,
            'grid' => 'K-10',
        ]);

        Tanggapan::create([
            'laporan_id' => $laporan->id,
            'user_id' => $user->id,
            'kategori_tanggapan' => 'instruksi',
            'isi' => 'Lakukan pemantauan lanjutan pada jam sibuk pagi.',
        ]);

        $response = $this->actingAs($user)->get(route('laporan.detail.user', $laporan->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => $laporan->id,
                'no_laporan' => $laporan->no_laporan,
                'nama_petugas' => 'Petugas AMC Test',
            ],
        ]);
        $response->assertJsonFragment(['nama_satwa' => 'Burung Blekok Sawah']);
        $response->assertJsonFragment(['isi' => 'Lakukan pemantauan lanjutan pada jam sibuk pagi.']);
    }

    public function test_user_cannot_view_other_users_report_detail(): void
    {
        $userA = $this->getPegawaiUser('amc');
        $userB = $this->getSecondPegawaiUser();

        $laporanB = Laporan::create([
            'user_id' => $userB->id,
            'nama_petugas' => 'Petugas B',
            'tanggal' => now()->toDateString(),
            'kondisi_cuaca' => 'Cerah',
            'unit_kerja' => 'ARFF',
            'area_inspeksi' => 'Perimeter',
            'status' => 'belum',
        ]);

        // User A mencoba mengakses detail laporan milik User B
        $response = $this->actingAs($userA)->get(route('laporan.detail.user', $laporanB->id));
        $response->assertStatus(404);
    }

    public function test_new_laporan_automatically_generates_no_laporan(): void
    {
        $user = $this->getPegawaiUser('amc');

        $laporan = Laporan::create([
            'user_id' => $user->id,
            'nama_petugas' => 'Petugas Test',
            'tanggal' => now()->toDateString(),
            'kondisi_cuaca' => 'Cerah',
            'unit_kerja' => 'Apron Movement Control (AMC)',
            'area_inspeksi' => 'Runway 28',
            'grid_lokasi' => 'D-09',
        ]);

        $this->assertNotEmpty($laporan->no_laporan);
        $this->assertStringStartsWith('BA-WHMS/AP1/AMC/', $laporan->no_laporan);
    }
}
