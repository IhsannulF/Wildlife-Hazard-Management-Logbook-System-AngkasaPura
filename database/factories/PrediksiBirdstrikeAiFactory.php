<?php

namespace Database\Factories;

use App\Models\PrediksiBirdstrikeAi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrediksiBirdstrikeAi>
 */
class PrediksiBirdstrikeAiFactory extends Factory
{
    protected $model = PrediksiBirdstrikeAi::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tanggal_prediksi' => now()->toDateString(),
            'waktu_analisis' => now(),
            'user_id' => null,
            'trigger' => 'terjadwal',
            'status_eksekusi' => 'sukses',
            'error_message' => null,
            'skor_probabilitas' => 0.45,
            'level_bahaya' => 'sedang',
            'jam_rawan_mulai' => '06:00',
            'jam_rawan_selesai' => '08:30',
            'grid_kritis' => ['K-8', 'D-9'],
            'spesies_target' => [
                [
                    'nama_lokal' => 'Kuntul Kerbau',
                    'nama_latin' => 'Bubulcus ibis',
                    'risiko_ancaman' => 'Kawanan mencari makan di rumput basah',
                ],
            ],
            'faktor_penyebab' => [
                'Aktivitas fajar dan rumput basah pasca hujan',
                'Kepadatan pergerakan pesawat jam sibuk pagi',
            ],
            'ringkasan_eksekutif' => 'Potensi birdstrike sedang pada pagi hari di sekitar threshold runway 10.',
            'rekomendasi_taktis' => [
                'Tingkatkan patroli bird dispersal di grid K-8 pukul 06:00 - 08:30 WIB.',
                'Gunakan bird distress call akustik sebelum jam sibuk keberangkatan.',
            ],
            'confidence_score' => 0.85,
            'model_version' => 'gemini-2.5-flash',
            'input_payload' => ['sample' => true],
            'raw_response' => ['sample' => true],
            'durasi_ms' => 1250,
            'status_advisory' => 'aktif',
            'diakui_oleh' => null,
            'diakui_at' => null,
            'insiden_terjadi' => null,
        ];
    }
}
