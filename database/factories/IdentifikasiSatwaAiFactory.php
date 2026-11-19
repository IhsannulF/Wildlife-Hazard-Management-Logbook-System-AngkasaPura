<?php

namespace Database\Factories;

use App\Models\IdentifikasiSatwaAi;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IdentifikasiSatwaAi>
 */
class IdentifikasiSatwaAiFactory extends Factory
{
    protected $model = IdentifikasiSatwaAi::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'laporan_id' => null,
            'foto_path' => 'foto_temuan/test_bird.jpg',
            'model_version' => 'gemini-2.5-flash',
            'hasil' => [
                'terdeteksi_satwa' => true,
                'kandidat' => [
                    [
                        'nama_lokal' => 'Kuntul Kerbau',
                        'nama_latin' => 'Bubulcus ibis',
                        'confidence' => 0.92,
                    ],
                ],
                'rekomendasi_sop' => [
                    [
                        'metode' => 'akustik',
                        'langkah' => 'Bunyikan distress call',
                    ],
                ],
            ],
            'nama_lokal' => 'Kuntul Kerbau',
            'nama_latin' => 'Bubulcus ibis',
            'confidence' => 0.92,
            'diterima' => true,
            'nama_final' => 'Kuntul Kerbau',
        ];
    }
}
