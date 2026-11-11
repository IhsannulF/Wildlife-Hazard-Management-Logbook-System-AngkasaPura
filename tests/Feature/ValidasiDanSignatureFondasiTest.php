<?php

namespace Tests\Feature;

use App\Models\Laporan;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ValidasiDanSignatureFondasiTest extends TestCase
{
    private const SAMPLE_PNG_DATA_URI = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

    /** @var array<int, int> */
    private array $createdLaporanIds = [];

    /** @var array<int, int> */
    private array $createdUserIds = [];

    protected function tearDown(): void
    {
        if (! empty($this->createdLaporanIds)) {
            Laporan::whereIn('id', $this->createdLaporanIds)->delete();
        }

        if (! empty($this->createdUserIds)) {
            User::whereIn('id', $this->createdUserIds)->delete();
        }

        parent::tearDown();
    }

    private function createTestUser(string $role, array $overrides = []): User
    {
        $unique = uniqid($role.'_', false);

        $user = User::create(array_merge([
            'username' => $unique,
            'password' => Hash::make('secret123'),
            'namalengkap' => 'Tester '.strtoupper($role),
            'jabatan' => $role === 'admin' ? 'Safety Manager' : 'Unit AMC',
            'role' => $role,
            'nama' => 'Tester '.strtoupper($role),
            'aktif' => true,
        ], $overrides));

        $this->createdUserIds[] = $user->id;

        return $user;
    }

    private function createTestLaporan(User $owner, array $overrides = []): Laporan
    {
        $laporan = Laporan::create(array_merge([
            'user_id' => $owner->id,
            'nama_petugas' => $owner->namalengkap ?: 'Petugas Lapangan',
            'tanggal' => '2026-11-10',
            'jam' => '08:15:00',
            'kondisi_cuaca' => 'Cerah Berawan',
            'unit_kerja' => 'Apron Movement Control (AMC)',
            'area_inspeksi' => 'Runway 10/28',
            'grid_lokasi' => 'K-10',
            'tanda_tangan' => self::SAMPLE_PNG_DATA_URI,
            'ciri_ukuran' => 'Burung air ukuran sedang',
            'kondisi_apron' => 'Kering',
            'aktivitas_satwa' => 'Melintas di bahu landasan',
            'tindak_lanjut' => 'Pengusiran aktif',
            'detail_pengusiran' => 'Sirene kendaraan patroli',
            'status' => 'belum',
        ], $overrides));

        $this->createdLaporanIds[] = $laporan->id;

        return $laporan;
    }

    public function test_dompdf_facade_renders_valid_a4_pdf_binary(): void
    {
        $html = '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"></head><body><h1>Berita Acara WHMS — Juanda 29°C</h1><p>Standar A4 Server-Side.</p></body></html>';

        $pdfBinary = Pdf::loadHTML($html)->setPaper('a4', 'portrait')->output();

        $this->assertSame('a4', config('dompdf.options.default_paper_size'));
        $this->assertFalse((bool) config('dompdf.options.enable_remote'));
        $this->assertFalse((bool) config('dompdf.options.enable_php'));
        $this->assertStringStartsWith('%PDF-', $pdfBinary);
        $this->assertGreaterThan(500, strlen($pdfBinary));
    }

    public function test_generate_no_laporan_uses_tahap_3_format_and_matches_created_id(): void
    {
        $pegawai = $this->createTestUser('pegawai');

        $this->assertSame(
            'BA-WHMS/ARFF/11/2026/0042',
            Laporan::generateNoLaporan('Airport Rescue & Fire Fighting (PK / ARFF)', '2026-11-05', 42)
        );
        $this->assertSame(
            'BA-WHMS/AVSEC/01/2026/0007',
            Laporan::generateNoLaporan('Airport Security (Avsec)', '2026-01-19', 7)
        );
        $this->assertSame(
            'BA-WHMS/SMS/06/2026/0123',
            Laporan::generateNoLaporan('Safety Management System (SMS)', '2026-06-30', 123)
        );

        $laporan = $this->createTestLaporan($pegawai, [
            'unit_kerja' => 'Apron Movement Control (AMC)',
            'tanggal' => '2026-11-09',
        ]);

        $expectedNo = sprintf('BA-WHMS/AMC/11/2026/%04d', $laporan->id);
        $this->assertSame($expectedNo, $laporan->no_laporan);
        $this->assertSame($expectedNo, $laporan->fresh()->no_laporan);
        $this->assertStringNotContainsString('/AP1/', $laporan->no_laporan);
    }

    public function test_guest_and_pegawai_cannot_store_or_update_user_signature(): void
    {
        $pegawai = $this->createTestUser('pegawai');

        $guestResponse = $this->post(route('admin.manajemen.users.store'), [
            'namalengkap' => 'User Baru',
            'username' => 'user_baru_test',
            'password' => 'secret123',
            'role' => 'admin',
        ]);

        $guestResponse->assertRedirect(route('login'));

        $pegawaiResponse = $this->actingAs($pegawai)->post(route('admin.manajemen.users.update', $pegawai->id), [
            'namalengkap' => 'Diubah Pegawai',
            'username' => $pegawai->username,
            'role' => 'admin',
            'tanda_tangan' => self::SAMPLE_PNG_DATA_URI,
        ]);

        $pegawaiResponse->assertRedirect(route('user.dashboard'));
    }

    public function test_admin_can_create_safety_manager_user_with_canvas_signature_specimen(): void
    {
        $admin = $this->createTestUser('admin');
        $newUsername = 'safety_mgr_'.uniqid('', false);

        $response = $this->actingAs($admin)->post(route('admin.manajemen.users.store'), [
            'namalengkap' => 'Capt. Hendra Wijaya',
            'username' => $newUsername,
            'password' => 'juanda123',
            'role' => 'admin',
            'jabatan' => 'Airport Safety Manager',
            'tanda_tangan' => self::SAMPLE_PNG_DATA_URI,
        ]);

        $created = User::where('username', $newUsername)->first();
        if ($created) {
            $this->createdUserIds[] = $created->id;
        }

        $response->assertRedirect(route('admin.users', ['tab' => 'users']));
        $response->assertSessionHas('sukses');
        $this->assertNotNull($created);
        $this->assertSame('Airport Safety Manager', $created->jabatan);
        $this->assertSame(self::SAMPLE_PNG_DATA_URI, $created->tanda_tangan);
    }

    public function test_admin_can_upload_and_remove_signature_specimen_when_updating_user(): void
    {
        $admin = $this->createTestUser('admin');
        $targetUser = $this->createTestUser('admin', [
            'jabatan' => 'Safety Manager',
        ]);

        $uploadedSig = UploadedFile::fake()->image('ttd_safety.png', 800, 300);

        $uploadResponse = $this->actingAs($admin)->post(route('admin.manajemen.users.update', $targetUser->id), [
            'namalengkap' => $targetUser->namalengkap,
            'username' => $targetUser->username,
            'role' => 'admin',
            'jabatan' => 'Airport Safety Manager',
            'tanda_tangan_file' => $uploadedSig,
        ]);

        $uploadResponse->assertRedirect(route('admin.users', ['tab' => 'users']));
        $targetUser->refresh();
        $this->assertNotNull($targetUser->tanda_tangan);
        $this->assertStringStartsWith('data:image/png;base64,', $targetUser->tanda_tangan);

        $removeResponse = $this->actingAs($admin)->post(route('admin.manajemen.users.update', $targetUser->id), [
            'namalengkap' => $targetUser->namalengkap,
            'username' => $targetUser->username,
            'role' => 'admin',
            'jabatan' => 'Airport Safety Manager',
            'remove_tanda_tangan' => '1',
        ]);

        $removeResponse->assertRedirect(route('admin.users', ['tab' => 'users']));
        $this->assertNull($targetUser->fresh()->tanda_tangan);
    }

    public function test_admin_user_update_rejects_invalid_signature_data_uri(): void
    {
        $admin = $this->createTestUser('admin');
        $targetUser = $this->createTestUser('admin', [
            'tanda_tangan' => self::SAMPLE_PNG_DATA_URI,
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.users'))
            ->post(route('admin.manajemen.users.update', $targetUser->id), [
                'namalengkap' => $targetUser->namalengkap,
                'username' => $targetUser->username,
                'role' => 'admin',
                'jabatan' => 'Safety Manager',
                'tanda_tangan' => 'invalid-non-base64-string',
            ]);

        $response->assertRedirect(route('admin.users'));
        $response->assertSessionHasErrors([
            'tanda_tangan' => 'Format tanda tangan digital tidak valid. Gunakan kanvas tanda tangan atau gambar PNG/JPG.',
        ]);
        $this->assertSame(self::SAMPLE_PNG_DATA_URI, $targetUser->fresh()->tanda_tangan);
    }

    public function test_admin_validation_snapshots_safety_manager_metadata_and_signature_immutably(): void
    {
        $safetyManager = $this->createTestUser('admin', [
            'namalengkap' => 'Bambang Safety, S.T.',
            'jabatan' => 'Safety & Risk Manager',
            'tanda_tangan' => self::SAMPLE_PNG_DATA_URI,
        ]);
        $pegawai = $this->createTestUser('pegawai');
        $laporan = $this->createTestLaporan($pegawai, ['status' => 'belum']);

        $response = $this->actingAs($safetyManager)->postJson(route('admin.laporan.update', $laporan->id), [
            'status_validasi' => 'sudah',
        ]);

        $response->assertOk()->assertJson([
            'success' => true,
            'status' => 'sudah',
            'validated_by' => $safetyManager->id,
        ]);

        $laporan->refresh();
        $this->assertSame('sudah', $laporan->status);
        $this->assertSame($safetyManager->id, $laporan->validated_by);
        $this->assertNotNull($laporan->validated_at);
        $this->assertSame(self::SAMPLE_PNG_DATA_URI, $laporan->validator_signature);

        // Hapus spesimen di profil admin untuk memastikan snapshot historis di laporan tidak ikut hilang
        $safetyManager->update(['tanda_tangan' => null]);
        $this->assertSame(self::SAMPLE_PNG_DATA_URI, $laporan->fresh()->validator_signature);

        $detailResponse = $this->actingAs($safetyManager)->getJson(route('admin.laporan.detail', $laporan->id));
        $detailResponse->assertOk()->assertJson([
            'id' => $laporan->id,
            'validator_name' => 'Bambang Safety, S.T.',
            'validator_jabatan' => 'Safety & Risk Manager',
        ]);
        $this->assertNotEmpty($detailResponse->json('validated_at_human'));
    }

    public function test_admin_closing_tanggapan_records_validation_metadata_and_reopen_clears_it(): void
    {
        $safetyManager = $this->createTestUser('admin', [
            'tanda_tangan' => self::SAMPLE_PNG_DATA_URI,
        ]);
        $pegawai = $this->createTestUser('pegawai');
        $laporan = $this->createTestLaporan($pegawai, ['status' => 'belum']);

        $closeResponse = $this->actingAs($safetyManager)->postJson(route('laporan.tanggapan.store', $laporan->id), [
            'kategori_tanggapan' => 'penutupan',
            'isi' => 'Pengusiran selesai dan area landasan terkonfirmasi aman.',
        ]);

        $closeResponse->assertOk()->assertJson([
            'success' => true,
            'laporan_status' => 'sudah',
        ]);

        $laporan->refresh();
        $this->assertSame($safetyManager->id, $laporan->validated_by);
        $this->assertNotNull($laporan->validated_at);
        $this->assertSame(self::SAMPLE_PNG_DATA_URI, $laporan->validator_signature);

        $reopenResponse = $this->actingAs($safetyManager)->postJson(route('admin.laporan.update', $laporan->id), [
            'status_validasi' => 'belum',
        ]);

        $reopenResponse->assertOk()->assertJson([
            'success' => true,
            'status' => 'belum',
            'validated_by' => null,
            'validated_at' => null,
        ]);

        $laporan->refresh();
        $this->assertNull($laporan->validated_by);
        $this->assertNull($laporan->validated_at);
        $this->assertNull($laporan->validator_signature);
    }

    public function test_pegawai_dashboard_and_cetak_view_use_authorized_route_and_standard_no_laporan(): void
    {
        $pegawai = $this->createTestUser('pegawai');
        $laporan = $this->createTestLaporan($pegawai);

        $dashboardResponse = $this->actingAs($pegawai)->get(route('user.dashboard'));

        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('printBtn.href = `/laporan/${id}/cetak`', false);
        $dashboardResponse->assertDontSee('printBtn.href = `/admin/laporan/${id}/cetak`', false);
        $dashboardResponse->assertSee('storage/uploads/${fotoDir}/', false);

        $cetakResponse = $this->actingAs($pegawai)->get(route('laporan.cetak', $laporan->id));

        $cetakResponse->assertOk();
        $cetakResponse->assertSee($laporan->no_laporan, false);
        $cetakResponse->assertDontSee('WHMS-AP1-', false);
    }
}
