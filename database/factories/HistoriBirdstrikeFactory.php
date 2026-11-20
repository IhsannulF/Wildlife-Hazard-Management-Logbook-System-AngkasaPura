<?php

namespace Database\Factories;

use App\Models\HistoriBirdstrike;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HistoriBirdstrike>
 */
class HistoriBirdstrikeFactory extends Factory
{
    protected $model = HistoriBirdstrike::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tanggal_kejadian' => fake()->dateTimeBetween('-60 days', 'now')->format('Y-m-d'),
            'jam_kejadian' => fake()->time('H:i'),
            'kode_runway' => fake()->randomElement(['10', '28', '07L', '25R']),
            'titik_lokasi' => fake()->randomElement(['touchdown', 'roll', 'threshold', 'grid']),
            'koordinat_lat' => -7.3798350,
            'koordinat_lng' => 112.7875420,
            'jenis_pesawat' => fake()->randomElement(['B737-800', 'A320-200', 'ATR 72-600', 'B777-300ER']),
            'fase_penerbangan' => fake()->randomElement(['take_off', 'landing', 'approach', 'climb', 'taxi']),
            'bagian_pesawat' => fake()->randomElement(['engine', 'windshield', 'wing', 'radome', 'gear']),
            'spesies_satwa' => fake()->randomElement(['Kuntul Kerbau', 'Blekok Sawah', 'Layang-layang Asia', 'Burung Gereja']),
            'jumlah_satwa' => fake()->numberBetween(1, 5),
            'tingkat_kerusakan' => fake()->randomElement(['nihil', 'minor', 'substansial']),
            'keterangan' => 'Insiden uji coba factory',
            'created_by' => User::factory(),
        ];
    }
}
