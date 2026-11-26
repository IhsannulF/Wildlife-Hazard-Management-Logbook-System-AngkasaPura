<?php

namespace Tests\Feature;

use App\Models\FotoLaporan;
use App\Models\Laporan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Foto bukti inspeksi: koordinat GPS perangkat & waktu ambil tersimpan dan terlihat oleh admin.
 */
class FotoBuktiLokasiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_laporan_menyimpan_koordinat_gps_dan_metadata_foto(): void
    {
        $pegawai = User::factory()->create(['role' => 'pegawai']);

        $this->actingAs($pegawai)->post(route('laporan.store'), $this->payload())->assertSessionHasNoErrors();

        $laporan = Laporan::where('user_id', $pegawai->id)->latest('id')->sole();
        $this->assertEqualsWithDelta(-7.3798123, (float) $laporan->gps_lat, 0.0000001);
        $this->assertEqualsWithDelta(112.7871234, (float) $laporan->gps_lng, 0.0000001);

        $foto = FotoLaporan::where('laporan_id', $laporan->id)->sole();
        $this->assertEqualsWithDelta(-7.3798123, $foto->gps_lat, 0.0000001);
        $this->assertSame(8, $foto->gps_akurasi_m);
        $this->assertSame('2026-11-20 06:14:30', $foto->diambil_at->format('Y-m-d H:i:s'));
        $this->assertSame('galeri', $foto->sumber);
    }

    public function test_koordinat_di_luar_rentang_ditolak(): void
    {
        $response = $this->actingAs(User::factory()->create(['role' => 'pegawai']))
            ->post(route('laporan.store'), $this->payload(['gps_lat' => 120, 'foto_sumber' => 'drone']));

        $response->assertSessionHasErrors(['gps_lat', 'foto_sumber']);
    }

    public function test_detail_admin_menampilkan_lokasi_foto_dan_tautan_peta(): void
    {
        $pegawai = User::factory()->create(['role' => 'pegawai']);
        $this->actingAs($pegawai)->post(route('laporan.store'), $this->payload());
        $laporan = Laporan::where('user_id', $pegawai->id)->latest('id')->sole();

        $response = $this->actingAs(User::factory()->admin()->create())
            ->getJson(route('admin.laporan.detail', $laporan->id));

        $response->assertJsonPath('satwa.0.foto_lokasi.lat', '-7.379812');
        $response->assertJsonPath('satwa.0.foto_lokasi.keterangan', '-7.379812, 112.787123 (±8 m) · lokasi saat unggah galeri · 20/11/2026 06:14 WIB');
        $response->assertJsonPath('satwa.0.foto_lokasi.peta_url', 'https://www.google.com/maps?q=-7.3798123,112.7871234');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
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
                'foto' => UploadedFile::fake()->image('bukti_inspeksi_20261120_061430.jpg', 800, 600),
                'lokasi' => [['grid' => 'K-10', 'jumlah' => 6]],
            ]],
            'gps_lat' => '-7.3798123',
            'gps_lng' => '112.7871234',
            'gps_akurasi_m' => '8',
            'foto_diambil_at' => '2026-11-20 06:14:30',
            'foto_sumber' => 'galeri',
            'ciri_ukuran' => 'Burung putih berkelompok',
            'kondisi_apron' => 'Kering',
            'aktivitas_satwa' => 'Mencari makan',
            'tindak_lanjut' => 'Telah ditangani',
            'detail_pengusiran' => 'Sirene patroli',
        ], $overrides);
    }
}
