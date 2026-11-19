<?php

namespace App\Http\Controllers;

use App\Http\Requests\IdentifySpeciesRequest;
use App\Models\IdentifikasiSatwaAi;
use App\Models\JenisSatwa;
use App\Services\Ai\GeminiApiService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AiIdentifikasiController extends Controller
{
    public function __construct(
        protected ?GeminiApiService $apiService = null
    ) {
        $this->apiService = $apiService ?? new GeminiApiService;
    }

    /**
     * Identify wildlife species from photo upload using Gemini Vision Multimodal.
     */
    public function identify(IdentifySpeciesRequest $request): JsonResponse
    {
        $file = $request->file('foto');
        $minConfidence = config('whms_ai.vision_min_confidence', 0.60);

        try {
            $result = $this->apiService->identifySpecies($file, $request->user());
            $data = $result['data'];
            $logId = $result['id'];

            $terdeteksi = (bool) ($data['terdeteksi_satwa'] ?? false);
            $topCandidate = $terdeteksi ? ($data['kandidat'][0] ?? null) : null;

            $matchedSatwa = null;
            $isUnlisted = false;

            if ($topCandidate && ! empty($topCandidate['nama_lokal'])) {
                $searchName = trim($topCandidate['nama_lokal']);

                // Find matching satwa in master catalog
                $matchedSatwa = JenisSatwa::query()
                    ->whereRaw('LOWER(nama) = ?', [strtolower($searchName)])
                    ->orWhere('nama', 'like', "%{$searchName}%")
                    ->first();

                if (! $matchedSatwa) {
                    $isUnlisted = true;
                }
            }

            $confidence = $topCandidate['confidence'] ?? 0;
            $confidenceLow = ($terdeteksi && $confidence < $minConfidence);

            return response()->json([
                'success' => true,
                'id' => $logId,
                'terdeteksi_satwa' => $terdeteksi,
                'kandidat' => $data['kandidat'] ?? [],
                'rekomendasi_sop' => $data['rekomendasi_sop'] ?? [],
                'catatan' => $data['catatan'] ?? null,
                'matched_satwa_id' => $matchedSatwa?->id,
                'matched_satwa_nama' => $matchedSatwa?->nama_satwa,
                'is_unlisted' => $isUnlisted,
                'confidence_low' => $confidenceLow,
                'min_confidence' => $minConfidence,
                'durasi_ms' => $result['durasi_ms'] ?? 0,
            ]);

        } catch (Exception $e) {
            Log::channel('ai')->error('Species identification failed', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengidentifikasi foto satwa: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark AI identification result acceptance or rejection.
     */
    public function markResult(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'diterima' => ['required', 'boolean'],
            'nama_final' => ['nullable', 'string', 'max:100'],
            'laporan_id' => ['nullable', 'integer'],
        ]);

        $log = IdentifikasiSatwaAi::query()
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $log->update([
            'diterima' => (bool) $request->diterima,
            'nama_final' => $request->nama_final ?: $log->nama_lokal,
            'laporan_id' => $request->laporan_id ?: $log->laporan_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status penerimaan hasil identifikasi AI berhasil diperbarui.',
        ]);
    }
}
