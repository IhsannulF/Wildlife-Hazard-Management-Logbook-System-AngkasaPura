<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrediksiBirdstrikeAi;
use App\Services\Ai\GeminiApiService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AiPrediksiController extends Controller
{
    public function __construct(
        protected ?GeminiApiService $apiService = null
    ) {
        $this->apiService = $apiService ?? new GeminiApiService;
    }

    /**
     * Get latest prediction JSON asynchronously for Dashboard widgets.
     */
    public function latest(): JsonResponse
    {
        $latestSuccess = PrediksiBirdstrikeAi::query()
            ->where('status_eksekusi', 'sukses')
            ->latest('waktu_analisis')
            ->first();

        $lastExecution = PrediksiBirdstrikeAi::query()
            ->latest('waktu_analisis')
            ->first();

        $isExpired = false;
        $dataMinim = false;

        if ($latestSuccess) {
            $diffHours = Carbon::parse($latestSuccess->waktu_analisis)->diffInHours(now());
            $isExpired = ($latestSuccess->status_advisory === 'kedaluwarsa' || $diffHours >= 24);

            $meta = $latestSuccess->input_payload['meta'] ?? [];
            if (($meta['logbook_tersedia_hari'] ?? 30) < 14 || ($meta['jumlah_histori'] ?? 10) < 5 || ! ($meta['traffic_tersedia'] ?? true)) {
                $dataMinim = true;
            }
        }

        return response()->json([
            'success' => true,
            'has_prediction' => $latestSuccess !== null,
            'prediksi' => $latestSuccess,
            'is_expired' => $isExpired,
            'data_minim' => $dataMinim,
            'last_execution' => $lastExecution ? [
                'status' => $lastExecution->status_eksekusi,
                'error_message' => $lastExecution->error_message,
                'waktu' => $lastExecution->waktu_analisis?->toIso8601String(),
            ] : null,
        ]);
    }

    /**
     * Trigger on-demand manual prediction.
     */
    public function run(Request $request): JsonResponse
    {
        $request->validate([
            'catatan_habitat' => ['nullable', 'string', 'max:500'],
        ]);

        $userId = $request->user()->id;
        $today = Carbon::today()->toDateString();

        // 1. Check cooldown (10 minutes)
        $cooldownKey = "ai_manual_cooldown_admin_{$userId}";
        $lastRunTime = Cache::get($cooldownKey);
        $cooldownSeconds = config('whms_ai.cooldown_minutes', 10) * 60;

        if ($lastRunTime && (time() - $lastRunTime) < $cooldownSeconds) {
            $remaining = $cooldownSeconds - (time() - $lastRunTime);
            $minutes = ceil($remaining / 60);

            return response()->json([
                'success' => false,
                'message' => "Analisis manual memiliki cooldown 10 menit. Harap tunggu {$minutes} menit lagi sebelum menjalankan ulang.",
                'remaining_seconds' => $remaining,
            ], 429);
        }

        // 2. Check daily quota (max 10 runs per day)
        $maxDaily = config('whms_ai.max_manual_runs_per_day', 10);
        $manualRunsToday = PrediksiBirdstrikeAi::query()
            ->whereDate('created_at', $today)
            ->where('trigger', 'manual')
            ->count();

        if ($manualRunsToday >= $maxDaily) {
            return response()->json([
                'success' => false,
                'message' => "Batas kuota analisis manual harian tercapai ({$maxDaily} kali per hari). Analisis otomatis berikutnya akan berjalan besok pukul 05:00 WIB.",
            ], 429);
        }

        try {
            $prediksi = $this->apiService->predictBirdstrike(
                date: $today,
                habitatNotes: $request->catatan_habitat,
                trigger: 'manual',
                user: $request->user()
            );

            // Record cooldown timestamp
            Cache::put($cooldownKey, time(), $cooldownSeconds);

            return response()->json([
                'success' => true,
                'message' => 'Analisis prediksi birdstrike AI Gemini berhasil dijalankan.',
                'prediksi' => $prediksi,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menjalankan analisis AI: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Acknowledge AI safety advisory.
     */
    public function acknowledge(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $prediksi = PrediksiBirdstrikeAi::findOrFail($id);

        $prediksi->update([
            'status_advisory' => 'diakui',
            'diakui_oleh' => $request->user()->id,
            'diakui_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Advisory keselamatan berhasil diakui oleh Safety Manager / Admin.',
                'prediksi' => $prediksi,
            ]);
        }

        return back()->with('success', 'Advisory keselamatan berhasil diakui.');
    }

    /**
     * History and accuracy evaluation page.
     */
    public function index(Request $request): View
    {
        $query = PrediksiBirdstrikeAi::query()
            ->with(['user', 'acknowledgedBy'])
            ->latest('waktu_analisis');

        if ($request->filled('level')) {
            $query->where('level_bahaya', $request->level);
        }

        if ($request->filled('status')) {
            $query->where('status_eksekusi', $request->status);
        }

        $predictions = $query->paginate(15)->withQueryString();

        // Accuracy evaluation metrics
        $totalPredictions = PrediksiBirdstrikeAi::where('status_eksekusi', 'sukses')->count();
        $highOrCritical = PrediksiBirdstrikeAi::where('status_eksekusi', 'sukses')
            ->whereIn('level_bahaya', ['tinggi', 'kritis'])
            ->count();

        $incidentConfirmed = PrediksiBirdstrikeAi::where('status_eksekusi', 'sukses')
            ->whereIn('level_bahaya', ['tinggi', 'kritis'])
            ->where('insiden_terjadi', true)
            ->count();

        $accuracyPercent = $highOrCritical > 0
            ? round(($incidentConfirmed / $highOrCritical) * 100, 1)
            : 0;

        $runsToday = PrediksiBirdstrikeAi::whereDate('created_at', Carbon::today())->count();
        $manualRunsToday = PrediksiBirdstrikeAi::whereDate('created_at', Carbon::today())
            ->where('trigger', 'manual')
            ->count();

        $metrics = [
            'total_prediksi' => $totalPredictions,
            'tinggi_kritis' => $highOrCritical,
            'diikuti_insiden' => $incidentConfirmed,
            'akurasi_persen' => $accuracyPercent,
            'panggilan_hari_ini' => $runsToday,
            'manual_hari_ini' => $manualRunsToday,
            'kuota_manual_max' => config('whms_ai.max_manual_runs_per_day', 10),
        ];

        return view('admin.ai.riwayat', compact('predictions', 'metrics'));
    }
}
