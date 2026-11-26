<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Pilar 4: Scheduled inference otomatis setiap pagi
Schedule::command('birdstrike:predict --trigger=terjadwal')
    ->dailyAt('05:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->onOneServer();

// PRD v2.1 FR-0.5: Snapshot cuaca bandara tiap 15 menit untuk variabel prediksi bird strike
Schedule::command('weather:snapshot')
    ->everyFifteenMinutes()
    ->withoutOverlapping(10)
    ->onOneServer();
