<?php

namespace App\Services\Weather;

use App\Models\Laporan;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenMeteoProvider implements WeatherProvider
{
    /**
     * @return array{
     *     kondisi_saat_ini: string,
     *     suhu_celsius: ?float,
     *     kelembapan_persen: ?int,
     *     kecepatan_angin_knot: ?float,
     *     arah_angin_derajat: ?int,
     *     curah_hujan_mm: ?float,
     *     jarak_pandang_km: ?float,
     *     prakiraan_24_jam: array<int, mixed>,
     *     sumber: string
     * }
     */
    public function getWeather(float $lat, float $lng, ?string $date = null): array
    {
        $cacheKey = sprintf('whms_weather_%s_%s_%s', round($lat, 4), round($lng, 4), $date ?? date('Y-m-d'));

        return Cache::remember($cacheKey, 1800, function () use ($lat, $lng) {
            try {
                $response = Http::timeout(6)->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'current' => 'temperature_2m,relative_humidity_2m,precipitation,wind_speed_10m,wind_direction_10m',
                    'hourly' => 'temperature_2m,precipitation_probability,precipitation,wind_speed_10m',
                    'wind_speed_unit' => 'kn',
                    'forecast_days' => 1,
                    'timezone' => 'Asia/Jakarta',
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $current = $json['current'] ?? [];
                    $hourly = $json['hourly'] ?? [];

                    $precip = (float) ($current['precipitation'] ?? 0);
                    $kondisi = 'Cerah / Berawan';
                    if ($precip > 5.0) {
                        $kondisi = 'Hujan Deras';
                    } elseif ($precip > 0.5) {
                        $kondisi = 'Hujan Ringan / Gerimis';
                    }

                    // Format 24 hour forecast simplified
                    $forecast24h = [];
                    $times = $hourly['time'] ?? [];
                    $temps = $hourly['temperature_2m'] ?? [];
                    $precips = $hourly['precipitation'] ?? [];
                    $winds = $hourly['wind_speed_10m'] ?? [];

                    for ($i = 0; $i < min(24, count($times)); $i++) {
                        $hourStr = substr($times[$i], 11, 5);
                        $forecast24h[] = [
                            'jam' => $hourStr,
                            'suhu_c' => $temps[$i] ?? null,
                            'curah_hujan_mm' => $precips[$i] ?? 0,
                            'angin_knot' => $winds[$i] ?? null,
                        ];
                    }

                    return [
                        'kondisi_saat_ini' => $kondisi,
                        'suhu_celsius' => isset($current['temperature_2m']) ? (float) $current['temperature_2m'] : null,
                        'kelembapan_persen' => isset($current['relative_humidity_2m']) ? (int) $current['relative_humidity_2m'] : null,
                        'kecepatan_angin_knot' => isset($current['wind_speed_10m']) ? (float) $current['wind_speed_10m'] : null,
                        'arah_angin_derajat' => isset($current['wind_direction_10m']) ? (int) $current['wind_direction_10m'] : null,
                        'curah_hujan_mm' => $precip,
                        'jarak_pandang_km' => 10.0,
                        'prakiraan_24_jam' => $forecast24h,
                        'sumber' => 'Open-Meteo Live API',
                    ];
                }
            } catch (Exception $e) {
                Log::channel('ai')->warning('WeatherProvider OpenMeteo failed, using fallback', [
                    'error' => $e->getMessage(),
                ]);
            }

            return $this->fallbackFromLogbook();
        });
    }

    /**
     * Fallback to most recent logbook entry condition.
     *
     * @return array{
     *     kondisi_saat_ini: string,
     *     suhu_celsius: ?float,
     *     kelembapan_persen: ?int,
     *     kecepatan_angin_knot: ?float,
     *     arah_angin_derajat: ?int,
     *     curah_hujan_mm: ?float,
     *     jarak_pandang_km: ?float,
     *     prakiraan_24_jam: array<int, mixed>,
     *     sumber: string
     * }
     */
    protected function fallbackFromLogbook(): array
    {
        $latestLaporan = Laporan::query()->latest('tanggal')->first();
        $cuaca = $latestLaporan?->kondisi_cuaca ?: 'Cerah';

        return [
            'kondisi_saat_ini' => $cuaca,
            'suhu_celsius' => 30.0,
            'kelembapan_persen' => 75,
            'kecepatan_angin_knot' => 6.0,
            'arah_angin_derajat' => 110,
            'curah_hujan_mm' => str_contains(strtolower($cuaca), 'hujan') ? 2.5 : 0.0,
            'jarak_pandang_km' => 9.0,
            'prakiraan_24_jam' => [],
            'sumber' => 'Logbook Terakhir (Fallback)',
        ];
    }
}
