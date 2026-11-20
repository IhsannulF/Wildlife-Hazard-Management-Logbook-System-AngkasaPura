<?php

namespace Tests\Unit;

use App\Services\Ai\BirdstrikePredictionValidator;
use App\Services\Ai\GeminiClient;
use Exception;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class GeminiLayerTest extends TestCase
{
    /**
     * TEST-AI-05: Normalisasi grid
     */
    public function test_normalizes_grid_and_filters_invalid_grids(): void
    {
        $validator = new BirdstrikePredictionValidator;

        $input = [
            'probability_score' => 0.5,
            'hazard_level' => 'sedang',
            'critical_time_window' => '06:00 - 08:00 WIB',
            'critical_zones' => ['K-08', 'D-09', 'Z-99', 'K-30', 'k8'],
            'target_species' => [
                [
                    'nama_lokal' => 'Kuntul',
                    'nama_latin' => 'Egretta',
                    'risiko_ancaman' => 'Tinggi',
                ],
            ],
            'contributing_factors' => ['Cuaca'],
            'confidence_score' => 0.8,
            'executive_summary' => 'Ringkasan',
            'actionable_recommendations' => ['Rekomendasi 1'],
        ];

        $result = $validator->validate($input);

        // K-08 -> K-8, D-09 -> D-9, Z-99 dropped, K-30 dropped, k8 -> K-8 (unique)
        $this->assertEquals(['K-8', 'D-9'], $result['grid_kritis']);
        $this->assertSame('06:00', $result['jam_rawan_mulai']);
        $this->assertSame('08:00', $result['jam_rawan_selesai']);
    }

    /**
     * TEST-AI-06: Mapping level bahaya
     */
    public function test_calculates_hazard_level_deterministically(): void
    {
        // < 0.30 = rendah
        $this->assertSame('rendah', BirdstrikePredictionValidator::calculateHazardLevel(0.00));
        $this->assertSame('rendah', BirdstrikePredictionValidator::calculateHazardLevel(0.29));

        // 0.30 - 0.59 = sedang
        $this->assertSame('sedang', BirdstrikePredictionValidator::calculateHazardLevel(0.30));
        $this->assertSame('sedang', BirdstrikePredictionValidator::calculateHazardLevel(0.45));
        $this->assertSame('sedang', BirdstrikePredictionValidator::calculateHazardLevel(0.59));

        // 0.60 - 0.84 = tinggi
        $this->assertSame('tinggi', BirdstrikePredictionValidator::calculateHazardLevel(0.60));
        $this->assertSame('tinggi', BirdstrikePredictionValidator::calculateHazardLevel(0.78));
        $this->assertSame('tinggi', BirdstrikePredictionValidator::calculateHazardLevel(0.84));

        // >= 0.85 = kritis
        $this->assertSame('kritis', BirdstrikePredictionValidator::calculateHazardLevel(0.85));
        $this->assertSame('kritis', BirdstrikePredictionValidator::calculateHazardLevel(0.99));
        $this->assertSame('kritis', BirdstrikePredictionValidator::calculateHazardLevel(1.00));
    }

    /**
     * TEST-AI-02: Validasi format JSON
     */
    public function test_rejects_invalid_score_and_missing_fields(): void
    {
        $validator = new BirdstrikePredictionValidator;

        // Score > 1.0
        $invalidScore = [
            'probability_score' => 1.45,
            'hazard_level' => 'tinggi',
            'critical_time_window' => '05:30 - 08:30 WIB',
            'critical_zones' => ['K-8'],
            'target_species' => [],
            'contributing_factors' => ['Faktor'],
            'confidence_score' => 0.88,
            'executive_summary' => 'Ringkasan',
            'actionable_recommendations' => ['Saran'],
        ];

        $this->expectException(ValidationException::class);
        $validator->validate($invalidScore);
    }

    /**
     * TEST-AI-04a: Error HTTP 400 tidak di-retry
     */
    public function test_gemini_client_does_not_retry_on_400(): void
    {
        $attempts400 = 0;
        Http::fake([
            '*/models/*:generateContent' => function (Request $request) use (&$attempts400) {
                $attempts400++;

                return Http::response([
                    'error' => [
                        'code' => 400,
                        'message' => 'API key not valid.',
                        'status' => 'INVALID_ARGUMENT',
                    ],
                ], 400);
            },
        ]);

        $client = new GeminiClient('invalid_key');

        try {
            $client->generate('gemini-2.5-flash', [['role' => 'user', 'parts' => [['text' => 'Hello']]]]);
            $this->fail('Expected exception was not thrown.');
        } catch (Exception $e) {
            $this->assertStringContainsString('Gemini API error [400]', $e->getMessage());
            $this->assertSame(1, $attempts400); // Only 1 attempt, no retry
        }
    }

    /**
     * TEST-AI-04b: Error HTTP 500 di-retry dan sukses
     */
    public function test_gemini_client_retries_on_500(): void
    {
        $attempts500 = 0;
        Http::fake([
            '*/models/*:generateContent' => function (Request $request) use (&$attempts500) {
                $attempts500++;

                if ($attempts500 < 2) {
                    return Http::response(['error' => ['message' => 'Internal Server Error']], 500);
                }

                $validJson = file_get_contents(base_path('tests/Fixtures/gemini/birdstrike_valid.json'));

                return Http::response(json_decode($validJson, true), 200);
            },
        ]);

        $client = new GeminiClient('valid_key');

        $res = $client->generate('gemini-2.5-flash', [['role' => 'user', 'parts' => [['text' => 'Predict']]]], timeout: 5);
        $this->assertSame(2, $attempts500); // Retried once and succeeded
        $this->assertSame(0.78, $res['data']['probability_score']);
    }
}
