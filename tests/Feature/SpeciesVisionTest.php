<?php

namespace Tests\Feature;

use App\Models\IdentifikasiSatwaAi;
use App\Models\JenisSatwa;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SpeciesVisionTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * TEST-AI-10: Otorisasi role
     */
    public function test_authorization_prevents_unauthorized_access(): void
    {
        $pegawai = User::factory()->create(['role' => 'pegawai']);
        $admin = User::factory()->admin()->create();

        // Pegawai trying to access admin AI routes -> 403 (JSON) or redirect (web)
        $this->actingAs($pegawai)->getJson(route('admin.ai.prediksi.index'))->assertStatus(403);
        $this->actingAs($pegawai)->getJson(route('admin.ai.histori.index'))->assertStatus(403);
        $this->actingAs($pegawai)->getJson(route('admin.ai.lalu-lintas.index'))->assertStatus(403);
        $this->actingAs($pegawai)->get(route('admin.ai.prediksi.index'))->assertRedirect(route('user.dashboard'));

        // Admin trying to call pegawai vision route -> 403
        $file = UploadedFile::fake()->image('bird.jpg');
        $this->actingAs($admin)->postJson(route('laporan.identifikasi'), [
            'foto' => $file,
        ])->assertStatus(403);
    }

    /**
     * TEST-AI-11: Vision sukses
     */
    public function test_vision_success_returns_schema_and_creates_log(): void
    {
        $pegawai = User::factory()->create(['role' => 'pegawai']);

        // Create master JenisSatwa
        JenisSatwa::create([
            'nama' => 'Kuntul Kerbau',
            'kategori' => 'burung',
            'tingkat_risiko' => 'sedang',
        ]);

        $validVisionJson = file_get_contents(base_path('tests/Fixtures/gemini/species_valid.json'));

        Http::fake([
            '*/models/*:generateContent' => Http::response(json_decode($validVisionJson, true), 200),
        ]);

        Config::set('services.gemini.key', 'test_valid_key');
        Config::set('whms_ai.enabled', true);

        $file = UploadedFile::fake()->image('kuntul.jpg', 600, 400)->size(500);

        $response = $this->actingAs($pegawai)->postJson(route('laporan.identifikasi'), [
            'foto' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'terdeteksi_satwa' => true,
        ]);

        $json = $response->json();
        $this->assertNotEmpty($json['kandidat']);
        $this->assertSame('Kuntul Kerbau', $json['kandidat'][0]['nama_lokal']);
        $this->assertSame('Bubulcus ibis', $json['kandidat'][0]['nama_latin']);
        $this->assertNotEmpty($json['rekomendasi_sop']);
        $this->assertNotNull($json['matched_satwa_id']);

        // Verify log is created in identifikasi_satwa_ai
        $this->assertDatabaseHas('identifikasi_satwa_ai', [
            'id' => $json['id'],
            'user_id' => $pegawai->id,
            'nama_lokal' => 'Kuntul Kerbau',
            'nama_latin' => 'Bubulcus ibis',
        ]);
    }

    /**
     * TEST-AI-12: Vision validasi unggahan (non-gambar atau > 5 MB)
     */
    public function test_vision_rejects_non_image_or_oversized_files(): void
    {
        $pegawai = User::factory()->create(['role' => 'pegawai']);
        Http::preventStrayRequests();

        // 1. Text file instead of image
        $textFile = UploadedFile::fake()->create('document.txt', 100);
        $response1 = $this->actingAs($pegawai)->postJson(route('laporan.identifikasi'), [
            'foto' => $textFile,
        ]);
        $response1->assertStatus(422);

        // 2. Oversized image (> 5 MB = 5120 KB)
        $largeFile = UploadedFile::fake()->create('large.jpg', 6000, 'image/jpeg');
        $response2 = $this->actingAs($pegawai)->postJson(route('laporan.identifikasi'), [
            'foto' => $largeFile,
        ]);
        $response2->assertStatus(422);
    }

    /**
     * TEST-AI-13: Vision tanpa satwa
     */
    public function test_vision_handles_no_animal_detected(): void
    {
        $pegawai = User::factory()->create(['role' => 'pegawai']);

        $noAnimalJson = file_get_contents(base_path('tests/Fixtures/gemini/species_no_animal.json'));

        Http::fake([
            '*/models/*:generateContent' => Http::response(json_decode($noAnimalJson, true), 200),
        ]);

        Config::set('services.gemini.key', 'test_valid_key');

        $file = UploadedFile::fake()->image('empty_runway.jpg', 600, 400);

        $response = $this->actingAs($pegawai)->postJson(route('laporan.identifikasi'), [
            'foto' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'terdeteksi_satwa' => false,
            'kandidat' => [],
            'rekomendasi_sop' => [],
        ]);
    }

    /**
     * TEST-AI-14: Penerapan ke form dan mark result
     */
    public function test_marking_ai_result_acceptance(): void
    {
        $pegawai = User::factory()->create(['role' => 'pegawai']);

        $log = IdentifikasiSatwaAi::create([
            'user_id' => $pegawai->id,
            'foto_path' => 'test.jpg',
            'model_version' => 'gemini-2.5-flash',
            'nama_lokal' => 'Kuntul Kerbau',
            'nama_latin' => 'Bubulcus ibis',
            'confidence' => 0.94,
        ]);

        $response = $this->actingAs($pegawai)->postJson(route('laporan.identifikasi.hasil', $log->id), [
            'diterima' => true,
            'nama_final' => 'Kuntul Kerbau',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('identifikasi_satwa_ai', [
            'id' => $log->id,
            'diterima' => true,
            'nama_final' => 'Kuntul Kerbau',
        ]);
    }
}
