<?php

namespace App\Console\Commands;

use App\Models\PrediksiBirdstrikeAi;
use App\Services\Ai\GeminiApiService;
use Exception;
use Illuminate\Console\Command;

class PredictBirdstrikeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'birdstrike:predict 
                            {--trigger=terjadwal : Pemicu inferensi (terjadwal|manual)} 
                            {--date= : Tanggal target prediksi format YYYY-MM-DD (default hari ini)}
                            {--force : Jalankan inferensi paksa meskipun sudah ada prediksi aktif untuk tanggal ini}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menjalankan inferensi AI Gemini untuk memprediksi probabilitas birdstrike harian bandara';

    /**
     * Execute the console command.
     */
    public function handle(GeminiApiService $apiService): int
    {
        $trigger = $this->option('trigger') ?: 'terjadwal';
        $targetDate = $this->option('date') ?: date('Y-m-d');
        $force = (bool) $this->option('force');

        $this->info("Menjalankan analisis prediksi birdstrike untuk tanggal {$targetDate} (Trigger: {$trigger})...");

        if (! config('whms_ai.enabled', true)) {
            $this->warn('Fitur AI dinonaktifkan via konfigurasi AI_ENABLED=false. Melewati proses inferensi.');

            return self::SUCCESS;
        }

        if (empty(config('services.gemini.key'))) {
            $this->warn('Kunci GEMINI_API_KEY belum dikonfigurasi. Melewati proses inferensi secara aman.');

            return self::SUCCESS;
        }

        $existing = PrediksiBirdstrikeAi::whereDate('tanggal_prediksi', $targetDate)
            ->where('status_advisory', 'aktif')
            ->first();

        if ($existing && ! $force) {
            $this->warn("Sudah ada prediksi aktif untuk tanggal {$targetDate} (ID: {$existing->id}, Level: ".strtoupper($existing->level_bahaya ?? 'N/A').'). Gunakan opsi --force untuk memperbarui.');

            return self::SUCCESS;
        }

        try {
            $prediksi = $apiService->predictBirdstrike(
                date: $targetDate,
                habitatNotes: null,
                trigger: $trigger
            );

            $this->info('Inferensi AI Gemini berhasil diselesaikan.');
            $this->table(
                ['Parameter', 'Nilai'],
                [
                    ['ID Prediksi', $prediksi->id],
                    ['Tanggal Target', $prediksi->tanggal_prediksi?->toDateString()],
                    ['Level Bahaya', strtoupper($prediksi->level_bahaya ?? 'N/A')],
                    ['Skor Probabilitas', $prediksi->skor_probabilitas],
                    ['Confidence Score', $prediksi->confidence_score],
                    ['Jam Rawan', "{$prediksi->jam_rawan_mulai} - {$prediksi->jam_rawan_selesai} WIB"],
                    ['Grid Kritis', implode(', ', $prediksi->grid_kritis ?? [])],
                    ['Durasi Komputasi', "{$prediksi->durasi_ms} ms"],
                ]
            );

            return self::SUCCESS;

        } catch (Exception $e) {
            $this->error('Gagal menjalankan inferensi birdstrike: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
