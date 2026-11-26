<?php

namespace Tests\Feature;

use App\Models\HistoriBirdstrike;
use App\Models\Laporan;
use App\Models\MasterAlatDispersal;
use App\Models\PrediksiBirdstrikeAi;
use App\Models\TindakanPengusiran;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * PRD v2.1 F0: kelengkapan data untuk prediksi bird strike.
 */
class PencatatanDataPrediksiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_laporan_tanpa_jam_pengamatan_ditolak(): void
    {
        $pegawai = User::factory()->create();
        $payload = $this->laporanPayload();
        unset($payload['jam']);

        $response = $this->actingAs($pegawai)->post(route('laporan.store'), $payload);

        $response->assertSessionHasErrors('jam');
        $this->assertDatabaseMissing('laporan', ['user_id' => $pegawai->id]);
    }

    public function test_ciri_ukuran_dan_aktivitas_satwa_boleh_dikosongkan(): void
    {
        $pegawai = User::factory()->create(['role' => 'pegawai']);
        $payload = $this->laporanPayload();
        unset($payload['ciri_ukuran'], $payload['aktivitas_satwa']);

        $response = $this->actingAs($pegawai)->post(route('laporan.store'), $payload);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('laporan', ['user_id' => $pegawai->id, 'jam' => '06:15:00']);
    }

    public function test_laporan_menyimpan_jam_perilaku_kawanan_dan_tindakan_pengusiran(): void
    {
        $pegawai = User::factory()->create();
        $alat = $this->activeDispersalTool();

        $response = $this->actingAs($pegawai)->post(route('laporan.store'), $this->laporanPayload([
            'pengusiran_alat_id' => $alat->id,
            'pengusiran_hasil' => 'berhasil',
        ]));

        $response->assertRedirect(route('user.dashboard'));
        $laporan = Laporan::where('user_id', $pegawai->id)->sole();
        $this->assertSame('06:15:00', $laporan->jam);
        $this->assertDatabaseHas('detail_satwa', [
            'laporan_id' => $laporan->id,
            'perilaku' => 'mencari_makan',
            'arah_terbang' => 'TL',
            'ketinggian' => '<50ft',
        ]);
        $this->assertDatabaseHas('tindakan_pengusiran', [
            'laporan_id' => $laporan->id,
            'alat_id' => $alat->id,
            'user_id' => $pegawai->id,
            'grid' => 'K-10',
            'waktu' => '2026-11-20 06:15:00',
            'hasil' => 'berhasil',
        ]);
    }

    public function test_laporan_tanpa_alat_pengusiran_tidak_membuat_log_pengusiran(): void
    {
        $pegawai = User::factory()->create();

        $this->actingAs($pegawai)->post(route('laporan.store'), $this->laporanPayload());

        $laporan = Laporan::where('user_id', $pegawai->id)->sole();
        $this->assertDatabaseMissing('tindakan_pengusiran', ['laporan_id' => $laporan->id]);
    }

    public function test_laporan_menolak_nilai_perilaku_di_luar_daftar(): void
    {
        $pegawai = User::factory()->create();
        $payload = $this->laporanPayload();
        $payload['satwa'][0]['lokasi'][0]['perilaku'] = 'menari';

        $response = $this->actingAs($pegawai)->post(route('laporan.store'), $payload);

        $response->assertSessionHasErrors('satwa.0.lokasi.0.perilaku');
    }

    public function test_sinkron_offline_membaca_kunci_datar_dari_draf_browser(): void
    {
        $pegawai = User::factory()->create();
        $alat = $this->activeDispersalTool();
        $syncId = 'pwa-flat-'.uniqid();

        $response = $this->actingAs($pegawai)->postJson(route('laporan.syncOffline'), [
            'offline_sync_id' => $syncId,
            'payload' => [
                'tanggal' => '2026-11-20',
                'jam' => '17:05',
                'kondisi_cuaca' => 'Cerah',
                'unit_kerja' => 'AMC',
                'area_inspeksi' => 'Runway',
                'satwa[0][nama]' => 'Burung Kuntul',
                'satwa[0][lokasi][0][jumlah]' => '7',
                'satwa[0][lokasi][0][grid]' => 'D-9',
                'satwa[0][lokasi][0][perilaku]' => 'melintas',
                'satwa[0][lokasi][0][arah_terbang]' => 'B',
                'satwa[0][lokasi][0][ketinggian]' => '50-200ft',
                'pengusiran_alat_id' => (string) $alat->id,
            ],
        ]);

        $response->assertOk();
        $laporan = Laporan::where('offline_sync_id', $syncId)->sole();
        $this->assertSame('D-9', $laporan->grid_lokasi);
        $this->assertDatabaseHas('detail_satwa', [
            'laporan_id' => $laporan->id,
            'nama_satwa' => 'Burung Kuntul',
            'jumlah' => 7,
            'perilaku' => 'melintas',
            'arah_terbang' => 'B',
            'ketinggian' => '50-200ft',
        ]);
        $this->assertDatabaseHas('tindakan_pengusiran', [
            'laporan_id' => $laporan->id,
            'grid' => 'D-9',
            'waktu' => '2026-11-20 17:05:00',
        ]);
    }

    public function test_pegawai_mencatat_tindakan_pengusiran_dengan_grid_dinormalisasi(): void
    {
        $pegawai = User::factory()->create();
        $alat = $this->activeDispersalTool();

        $response = $this->actingAs($pegawai)
            ->from(route('user.dashboard'))
            ->post(route('pengusiran.store'), [
                'alat_id' => $alat->id,
                'waktu' => '2026-11-24T07:40',
                'grid' => 'k-08',
                'jumlah_digunakan' => 3,
                'hasil' => 'sebagian',
                'keterangan' => 'Kawanan kuntul pindah ke timur',
            ]);

        $response->assertRedirect(route('user.dashboard'));
        $response->assertSessionHas('sukses');
        $this->assertDatabaseHas('tindakan_pengusiran', [
            'user_id' => $pegawai->id,
            'alat_id' => $alat->id,
            'grid' => 'K-8',
            'waktu' => '2026-11-24 07:40:00',
            'jumlah_digunakan' => 3,
            'hasil' => 'sebagian',
            'laporan_id' => null,
        ]);
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidDispersalInputs(): array
    {
        return [
            'grid di luar A-L / 1-29' => [['grid' => 'Z-40'], 'grid'],
            'waktu di masa depan' => [['waktu' => '2099-01-01T08:00'], 'waktu'],
            'hasil di luar daftar' => [['hasil' => 'meledak'], 'hasil'],
            'alat tidak dipilih' => [['alat_id' => null], 'alat_id'],
        ];
    }

    /**
     * @param  array<string, mixed>  $override
     */
    #[DataProvider('invalidDispersalInputs')]
    public function test_tindakan_pengusiran_menolak_input_tidak_valid(array $override, string $errorKey): void
    {
        $pegawai = User::factory()->create();
        $input = array_merge([
            'alat_id' => $this->activeDispersalTool()->id,
            'waktu' => '2026-11-24T07:40',
            'grid' => 'K-10',
        ], $override);

        $response = $this->actingAs($pegawai)->post(route('pengusiran.store'), $input);

        $response->assertSessionHasErrors($errorKey);
        $this->assertDatabaseMissing('tindakan_pengusiran', ['user_id' => $pegawai->id]);
    }

    public function test_tindakan_pengusiran_menolak_alat_nonaktif(): void
    {
        $pegawai = User::factory()->create();
        $alatNonaktif = MasterAlatDispersal::create(['nama_alat' => 'Alat Lama', 'kategori' => 'Akustik', 'satuan' => 'Kali', 'aktif' => false]);

        $response = $this->actingAs($pegawai)->post(route('pengusiran.store'), [
            'alat_id' => $alatNonaktif->id,
            'waktu' => '2026-11-24T07:40',
            'grid' => 'K-10',
        ]);

        $response->assertSessionHasErrors('alat_id');
    }

    public function test_tamu_tidak_bisa_mencatat_tindakan_pengusiran(): void
    {
        $response = $this->post(route('pengusiran.store'), [
            'alat_id' => $this->activeDispersalTool()->id,
            'waktu' => '2026-11-24T07:40',
            'grid' => 'K-10',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_pegawai_menampilkan_pengusiran_terakhir_miliknya(): void
    {
        $pegawai = User::factory()->create();
        TindakanPengusiran::factory()->for($pegawai)->create(['grid' => 'E-12']);
        TindakanPengusiran::factory()->create(['grid' => 'A-29']);

        $response = $this->actingAs($pegawai)->get(route('user.dashboard'));

        $response->assertOk();
        $response->assertSee('Log Tindakan Pengusiran');
        $response->assertSee('E-12');
        $response->assertDontSee('A-29');
    }

    public function test_near_miss_tidak_menandai_prediksi_sebagai_insiden(): void
    {
        $admin = User::factory()->admin()->create();
        $prediksi = PrediksiBirdstrikeAi::factory()->create(['tanggal_prediksi' => '2026-11-20', 'insiden_terjadi' => null]);

        $response = $this->actingAs($admin)->post(route('admin.ai.histori.store'), [
            ...$this->historiPayload(),
            'jenis_kejadian' => 'near_miss',
            'grid' => 'k-09',
            'ketinggian_ft' => 120,
            'jarak_centerline_m' => 30,
        ]);

        $response->assertRedirect(route('admin.ai.histori.index'));
        $this->assertDatabaseHas('histori_birdstrike', [
            'jenis_kejadian' => 'near_miss',
            'grid' => 'K-9',
            'ketinggian_ft' => 120,
            'jarak_centerline_m' => 30,
        ]);
        $this->assertNull($prediksi->fresh()->insiden_terjadi);
    }

    public function test_histori_tanpa_jenis_kejadian_tercatat_sebagai_strike(): void
    {
        $admin = User::factory()->admin()->create();
        $prediksi = PrediksiBirdstrikeAi::factory()->create(['tanggal_prediksi' => '2026-11-20', 'insiden_terjadi' => null]);

        $this->actingAs($admin)->post(route('admin.ai.histori.store'), $this->historiPayload());

        $this->assertSame('strike', HistoriBirdstrike::latest('id')->first()->jenis_kejadian);
        $this->assertTrue($prediksi->fresh()->insiden_terjadi);
    }

    private function activeDispersalTool(): MasterAlatDispersal
    {
        return MasterAlatDispersal::create(['nama_alat' => 'Sirene Uji', 'kategori' => 'Akustik', 'satuan' => 'Kali']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function laporanPayload(array $overrides = []): array
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
            'satwa' => [[
                'nama' => 'Burung Kuntul',
                'lokasi' => [[
                    'grid' => 'K-10',
                    'jumlah' => 6,
                    'perilaku' => 'mencari_makan',
                    'arah_terbang' => 'TL',
                    'ketinggian' => '<50ft',
                ]],
            ]],
            'ciri_ukuran' => 'Burung putih berkelompok',
            'kondisi_apron' => 'Kering',
            'aktivitas_satwa' => 'Mencari makan dekat threshold',
            'tindak_lanjut' => 'Telah ditangani',
            'detail_pengusiran' => 'Sirene patroli',
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    private function historiPayload(): array
    {
        return [
            'tanggal_kejadian' => '2026-11-20',
            'jam_kejadian' => '06:40',
            'kode_runway' => '10',
            'titik_lokasi' => 'threshold',
            'jenis_pesawat' => 'B737-800',
            'fase_penerbangan' => 'landing',
            'bagian_pesawat' => 'engine',
            'spesies_satwa' => 'Kuntul Kerbau',
            'jumlah_satwa' => 3,
            'tingkat_kerusakan' => 'nihil',
        ];
    }
}
