<?php

namespace Tests\Feature;

use App\Models\HistoriBirdstrike;
use App\Models\PrediksiBirdstrikeAi;
use App\Models\User;
use App\Services\Ai\GeminiApiService;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BirdstrikePredictionTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * TEST-AI-01: Prediksi sukses
     */
    public function test_prediction_success_saves_record_with_server_hazard_level(): void
    {
        $validJson = file_get_contents(base_path('tests/Fixtures/gemini/birdstrike_valid.json'));

        Http::fake([
            '*/models/*:generateContent' => Http::response(json_decode($validJson, true), 200),
            'https://api.open-meteo.com/*' => Http::response(['current' => ['temperature_2m' => 30]], 200),
        ]);

        Config::set('services.gemini.key', 'test_valid_key');
        Config::set('whms_ai.enabled', true);

        $admin = User::factory()->admin()->create();

        $service = app(GeminiApiService::class);
        $prediksi = $service->predictBirdstrike(
            date: now()->toDateString(),
            habitatNotes: 'Area rumput baru dipotong',
            trigger: 'manual',
            user: $admin
        );

        $this->assertInstanceOf(PrediksiBirdstrikeAi::class, $prediksi);
        $this->assertSame('sukses', $prediksi->status_eksekusi);
        $this->assertSame(0.78, (float) $prediksi->skor_probabilitas);
        // Server calculates 0.78 as 'tinggi'
        $this->assertSame('tinggi', $prediksi->level_bahaya);
        $this->assertSame('05:30', $prediksi->jam_rawan_mulai);
        $this->assertSame('08:30', $prediksi->jam_rawan_selesai);
        $this->assertEquals(['K-8', 'D-9'], $prediksi->grid_kritis);
        $this->assertSame('aktif', $prediksi->status_advisory);

        $this->assertDatabaseHas('prediksi_birdstrike_ai', [
            'id' => $prediksi->id,
            'status_eksekusi' => 'sukses',
            'level_bahaya' => 'tinggi',
        ]);
    }

    /**
     * TEST-AI-03: Timeout API
     */
    public function test_api_timeout_records_failed_execution(): void
    {
        $attempts = 0;
        Http::fake([
            '*/models/*:generateContent' => function (Request $request) use (&$attempts) {
                $attempts++;
                throw new ConnectionException('Connection timed out after 45 seconds');
            },
            'https://api.open-meteo.com/*' => Http::response([], 500),
        ]);

        Config::set('services.gemini.key', 'test_valid_key');

        $service = app(GeminiApiService::class);

        try {
            $service->predictBirdstrike(now()->toDateString());
            $this->fail('Expected ConnectionException');
        } catch (ConnectionException $e) {
            $this->assertStringContainsString('Connection timed out', $e->getMessage());
        }

        // Must retry 2 times (total 3 attempts)
        $this->assertSame(3, $attempts);

        // A failed record must be saved in database for fail-safe auditing
        $this->assertDatabaseHas('prediksi_birdstrike_ai', [
            'tanggal_prediksi' => now()->toDateString(),
            'status_eksekusi' => 'gagal',
        ]);
    }

    /**
     * TEST-AI-08: Scheduler
     */
    public function test_scheduler_registers_birdstrike_predict_daily_at_0500_wib(): void
    {
        $schedule = app(Schedule::class);

        $events = collect($schedule->events())->filter(function (Event $event) {
            return str_contains($event->command, 'birdstrike:predict');
        });

        $this->assertNotEmpty($events, 'Command birdstrike:predict must be scheduled.');

        /** @var Event $event */
        $event = $events->first();
        $this->assertSame('Asia/Jakarta', $event->timezone);
        // dailyAt 05:00 corresponds to '0 5 * * *'
        $this->assertSame('0 5 * * *', $event->expression);
    }

    /**
     * TEST-AI-09: Cooldown & kuota manual
     */
    public function test_manual_run_respects_cooldown_and_daily_quota(): void
    {
        $admin = User::factory()->admin()->create();

        // Simulate that this admin ran manual prediction 1 minute ago
        Cache::put("ai_manual_cooldown_admin_{$admin->id}", time() - 60, 600);

        $response = $this->actingAs($admin)->postJson(route('admin.ai.prediksi.run'), [
            'catatan_habitat' => 'Test',
        ]);

        $response->assertStatus(429);
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('cooldown', strtolower($response->json('message')));
    }

    /**
     * TEST-AI-15: Fallback tanpa kunci
     */
    public function test_command_exits_cleanly_when_api_key_is_missing(): void
    {
        Config::set('services.gemini.key', '');

        $exitCode = Artisan::call('birdstrike:predict');
        $this->assertSame(0, $exitCode);

        $output = Artisan::output();
        $this->assertStringContainsString('belum dikonfigurasi', $output);
    }

    /**
     * TEST-AI-16: Validasi akurasi saat insiden terjadi
     */
    public function test_adding_birdstrike_incident_updates_prediction_to_incident_occurred(): void
    {
        $date = now()->toDateString();

        // Create an active prediction for today
        $prediksi = PrediksiBirdstrikeAi::factory()->create([
            'tanggal_prediksi' => $date,
            'level_bahaya' => 'tinggi',
            'insiden_terjadi' => null,
        ]);

        $this->assertNull($prediksi->insiden_terjadi);

        // Create an incident on that date
        HistoriBirdstrike::factory()->create([
            'tanggal_kejadian' => $date,
            'spesies_satwa' => 'Kuntul Kerbau',
        ]);

        // Refresh prediction model
        $prediksi->refresh();
        $this->assertTrue($prediksi->insiden_terjadi);
    }
}
