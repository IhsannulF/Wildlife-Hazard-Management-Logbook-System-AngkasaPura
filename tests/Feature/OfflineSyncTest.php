<?php

namespace Tests\Feature;

use App\Models\Laporan;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OfflineSyncTest extends TestCase
{
    protected function getPegawaiUser(string $username = 'amc'): User
    {
        return User::where('username', $username)->first()
            ?? User::where('role', 'pegawai')->first()
            ?? User::factory()->create(['role' => 'pegawai', 'username' => $username]);
    }

    protected function getAdminUser(): User
    {
        return User::where('role', 'admin')->first()
            ?? User::factory()->create(['role' => 'admin', 'username' => 'admin_safety']);
    }

    public function test_guest_cannot_access_offline_sync(): void
    {
        $response = $this->postJson(route('laporan.syncOffline'), [
            'offline_sync_id' => 'test-guest-uuid-123',
        ]);

        $response->assertStatus(401);
    }

    public function test_admin_cannot_access_offline_sync(): void
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin)->postJson(route('laporan.syncOffline'), [
            'offline_sync_id' => 'test-admin-uuid-123',
        ]);

        $response->assertStatus(403);
    }

    public function test_validation_requires_offline_sync_id(): void
    {
        $pegawai = $this->getPegawaiUser();

        $response = $this->actingAs($pegawai)->postJson(route('laporan.syncOffline'), []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Parameter offline_sync_id wajib disertakan.',
            ]);
    }

    public function test_pegawai_can_sync_offline_report_successfully(): void
    {
        Storage::fake('public');
        $pegawai = $this->getPegawaiUser();
        $syncId = 'pwa-sync-uuid-'.uniqid();

        // 1x1 transparent PNG base64
        $fakeBase64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $payload = [
            'offline_sync_id' => $syncId,
            'payload' => [
                'tanggal' => now()->format('Y-m-d'),
                'jam' => '09:15:00',
                'nama_petugas' => 'Petugas AMC Airside',
                'unit_kerja' => 'Apron Movement Control (AMC)',
                'area_inspeksi' => 'Runway 07L/25R Bahu Landasan',
                'grid_lokasi' => 'K-10',
                'kondisi_cuaca' => 'Cerah Berawan',
                'tanda_tangan' => 'data:image/png;base64,sample_signature_data_abc123',
                'satwa' => [
                    [
                        'nama' => 'Burung Cangak Abu',
                        'lokasi' => [
                            [
                                'grid' => 'K-10',
                                'jumlah' => 5,
                            ],
                        ],
                    ],
                ],
                'ciri_ukuran' => 'Burung besar di rumput dekat runway',
                'kondisi_apron' => 'Kering',
                'aktivitas_satwa' => 'Mencari makan',
                'tindak_lanjut' => 'Telah ditangani',
                'detail_pengusiran' => 'Pengusiran menggunakan flare gun amunisi merah',
            ],
            'photos' => [
                [
                    'field_name' => 'satwa[0][foto]',
                    'name' => 'satwa_kuntul.png',
                    'type' => 'image/png',
                    'data' => $fakeBase64Image,
                ],
            ],
            'signature' => 'data:image/png;base64,sample_signature_data_abc123',
        ];

        $response = $this->actingAs($pegawai)->postJson(route('laporan.syncOffline'), $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'offline_sync_id' => $syncId,
            ]);

        $this->assertDatabaseHas('laporan', [
            'offline_sync_id' => $syncId,
            'grid_lokasi' => 'K-10',
            'status' => 'sudah',
        ]);

        $laporan = Laporan::where('offline_sync_id', $syncId)->first();
        $this->assertNotNull($laporan);
        $this->assertNotEmpty($laporan->no_laporan);

        // Verifikasi detail satwa tersimpan
        $this->assertDatabaseHas('detail_satwa', [
            'laporan_id' => $laporan->id,
            'nama_satwa' => 'Burung Cangak Abu',
            'jumlah' => 5,
            'grid' => 'K-10',
        ]);

        // Verifikasi foto tersimpan
        $this->assertDatabaseHas('foto_laporan', [
            'laporan_id' => $laporan->id,
            'tipe' => 'satwa',
        ]);
    }

    /**
     * TEST-SYNC-01: PWA Offline Sync Deduplication
     * Pengunggahan draf dengan offline_sync_id yang sama ditolak peladen untuk mencegah duplikasi data (idempotent).
     */
    public function test_sync_deduplication_prevents_duplicate_reports_idempotent(): void
    {
        $pegawai = $this->getPegawaiUser();
        $syncId = 'dedup-sync-test-'.uniqid();

        $payload = [
            'offline_sync_id' => $syncId,
            'payload' => [
                'tanggal' => now()->format('Y-m-d'),
                'jam' => '10:00:00',
                'nama_petugas' => 'Petugas Patroli',
                'unit_kerja' => 'AMC',
                'area_inspeksi' => 'Taxiway Eco',
                'grid_lokasi' => 'D-09',
                'satwa' => [
                    [
                        'nama' => 'Biawak Air Asia',
                        'lokasi' => [
                            ['grid' => 'D-09', 'jumlah' => 1],
                        ],
                    ],
                ],
            ],
        ];

        // Pengiriman pertama: berhasil dibuat
        $firstResponse = $this->actingAs($pegawai)->postJson(route('laporan.syncOffline'), $payload);
        $firstResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'offline_sync_id' => $syncId,
            ]);

        $laporanCountBefore = Laporan::where('offline_sync_id', $syncId)->count();
        $this->assertEquals(1, $laporanCountBefore);

        // Pengiriman kedua (duplikat): peladen mendeteksi offline_sync_id yang sama
        $secondResponse = $this->actingAs($pegawai)->postJson(route('laporan.syncOffline'), $payload);
        $secondResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'already_synced' => true,
                'offline_sync_id' => $syncId,
            ]);

        // Pastikan jumlah baris tetap 1 (tidak terduplikasi di database)
        $laporanCountAfter = Laporan::where('offline_sync_id', $syncId)->count();
        $this->assertEquals(1, $laporanCountAfter);
    }

    public function test_offline_fallback_page_is_accessible(): void
    {
        $response = $this->get(route('offline'));
        $response->assertStatus(200);
        $response->assertSee('Mode Offline');
        $response->assertSee('Koneksi Internet Terputus');
    }

    public function test_manifest_json_file_exists_and_valid(): void
    {
        $manifestPath = public_path('manifest.json');
        $this->assertFileExists($manifestPath);

        $content = json_decode(file_get_contents($manifestPath), true);
        $this->assertIsArray($content);
        $this->assertEquals('WHMS InJourney', $content['short_name']);
        $this->assertEquals('standalone', $content['display']);
        $this->assertNotEmpty($content['icons']);
    }

    public function test_service_worker_file_exists(): void
    {
        $swPath = public_path('sw.js');
        $this->assertFileExists($swPath);

        $content = file_get_contents($swPath);
        $this->assertStringContainsString('whms-v2.0', $content);
        $this->assertStringContainsString('/offline', $content);
    }
}
