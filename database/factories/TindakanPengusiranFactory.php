<?php

namespace Database\Factories;

use App\Models\MasterAlatDispersal;
use App\Models\TindakanPengusiran;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TindakanPengusiran>
 */
class TindakanPengusiranFactory extends Factory
{
    protected $model = TindakanPengusiran::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'alat_id' => fn () => MasterAlatDispersal::query()->value('id')
                ?? MasterAlatDispersal::create(['nama_alat' => 'Sirene Uji', 'kategori' => 'Akustik', 'satuan' => 'Kali'])->id,
            'user_id' => User::factory(),
            'waktu' => fake()->dateTimeBetween('-7 days', 'now'),
            'grid' => fake()->randomElement(['K-10', 'K-9', 'D-9', 'E-12']),
            'jumlah_digunakan' => 1,
            'hasil' => fake()->randomElement(array_keys(TindakanPengusiran::HASIL)),
        ];
    }
}
