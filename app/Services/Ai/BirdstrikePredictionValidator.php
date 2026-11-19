<?php

namespace App\Services\Ai;

use App\Support\GridCode;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BirdstrikePredictionValidator
{
    /**
     * Calculate danger level deterministically on server side.
     */
    public static function calculateHazardLevel(float $score): string
    {
        if ($score < 0.30) {
            return 'rendah';
        }

        if ($score < 0.60) {
            return 'sedang';
        }

        if ($score < 0.85) {
            return 'tinggi';
        }

        return 'kritis';
    }

    /**
     * Validate and normalize AI prediction output.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    public function validate(array $data): array
    {
        $validator = Validator::make($data, [
            'probability_score' => ['required', 'numeric', 'min:0', 'max:1'],
            'hazard_level' => ['required', 'string'],
            'critical_time_window' => ['required', 'string'],
            'critical_zones' => ['required', 'array'],
            'critical_zones.*' => ['string'],
            'target_species' => ['required', 'array'],
            'target_species.*.nama_lokal' => ['required', 'string'],
            'target_species.*.nama_latin' => ['required', 'string'],
            'target_species.*.risiko_ancaman' => ['required', 'string'],
            'contributing_factors' => ['required', 'array'],
            'contributing_factors.*' => ['string'],
            'confidence_score' => ['required', 'numeric', 'min:0', 'max:1'],
            'executive_summary' => ['required', 'string'],
            'actionable_recommendations' => ['required', 'array', 'min:1'],
            'actionable_recommendations.*' => ['string'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $probabilityScore = (float) $data['probability_score'];
        $confidenceScore = (float) $data['confidence_score'];
        $serverHazardLevel = self::calculateHazardLevel($probabilityScore);

        if (isset($data['hazard_level']) && $data['hazard_level'] !== $serverHazardLevel) {
            Log::channel('ai')->info('Discrepancy in AI hazard level vs server level', [
                'ai_level' => $data['hazard_level'],
                'server_level' => $serverHazardLevel,
                'score' => $probabilityScore,
            ]);
        }

        // Normalize critical zones & drop invalid ones
        $normalizedZones = [];
        foreach ($data['critical_zones'] as $zone) {
            $norm = GridCode::normalize($zone);
            if (GridCode::isValid($norm)) {
                $normalizedZones[] = $norm;
            }
        }
        $normalizedZones = array_values(array_unique($normalizedZones));

        // Parse critical time window
        [$jamMulai, $jamSelesai] = $this->parseTimeWindow($data['critical_time_window'] ?? '');

        return [
            'skor_probabilitas' => round($probabilityScore, 2),
            'level_bahaya' => $serverHazardLevel,
            'jam_rawan_mulai' => $jamMulai,
            'jam_rawan_selesai' => $jamSelesai,
            'grid_kritis' => $normalizedZones,
            'spesies_target' => $data['target_species'],
            'faktor_penyebab' => $data['contributing_factors'],
            'ringkasan_eksekutif' => $data['executive_summary'],
            'rekomendasi_taktis' => array_values(array_slice($data['actionable_recommendations'], 0, 6)),
            'confidence_score' => round($confidenceScore, 2),
        ];
    }

    /**
     * Parse time window string (e.g. "06:00 - 08:30 WIB", "06:00-08:00") into [mulai, selesai].
     *
     * @return array{0: ?string, 1: ?string}
     */
    protected function parseTimeWindow(string $window): array
    {
        if (preg_match('/(\d{1,2}:\d{2})\s*[-–—]\s*(\d{1,2}:\d{2})/', $window, $matches)) {
            $start = str_pad($matches[1], 5, '0', STR_PAD_LEFT);
            $end = str_pad($matches[2], 5, '0', STR_PAD_LEFT);

            return [$start, $end];
        }

        return [null, null];
    }
}
