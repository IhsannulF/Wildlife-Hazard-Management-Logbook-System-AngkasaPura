<?php

namespace App\Services\Ai;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiClient
{
    public function __construct(
        protected ?string $apiKey = null,
        protected ?string $baseUrl = null,
    ) {
        $this->apiKey = $apiKey ?? config('services.gemini.key');
        $this->baseUrl = rtrim($baseUrl ?? config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/');
    }

    /**
     * Send structured generateContent request to Gemini REST API.
     *
     * @param  array<string, mixed>  $contents
     * @param  array<string, mixed>|null  $systemInstruction
     * @param  array<string, mixed>|null  $responseSchema
     *
     * @throws ConnectionException|RequestException|Exception
     */
    public function generate(
        string $model,
        array $contents,
        ?array $systemInstruction = null,
        ?array $responseSchema = null,
        float $temperature = 0.2,
        int $timeout = 45
    ): array {
        if (empty($this->apiKey)) {
            throw new Exception('GEMINI_API_KEY tidak dikonfigurasi pada server.');
        }

        $url = "{$this->baseUrl}/models/{$model}:generateContent";

        $body = [
            'contents' => $contents,
            'generationConfig' => array_filter([
                'temperature' => $temperature,
                'responseMimeType' => 'application/json',
                'responseSchema' => $responseSchema,
            ]),
        ];

        if ($systemInstruction !== null) {
            $body['systemInstruction'] = $systemInstruction;
        }

        $startTime = microtime(true);
        $statusCode = null;

        try {
            /** @var Response $response */
            $response = Http::withHeaders([
                'x-goog-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])
                ->timeout($timeout)
                ->retry([1000, 3000], when: function (Exception $exception, $request) {
                    // Only retry on network/connection errors, 429 (rate limit), or 5xx server errors
                    if ($exception instanceof ConnectionException) {
                        return true;
                    }

                    if ($exception instanceof RequestException && $exception->response !== null) {
                        $status = $exception->response->status();

                        return $status === 429 || $status >= 500;
                    }

                    return false;
                }, throw: false)
                ->post($url, $body);

            $statusCode = $response->status();
            $durationMs = (int) round((microtime(true) - $startTime) * 1000);

            if ($response->failed()) {
                Log::channel('ai')->warning('Gemini API call failed', [
                    'model' => $model,
                    'status' => $statusCode,
                    'duration_ms' => $durationMs,
                    'error' => $response->json('error.message') ?? $response->body(),
                ]);

                throw new Exception("Gemini API error [{$statusCode}]: ".($response->json('error.message') ?? 'Gagal menghubungi layanan AI.'));
            }

            Log::channel('ai')->info('Gemini API call success', [
                'model' => $model,
                'status' => $statusCode,
                'duration_ms' => $durationMs,
            ]);

            $json = $response->json();
            $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (empty($text)) {
                $blockReason = $json['promptFeedback']['blockReason'] ?? null;
                if ($blockReason) {
                    throw new Exception("Respons AI diblokir oleh safety filter: {$blockReason}");
                }

                throw new Exception('Respons AI tidak dapat diproses atau kosong.');
            }

            $decoded = json_decode($text, true);
            if (! is_array($decoded)) {
                throw new Exception('Format keluaran AI bukan JSON yang valid.');
            }

            return [
                'raw_response' => $json,
                'data' => $decoded,
                'durasi_ms' => $durationMs,
            ];
        } catch (Exception $e) {
            $durationMs = (int) round((microtime(true) - $startTime) * 1000);
            Log::channel('ai')->error('Gemini API Exception', [
                'model' => $model,
                'status' => $statusCode,
                'duration_ms' => $durationMs,
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
