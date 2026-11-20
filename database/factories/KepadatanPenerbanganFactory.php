<?php

namespace Database\Factories;

use App\Models\KepadatanPenerbangan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KepadatanPenerbangan>
 */
class KepadatanPenerbanganFactory extends Factory
{
    protected $model = KepadatanPenerbangan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tanggal' => now()->toDateString(),
            'jam' => fake()->unique()->numberBetween(0, 23),
            'jumlah_pergerakan' => fake()->numberBetween(5, 35),
        ];
    }
}
