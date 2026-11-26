<?php

namespace App\Console\Commands;

use App\Services\Weather\WeatherSnapshotService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class WeatherSnapshotCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weather:snapshot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menyimpan snapshot cuaca bandara (Open-Meteo) untuk variabel prediksi bird strike';

    /**
     * Execute the console command.
     */
    public function handle(WeatherSnapshotService $service): int
    {
        try {
            $snapshot = $service->capture();
        } catch (Throwable $e) {
            Log::warning('Snapshot cuaca Open-Meteo gagal', ['error' => $e->getMessage()]);
            $this->error('Gagal mengambil cuaca: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Snapshot %s WIB: kode %s, hujan %s mm, angin %s kt dari %s° (silang %s kt), jarak pandang %s km.',
            $snapshot->waktu->format('Y-m-d H:i'),
            $snapshot->kode_cuaca ?? '-',
            $snapshot->hujan_mm,
            $snapshot->kecepatan_angin_kt ?? '-',
            $snapshot->arah_angin_derajat ?? '-',
            $snapshot->angin_silang_kt ?? '-',
            $snapshot->jarak_pandang_km ?? '-',
        ));

        return self::SUCCESS;
    }
}
