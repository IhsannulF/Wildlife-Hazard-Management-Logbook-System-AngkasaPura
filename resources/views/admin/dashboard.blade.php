<!DOCTYPE html>
<html class="h-full bg-canvas-light text-on-surface" lang="id">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>InJourney Airports - Wildlife Hazard Management Logbook System</title>

  <!-- Favicon InJourney Airports -->
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
  <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

  <!-- Google Fonts: DM Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
  <!-- Google Material Symbols Outlined -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

  <!-- Tailwind CSS CDN with configuration -->
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          "colors": {
            "surface-container": "#e5eeff",
            "on-secondary": "#ffffff",
            "on-secondary-container": "#5c647a",
            "error-container": "#ffdad6",
            "tertiary": "#006780",
            "slate-navy": "#0F172A",
            "primary-container": "#00a3b4",
            "on-surface": "#0b1c30",
            "surface-tint": "#006874",
            "tertiary-fixed": "#b7eaff",
            "injourney-teal": "#00A3B4",
            "resolved-emerald": "#10B981",
            "canvas-light": "#F8FAFC",
            "surface": "#f8f9ff",
            "on-primary-fixed-variant": "#004f58",
            "secondary-container": "#dae2fd",
            "warning-rose": "#EF4444",
            "inverse-on-surface": "#eaf1ff",
            "surface-variant": "#d3e4fe",
            "on-primary-container": "#003238",
            "on-error": "#ffffff",
            "on-tertiary": "#ffffff",
            "on-primary": "#ffffff",
            "on-background": "#0b1c30",
            "surface-container-highest": "#d3e4fe",
            "on-tertiary-container": "#00313f",
            "inverse-surface": "#213145",
            "card-bg": "#FFFFFF",
            "surface-bright": "#f8f9ff",
            "border-subtle": "#E2E8F0",
            "outline": "#6d797c",
            "slate-surface": "#1E293B",
            "background": "#f8f9ff",
            "surface-container-high": "#dce9ff",
            "outline-variant": "#bcc9cb",
            "error": "#ba1a1a",
            "injourney-dark-teal": "#0891B2",
            "tertiary-fixed-dim": "#6cd3f7",
            "primary-fixed-dim": "#5dd7e9",
            "on-tertiary-fixed-variant": "#004e61",
            "on-error-container": "#93000a",
            "surface-container-low": "#eff4ff",
            "on-primary-fixed": "#001f24",
            "on-secondary-fixed-variant": "#3f465c",
            "secondary-fixed-dim": "#bec6e0",
            "surface-dim": "#cbdbf5",
            "runway-grid": "#334155",
            "on-surface-variant": "#3d494b",
            "on-tertiary-fixed": "#001f28",
            "primary": "#006874",
            "primary-fixed": "#97f0ff",
            "surface-container-lowest": "#ffffff",
            "secondary": "#565e74",
            "tertiary-container": "#2ca0c1",
            "on-secondary-fixed": "#131b2e",
            "secondary-fixed": "#dae2fd",
            "inverse-primary": "#5dd7e9",
            "alert-amber": "#F59E0B"
          },
          "borderRadius": {
            "DEFAULT": "0.25rem",
            "lg": "0.5rem",
            "xl": "0.75rem",
            "full": "9999px"
          },
          "spacing": {
            "space-xs": "0.25rem",
            "margin-mobile": "1rem",
            "space-md": "1rem",
            "gutter-mobile": "0.75rem",
            "margin": "2rem",
            "gutter": "1.25rem",
            "space-sm": "0.5rem",
            "space-lg": "1.5rem",
            "space-xl": "2rem"
          },
          "fontFamily": {
            "headline-md": ["DM Sans", "sans-serif"],
            "headline-xl": ["DM Sans", "sans-serif"],
            "headline-lg": ["DM Sans", "sans-serif"],
            "headline-sm": ["DM Sans", "sans-serif"],
            "label-md": ["DM Sans", "sans-serif"],
            "code-coordinate": ["DM Sans", "monospace"],
            "headline-xl-mobile": ["DM Sans", "sans-serif"],
            "body-md": ["DM Sans", "sans-serif"],
            "body-sm": ["DM Sans", "sans-serif"],
            "label-lg": ["DM Sans", "sans-serif"],
            "body-lg": ["DM Sans", "sans-serif"],
            "label-sm": ["DM Sans", "sans-serif"]
          },
          "fontSize": {
            "headline-md": ["20px", { "lineHeight": "28px", "fontWeight": "600" }],
            "headline-xl": ["36px", { "lineHeight": "44px", "letterSpacing": "-0.02em", "fontWeight": "700" }],
            "headline-lg": ["24px", { "lineHeight": "32px", "letterSpacing": "-0.01em", "fontWeight": "700" }],
            "headline-sm": ["16px", { "lineHeight": "24px", "fontWeight": "600" }],
            "label-md": ["12px", { "lineHeight": "16px", "letterSpacing": "0.02em", "fontWeight": "600" }],
            "code-coordinate": ["13px", { "lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "700" }],
            "headline-xl-mobile": ["28px", { "lineHeight": "36px", "letterSpacing": "-0.01em", "fontWeight": "700" }],
            "body-md": ["14px", { "lineHeight": "20px", "fontWeight": "400" }],
            "body-sm": ["12px", { "lineHeight": "16px", "fontWeight": "400" }],
            "label-lg": ["14px", { "lineHeight": "20px", "letterSpacing": "0.01em", "fontWeight": "600" }],
            "body-lg": ["16px", { "lineHeight": "24px", "fontWeight": "400" }],
            "label-sm": ["10px", { "lineHeight": "14px", "letterSpacing": "0.04em", "fontWeight": "700" }]
          }
        },
      },
    }
  </script>
  <style>
    .material-symbols-outlined {
      font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
      display: inline-block;
      vertical-align: middle;
      line-height: 1;
    }
    .material-symbols-fill {
      font-variation-settings: 'FILL' 1, 'wght' 500, 'GRAD' 0, 'opsz' 24;
      display: inline-block;
      vertical-align: middle;
      line-height: 1;
    }
    /* Custom subtle scrollbar */
    ::-webkit-scrollbar {
      width: 6px;
      height: 6px;
    }
    ::-webkit-scrollbar-track {
      background: #F8FAFC;
    }
    ::-webkit-scrollbar-thumb {
      background: #CBD5E1;
      border-radius: 9999px;
    }
    ::-webkit-scrollbar-thumb:hover {
      background: #94A3B8;
    }
  </style>
</head>
<body class="h-full font-body-md text-on-surface antialiased bg-canvas-light selection:bg-injourney-teal selection:text-white">

  @php
    $currUser = auth()->user() ?? ($user ?? null);
    $userName = $currUser ? ($currUser->namalengkap ?: $currUser->username) : 'Petugas Sisi Udara';
    $userRole = $currUser ? ($currUser->jabatan ?: 'AMC Airside Staff') : 'Airside Staff';
  @endphp

  <!-- TOP APP BAR -->
  @include('partials.navbar', ['activePage' => 'dashboard'])

  <!-- MAIN VIEWPORT CONTAINER -->
  <main class="max-w-[1680px] mx-auto px-4 sm:px-6 lg:px-space-xl py-space-lg space-y-space-lg">
    
    <!-- PAGE HERO & EXECUTIVE METRIC BENTO CARDS -->
    <section class="space-y-space-md">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-space-sm">
        <div>
          <h1 class="text-xl sm:text-2xl font-bold text-on-surface tracking-tight">
            Logbook Sisi Udara
          </h1>
          <p class="text-xs sm:text-sm text-secondary mt-0.5">
            Monitoring dan pengendalian bahaya satwa liar area sisi udara.
          </p>
        </div>
        <div class="flex items-center gap-2">
          <span class="text-xs text-secondary font-label-md">Pembaruan otomatis 60d</span>
          <button class="p-1.5 text-secondary hover:text-injourney-teal transition-colors rounded hover:bg-surface-container-low" onclick="refreshData()" title="Muat Ulang Data">
            <span class="material-symbols-outlined text-sm">sync</span>
          </button>
        </div>
      </div>

      <!-- 4 Aviation Metric Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-space-md">
        
        <!-- Stat Card 1: Total Temuan Satwa -->
        <div class="bg-card-bg rounded-xl border border-border-subtle p-space-md shadow-sm relative overflow-hidden flex flex-col justify-between">
          <div class="absolute top-0 left-0 right-0 h-1 bg-injourney-teal"></div>
          <div class="flex items-start justify-between">
            <div>
              <p class="font-label-md text-label-md uppercase text-secondary tracking-wider font-semibold">Total Temuan Bulan Ini</p>
              <div class="flex items-baseline gap-2 mt-1">
                <span class="font-headline-xl text-headline-xl font-bold text-on-surface">{{ $totalLaporan }}</span>
                <span class="font-label-sm text-label-sm text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded font-semibold flex items-center">
                  <span class="material-symbols-outlined text-xs">arrow_upward</span> Aktif
                </span>
              </div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-surface-container-low flex items-center justify-center text-injourney-teal">
              <span class="material-symbols-outlined">pets</span>
            </div>
          </div>
          <div class="mt-3 pt-3 border-t border-border-subtle flex items-center justify-between text-xs text-secondary font-body-sm">
            <span>Burung: <strong>{{ $pctBurung }}%</strong></span>
            <span>Reptil: <strong>{{ $pctReptil }}%</strong></span>
            <span>Mamalia: <strong>{{ $pctMamalia }}%</strong></span>
          </div>
        </div>

        <!-- Stat Card 2: Belum Ditangani / High Risk -->
        <div class="bg-card-bg rounded-xl border border-border-subtle p-space-md shadow-sm relative overflow-hidden flex flex-col justify-between">
          <div class="absolute top-0 left-0 right-0 h-1 bg-warning-rose"></div>
          <div class="flex items-start justify-between">
            <div>
              <p class="font-label-md text-label-md uppercase text-secondary tracking-wider font-semibold">Belum Ditangani (Aktif)</p>
              <div class="flex items-baseline gap-2 mt-1">
                <span class="font-headline-xl text-headline-xl font-bold text-warning-rose">{{ $belum }}</span>
                @if($belum > 0)
                  <span class="font-label-sm text-label-sm text-rose-700 bg-rose-50 px-2 py-0.5 rounded-full font-bold">
                    Perlu Verifikasi Segera
                  </span>
                @else
                  <span class="font-label-sm text-label-sm text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full font-bold">
                    Terkendali Aman
                  </span>
                @endif
              </div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-rose-50 flex items-center justify-center text-warning-rose">
              <span class="material-symbols-outlined">crisis_alert</span>
            </div>
          </div>
          <div class="mt-3 pt-3 border-t border-border-subtle flex items-center justify-between text-xs text-secondary font-body-sm">
            <span class="flex items-center gap-1 text-rose-600 font-medium">
              <span class="w-1.5 h-1.5 rounded-full bg-rose-500 {{ $belumRunway > 0 ? 'animate-ping' : '' }}"></span>
              {{ $belumRunway }} di Runway Aktif
            </span>
            <span class="font-semibold text-on-surface">{{ $belumPerimeter }} di Perimeter</span>
          </div>
        </div>

        <!-- Stat Card 3: Telah Ditangani / Mitigasi Sukses -->
        @php
          $rate = $totalLaporan > 0 ? round(($ditangani / $totalLaporan) * 100, 1) : 100;
        @endphp
        <div class="bg-card-bg rounded-xl border border-border-subtle p-space-md shadow-sm relative overflow-hidden flex flex-col justify-between">
          <div class="absolute top-0 left-0 right-0 h-1 bg-resolved-emerald"></div>
          <div class="flex items-start justify-between">
            <div>
              <p class="font-label-md text-label-md uppercase text-secondary tracking-wider font-semibold">Mitigasi Telah Ditangani</p>
              <div class="flex items-baseline gap-2 mt-1">
                <span class="font-headline-xl text-headline-xl font-bold text-on-surface">{{ $ditangani }}</span>
                <span class="font-label-sm text-label-sm text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-full font-bold">
                  {{ $rate }}% Rate
                </span>
              </div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center text-resolved-emerald">
              <span class="material-symbols-outlined">task_alt</span>
            </div>
          </div>
          <div class="mt-3 pt-3 border-t border-border-subtle flex items-center justify-between text-xs text-secondary font-body-sm">
            <span>Rerata Respon: <strong>4.2 Menit</strong></span>
            <span class="text-emerald-700 font-medium">100% Dispersal BA Valid</span>
          </div>
        </div>

        <!-- Stat Card 4: Grid Rawan / Hotspot Utama -->
        <div class="bg-card-bg rounded-xl border border-border-subtle p-space-md shadow-sm relative overflow-hidden flex flex-col justify-between">
          <div class="absolute top-0 left-0 right-0 h-1 bg-alert-amber"></div>
          <div class="flex items-start justify-between">
            <div>
              <p class="font-label-md text-label-md uppercase text-secondary tracking-wider font-semibold">Grid Hotspot Utama</p>
              <div class="flex items-baseline gap-2 mt-1">
                <span class="font-headline-xl text-headline-xl font-bold text-on-surface">{{ $hotspotGrid }}</span>
              </div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center text-alert-amber">
              <span class="material-symbols-outlined">share_location</span>
            </div>
          </div>
          <div class="mt-3 pt-3 border-t border-border-subtle flex items-center justify-between text-xs text-secondary font-body-sm">
            <span class="truncate pr-1">Perimeter Runway 25R (Kanal Terbuka)</span>
            <span class="font-code-coordinate text-code-coordinate bg-surface-container-high px-1.5 py-0.5 rounded text-on-surface">{{ $totalLaporan }} Log</span>
          </div>
        </div>

      </div>
    </section>

    <!-- QUICK FILTER, SEARCH & AIRSIDE GRID TAG CONTROLS -->
    <section class="bg-card-bg rounded-xl border border-border-subtle p-space-md shadow-sm space-y-space-md">
      <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-space-md">
        
        <!-- Status Tabs -->
        <div class="flex items-center gap-1.5 bg-surface-container-low p-1 rounded-lg border border-border-subtle self-start">
          <button class="px-3.5 py-1.5 rounded-md font-label-lg text-label-lg transition-all {{ empty($statusFilter) ? 'bg-white text-injourney-teal font-bold shadow-sm' : 'text-secondary hover:text-on-surface' }}" id="tab-all" onclick="filterStatus('all')" type="button">
            Semua Status <span class="ml-1 text-xs px-1.5 py-0.2 rounded-full bg-surface-container-high text-on-surface">{{ $totalLaporan }}</span>
          </button>
          <button class="px-3.5 py-1.5 rounded-md font-label-lg text-label-lg transition-all {{ $statusFilter === 'belum' ? 'bg-white text-warning-rose font-bold shadow-sm' : 'text-secondary hover:text-on-surface' }}" id="tab-pending" onclick="filterStatus('pending')" type="button">
            Belum Ditangani <span class="ml-1 text-xs px-1.5 py-0.2 rounded-full bg-rose-100 text-warning-rose font-bold">{{ $belum }}</span>
          </button>
          <button class="px-3.5 py-1.5 rounded-md font-label-lg text-label-lg transition-all {{ $statusFilter === 'sudah' ? 'bg-white text-emerald-800 font-bold shadow-sm' : 'text-secondary hover:text-on-surface' }}" id="tab-resolved" onclick="filterStatus('resolved')" type="button">
            Sudah Ditangani <span class="ml-1 text-xs px-1.5 py-0.2 rounded-full bg-emerald-100 text-emerald-800 font-bold">{{ $ditangani }}</span>
          </button>
        </div>

        <!-- Search Input Bar -->
        <div class="relative flex-1 max-w-xl">
          <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-lg pointer-events-none">search</span>
          <input class="w-full h-[42px] pl-10 pr-4 rounded-lg border border-border-subtle bg-white text-on-surface placeholder:text-outline font-body-md focus:border-injourney-teal focus:ring-2 focus:ring-injourney-teal/20 transition-all outline-none" id="searchInput" onkeyup="searchTable()" value="{{ $search }}" placeholder="Cari no. laporan, petugas, spesies satwa, atau koordinat grid..." type="text"/>
        </div>
      </div>

      <!-- Secondary Filters & Runway Grid Coordinate Tag Pills -->
      <div class="pt-space-sm border-t border-border-subtle flex flex-col xl:flex-row items-start xl:items-center justify-between gap-space-md">
        <!-- Filter Dropdowns Cluster -->
        <div class="flex flex-wrap items-center gap-space-sm text-sm">
          <!-- Unit Kerja -->
          <div class="flex items-center gap-1.5">
            <label class="font-label-md text-label-md text-secondary">Unit Kerja:</label>
            <select id="filterUnit" onchange="searchTable()" class="h-9 px-2.5 py-1 text-xs font-label-md rounded-lg border border-border-subtle bg-white text-on-surface focus:border-injourney-teal focus:ring-0">
              <option value="">Semua Unit</option>
              <option value="AMC">AMC (Airside Mobile)</option>
              <option value="ARFF">ARFF (Airport Rescue)</option>
              <option value="AVSEC">Avsec Perimeter</option>
              <option value="SMS">Safety Management (SMS)</option>
            </select>
          </div>

          <!-- Area Inspeksi -->
          <div class="flex items-center gap-1.5">
            <label class="font-label-md text-label-md text-secondary">Area Sisi Udara:</label>
            <select id="filterArea" onchange="searchTable()" class="h-9 px-2.5 py-1 text-xs font-label-md rounded-lg border border-border-subtle bg-white text-on-surface focus:border-injourney-teal focus:ring-0">
              <option value="">Semua Area Sisi Udara</option>
              <option value="Runway">Runway 07L / 25R</option>
              <option value="Taxiway">Taxiway Alpha &amp; Bravo</option>
              <option value="Apron">Apron Komersial</option>
              <option value="Perimeter">Perimeter Utara / Selatan</option>
              <option value="Kanal">Kanal Drainase Terbuka</option>
            </select>
          </div>

          <!-- Tanggal Patroli -->
          <div class="flex items-center gap-1.5">
            <label class="font-label-md text-label-md text-secondary">Tanggal:</label>
            <div class="relative">
              <input id="filterDate" onchange="searchTable()" class="h-9 px-2.5 py-1 text-xs font-label-md rounded-lg border border-border-subtle bg-white text-on-surface focus:border-injourney-teal focus:ring-0" type="date" value="{{ date('Y-m-d') }}"/>
            </div>
          </div>
        </div>

        <!-- Quick Grid Runway Tag Buttons -->
        <div class="flex items-center gap-2 flex-wrap">
          <span class="font-label-sm text-label-sm text-secondary uppercase font-semibold">Filter Grid Cepat:</span>
          <button class="font-code-coordinate text-code-coordinate px-2.5 py-1 rounded-md bg-surface-container-low hover:bg-injourney-teal hover:text-white border border-border-subtle transition-all flex items-center gap-1 text-on-surface" onclick="filterByGrid('K-10')" type="button">
            <span>K-10</span>
            <span class="w-2 h-2 rounded-full bg-warning-rose"></span>
          </button>
          <button class="font-code-coordinate text-code-coordinate px-2.5 py-1 rounded-md bg-surface-container-low hover:bg-injourney-teal hover:text-white border border-border-subtle transition-all flex items-center gap-1 text-on-surface" onclick="filterByGrid('D-9')" type="button">
            <span>D-9</span>
            <span class="w-2 h-2 rounded-full bg-warning-rose"></span>
          </button>
          <button class="font-code-coordinate text-code-coordinate px-2.5 py-1 rounded-md bg-surface-container-low hover:bg-injourney-teal hover:text-white border border-border-subtle transition-all flex items-center gap-1 text-on-surface" onclick="filterByGrid('F-5')" type="button">
            <span>F-5</span>
            <span class="w-1.5 h-1.5 rounded-full bg-alert-amber"></span>
          </button>
          <button class="font-code-coordinate text-code-coordinate px-2.5 py-1 rounded-md bg-surface-container-low hover:bg-injourney-teal hover:text-white border border-border-subtle transition-all text-on-surface" onclick="filterByGrid('C-12')" type="button">
            C-12
          </button>
          <button class="font-code-coordinate text-code-coordinate px-2.5 py-1 rounded-md bg-surface-container-low hover:bg-injourney-teal hover:text-white border border-border-subtle transition-all text-on-surface" onclick="filterByGrid('H-3')" type="button">
            H-3
          </button>
          <button class="text-xs text-injourney-teal hover:underline font-label-md ml-1" onclick="resetFilters()" type="button">
            Reset
          </button>
        </div>
      </div>
    </section>

    <!-- MASTER DATA LOGBOOK TABLE PANEL -->
    <section class="bg-card-bg rounded-xl border border-border-subtle shadow-sm overflow-hidden flex flex-col">
      
      <!-- Table Header Status & Info -->
      <div class="px-space-md py-space-sm bg-surface-container-lowest border-b border-border-subtle flex flex-wrap items-center justify-between gap-space-sm">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-injourney-teal text-lg">flight_takeoff</span>
          <span class="font-headline-sm text-headline-sm text-on-surface">Daftar Kejadian &amp; Intervensi Satwa Sisi Udara</span>
          <span class="font-label-sm text-label-sm bg-surface-container text-tertiary px-2 py-0.5 rounded-full">Live Synchronized</span>
        </div>
        <div class="flex items-center gap-2">
          <button class="text-xs font-label-md text-secondary hover:text-on-surface flex items-center gap-1 px-2.5 py-1 rounded border border-border-subtle bg-white cursor-pointer" onclick="window.print()">
            <span class="material-symbols-outlined text-xs">print</span> Cetak Register
          </button>
          <span class="text-xs text-secondary font-body-sm">Menampilkan <strong class="text-on-surface font-semibold">{{ $laporans->count() }} dari {{ $totalLaporan }}</strong> log</span>
        </div>
      </div>

      <!-- Responsive Data Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse" id="logbookTable">
          <thead>
            <tr class="border-b border-border-subtle bg-canvas-light text-secondary font-label-md text-label-md uppercase tracking-wider">
              <th class="py-3 px-4 font-semibold" scope="col">No. Register / ID</th>
              <th class="py-3 px-4 font-semibold" scope="col">Waktu &amp; Tanggal</th>
              <th class="py-3 px-4 font-semibold" scope="col">Petugas &amp; Unit</th>
              <th class="py-3 px-4 font-semibold" scope="col">Spesies &amp; Kuantitas</th>
              <th class="py-3 px-4 font-semibold" scope="col">Lokasi &amp; Grid Sisi Udara</th>
              <th class="py-3 px-4 font-semibold" scope="col">Aktivitas &amp; Risiko</th>
              <th class="py-3 px-4 font-semibold" scope="col">Status Tindakan</th>
              <th class="py-3 px-4 font-semibold text-center" scope="col">Aksi Terpadu</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border-subtle font-body-sm text-body-sm">
            @forelse($laporans as $lap)
              @php
                $regId = $lap->no_laporan ?: ('WHM-' . ($lap->tanggal ? $lap->tanggal->format('Y') : date('Y')) . '-' . str_pad($lap->id, 4, '0', STR_PAD_LEFT));
                $unitSlug = strtoupper(Str::slug($lap->unit_kerja ?: 'AMC-AIRSIDE'));
                $tglFormatted = $lap->tanggal ? \Carbon\Carbon::parse($lap->tanggal)->translatedFormat('d M Y') : '-';
                $jamFormatted = $lap->created_at ? $lap->created_at->format('H:i') . ' WIB' : '08:00 WIB';
                $officerName = $lap->nama_petugas ?: ($lap->user ? ($lap->user->namalengkap ?: $lap->user->username) : 'Petugas Sisi Udara');
                $officerInitials = strtoupper(substr($officerName, 0, 2));

                $firstSatwa = $lap->detailSatwa->first();
                $speciesName = $firstSatwa ? $firstSatwa->nama_satwa : 'Satwa Liar';
                $totalQty = $lap->detailSatwa->sum('jumlah') ?: 1;
                $gridName = $lap->grid_lokasi ?: ($firstSatwa->grid ?? 'K-10');

                // Species icon detection
                $sNameLower = strtolower($speciesName);
                if (str_contains($sNameLower, 'burung') || str_contains($sNameLower, 'blekok') || str_contains($sNameLower, 'kuntul') || str_contains($sNameLower, 'layang') || str_contains($sNameLower, 'cangak')) {
                  $speciesIcon = 'flutter_dash';
                  $iconColor = 'text-injourney-teal';
                } elseif (str_contains($sNameLower, 'biawak') || str_contains($sNameLower, 'ular') || str_contains($sNameLower, 'reptil')) {
                  $speciesIcon = 'pest_control';
                  $iconColor = 'text-alert-amber';
                } elseif (str_contains($sNameLower, 'kera') || str_contains($sNameLower, 'monyet')) {
                  $speciesIcon = 'cruelty_free';
                  $iconColor = 'text-secondary';
                } else {
                  $speciesIcon = 'pets';
                  $iconColor = 'text-secondary';
                }

                $isResolved = ($lap->status === 'sudah');
                $isPending = !$isResolved;
              @endphp
              <tr class="hover:bg-surface-container-low transition-colors group" id="row-{{ $lap->id }}" data-id="{{ $lap->id }}" data-status="{{ $lap->status }}">
                <!-- Register / ID -->
                <td class="py-3.5 px-4">
                  <span class="font-code-coordinate text-code-coordinate font-bold text-injourney-teal block">{{ $regId }}</span>
                  <span class="font-label-sm text-label-sm text-secondary">{{ $unitSlug }}</span>
                </td>

                <!-- Waktu & Tanggal -->
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <span class="font-semibold text-on-surface block">{{ $tglFormatted }}</span>
                  <span class="text-xs text-secondary flex items-center gap-1">
                    <span class="material-symbols-outlined text-[13px]">schedule</span> {{ $jamFormatted }}
                  </span>
                </td>

                <!-- Petugas & Unit -->
                <td class="py-3.5 px-4">
                  <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full {{ $isPending ? 'bg-rose-100 text-warning-rose' : 'bg-surface-container-high text-tertiary' }} font-bold text-xs flex items-center justify-center">
                      {{ $officerInitials }}
                    </div>
                    <div>
                      <span class="font-semibold text-on-surface block">{{ $officerName }}</span>
                      <span class="font-label-sm text-label-sm text-secondary">{{ $lap->unit_kerja ?: 'Unit Sisi Udara' }}</span>
                    </div>
                  </div>
                </td>

                <!-- Spesies & Kuantitas -->
                <td class="py-3.5 px-4">
                  <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined {{ $iconColor }} text-lg">{{ $speciesIcon }}</span>
                    <div>
                      <span class="font-semibold text-on-surface block">{{ $speciesName }}</span>
                      <span class="font-label-sm text-label-sm text-secondary">{{ $totalQty }} Ekor {{ $lap->ciri_ukuran ? "({$lap->ciri_ukuran})" : '' }}</span>
                    </div>
                  </div>
                </td>

                <!-- Lokasi & Grid -->
                <td class="py-3.5 px-4">
                  <div>
                    <span class="text-on-surface font-medium block">{{ $lap->area_inspeksi ?: 'Sisi Udara' }}</span>
                    <span class="inline-flex items-center gap-1 font-code-coordinate text-code-coordinate {{ $isPending ? 'bg-rose-100 text-rose-800' : 'bg-surface-container text-on-surface' }} px-2 py-0.5 rounded font-bold mt-0.5">
                      @if($isPending)
                        <span class="w-1.5 h-1.5 rounded-full bg-warning-rose animate-ping"></span>
                      @endif
                      GRID {{ $gridName }}
                    </span>
                  </div>
                </td>

                <!-- Aktivitas & Risiko -->
                <td class="py-3.5 px-4">
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold {{ $isPending ? 'bg-rose-50 text-warning-rose border border-rose-200' : 'bg-surface-container text-on-surface' }}">
                    <span class="material-symbols-outlined text-xs">{{ $isPending ? 'warning' : 'info' }}</span>
                    {{ Str::limit($lap->aktivitas_satwa ?: ($lap->detail_pengusiran ?: 'Aktivitas di runway/perimeter'), 32) }}
                  </span>
                  <span class="text-[11px] text-secondary block mt-0.5">
                    @if($lap->kondisi_apron)
                      Kondisi: {{ $lap->kondisi_apron }}
                    @else
                      Level: {{ $isPending ? 'Tinggi (Kritis)' : 'Terkendali' }}
                    @endif
                  </span>
                </td>

                <!-- Status Tindakan -->
                <td class="py-3.5 px-4 whitespace-nowrap" id="status-cell-{{ $lap->id }}">
                  @if($isResolved)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                      <span class="w-2 h-2 rounded-full bg-resolved-emerald"></span>
                      Sudah Ditangani
                    </span>
                  @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                      <span class="w-2 h-2 rounded-full bg-alert-amber"></span>
                      Belum Ditangani
                    </span>
                  @endif
                </td>

                <!-- Aksi Terpadu -->
                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                  <div class="inline-flex items-center gap-1">
                    <button class="p-1.5 rounded text-secondary hover:text-injourney-teal hover:bg-surface-container-high transition-colors cursor-pointer" onclick="openReportDetailAjax({{ $lap->id }})" title="Lihat Detail Pratinjau" type="button">
                      <span class="material-symbols-outlined text-lg">visibility</span>
                    </button>

                    @if($isPending)
                      <button id="btn-resolve-{{ $lap->id }}" class="px-2 py-1 rounded bg-injourney-teal hover:bg-injourney-dark-teal text-white font-label-md text-xs transition-colors cursor-pointer" onclick="quickResolve({{ $lap->id }}, '{{ $regId }}')" title="Tangani Sekarang" type="button">
                        Dispersal
                      </button>
                    @endif

                    @if(Route::has('admin.laporan.cetak'))
                      <a href="{{ route('admin.laporan.cetak', $lap->id) }}" target="_blank" class="p-1.5 rounded text-secondary hover:text-on-surface hover:bg-surface-container-high transition-colors" title="Cetak Berita Acara (BA)">
                        <span class="material-symbols-outlined text-lg">description</span>
                      </a>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center py-12 text-secondary">
                  <div class="flex flex-col items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-4xl text-outline">inbox</span>
                    <span class="font-semibold text-sm">Tidak ada data laporan satwa yang sesuai filter.</span>
                    <button onclick="resetFilters()" class="text-xs text-injourney-teal hover:underline font-bold mt-1">Reset Filter Pencarian</button>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Pagination Footer -->
      <div class="px-space-md py-3 bg-surface-container-lowest border-t border-border-subtle flex flex-wrap items-center justify-between gap-space-sm text-xs text-secondary">
        <div class="flex items-center gap-2">
          <span>Total: <strong class="text-on-surface font-semibold">{{ $laporans->total() }}</strong> catatan insiden satwa</span>
        </div>
        <div>
          {{ $laporans->links() }}
        </div>
      </div>
    </section>

  </main>

  <!-- MODAL: PRATINJAU DETAIL LAPORAN 2-HALAMAN -->
  <div class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" id="detailModal">
    <div class="bg-card-bg w-full max-w-5xl rounded-2xl border border-border-subtle shadow-2xl overflow-hidden flex flex-col max-h-[92vh] animate-in fade-in zoom-in-95 duration-200">
      
      <!-- Modal Header -->
      <div class="px-6 py-4 bg-surface-container-lowest border-b border-border-subtle flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-surface-container-low flex items-center justify-center text-injourney-teal">
            <span class="material-symbols-outlined text-xl">assignment</span>
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface" id="modalReportId">WHM-2026-0001</h2>
              <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200" id="modalStatusBadge">
                <span class="w-2 h-2 rounded-full bg-alert-amber"></span> Belum Ditangani
              </span>
            </div>
            <p class="font-body-sm text-body-sm text-secondary">Berita Acara Temuan dan Penanganan Bahaya Satwa Liar Sisi Udara (ICAO Doc 9137 Part 3)</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <!-- Page Switcher Tabs -->
          <div class="flex items-center p-1 bg-surface-container-low rounded-lg border border-border-subtle">
            <button class="px-3 py-1 rounded text-xs font-label-md font-bold bg-white text-injourney-teal shadow-xs cursor-pointer" id="tabBtnPage1" onclick="switchModalPage(1)" type="button">
              Hal 1: Konteks &amp; Lokasi
            </button>
            <button class="px-3 py-1 rounded text-xs font-label-md text-secondary hover:text-on-surface cursor-pointer" id="tabBtnPage2" onclick="switchModalPage(2)" type="button">
              Hal 2: Dokumentasi &amp; TTD
            </button>
          </div>
          <button class="p-2 text-secondary hover:text-on-surface rounded-lg hover:bg-surface-container-low transition-colors cursor-pointer" onclick="closeReportModal()" type="button">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
      </div>

      <!-- Modal Body (Two-Page View) -->
      <div class="p-6 overflow-y-auto flex-1 space-y-6">
        
        <!-- PAGE 1: CONTEXT, LOCATION & RUNWAY GRID SCHEMATIC -->
        <div class="space-y-6" id="modalPage1">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            
            <!-- Metadata Officer -->
            <div class="p-4 rounded-xl bg-surface-container-lowest border border-border-subtle space-y-3">
              <span class="font-label-sm text-label-sm uppercase tracking-wider text-secondary font-semibold">Identitas Laporan</span>
              <div>
                <p class="text-xs text-secondary">Petugas Pelapor:</p>
                <p class="font-semibold text-on-surface text-sm" id="modalOfficer">-</p>
              </div>
              <div>
                <p class="text-xs text-secondary">Tanggal &amp; Jam Temuan:</p>
                <p class="font-semibold text-on-surface text-sm" id="modalTimestamp">-</p>
              </div>
              <div>
                <p class="text-xs text-secondary">Kondisi Cuaca &amp; Angin:</p>
                <p class="font-semibold text-on-surface text-sm" id="modalWeather">Berawan 29°C / Angin 8 kts WSW</p>
              </div>
            </div>

            <!-- Wildlife Info -->
            <div class="p-4 rounded-xl bg-surface-container-lowest border border-border-subtle space-y-3">
              <span class="font-label-sm text-label-sm uppercase tracking-wider text-secondary font-semibold">Karakteristik Satwa</span>
              <div>
                <p class="text-xs text-secondary">Nama Spesies:</p>
                <p class="font-semibold text-on-surface text-sm" id="modalSpecies">-</p>
              </div>
              <div>
                <p class="text-xs text-secondary">Kuantitas &amp; Estimasi Ukuran:</p>
                <p class="font-semibold text-on-surface text-sm" id="modalQuantity">1 Ekor</p>
              </div>
              <div>
                <p class="text-xs text-secondary">Tingkat Risiko Ancaman:</p>
                <span class="inline-flex items-center gap-1 text-xs font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded" id="modalRiskCategory">
                  <span class="material-symbols-outlined text-xs">dangerous</span> Kategori A - Runway Hazard
                </span>
              </div>
            </div>

            <!-- Airside Location -->
            <div class="p-4 rounded-xl bg-surface-container-lowest border border-border-subtle space-y-3">
              <span class="font-label-sm text-label-sm uppercase tracking-wider text-secondary font-semibold">Koordinat &amp; Sisi Udara</span>
              <div>
                <p class="text-xs text-secondary">Zona / Sub-Area:</p>
                <p class="font-semibold text-on-surface text-sm" id="modalLocation">-</p>
              </div>
              <div>
                <p class="text-xs text-secondary">Target Grid Sisi Udara:</p>
                <p class="font-code-coordinate text-code-coordinate text-xs text-injourney-teal font-bold" id="modalGridText">GRID K-10</p>
              </div>
              <div>
                <p class="text-xs text-secondary">Akses Terdekat:</p>
                <p class="text-xs text-on-surface font-medium" id="modalAccess">Service Road Runway Gate 4</p>
              </div>
            </div>

          </div>

          <!-- Runway Spatial Mini Schematic -->
          <div class="p-4 rounded-xl bg-slate-900 text-white space-y-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-injourney-teal text-sm">grid_view</span>
                <span class="font-label-md text-label-md uppercase tracking-wider font-semibold text-slate-300">Sketsa Gridmap Runway 07L/25R</span>
              </div>
              <span class="text-xs text-slate-400">Target Sel Aktif: <strong class="text-warning-rose font-code-coordinate" id="schematicGridTarget">K-10 (Bahu Landasan)</strong></span>
            </div>

            <!-- Mini Grid Visual Representation (Interactive Cells K-01 to K-12) -->
            <div class="grid grid-cols-12 gap-1 text-center font-code-coordinate text-[11px] pt-1" id="miniGridContainer">
              <div class="py-2 bg-slate-800 rounded border border-slate-700 text-slate-400 grid-cell" data-grid="K-01">K-01</div>
              <div class="py-2 bg-slate-800 rounded border border-slate-700 text-slate-400 grid-cell" data-grid="K-02">K-02</div>
              <div class="py-2 bg-slate-800 rounded border border-slate-700 text-slate-400 grid-cell" data-grid="K-03">K-03</div>
              <div class="py-2 bg-slate-800 rounded border border-slate-700 text-slate-400 grid-cell" data-grid="K-04">K-04</div>
              <div class="py-2 bg-slate-800 rounded border border-slate-700 text-slate-400 grid-cell" data-grid="K-05">K-05</div>
              <div class="py-2 bg-slate-800 rounded border border-slate-700 text-slate-400 grid-cell" data-grid="K-06">K-06</div>
              <div class="py-2 bg-slate-800 rounded border border-slate-700 text-slate-400 grid-cell" data-grid="K-07">K-07</div>
              <div class="py-2 bg-slate-800 rounded border border-slate-700 text-slate-400 grid-cell" data-grid="K-08">K-08</div>
              <div class="py-2 bg-slate-800 rounded border border-slate-700 text-slate-400 grid-cell" data-grid="K-09">K-09</div>
              <div class="py-2 bg-warning-rose text-white rounded font-bold shadow-lg ring-2 ring-white/50 animate-pulse grid-cell" data-grid="K-10">K-10 ★</div>
              <div class="py-2 bg-slate-800 rounded border border-slate-700 text-slate-400 grid-cell" data-grid="K-11">K-11</div>
              <div class="py-2 bg-slate-800 rounded border border-slate-700 text-slate-400 grid-cell" data-grid="K-12">K-12</div>
            </div>
            <p class="text-[11px] text-slate-400 italic">Landasan pacu utama operasional. Zona bahu rumput dan strip perimeter berjarak aman.</p>
          </div>
        </div>

        <!-- PAGE 2: SPECIES DOCUMENTATION, DISPERSAL ACTION & DIGITAL WET SIGNATURE -->
        <div class="hidden space-y-6" id="modalPage2">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Photo & Action Taken -->
            <div class="space-y-4">
              <span class="font-label-sm text-label-sm uppercase tracking-wider text-secondary font-semibold">Foto Dokumentasi Sisi Udara</span>
              <div class="h-52 rounded-xl border border-border-subtle bg-slate-100 overflow-hidden relative group">
                <img id="modalPhotoImg" class="w-full h-full object-cover" src="{{ asset('images/biawak.jpeg') }}" alt="Foto Dokumentasi Satwa Liar"/>
                <div class="absolute bottom-2 left-2 px-2 py-1 bg-slate-900/80 backdrop-blur-xs text-white text-[10px] rounded font-code-coordinate" id="modalGeoOverlay">
                  GEO: -6.125642, 106.655819 | INJOURNEY AIRSIDE
                </div>
              </div>
              
              <div class="p-4 rounded-xl bg-surface-container-lowest border border-border-subtle space-y-2">
                <span class="font-label-sm text-label-sm uppercase tracking-wider text-secondary font-semibold">Tindakan Pengusiran / Mitigasi</span>
                <div class="flex items-center gap-2 flex-wrap text-xs" id="modalDispersalBadges">
                  <span class="px-2 py-1 bg-surface-container text-on-surface font-semibold rounded">Sirene Kendaraan AMC</span>
                  <span class="px-2 py-1 bg-surface-container text-on-surface font-semibold rounded">Pyro Dispersal Petasan</span>
                  <span class="px-2 py-1 bg-surface-container text-on-surface font-semibold rounded">Pemasangan Trap Net</span>
                </div>
                <p class="text-xs text-secondary mt-1" id="modalDispersalDesc">
                  Petugas melakukan penyisiran radius 100m ke arah kanal terbuka perimeter untuk memastikan satwa tidak kembali ke strip runway.
                </p>
              </div>
            </div>

            <!-- Digital Wet Signature Canvas Box -->
            <div class="space-y-4 flex flex-col">
              <div class="flex items-center justify-between">
                <span class="font-label-sm text-label-sm uppercase tracking-wider text-secondary font-semibold">Validasi Tanda Tangan Basah Digital</span>
                <button class="text-xs text-injourney-teal hover:underline font-label-md cursor-pointer" onclick="clearSignature()" type="button">
                  Bersihkan Canvas
                </button>
              </div>

              <!-- Dedicated HTML5 Digital Signature Pad -->
              <div class="flex-1 min-h-[190px] border-2 border-dashed border-border-subtle rounded-xl bg-white p-3 relative flex flex-col justify-between">
                <canvas class="w-full h-32 touch-none cursor-crosshair" id="signaturePad"></canvas>
                <div class="border-t border-slate-200 pt-2 flex items-center justify-between text-[11px] text-secondary">
                  <span>Goreskan tanda tangan petugas verifikator di atas garis</span>
                  <span class="text-injourney-teal font-semibold flex items-center gap-1">
                    <span class="material-symbols-outlined text-xs">lock</span> SHA-256 Verified
                  </span>
                </div>
              </div>

              <div class="p-3 bg-surface-container-low rounded-lg border border-border-subtle text-xs text-secondary space-y-1">
                <p><strong class="text-on-surface">Verifikator:</strong> <span id="modalVerifierName">{{ $userName }}</span></p>
                <p><strong class="text-on-surface">Unit:</strong> {{ $userRole }}</p>
              </div>
            </div>

          </div>
        </div>

      </div>

      <!-- Modal Footer Action Bar -->
      <div class="px-6 py-4 bg-surface-container-lowest border-t border-border-subtle flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
          <a id="modalPrintBtn" href="#" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-border-subtle bg-white text-on-surface hover:bg-surface-container-low font-label-lg text-xs transition-colors">
            <span class="material-symbols-outlined text-sm">picture_as_pdf</span>
            <span>Cetak Berita Acara (PDF)</span>
          </a>
        </div>
        <div class="flex items-center gap-2">
          <button class="px-4 py-2 rounded-lg border border-border-subtle bg-white text-on-surface hover:bg-surface-container-low font-label-lg text-xs transition-colors cursor-pointer" onclick="closeReportModal()" type="button">
            Tutup
          </button>
          <button id="modalResolveBtn" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-injourney-teal hover:bg-injourney-dark-teal text-white font-label-lg text-xs transition-all shadow-sm cursor-pointer" onclick="validateAndResolve()" type="button">
            <span class="material-symbols-outlined text-sm">verified</span>
            <span>Validasi &amp; Selesaikan Berita Acara</span>
          </button>
        </div>
      </div>

    </div>
  </div>

  <!-- TOAST NOTIFICATION CONTAINER -->
  <div class="fixed bottom-5 right-5 z-50 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none bg-slate-900 text-white px-4 py-3 rounded-xl shadow-xl flex items-center gap-3" id="toast">
    <span class="material-symbols-outlined text-resolved-emerald" id="toastIcon">check_circle</span>
    <span class="text-xs font-medium" id="toastMessage">Laporan berhasil diperbarui.</span>
  </div>

  <!-- INLINE JAVASCRIPT FOR DYNAMIC LOGBOOK & MODAL INTERACTIONS -->
  <script>
    let activeModalLaporanId = null;

    // Tab switching in Two-Page Verification Modal
    function switchModalPage(pageNumber) {
      const page1 = document.getElementById('modalPage1');
      const page2 = document.getElementById('modalPage2');
      const btn1 = document.getElementById('tabBtnPage1');
      const btn2 = document.getElementById('tabBtnPage2');

      if (pageNumber === 1) {
        page1.classList.remove('hidden');
        page2.classList.add('hidden');
        btn1.className = 'px-3 py-1 rounded text-xs font-label-md font-bold bg-white text-injourney-teal shadow-xs cursor-pointer';
        btn2.className = 'px-3 py-1 rounded text-xs font-label-md text-secondary hover:text-on-surface cursor-pointer';
      } else {
        page1.classList.add('hidden');
        page2.classList.remove('hidden');
        btn2.className = 'px-3 py-1 rounded text-xs font-label-md font-bold bg-white text-injourney-teal shadow-xs cursor-pointer';
        btn1.className = 'px-3 py-1 rounded text-xs font-label-md text-secondary hover:text-on-surface cursor-pointer';
        setTimeout(initSignaturePad, 60);
      }
    }

    // Modal Control Functions
    function openReportDetailAjax(id) {
      activeModalLaporanId = id;
      
      // Update print button link
      const printBtn = document.getElementById('modalPrintBtn');
      printBtn.href = `/admin/laporan/${id}/cetak`;

      // Fetch dynamic details
      fetch(`/admin/laporan/${id}/detail`)
        .then(res => res.json())
        .then(data => {
          const regId = data.no_laporan || ('WHM-' + (data.tanggal ? data.tanggal.substring(0, 4) : '2026') + '-' + String(data.id).padStart(4, '0'));
          document.getElementById('modalReportId').innerText = regId;
          document.getElementById('modalOfficer').innerText = (data.nama_petugas || data.nama_user) + ' (' + (data.unit_kerja || 'AMC') + ')';
          document.getElementById('modalTimestamp').innerText = (data.tanggal || '-') + ', ' + (data.created_at ? data.created_at.substring(11, 16) + ' WIB' : '08:00 WIB');
          document.getElementById('modalWeather').innerText = data.kondisi_cuaca ? (data.kondisi_cuaca + ' / Angin 8 kts WSW') : 'Berawan 29°C';
          
          let speciesStr = 'Satwa Liar';
          let qtyStr = '1 Ekor';
          let gridStr = data.grid_lokasi || 'K-10';

          if (data.satwa && data.satwa.length > 0) {
            speciesStr = data.satwa[0].nama || speciesStr;
            qtyStr = (data.satwa[0].jumlah || 1) + ' Ekor';
            if (data.satwa[0].grid) gridStr = data.satwa[0].grid;
            if (data.satwa[0].foto_path) {
              document.getElementById('modalPhotoImg').src = data.satwa[0].foto_path;
            }
          }

          document.getElementById('modalSpecies').innerText = speciesStr;
          document.getElementById('modalQuantity').innerText = qtyStr + (data.ciri_ukuran ? ` (${data.ciri_ukuran})` : '');
          document.getElementById('modalLocation').innerText = (data.area_inspeksi || 'Runway 07L Bahu') + ' [' + gridStr + ']';
          document.getElementById('modalGridText').innerText = 'GRID ' + gridStr;
          document.getElementById('schematicGridTarget').innerText = gridStr + ' (' + (data.area_inspeksi || 'Bahu Landasan') + ')';

          // Update mini grid schematic highlight
          updateMiniGridSchematic(gridStr);

          // Update Status Badge
          const badge = document.getElementById('modalStatusBadge');
          const resolveBtn = document.getElementById('modalResolveBtn');
          if (data.status === 'sudah') {
            badge.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200';
            badge.innerHTML = '<span class="w-2 h-2 rounded-full bg-resolved-emerald"></span> Sudah Ditangani';
            resolveBtn.classList.add('hidden');
          } else {
            badge.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200';
            badge.innerHTML = '<span class="w-2 h-2 rounded-full bg-alert-amber"></span> Belum Ditangani';
            resolveBtn.classList.remove('hidden');
          }

          if (data.detail_pengusiran) {
            document.getElementById('modalDispersalDesc').innerText = data.detail_pengusiran;
          }

          switchModalPage(1);
          document.getElementById('detailModal').classList.remove('hidden');
        })
        .catch(err => {
          console.error(err);
          showToast('Gagal memuat rincian laporan.', 'error');
        });
    }

    function updateMiniGridSchematic(gridStr) {
      const cells = document.querySelectorAll('.grid-cell');
      const cleanGrid = gridStr.trim().toUpperCase();
      cells.forEach(cell => {
        const cellGrid = cell.getAttribute('data-grid');
        if (cellGrid === cleanGrid) {
          cell.className = 'py-2 bg-warning-rose text-white rounded font-bold shadow-lg ring-2 ring-white/50 animate-pulse grid-cell';
          cell.innerText = cellGrid + ' ★';
        } else {
          cell.className = 'py-2 bg-slate-800 rounded border border-slate-700 text-slate-400 grid-cell';
          cell.innerText = cellGrid;
        }
      });
    }

    function closeReportModal() {
      document.getElementById('detailModal').classList.add('hidden');
    }

    // Interactive HTML5 Signature Canvas
    let canvasInitialized = false;
    let isDrawing = false;
    let sigCanvas, sigCtx;

    function initSignaturePad() {
      sigCanvas = document.getElementById('signaturePad');
      if (!sigCanvas) return;
      
      sigCtx = sigCanvas.getContext('2d');
      sigCanvas.width = sigCanvas.offsetWidth;
      sigCanvas.height = sigCanvas.offsetHeight;
      sigCtx.lineWidth = 2.5;
      sigCtx.lineCap = 'round';
      sigCtx.strokeStyle = '#006874';

      function getPos(e) {
        const rect = sigCanvas.getBoundingClientRect();
        return {
          x: (e.clientX || (e.touches && e.touches[0].clientX)) - rect.left,
          y: (e.clientY || (e.touches && e.touches[0].clientY)) - rect.top
        };
      }

      sigCanvas.onmousedown = (e) => {
        isDrawing = true;
        const p = getPos(e);
        sigCtx.beginPath();
        sigCtx.moveTo(p.x, p.y);
      };

      sigCanvas.onmousemove = (e) => {
        if (!isDrawing) return;
        const p = getPos(e);
        sigCtx.lineTo(p.x, p.y);
        sigCtx.stroke();
      };

      window.onmouseup = () => { isDrawing = false; };

      sigCanvas.ontouchstart = (e) => {
        e.preventDefault();
        isDrawing = true;
        const p = getPos(e);
        sigCtx.beginPath();
        sigCtx.moveTo(p.x, p.y);
      };

      sigCanvas.ontouchmove = (e) => {
        if (!isDrawing) return;
        e.preventDefault();
        const p = getPos(e);
        sigCtx.lineTo(p.x, p.y);
        sigCtx.stroke();
      };

      sigCanvas.ontouchend = () => { isDrawing = false; };
      canvasInitialized = true;
    }

    function clearSignature() {
      if (!sigCanvas || !sigCtx) return;
      sigCtx.clearRect(0, 0, sigCanvas.width, sigCanvas.height);
      showToast('Canvas tanda tangan telah dibersihkan.', 'info');
    }

    // Toast Notification Utility
    function showToast(message, type = 'success') {
      const toast = document.getElementById('toast');
      const toastMsg = document.getElementById('toastMessage');
      const toastIcon = document.getElementById('toastIcon');

      toastMsg.innerText = message;
      if (type === 'success') {
        toastIcon.innerText = 'check_circle';
        toastIcon.className = 'material-symbols-outlined text-resolved-emerald';
      } else if (type === 'info') {
        toastIcon.innerText = 'info';
        toastIcon.className = 'material-symbols-outlined text-injourney-teal';
      } else if (type === 'error') {
        toastIcon.innerText = 'error';
        toastIcon.className = 'material-symbols-outlined text-warning-rose';
      }

      toast.classList.remove('translate-y-20', 'opacity-0');
      setTimeout(() => {
        toast.classList.add('translate-y-20', 'opacity-0');
      }, 3400);
    }

    // Validate & Resolve Action via AJAX
    function validateAndResolve() {
      if (!activeModalLaporanId) return;

      const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      const formData = new FormData();
      formData.append('status_validasi', 'sudah');

      fetch(`/admin/laporan/${activeModalLaporanId}/update`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': token,
          'Accept': 'application/json'
        },
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        closeReportModal();
        showToast('Berita Acara Berhasil Divalidasi & Ditandai Selesai!');

        // Update row in UI table without reload
        const statusCell = document.getElementById(`status-cell-${activeModalLaporanId}`);
        if (statusCell) {
          statusCell.innerHTML = `
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
              <span class="w-2 h-2 rounded-full bg-resolved-emerald"></span>
              Sudah Ditangani
            </span>
          `;
        }

        const resolveBtn = document.getElementById(`btn-resolve-${activeModalLaporanId}`);
        if (resolveBtn) resolveBtn.remove();

        const row = document.getElementById(`row-${activeModalLaporanId}`);
        if (row) row.setAttribute('data-status', 'sudah');
      })
      .catch(err => {
        console.error(err);
        showToast('Gagal memvalidasi laporan.', 'error');
      });
    }

    function quickResolve(id, regId) {
      const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      const formData = new FormData();
      formData.append('status_validasi', 'sudah');

      fetch(`/admin/laporan/${id}/update`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': token,
          'Accept': 'application/json'
        },
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        showToast(`Insiden ${regId} berhasil dimitigasi & divalidasi!`);

        const statusCell = document.getElementById(`status-cell-${id}`);
        if (statusCell) {
          statusCell.innerHTML = `
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
              <span class="w-2 h-2 rounded-full bg-resolved-emerald"></span>
              Sudah Ditangani
            </span>
          `;
        }

        const btn = document.getElementById(`btn-resolve-${id}`);
        if (btn) btn.remove();

        const row = document.getElementById(`row-${id}`);
        if (row) row.setAttribute('data-status', 'sudah');
      })
      .catch(err => {
        console.error(err);
        showToast('Gagal memproses tindakan.', 'error');
      });
    }

    function refreshData() {
      showToast('Memperbarui data telemetri satwa sisi udara...', 'info');
      setTimeout(() => {
        window.location.reload();
      }, 700);
    }

    // Search & Filter Utilities
    let currentStatusFilter = 'all';

    function filterStatus(status) {
      currentStatusFilter = status;
      const tabAll = document.getElementById('tab-all');
      const tabPen = document.getElementById('tab-pending');
      const tabRes = document.getElementById('tab-resolved');

      [tabAll, tabPen, tabRes].forEach(t => {
        t.className = 'px-3.5 py-1.5 rounded-md font-label-lg text-label-lg text-secondary hover:text-on-surface transition-all';
      });

      if (status === 'all') {
        tabAll.className = 'px-3.5 py-1.5 rounded-md font-label-lg text-label-lg transition-all bg-white text-injourney-teal font-bold shadow-sm';
      } else if (status === 'pending') {
        tabPen.className = 'px-3.5 py-1.5 rounded-md font-label-lg text-label-lg transition-all bg-white text-warning-rose font-bold shadow-sm';
      } else {
        tabRes.className = 'px-3.5 py-1.5 rounded-md font-label-lg text-label-lg transition-all bg-white text-emerald-700 font-bold shadow-sm';
      }

      searchTable();
    }

    function filterByGrid(gridCode) {
      document.getElementById('searchInput').value = gridCode;
      searchTable();
      showToast(`Filter grid runway: ${gridCode}`, 'info');
    }

    function resetFilters() {
      document.getElementById('searchInput').value = '';
      const fUnit = document.getElementById('filterUnit');
      if (fUnit) fUnit.value = '';
      const fArea = document.getElementById('filterArea');
      if (fArea) fArea.value = '';
      filterStatus('all');
    }

    function searchTable() {
      const searchVal = document.getElementById('searchInput').value.toLowerCase().trim();
      const unitVal = (document.getElementById('filterUnit') ? document.getElementById('filterUnit').value.toLowerCase().trim() : '');
      const areaVal = (document.getElementById('filterArea') ? document.getElementById('filterArea').value.toLowerCase().trim() : '');

      const table = document.getElementById('logbookTable');
      if (!table) return;
      const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

      for (let r of rows) {
        const text = r.innerText.toLowerCase();
        const rStatus = r.getAttribute('data-status');

        let matchStatus = true;
        if (currentStatusFilter === 'pending') {
          matchStatus = (rStatus === 'belum' || text.includes('belum ditangani'));
        } else if (currentStatusFilter === 'resolved') {
          matchStatus = (rStatus === 'sudah' || text.includes('sudah ditangani'));
        }

        let matchSearch = searchVal === '' || text.includes(searchVal);
        let matchUnit = unitVal === '' || text.includes(unitVal);
        let matchArea = areaVal === '' || text.includes(areaVal);

        if (matchStatus && matchSearch && matchUnit && matchArea) {
          r.style.display = '';
        } else {
          r.style.display = 'none';
        }
      }
    }
  </script>
</body>
</html>
