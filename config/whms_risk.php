<?php

/*
|--------------------------------------------------------------------------
| Konfigurasi Risiko Bird Strike (PRD v2.1, spec.md §9)
|--------------------------------------------------------------------------
|
| Semua waktu lapangan (jam laporan, waktu pengusiran, snapshot cuaca)
| disimpan sebagai waktu lokal bandara tanpa zona. Aplikasi sendiri
| berjalan dalam UTC, jadi "sekarang" untuk data lapangan wajib memakai
| zona waktu di bawah ini.
|
*/

return [
    'zona_waktu' => env('AIRPORT_TIMEZONE', 'Asia/Jakarta'),

    'runway' => [
        // Heading magnetik RWY 10/28 Juanda; arah sebaliknya (280°) memberi angin silang yang sama.
        'heading_derajat' => (int) env('AIRPORT_RUNWAY_HEADING', 100),
    ],

    'cuaca' => [
        'url' => 'https://api.open-meteo.com/v1/forecast',
    ],

    /*
    | FR-0.7: peringatan muncul bila data kosong lebih dari `maks_interval_kosong`
    | interval berturut-turut dalam `jendela_jam` terakhir. Observasi hanya dinilai
    | pada jam patroli, karena malam tanpa laporan memang wajar.
    */
    'kualitas_data' => [
        'maks_interval_kosong' => (int) env('DATA_QUALITY_MAX_EMPTY_INTERVALS', 2),
        'jendela_jam' => (int) env('DATA_QUALITY_WINDOW_HOURS', 24),
        'cuaca_interval_menit' => 15,
        'observasi_interval_menit' => 30,
        'observasi_jam_mulai' => (int) env('DATA_QUALITY_PATROL_START_HOUR', 5),
        'observasi_jam_selesai' => (int) env('DATA_QUALITY_PATROL_END_HOUR', 19),
    ],
];
