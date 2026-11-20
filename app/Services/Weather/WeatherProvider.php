<?php

namespace App\Services\Weather;

interface WeatherProvider
{
    /**
     * Get weather conditions and 24-hour forecast for coordinates.
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
    public function getWeather(float $lat, float $lng, ?string $date = null): array;
}
