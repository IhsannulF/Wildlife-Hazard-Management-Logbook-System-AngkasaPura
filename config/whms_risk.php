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

        // Posisi runway pada gridmap yang sudah ada (admin/statistik): kolom 5–25, garis tengah
        // di batas bawah baris G. Perkiraan dari gridmap, wajib dikalibrasi dengan aerodrome chart.
        'kolom_awal' => 5,
        'kolom_akhir' => 25,
        'baris_garis_tengah' => 'G',
    ],

    /*
    | Ukuran sel gridmap A–L × 1–29. Pada gambar gridmap runway 3.000 m membentang
    | 21 kolom (5–25) dan sel berbentuk persegi, jadi 1 sel ≈ 3.000 ÷ 21 ≈ 142,9 m.
    */
    'grid' => [
        'lebar_sel_m' => 142.9,
        'tinggi_sel_m' => 142.9,
    ],

    /*
    | F1 skor zonasi grid (PRD v2.1 §7.1): S = N̄ × W_jarak × W_spesies × W_attractor.
    | N̄ = rata-rata ekor burung per pengamatan di grid; W_spesies dirata-rata menurut jumlah ekor.
    */
    'zonasi' => [
        'jendela_hari' => 180,

        // Bobot jarak menurun mulus: W = min + (maks − min) × e^(−jarak / skala).
        // ±70 m dari runway ≈ 2,6; 500 m ≈ 1,1; 1 km ≈ 0,5. Kerucut approach selalu `w_jarak_approach`.
        'w_jarak' => [
            'maks' => 3.0,
            'min' => 0.3,
            'skala_m' => 400,
        ],
        'w_jarak_approach' => 3.0,

        // Tampilan heatmap: aktivitas burung tiap grid disebar ke sel sekitarnya (kernel Gauss),
        // lalu dikali bobot jarak sel itu sendiri, sehingga sel dekat runway lebih cepat memerah.
        'heatmap' => [
            'sigma_sel' => 1.0,
            'radius_sel' => 2,
        ],

        // Kerucut approach/departure di luar ujung runway: setengah lebar awal + divergensi per meter.
        'approach' => [
            'setengah_lebar_m' => 150,
            'divergensi' => 0.15,
        ],

        // Nama satwa dicocokkan dengan kata kunci; burung yang tidak cocok dianggap kecil.
        'w_spesies' => [
            'besar' => ['bobot' => 2.0, 'kata_kunci' => ['cangak', 'kuntul', 'blekok', 'elang', 'kokokan', 'bangau']],
            'sedang' => ['bobot' => 1.5, 'kata_kunci' => ['pecuk', 'dara laut', 'cabak', 'kareo', 'belibis', 'gagak', 'merpati', 'dara', 'alap']],
            'kecil' => ['bobot' => 1.0, 'kata_kunci' => []],
        ],

        // F8 (attractor) belum ada; bobot 1,2 dipakai setelah master attractor tersedia.
        'w_attractor' => 1.0,

        // Batas bawah tiap kelas; dapat diubah admin di halaman statistik (tabel `pengaturan`).
        // PRD awal 3/9/15 membuat hampir semua grid merah karena kawanan lapangan rata-rata ±11 ekor;
        // 10/20/30 memberi sebaran ±55/24/12/9% pada data 12 bulan. Kalibrasi ulang dengan data riil.
        'ambang' => [
            'sedang' => 10.0,
            'tinggi' => 20.0,
            'sangat_tinggi' => 30.0,
        ],
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
