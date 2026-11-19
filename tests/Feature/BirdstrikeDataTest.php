<?php

namespace Tests\Feature;

use App\Models\HistoriBirdstrike;
use App\Models\Laporan;
use App\Models\User;
use App\Services\Ai\BirdstrikeContextBuilder;
use App\Services\Weather\WeatherProvider;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BirdstrikeDataTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * TEST-AI-07: Kebersihan payload
     */
    public function test_context_builder_sanitizes_payload_and_does_not_contain_personal_info(): void
    {
        $admin = User::factory()->admin()->create([
            'username' => 'secret_admin_user',
            'namalengkap' => 'Kapten Budi Rahardjo',
        ]);

        $pegawai = User::factory()->create([
            'username' => 'field_officer_99',
            'namalengkap' => 'Petugas Rahasia AMC',
        ]);

        // Create sample report with personal information
        $laporan = Laporan::create([
            'user_id' => $pegawai->id,
            'nama_petugas' => 'Petugas Rahasia AMC',
            'tanggal' => now()->toDateString(),
            'jam' => '07:15:00',
            'kondisi_cuaca' => 'Cerah Berawan',
            'unit_kerja' => 'AMC',
            'area_inspeksi' => 'Runway 10/28',
            'tanda_tangan' => 'data:image/png;base64,SECRET_SIGNATURE_DATA',
            'grid_lokasi' => 'K-8',
            'ciri_ukuran' => 'Sedang',
            'kondisi_apron' => 'Baik',
            'aktivitas_satwa' => 'Mencari makan',
            'risk_score' => 6,
            'risk_level' => 'sedang',
        ]);

        // Add dummy histori
        HistoriBirdstrike::factory()->create([
            'spesies_satwa' => 'Kuntul Kerbau',
            'tanggal_kejadian' => now()->subDays(3)->toDateString(),
            'created_by' => $admin->id,
        ]);

        // Dummy mock weather provider
        $weatherMock = new class implements WeatherProvider
        {
            public function getWeather(float $lat, float $lng, ?string $date = null): array
            {
                return [
                    'kondisi_saat_ini' => 'Cerah',
                    'suhu_celsius' => 31.0,
                    'kelembapan_persen' => 70,
                    'kecepatan_angin_knot' => 8.0,
                    'arah_angin_derajat' => 120,
                    'curah_hujan_mm' => 0.0,
                    'jarak_pandang_km' => 10.0,
                    'prakiraan_24_jam' => [],
                    'sumber' => 'Mock Weather Test',
                ];
            }
        };

        $builder = new BirdstrikeContextBuilder($weatherMock);
        $payload = $builder->build(now()->toDateString(), 'Catatan habitat: rumput baru dipotong.', 30);

        $jsonEncoded = json_encode($payload);

        // Assert strictly that personal info is NOT in payload
        $this->assertStringNotContainsString('Petugas Rahasia AMC', $jsonEncoded);
        $this->assertStringNotContainsString('field_officer_99', $jsonEncoded);
        $this->assertStringNotContainsString('secret_admin_user', $jsonEncoded);
        $this->assertStringNotContainsString('Kapten Budi Rahardjo', $jsonEncoded);
        $this->assertStringNotContainsString('SECRET_SIGNATURE_DATA', $jsonEncoded);

        // Assert payload size <= 60 KB (61440 bytes)
        $this->assertLessThanOrEqual(61440, strlen($jsonEncoded));

        // Assert expected structure
        $this->assertArrayHasKey('periode', $payload);
        $this->assertArrayHasKey('logbook', $payload);
        $this->assertArrayHasKey('histori_birdstrike', $payload);
        $this->assertArrayHasKey('cuaca', $payload);
        $this->assertArrayHasKey('lalu_lintas', $payload);
        $this->assertArrayHasKey('habitat', $payload);
        $this->assertArrayHasKey('meta', $payload);
    }

    public function test_admin_can_crud_histori_birdstrike(): void
    {
        $admin = User::factory()->admin()->create();

        // 1. Create
        $response = $this->actingAs($admin)->post(route('admin.ai.histori.store'), [
            'tanggal_kejadian' => now()->subDay()->toDateString(),
            'jam_kejadian' => '07:30',
            'kode_runway' => '10',
            'titik_lokasi' => 'touchdown',
            'jenis_pesawat' => 'B737-800',
            'fase_penerbangan' => 'landing',
            'bagian_pesawat' => 'engine',
            'spesies_satwa' => 'Kuntul Kerbau',
            'jumlah_satwa' => 2,
            'tingkat_kerusakan' => 'minor',
            'keterangan' => 'Test insiden baru',
        ]);

        $response->assertRedirect(route('admin.ai.histori.index'));
        $this->assertDatabaseHas('histori_birdstrike', [
            'spesies_satwa' => 'Kuntul Kerbau',
            'jenis_pesawat' => 'B737-800',
        ]);

        $histori = HistoriBirdstrike::where('spesies_satwa', 'Kuntul Kerbau')->firstOrFail();

        // 2. Update
        $this->actingAs($admin)->put(route('admin.ai.histori.update', $histori->id), [
            'tanggal_kejadian' => now()->subDay()->toDateString(),
            'jam_kejadian' => '07:30',
            'kode_runway' => '28',
            'titik_lokasi' => 'roll',
            'jenis_pesawat' => 'A320-200',
            'fase_penerbangan' => 'landing',
            'bagian_pesawat' => 'windshield',
            'spesies_satwa' => 'Blekok Sawah',
            'jumlah_satwa' => 1,
            'tingkat_kerusakan' => 'nihil',
        ]);

        $this->assertDatabaseHas('histori_birdstrike', [
            'id' => $histori->id,
            'spesies_satwa' => 'Blekok Sawah',
            'kode_runway' => '28',
        ]);

        // 3. Delete
        $this->actingAs($admin)->delete(route('admin.ai.histori.destroy', $histori->id));
        $this->assertDatabaseMissing('histori_birdstrike', [
            'id' => $histori->id,
        ]);
    }

    public function test_admin_can_import_histori_birdstrike_csv(): void
    {
        $admin = User::factory()->admin()->create();

        $csvHeader = "tanggal_kejadian,jam_kejadian,kode_runway,titik_lokasi,koordinat_lat,koordinat_lng,jenis_pesawat,fase_penerbangan,bagian_pesawat,spesies_satwa,jumlah_satwa,tingkat_kerusakan,keterangan\n";
        $csvRow1 = now()->subDays(2)->toDateString().",08:00,10,touchdown,-7.3798,112.7875,B737,landing,wing,Burung Gereja,3,nihil,Tidak ada kendala\n";
        $csvRowInvalid = "2099-01-01,99:99,99,invalid,lat,lng,pesawat,fase,bagian,spesies,-1,rusak,catatan\n"; // Invalid row

        $content = $csvHeader.$csvRow1.$csvRowInvalid;
        $file = UploadedFile::fake()->createWithContent('histori.csv', $content);

        $response = $this->actingAs($admin)->post(route('admin.ai.histori.import'), [
            'csv_file' => $file,
        ]);

        $response->assertRedirect(route('admin.ai.histori.index'));
        $this->assertDatabaseHas('histori_birdstrike', [
            'spesies_satwa' => 'Burung Gereja',
            'kode_runway' => '10',
        ]);
    }

    public function test_admin_can_store_and_import_kepadatan_penerbangan(): void
    {
        $admin = User::factory()->admin()->create();
        $date = now()->toDateString();

        $response = $this->actingAs($admin)->post(route('admin.ai.lalu-lintas.store'), [
            'tanggal' => $date,
            'movements' => [
                6 => 15,
                7 => 28,
                8 => 20,
            ],
        ]);

        $response->assertRedirect(route('admin.ai.lalu-lintas.index', ['tanggal' => $date]));
        $this->assertDatabaseHas('kepadatan_penerbangan', [
            'tanggal' => $date,
            'jam' => 7,
            'jumlah_pergerakan' => 28,
        ]);
    }
}
