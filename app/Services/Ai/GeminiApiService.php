<?php

namespace App\Services\Ai;

use App\Models\IdentifikasiSatwaAi;
use App\Models\JenisSatwa;
use App\Models\PrediksiBirdstrikeAi;
use App\Models\User;
use App\Notifications\BirdstrikeAdvisoryNotification;
use App\Services\Ai\Schemas\BirdstrikeSchema;
use App\Services\Ai\Schemas\SpeciesSchema;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class GeminiApiService
{
    public function __construct(
        protected ?GeminiClient $client = null,
        protected ?BirdstrikeContextBuilder $contextBuilder = null,
        protected ?BirdstrikePredictionValidator $predictionValidator = null,
        protected ?SpeciesIdentificationValidator $speciesValidator = null,
    ) {
        $this->client = $client ?? new GeminiClient;
        $this->contextBuilder = $contextBuilder ?? new BirdstrikeContextBuilder;
        $this->predictionValidator = $predictionValidator ?? new BirdstrikePredictionValidator;
        $this->speciesValidator = $speciesValidator ?? new SpeciesIdentificationValidator;
    }

    /**
     * Run birdstrike predictive inference.
     *
     * @throws Exception
     */
    public function predictBirdstrike(
        ?string $date = null,
        ?string $habitatNotes = null,
        string $trigger = 'terjadwal',
        ?User $user = null
    ): PrediksiBirdstrikeAi {
        $targetDate = $date ? Carbon::parse($date)->toDateString() : Carbon::today()->toDateString();
        $model = config('services.gemini.model_prediction', 'gemini-3.7-flash');
        $timeout = config('services.gemini.timeout_prediction', 45);
        $startTime = microtime(true);

        if (! config('whms_ai.enabled', true)) {
            throw new Exception('Fitur AI dinonaktifkan via konfigurasi AI_ENABLED.');
        }

        if (empty(config('services.gemini.key'))) {
            throw new Exception('GEMINI_API_KEY belum dikonfigurasi.');
        }

        // 1. Build structured payload
        $windowDays = config('whms_ai.logbook_window_days', 30);
        $payload = $this->contextBuilder->build($targetDate, $habitatNotes, $windowDays);

        // 2. Read system prompt
        $promptPath = resource_path('ai/prompts/birdstrike_system.md');
        $systemPrompt = File::exists($promptPath)
            ? File::get($promptPath)
            : 'Anda adalah AI Wildlife Safety Advisor. Analisis data payload JSON yang diberikan dan hasilkan prediksi birdstrike sesuai skema JSON.';

        $systemInstruction = [
            'parts' => [
                ['text' => $systemPrompt],
            ],
        ];

        $contents = [
            [
                'role' => 'user',
                'parts' => [
                    [
                        'text' => "Berikut adalah data operasional penerbangan dan logbook satwa liar terkini:\n".json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                    ],
                ],
            ],
        ];

        try {
            // 3. Inference call
            $response = $this->client->generate(
                model: $model,
                contents: $contents,
                systemInstruction: $systemInstruction,
                responseSchema: BirdstrikeSchema::get(),
                temperature: 0.2,
                timeout: $timeout
            );

            // 4. Validate output & calculate server hazard level
            $validated = $this->predictionValidator->validate($response['data']);
            $durationMs = $response['durasi_ms'] ?? (int) round((microtime(true) - $startTime) * 1000);

            // 5. Store in database inside transaction
            $prediksi = DB::transaction(function () use ($targetDate, $user, $trigger, $model, $payload, $response, $validated, $durationMs) {
                // Expire any existing active predictions for the same date
                PrediksiBirdstrikeAi::query()
                    ->whereDate('tanggal_prediksi', $targetDate)
                    ->where('status_advisory', 'aktif')
                    ->update(['status_advisory' => 'kedaluwarsa']);

                return PrediksiBirdstrikeAi::create([
                    'tanggal_prediksi' => $targetDate,
                    'waktu_analisis' => now(),
                    'user_id' => $user?->id,
                    'trigger' => $trigger,
                    'status_eksekusi' => 'sukses',
                    'error_message' => null,
                    'skor_probabilitas' => $validated['skor_probabilitas'],
                    'level_bahaya' => $validated['level_bahaya'],
                    'jam_rawan_mulai' => $validated['jam_rawan_mulai'],
                    'jam_rawan_selesai' => $validated['jam_rawan_selesai'],
                    'grid_kritis' => $validated['grid_kritis'],
                    'spesies_target' => $validated['spesies_target'],
                    'faktor_penyebab' => $validated['faktor_penyebab'],
                    'ringkasan_eksekutif' => $validated['ringkasan_eksekutif'],
                    'rekomendasi_taktis' => $validated['rekomendasi_taktis'],
                    'confidence_score' => $validated['confidence_score'],
                    'model_version' => $model,
                    'input_payload' => $payload,
                    'raw_response' => $response['raw_response'],
                    'durasi_ms' => $durationMs,
                    'status_advisory' => 'aktif',
                ]);
            });

            // 6. Notify all Admins in-app
            try {
                $admins = User::where('role', 'admin')->get();
                foreach ($admins as $admin) {
                    $admin->notify(new BirdstrikeAdvisoryNotification($prediksi));
                }
            } catch (Exception $notifEx) {
                Log::channel('ai')->warning('Failed to dispatch in-app advisory notification', [
                    'error' => $notifEx->getMessage(),
                ]);
            }

            return $prediksi;

        } catch (Exception $e) {
            $durationMs = (int) round((microtime(true) - $startTime) * 1000);

            // Record failed prediction for audit & fail-safe alerting
            $failedRecord = PrediksiBirdstrikeAi::create([
                'tanggal_prediksi' => $targetDate,
                'waktu_analisis' => now(),
                'user_id' => $user?->id,
                'trigger' => $trigger,
                'status_eksekusi' => 'gagal',
                'error_message' => mb_substr($e->getMessage(), 0, 1000),
                'model_version' => $model,
                'input_payload' => $payload ?? null,
                'durasi_ms' => $durationMs,
                'status_advisory' => 'kedaluwarsa',
            ]);

            throw $e;
        }
    }

    /**
     * Identify species and SOP recommendations via Gemini Vision multimodal.
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    public function identifySpecies(UploadedFile|string $image, ?User $user = null): array
    {
        $model = config('services.gemini.model_vision', 'gemini-3.7-flash');
        $timeout = config('services.gemini.timeout_vision', 20);
        $startTime = microtime(true);

        if (! config('whms_ai.enabled', true)) {
            throw new Exception('Fitur AI dinonaktifkan via konfigurasi AI_ENABLED.');
        }

        if (empty(config('services.gemini.key'))) {
            throw new Exception('GEMINI_API_KEY belum dikonfigurasi.');
        }

        // Get image bytes & mime type
        if ($image instanceof UploadedFile) {
            $mimeType = $image->getMimeType();
            $base64Data = base64_encode(file_get_contents($image->getRealPath()));
            $originalName = $image->getClientOriginalName();
        } else {
            $path = public_path($image);
            if (! File::exists($path)) {
                $path = storage_path('app/public/'.$image);
            }
            if (! File::exists($path)) {
                $path = $image;
            }
            $mimeType = File::mimeType($path) ?: 'image/jpeg';
            $base64Data = base64_encode(File::get($path));
            $originalName = basename($image);
        }

        // Get airport master species catalog for taxonomy matching
        $masterSpecies = JenisSatwa::query()->pluck('nama')->toArray();
        $speciesListStr = implode(', ', array_slice($masterSpecies, 0, 60));

        // Prompt
        $promptPath = resource_path('ai/prompts/species_vision.md');
        $systemPrompt = File::exists($promptPath)
            ? File::get($promptPath)
            : 'Identifikasi spesies satwa pada gambar dan berikan rekomendasi SOP pengusiran ICAO.';

        $systemInstruction = [
            'parts' => [
                ['text' => $systemPrompt],
            ],
        ];

        $contents = [
            [
                'role' => 'user',
                'parts' => [
                    [
                        'text' => "Daftar master satwa bandara yang biasa ditemukan di lokasi:\n{$speciesListStr}\n\nIdentifikasi satwa pada gambar berikut:",
                    ],
                    [
                        'inlineData' => [
                            'mimeType' => $mimeType,
                            'data' => $base64Data,
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->client->generate(
            model: $model,
            contents: $contents,
            systemInstruction: $systemInstruction,
            responseSchema: SpeciesSchema::get(),
            temperature: 0.1,
            timeout: $timeout
        );

        $validated = $this->speciesValidator->validate($response['data']);
        $durationMs = $response['durasi_ms'] ?? (int) round((microtime(true) - $startTime) * 1000);

        // Best candidate info
        $topCandidate = $validated['kandidat'][0] ?? null;
        $namaLokal = $topCandidate['nama_lokal'] ?? null;
        $namaLatin = $topCandidate['nama_latin'] ?? null;
        $confidence = isset($topCandidate['confidence']) ? round((float) $topCandidate['confidence'], 2) : null;

        // Log to database
        $log = IdentifikasiSatwaAi::create([
            'user_id' => $user?->id ?? auth()->id() ?? 1,
            'laporan_id' => null,
            'foto_path' => $originalName,
            'model_version' => $model,
            'hasil' => $validated,
            'nama_lokal' => $namaLokal,
            'nama_latin' => $namaLatin,
            'confidence' => $confidence,
            'diterima' => null,
            'nama_final' => null,
        ]);

        return [
            'id' => $log->id,
            'data' => $validated,
            'durasi_ms' => $durationMs,
        ];
    }
}
