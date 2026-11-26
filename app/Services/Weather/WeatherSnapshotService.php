<?php

namespace App\Services\Weather;

use App\Models\CuacaSnapshot;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Mengambil kondisi cuaca terkini bandara dari Open-Meteo dan menyimpannya sebagai snapshot 15 menit
 * (PRD v2.1 FR-0.5). Nilai yang tidak dikirim API disimpan null, bukan diisi angka perkiraan.
 */
class WeatherSnapshotService
{
    public const SOURCE = 'Open-Meteo';

    private const CURRENT_VARIABLES = [
        'temperature_2m',
        'relative_humidity_2m',
        'precipitation',
        'weather_code',
        'cloud_cover',
        'wind_speed_10m',
        'wind_direction_10m',
        'wind_gusts_10m',
        'visibility',
    ];

    /**
     * Ambil cuaca terkini dan simpan; snapshot untuk waktu yang sama diperbarui, bukan diduplikasi.
     *
     * @throws RuntimeException bila Open-Meteo gagal dihubungi atau responsnya tidak lengkap
     */
    public function capture(): CuacaSnapshot
    {
        $response = Http::connectTimeout(5)
            ->timeout(10)
            ->retry([500, 2000])
            ->get(config('whms_risk.cuaca.url'), [
                'latitude' => config('whms_ai.airport.lat'),
                'longitude' => config('whms_ai.airport.lng'),
                'current' => implode(',', self::CURRENT_VARIABLES),
                'wind_speed_unit' => 'kn',
                'timezone' => config('whms_risk.zona_waktu'),
            ]);

        $current = $response->json('current');
        if (! is_array($current) || empty($current['time'])) {
            throw new RuntimeException('Respons Open-Meteo tidak memuat data cuaca terkini.');
        }

        $attributes = $this->mapCurrent($current);

        return CuacaSnapshot::updateOrCreate(['waktu' => $attributes['waktu']], $attributes);
    }

    /**
     * Komponen angin tegak lurus landasan (knot). Arah angin adalah arah datangnya angin.
     */
    public static function crosswindKnots(?float $speedKnots, ?int $directionDegrees, int $runwayHeadingDegrees): ?float
    {
        if ($speedKnots === null || $directionDegrees === null) {
            return null;
        }

        $angle = deg2rad($directionDegrees - $runwayHeadingDegrees);

        return round(abs($speedKnots * sin($angle)), 1);
    }

    /**
     * @param  array<string, mixed>  $current
     * @return array<string, mixed>
     */
    private function mapCurrent(array $current): array
    {
        $number = fn (string $key): ?float => isset($current[$key]) && is_numeric($current[$key]) ? (float) $current[$key] : null;
        $integer = fn (string $key): ?int => ($value = $number($key)) === null ? null : (int) round($value);

        $windSpeed = $number('wind_speed_10m');
        $windDirection = $integer('wind_direction_10m');
        $visibilityMeters = $number('visibility');

        return [
            'waktu' => Carbon::parse($current['time'])->format('Y-m-d H:i:s'),
            'kode_cuaca' => $integer('weather_code'),
            'hujan_mm' => $number('precipitation') ?? 0.0,
            'jarak_pandang_km' => $visibilityMeters === null ? null : round($visibilityMeters / 1000, 2),
            'arah_angin_derajat' => $windDirection,
            'kecepatan_angin_kt' => $windSpeed,
            'hembusan_kt' => $number('wind_gusts_10m'),
            'angin_silang_kt' => self::crosswindKnots($windSpeed, $windDirection, (int) config('whms_risk.runway.heading_derajat')),
            'suhu_c' => $number('temperature_2m'),
            'kelembapan_persen' => $integer('relative_humidity_2m'),
            'tutupan_awan_persen' => $integer('cloud_cover'),
            'sumber' => self::SOURCE,
        ];
    }
}
