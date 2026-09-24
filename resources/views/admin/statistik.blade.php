<!DOCTYPE html>
<html lang="id" class="h-full bg-canvas-light text-on-surface">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Peta Sebaran Grid &amp; Heatmap Bahaya Satwa - InJourney Airports</title>

  <!-- Favicon InJourney Airports -->
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
  <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

  <!-- Google Fonts: DM Sans & Material Symbols -->
  <link href="https://fonts.googleapis.com" rel="preconnect"/>
  <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          "colors": {
            "inverse-on-surface": "#eaf1ff",
            "injourney-dark-teal": "#0891B2",
            "runway-grid": "#334155",
            "on-tertiary": "#ffffff",
            "resolved-emerald": "#10B981",
            "injourney-teal": "#00A3B4",
            "surface-container-highest": "#d3e4fe",
            "error-container": "#ffdad6",
            "on-primary-container": "#003238",
            "surface-container-high": "#dce9ff",
            "on-tertiary-fixed": "#001f28",
            "on-secondary-fixed": "#131b2e",
            "surface-dim": "#cbdbf5",
            "tertiary-container": "#2ca0c1",
            "outline": "#6d797c",
            "secondary-fixed": "#dae2fd",
            "inverse-surface": "#213145",
            "warning-rose": "#EF4444",
            "surface-tint": "#006874",
            "surface": "#f8f9ff",
            "surface-container": "#e5eeff",
            "error": "#ba1a1a",
            "on-secondary-fixed-variant": "#3f465c",
            "primary-fixed-dim": "#5dd7e9",
            "on-error": "#ffffff",
            "primary-container": "#00a3b4",
            "on-secondary-container": "#5c647a",
            "slate-navy": "#0F172A",
            "on-surface": "#0b1c30",
            "on-error-container": "#93000a",
            "inverse-primary": "#5dd7e9",
            "tertiary-fixed": "#b7eaff",
            "on-tertiary-container": "#00313f",
            "border-subtle": "#E2E8F0",
            "on-primary-fixed-variant": "#004f58",
            "background": "#f8f9ff",
            "surface-variant": "#d3e4fe",
            "tertiary-fixed-dim": "#6cd3f7",
            "canvas-light": "#F8FAFC",
            "on-background": "#0b1c30",
            "tertiary": "#006780",
            "primary-fixed": "#97f0ff",
            "on-surface-variant": "#3d494b",
            "surface-container-lowest": "#ffffff",
            "on-tertiary-fixed-variant": "#004e61",
            "outline-variant": "#bcc9cb",
            "on-secondary": "#ffffff",
            "alert-amber": "#F59E0B",
            "secondary-fixed-dim": "#bec6e0",
            "primary": "#006874",
            "on-primary-fixed": "#001f24",
            "surface-container-low": "#eff4ff",
            "slate-surface": "#1E293B",
            "secondary-container": "#dae2fd",
            "on-primary": "#ffffff",
            "surface-bright": "#f8f9ff",
            "card-bg": "#FFFFFF",
            "secondary": "#565e74"
          },
          "borderRadius": {
            "DEFAULT": "0.25rem",
            "lg": "0.5rem",
            "xl": "0.75rem",
            "full": "9999px"
          },
          "fontFamily": {
            "headline-sm": ["DM Sans"],
            "label-lg": ["DM Sans"],
            "headline-lg": ["DM Sans"],
            "label-sm": ["DM Sans"],
            "body-sm": ["DM Sans"],
            "body-lg": ["DM Sans"],
            "label-md": ["DM Sans"],
            "headline-md": ["DM Sans"],
            "headline-xl": ["DM Sans"],
            "code-coordinate": ["DM Sans"],
            "body-md": ["DM Sans"]
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
    .grid-cell-pulse {
      animation: cellPulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    @keyframes cellPulse {
      0%, 100% { opacity: 0.95; }
      50% { opacity: 0.65; }
    }
    /* Custom scrollbar */
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
<body class="bg-canvas-light text-on-surface font-body-md min-h-screen antialiased flex flex-col selection:bg-injourney-teal selection:text-white">

  <!-- TOP APP BAR (Unified Navbar) -->
  @include('partials.navbar', ['activePage' => 'statistic'])

  <!-- SUB-HEADER & BREADCRUMB COMMAND STRIP -->
  <section class="bg-card-bg border-b border-border-subtle px-4 sm:px-6 lg:px-8 py-4">
    <div class="max-w-[1920px] mx-auto flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
      
      <!-- Titles & Breadcrumb -->
      <div>
        <div class="flex items-center gap-1.5 text-xs font-semibold text-secondary mb-1">
          <span>Sistem WHMS</span>
          <span class="material-symbols-outlined text-xs">chevron_right</span>
          <span>Analisis Spasial</span>
          <span class="material-symbols-outlined text-xs">chevron_right</span>
          <span class="text-injourney-teal font-semibold">Peta Sebaran Grid Bandara</span>
        </div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-navy tracking-tight">
          Peta Sebaran Grid &amp; Heatmap Bahaya Satwa Sisi Udara
        </h1>
        <p class="text-xs sm:text-sm text-secondary mt-0.5 max-w-4xl">
          Visualisasi geospasial konsentrasi satwa liar, zona risiko tinggi pergerakan runway 07L/25R, taxiway, dan perimeter keselamatan ICAO Annex 14.
        </p>
      </div>

      <!-- Quick Action Controls & Time Toggles -->
      <div class="flex flex-wrap items-center gap-2.5">
        <!-- Time Filter Pills -->
        <div class="flex items-center bg-surface-container-low rounded-lg p-1 border border-border-subtle text-xs">
          <a href="{{ route('admin.statistik', ['time_filter' => 'month', 'kategori' => $kategori, 'zona' => $zonaFilter]) }}" class="px-3 py-1.5 rounded font-bold transition-all {{ $timeFilter === 'month' ? 'bg-card-bg text-injourney-teal shadow-xs' : 'text-secondary hover:text-on-surface' }}">
            Bulan Ini
          </a>
          <a href="{{ route('admin.statistik', ['time_filter' => 'quarter', 'kategori' => $kategori, 'zona' => $zonaFilter]) }}" class="px-3 py-1.5 rounded font-bold transition-all {{ $timeFilter === 'quarter' ? 'bg-card-bg text-injourney-teal shadow-xs' : 'text-secondary hover:text-on-surface' }}">
            3 Bulan Terakhir
          </a>
          <a href="{{ route('admin.statistik', ['time_filter' => 'year', 'tahun' => $tahun, 'kategori' => $kategori, 'zona' => $zonaFilter]) }}" class="px-3 py-1.5 rounded font-bold transition-all {{ $timeFilter === 'year' ? 'bg-card-bg text-injourney-teal shadow-xs' : 'text-secondary hover:text-on-surface' }}">
            Tahun {{ $tahun }}
          </a>
        </div>

        <!-- Export Excel -->
        <a href="{{ route('admin.export.excel', ['tahun' => $tahun]) }}" class="inline-flex items-center gap-1.5 px-3 py-2 border border-border-subtle rounded-lg bg-card-bg text-on-surface hover:bg-surface-container-low text-xs font-semibold transition-colors shadow-2xs">
          <span class="material-symbols-outlined text-sm text-secondary">file_download</span>
          <span>Export Excel</span>
        </a>

        <!-- Print Operational PDF Button -->
        <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-2 border border-border-subtle rounded-lg bg-card-bg text-on-surface hover:bg-surface-container-low text-xs font-semibold transition-colors shadow-2xs">
          <span class="material-symbols-outlined text-sm text-secondary">print</span>
          <span>Cetak Peta PDF</span>
        </button>
      </div>

    </div>
  </section>

  <!-- MAIN OPERATIONAL CONTAINER -->
  <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6 max-w-[1920px] mx-auto w-full flex flex-col gap-6">

    <!-- 1. KPI SUMMARY BAR (4 CARDS) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
      
      <!-- KPI 1: Grid Terpadat -->
      <div class="bg-card-bg rounded-xl border border-border-subtle p-4 shadow-sm relative overflow-hidden flex flex-col justify-between">
        <div class="absolute top-0 left-0 right-0 h-[3px] bg-warning-rose"></div>
        <div>
          <div class="flex items-center justify-between mb-1">
            <span class="text-xs uppercase text-secondary font-semibold tracking-wider">Grid Terpadat (Hotspot Utama)</span>
            <span class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded-full bg-error-container text-error font-bold">
              <span class="w-1.5 h-1.5 rounded-full bg-warning-rose animate-ping"></span>
              Level Kritis
            </span>
          </div>
          <div class="text-2xl sm:text-3xl font-bold text-slate-navy">Grid {{ $gridTerpadatTitle }}</div>
        </div>
        <div class="mt-2 pt-2 border-t border-border-subtle flex items-center justify-between text-xs text-secondary">
          <span>{{ $gridTerpadatCount }} Temuan / {{ $gridTerpadatPct }}% insiden</span>
          <span class="font-code-coordinate text-warning-rose font-bold">Bahu RWY 25R</span>
        </div>
      </div>

      <!-- KPI 2: Total Titik Terpetakan -->
      <div class="bg-card-bg rounded-xl border border-border-subtle p-4 shadow-sm relative overflow-hidden flex flex-col justify-between">
        <div class="absolute top-0 left-0 right-0 h-[3px] bg-injourney-teal"></div>
        <div>
          <div class="flex items-center justify-between mb-1">
            <span class="text-xs uppercase text-secondary font-semibold tracking-wider">Total Titik Insiden Terpetakan</span>
            <span class="inline-flex items-center text-[11px] px-2 py-0.5 rounded-full bg-surface-container-high text-injourney-teal font-semibold">
              Aktif Terpantau
            </span>
          </div>
          <div class="text-2xl sm:text-3xl font-bold text-slate-navy">{{ $totalTitikTerpetakan }} Titik</div>
        </div>
        <div class="mt-2 pt-2 border-t border-border-subtle flex items-center justify-between text-xs text-secondary">
          <span>Tersebar di {{ $totalSelAktif }} sel grid aktif</span>
          <span class="font-code-coordinate text-injourney-teal font-bold">15 Kolom x 12 Baris</span>
        </div>
      </div>

      <!-- KPI 3: Zona Kritis Runway Strip -->
      <div class="bg-card-bg rounded-xl border border-border-subtle p-4 shadow-sm relative overflow-hidden flex flex-col justify-between">
        <div class="absolute top-0 left-0 right-0 h-[3px] bg-alert-amber"></div>
        <div>
          <div class="flex items-center justify-between mb-1">
            <span class="text-xs uppercase text-secondary font-semibold tracking-wider">Status Zona Kritis Strip 35M</span>
            <span class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded-full bg-amber-100 text-amber-900 font-semibold">
              Perlu Patroli
            </span>
          </div>
          <div class="text-2xl sm:text-3xl font-bold text-slate-navy">{{ $runwayActiveCount }} Kejadian Aktif</div>
        </div>
        <div class="mt-2 pt-2 border-t border-border-subtle flex items-center justify-between text-xs text-secondary">
          <span>Perlu Pemantauan Mobile AMC</span>
          <span class="font-code-coordinate text-alert-amber font-bold">Siaga Unit 02</span>
        </div>
      </div>

      <!-- KPI 4: Efektivitas Dispersal Spasial -->
      <div class="bg-card-bg rounded-xl border border-border-subtle p-4 shadow-sm relative overflow-hidden flex flex-col justify-between">
        <div class="absolute top-0 left-0 right-0 h-[3px] bg-resolved-emerald"></div>
        <div>
          <div class="flex items-center justify-between mb-1">
            <span class="text-xs uppercase text-secondary font-semibold tracking-wider">Efektivitas Dispersal Spasial</span>
            <span class="inline-flex items-center text-[11px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-900 font-semibold">
              Target Terlampaui
            </span>
          </div>
          <div class="text-2xl sm:text-3xl font-bold text-slate-navy">{{ $efektivitasDispersal }}% Area Steril</div>
        </div>
        <div class="mt-2 pt-2 border-t border-border-subtle flex items-center justify-between text-xs text-secondary">
          <span>Pasca intervensi regu jaga AMC</span>
          <span class="font-code-coordinate text-resolved-emerald font-bold">{{ $totalHandledAll }}/{{ $totalTitikTerpetakan }} Ditangani</span>
        </div>
      </div>

    </div>

    <!-- 2. MAIN 2-COLUMN OPERATIONAL LAYOUT: AIRSIDE GRID MAP (LEFT) & HOTSPOT DETAILS (RIGHT) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
      
      <!-- LEFT / CENTER COLUMN: INTERACTIVE AIRSIDE RUNWAY GRID MAP (8 Cols) -->
      <div class="lg:col-span-8 bg-card-bg rounded-xl border border-border-subtle p-4 sm:p-5 shadow-sm flex flex-col relative">
        
        <!-- Airside Map Top Controls & Heading Strip -->
        <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-border-subtle mb-4">
          <div class="flex items-center gap-3">
            <div class="flex items-center gap-2">
              <span class="material-symbols-outlined text-injourney-teal text-xl">grid_4x4</span>
              <span class="text-base sm:text-lg font-bold text-slate-navy">Denah Sektoral Grid Sisi Udara Bandara</span>
            </div>
            <div class="hidden sm:flex items-center gap-1.5 text-xs font-code-coordinate bg-surface-container-low px-2.5 py-1 rounded border border-border-subtle text-secondary font-semibold">
              <span>RUNWAY ORIENTATION: 070° - 250° (07L / 25R)</span>
            </div>
          </div>
          
          <!-- Active Layer Chips -->
          <div class="flex items-center gap-2">
            <span class="text-xs text-secondary hidden md:inline">Layer:</span>
            <span class="text-xs px-2.5 py-1 rounded bg-surface-container-high text-injourney-teal font-bold flex items-center gap-1">
              <span class="material-symbols-outlined text-xs">check</span> Heatmap Kepadatan
            </span>
            <span class="text-xs px-2.5 py-1 rounded bg-surface-container-low text-secondary font-medium">
              Runway Buffer 35m
            </span>
          </div>
        </div>

        <!-- AIRSIDE INTERACTIVE SPATIAL CANVAS -->
        <div class="relative w-full overflow-x-auto bg-slate-900 rounded-xl p-4 sm:p-5 border border-runway-grid select-none shadow-inner">
          
          <!-- Floating Map Tools (Overlay Controls) -->
          <div class="absolute top-4 right-4 z-20 flex flex-col gap-1.5 bg-slate-navy/90 backdrop-blur-md p-1.5 rounded-lg border border-runway-grid shadow-lg">
            <button type="button" onclick="zoomMap(0.1)" class="w-8 h-8 rounded flex items-center justify-center text-white hover:bg-slate-surface transition-colors active:scale-95" title="Perbesar">
              <span class="material-symbols-outlined text-base">add</span>
            </button>
            <button type="button" onclick="zoomMap(-0.1)" class="w-8 h-8 rounded flex items-center justify-center text-white hover:bg-slate-surface transition-colors active:scale-95" title="Perkecil">
              <span class="material-symbols-outlined text-base">remove</span>
            </button>
            <button type="button" onclick="resetMap()" class="w-8 h-8 rounded flex items-center justify-center text-white hover:bg-slate-surface transition-colors active:scale-95" title="Reset Posisi Peta">
              <span class="material-symbols-outlined text-base">restart_alt</span>
            </button>
            <div class="w-full h-px bg-runway-grid my-0.5"></div>
            <!-- Wind Compass Heading -->
            <div class="w-8 h-8 rounded flex items-center justify-center text-injourney-teal" title="Arah Angin WSW 250°">
              <span class="material-symbols-outlined text-base transform rotate-[65deg]">navigation</span>
            </div>
          </div>

          <!-- Zone Indicators Left Sticky Legend -->
          <div class="absolute top-4 left-4 z-20 flex items-center gap-2 bg-slate-navy/90 backdrop-blur-md px-3 py-1.5 rounded-lg border border-runway-grid text-xs text-slate-300">
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-400"></span> Apron A-C</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-sky-400"></span> Rawa/Taxi D-G</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-400"></span> Strip RWY H-K</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-indigo-400"></span> Kanal L</span>
          </div>

          <!-- The Precise Grid Layout: 16 columns (1 Header Col + 15 Grid Cols) -->
          <div id="gridMatrixContainer" class="min-w-[780px] w-full pt-8 transition-transform duration-200 origin-top-left">
            
            <!-- Column Numbers Axis (01 - 15) -->
            <div class="grid grid-cols-16 gap-1 mb-1 text-center font-code-coordinate text-xs text-slate-400 font-bold">
              <div class="py-1"></div> <!-- Empty corner for Row label -->
              @for($c = 1; $c <= 15; $c++)
                <div class="py-1">{{ str_pad($c, 2, '0', STR_PAD_LEFT) }}</div>
              @endfor
            </div>

            <!-- GRID ROWS A through L -->
            @php
              $rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
            @endphp

            @foreach($rows as $r)
              @if($r === 'I')
                <!-- ROW I (Runway Strip North Buffer 35M) -->
                <div class="grid grid-cols-16 gap-1 mb-1 items-center">
                  <div class="text-center font-code-coordinate font-bold text-amber-400 text-xs">I</div>
                  <div class="col-span-15 h-5 rounded bg-slate-800/70 border border-dashed border-amber-500/40 flex items-center justify-center px-4">
                    <span class="text-[10px] font-code-coordinate text-amber-300 tracking-wider font-semibold">
                      ▲ RUNWAY STRIP BUFFER ZONE 35M (ICAO ANNEX 14 STANDARD COMPLIANCE AREA) ▲
                    </span>
                  </div>
                </div>
              @elseif($r === 'J')
                <!-- ROW J & K (PRIMARY RUNWAY 07L / 25R - ASPHALT SURFACE WITH MARKINGS) -->
                <div class="relative py-2 my-1 bg-slate-950 rounded border-2 border-slate-600 shadow-2xl">
                  <!-- Threshold Markings Left (07L) -->
                  <div class="absolute left-2 top-2 bottom-2 w-8 flex flex-col justify-between py-1 border-r border-dashed border-white/40">
                    <div class="h-1 bg-white"></div>
                    <div class="h-1 bg-white"></div>
                    <div class="h-1 bg-white"></div>
                    <div class="h-1 bg-white"></div>
                    <div class="text-center font-code-coordinate text-[11px] font-bold text-white tracking-tighter">07L</div>
                  </div>
                  <!-- Threshold Markings Right (25R) -->
                  <div class="absolute right-2 top-2 bottom-2 w-8 flex flex-col justify-between py-1 border-l border-dashed border-white/40">
                    <div class="h-1 bg-white"></div>
                    <div class="h-1 bg-white"></div>
                    <div class="h-1 bg-white"></div>
                    <div class="h-1 bg-white"></div>
                    <div class="text-center font-code-coordinate text-[11px] font-bold text-white tracking-tighter">25R</div>
                  </div>
                  <!-- Centerline dashed line -->
                  <div class="absolute left-14 right-14 top-1/2 -translate-y-1/2 border-t-2 border-dashed border-white/50 pointer-events-none"></div>
                  
                  <!-- Runway Grid Matrix J -->
                  <div class="grid grid-cols-16 gap-1 mb-1 items-center pl-10 pr-10">
                    <div class="text-center font-code-coordinate font-bold text-white text-xs">J</div>
                    @for($col = 1; $col <= 15; $col++)
                      @php
                        $code = 'J-' . $col;
                        $hasData = isset($gridAgg[$code]);
                        $item = $hasData ? $gridAgg[$code] : null;
                      @endphp
                      @if($hasData)
                        <div onclick="selectGrid('{{ $code }}')" class="h-11 rounded border-2 {{ $item['risk_border'] }} {{ $item['risk_bg'] }} {{ $item['risk_level'] === 'Kritis' ? 'grid-cell-pulse' : '' }} hover:border-white transition-all flex items-center justify-between px-1.5 cursor-pointer relative z-10" title="Grid {{ $code }}: {{ $item['dominant_species'] }} ({{ $item['count'] }} temuan)">
                          <span class="text-xs font-code-coordinate {{ $item['risk_text'] }} font-bold">{{ $code }}</span>
                          <span class="text-[11px] font-bold bg-white text-slate-navy px-1 rounded">{{ $item['count'] }}</span>
                        </div>
                      @else
                        <div onclick="selectGrid('{{ $code }}')" class="h-11 rounded border border-slate-700/50 hover:border-injourney-teal bg-slate-900/60 flex items-center justify-center cursor-pointer text-slate-600 hover:text-slate-300 text-[10px] font-code-coordinate" title="Grid {{ $code }} (Steril)">
                          {{ $col }}
                        </div>
                      @endif
                    @endfor
                  </div>

                  <!-- ROW K (MAIN RUNWAY BODY & CRITICAL HOTSPOT K-10) -->
                  <div class="grid grid-cols-16 gap-1 items-center pl-10 pr-10">
                    <div class="text-center font-code-coordinate font-bold text-white text-xs">K</div>
                    @for($col = 1; $col <= 15; $col++)
                      @php
                        $code = 'K-' . $col;
                        $hasData = isset($gridAgg[$code]);
                        $item = $hasData ? $gridAgg[$code] : null;
                      @endphp
                      @if($hasData)
                        <div onclick="selectGrid('{{ $code }}')" class="h-11 rounded-md border-2 {{ $item['risk_border'] }} {{ $item['risk_bg'] }} {{ $item['risk_level'] === 'Kritis' ? 'grid-cell-pulse shadow-lg ring-2 ring-warning-rose/40' : '' }} hover:border-white transition-all flex items-center justify-between px-1.5 cursor-pointer relative z-10" title="Grid {{ $code }}: {{ $item['dominant_species'] }} ({{ $item['count'] }} temuan)">
                          <span class="text-xs font-code-coordinate {{ $item['risk_text'] }} font-bold">{{ $code }}</span>
                          <span class="text-[11px] font-bold bg-white text-slate-navy px-1 rounded shadow-xs">{{ $item['count'] }}</span>
                          @if($item['risk_level'] === 'Kritis')
                            <span class="absolute -top-1 -right-1 flex h-2.5 w-2.5">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-400"></span>
                            </span>
                          @endif
                        </div>
                      @else
                        <div onclick="selectGrid('{{ $code }}')" class="h-11 rounded border border-slate-700/50 hover:border-injourney-teal bg-slate-900/60 flex items-center justify-center cursor-pointer text-slate-600 hover:text-slate-300 text-[10px] font-code-coordinate" title="Grid {{ $code }} (Steril)">
                          {{ $col }}
                        </div>
                      @endif
                    @endfor
                  </div>

                </div>
              @elseif($r === 'K')
                {{-- Row K is rendered inside the Runway block above --}}
              @else
                <!-- STANDARD ROW: {{ $r }} -->
                <div class="grid grid-cols-16 gap-1 mb-1 items-center">
                  <div class="text-center font-code-coordinate font-bold text-slate-400 text-xs">{{ $r }}</div>
                  @for($col = 1; $col <= 15; $col++)
                    @php
                      $code = $r . '-' . $col;
                      $hasData = isset($gridAgg[$code]);
                      $item = $hasData ? $gridAgg[$code] : null;
                    @endphp
                    @if($hasData)
                      <div onclick="selectGrid('{{ $code }}')" class="h-10 rounded border-2 {{ $item['risk_border'] }} {{ $item['risk_bg'] }} {{ $item['risk_level'] === 'Kritis' ? 'grid-cell-pulse' : '' }} hover:border-white transition-all flex items-center justify-between px-1.5 cursor-pointer relative z-10" title="Grid {{ $code }}: {{ $item['dominant_species'] }} ({{ $item['count'] }} temuan)">
                        <span class="text-xs font-code-coordinate {{ $item['risk_text'] }} font-semibold">{{ $code }}</span>
                        <span class="text-[11px] font-bold bg-white/20 text-white px-1 rounded">{{ $item['count'] }}</span>
                      </div>
                    @else
                      <div onclick="selectGrid('{{ $code }}')" class="h-10 rounded border border-slate-700/60 bg-slate-800/40 hover:border-injourney-teal transition-all flex items-center justify-center cursor-pointer text-slate-600 hover:text-slate-400 text-[10px] font-code-coordinate" title="Grid {{ $code }} (Steril)">
                        {{ $col }}
                      </div>
                    @endif
                  @endfor
                </div>
              @endif
            @endforeach

          </div>

          <!-- BOTTOM MAP FOOTER: RISK LEVEL LEGEND -->
          <div class="mt-5 pt-3 border-t border-runway-grid flex flex-wrap items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-4 flex-wrap">
              <span class="text-slate-400 font-bold uppercase tracking-wider text-[11px]">Legenda Tingkat Risiko:</span>
              <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-warning-rose"></span>
                <span class="text-white">Kritis / Kategori 4 (&gt;30 Temuan)</span>
              </div>
              <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-amber-500"></span>
                <span class="text-white">Sedang / Flocking (15-30)</span>
              </div>
              <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-sky-500"></span>
                <span class="text-white">Rendah / Terpantau (&lt;15)</span>
              </div>
              <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded border border-slate-600 bg-slate-800/40"></span>
                <span class="text-slate-400">Steril / Tanpa Laporan</span>
              </div>
            </div>
            <div class="text-slate-400 font-code-coordinate text-xs">
              Sistem Koordinat WGS84: -6.1275° S, 106.6537° E
            </div>
          </div>

        </div>

      </div>

      <!-- RIGHT COLUMN: HOTSPOT DETAIL & INSPECTION PANEL (4 Cols) -->
      <div class="lg:col-span-4 flex flex-col gap-4">
        
        <!-- CARD 1: DETAIL GRID TERPILIH (DYNAMIC) -->
        <div id="detailCard" class="bg-card-bg rounded-xl border-2 border-warning-rose/30 p-5 shadow-sm relative overflow-hidden transition-all duration-200">
          <div id="detailTopBar" class="absolute top-0 left-0 right-0 h-1 bg-warning-rose"></div>
          
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
              <span id="detailGridBadge" class="font-code-coordinate text-sm font-bold bg-error-container text-error px-2.5 py-1 rounded">
                GRID {{ $selectedGrid['grid'] ?? 'K-10' }}
              </span>
              <span id="detailRiskBadge" class="text-xs px-2.5 py-0.5 rounded-full bg-red-100 text-red-800 font-bold">
                {{ $selectedGrid['risk_category'] ?? 'Kategori 4 - Kritis' }}
              </span>
            </div>
            <span class="text-xs text-secondary font-medium">Terpilih Aktif</span>
          </div>

          <h2 id="detailZoneName" class="text-base sm:text-lg font-bold text-slate-navy">
            {{ $selectedGrid['zone_name'] ?? 'Bahu Runway 25R & Kanal Selatan' }}
          </h2>
          <p id="detailDesc" class="text-xs sm:text-sm text-secondary mt-0.5 mb-4 leading-relaxed">
            {{ $selectedGrid['desc'] ?? 'Zona perimeter strip aktif runway 25R berdekatan dengan jalur drainase induk sisi barat.' }}
          </p>

          <!-- Detail Metrics Grid -->
          <div class="grid grid-cols-2 gap-3 mb-4">
            <div class="bg-surface-container-low p-3 rounded-lg border border-border-subtle">
              <span class="text-[11px] uppercase text-secondary font-semibold block">Total Temuan</span>
              <span id="detailCount" class="text-xl font-bold text-slate-navy">{{ $selectedGrid['count'] ?? 38 }} Kejadian</span>
              <span id="detailFreq" class="text-[11px] text-warning-rose font-medium mt-0.5 block">Hotspot Utama</span>
            </div>
            <div class="bg-surface-container-low p-3 rounded-lg border border-border-subtle">
              <span class="text-[11px] uppercase text-secondary font-semibold block">Spesies Dominan</span>
              <span id="detailSpecies" class="text-base font-bold text-slate-navy truncate block">{{ $selectedGrid['dominant_species'] ?? 'Biawak Air' }}</span>
              <span id="detailStatus" class="text-[11px] text-secondary mt-0.5 block">{{ $selectedGrid['status'] ?? 'Telah Ditangani' }}</span>
            </div>
          </div>

          <!-- Operational Time Pattern -->
          <div class="mb-4 bg-surface-container-low/70 p-3 rounded-lg border border-border-subtle text-xs">
            <div class="flex items-center gap-1.5 font-bold text-slate-navy mb-1">
              <span class="material-symbols-outlined text-sm text-injourney-teal">schedule</span>
              <span>Puncak Frekuensi Waktu Muncul:</span>
            </div>
            <p id="detailHours" class="text-secondary leading-relaxed">
              {{ $selectedGrid['peak_hours'] ?? 'Paling sering teridentifikasi pukul 06:30 - 08:30 WIB & 16:00 - 17:30 WIB saat pasang air kanal drainase dan kelembaban rumput shoulder meningkat.' }}
            </p>
          </div>

          <!-- Mitigation SOP Recommendation -->
          <div class="mb-5 p-3 rounded-lg bg-amber-50/80 border border-amber-200 text-xs">
            <div class="flex items-center gap-1.5 font-bold text-amber-900 mb-1.5">
              <span class="material-symbols-outlined text-sm text-alert-amber">warning</span>
              <span>Rekomendasi Tindakan Unit AMC:</span>
            </div>
            <ul id="detailSopList" class="text-amber-950 space-y-1 list-disc list-inside leading-relaxed">
              @if(!empty($selectedGrid['mitigation_sop']))
                @foreach($selectedGrid['mitigation_sop'] as $sop)
                  <li>{{ $sop }}</li>
                @endforeach
              @else
                <li>Peningkatan patroli kendaraan bersirene unit AMC interval 30 menit.</li>
                <li>Pembersihan vegetasi gulma pada saluran gorong-gorong sekitar sel.</li>
                <li>Aktivasi repellent trap &amp; pagar penghalau satwa melata.</li>
              @endif
            </ul>
          </div>

          <!-- Quick Action Buttons -->
          <div class="flex items-center gap-2.5">
            <a id="btnViewLog" href="{{ route('admin.dashboard', ['search' => $selectedGrid['grid'] ?? 'K-10']) }}" class="flex-1 py-2 px-3 border border-border-subtle rounded-lg bg-card-bg hover:bg-surface-container-low text-on-surface text-xs font-semibold transition-colors text-center shadow-2xs">
              Lihat Log Terkait
            </a>
            <button type="button" onclick="triggerDispersalAction()" class="flex-1 py-2 px-3 rounded-lg bg-injourney-teal hover:bg-injourney-dark-teal text-white text-xs font-semibold transition-colors text-center shadow-xs">
              Perintah Dispersal
            </button>
          </div>

        </div>

        <!-- CARD 2: TOP 5 AIRSIDE HAZARD ZONES RANKING -->
        <div class="bg-card-bg rounded-xl border border-border-subtle p-4 sm:p-5 shadow-sm">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-bold text-slate-navy">
              Peringkat 5 Grid Hotspot Teratas
            </h2>
            <span class="text-xs text-injourney-teal font-semibold">Bulan Berjalan</span>
          </div>

          <div class="space-y-3.5">
            @foreach($top5Grids as $top)
              <div onclick="selectGrid('{{ $top['grid'] }}')" class="cursor-pointer group">
                <div class="flex justify-between items-center text-xs mb-1">
                  <span class="font-bold text-slate-navy group-hover:text-injourney-teal transition-colors flex items-center gap-1.5">
                    <span class="w-4 h-4 rounded-full bg-slate-100 text-slate-700 text-center text-[10px] font-black inline-flex items-center justify-center">
                      {{ $top['rank'] }}
                    </span>
                    Grid {{ $top['grid'] }} ({{ $top['name'] }})
                  </span>
                  <span class="font-code-coordinate font-bold text-slate-700">{{ $top['count'] }} Laporan</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                  <div class="{{ $top['color'] }} h-2 rounded-full transition-all duration-500" style="width: {{ $top['bar_width'] }}%"></div>
                </div>
              </div>
            @endforeach
          </div>
        </div>

        <!-- CARD 3: QUICK FILTERS (KATEGORI SATWA & ZONA) -->
        <div class="bg-card-bg rounded-xl border border-border-subtle p-4 sm:p-5 shadow-sm">
          <h2 class="text-base font-bold text-slate-navy mb-3">
            Filter Cepat Kategori Satwa &amp; Zona
          </h2>
          
          <div class="space-y-3">
            <div>
              <span class="text-xs uppercase text-secondary font-semibold block mb-1.5">Kategori Fauna:</span>
              <div class="flex flex-wrap gap-1.5 text-xs font-semibold">
                <a href="{{ route('admin.statistik', ['time_filter' => $timeFilter, 'kategori' => 'all', 'zona' => $zonaFilter]) }}" class="px-2.5 py-1 rounded transition-colors {{ $kategori === 'all' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                  Semua Satwa
                </a>
                <a href="{{ route('admin.statistik', ['time_filter' => $timeFilter, 'kategori' => 'burung', 'zona' => $zonaFilter]) }}" class="px-2.5 py-1 rounded transition-colors {{ $kategori === 'burung' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                  Avian / Burung
                </a>
                <a href="{{ route('admin.statistik', ['time_filter' => $timeFilter, 'kategori' => 'reptil', 'zona' => $zonaFilter]) }}" class="px-2.5 py-1 rounded transition-colors {{ $kategori === 'reptil' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                  Reptil
                </a>
                <a href="{{ route('admin.statistik', ['time_filter' => $timeFilter, 'kategori' => 'mamalia', 'zona' => $zonaFilter]) }}" class="px-2.5 py-1 rounded transition-colors {{ $kategori === 'mamalia' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                  Mamalia
                </a>
              </div>
            </div>

            <div class="pt-2 border-t border-border-subtle">
              <span class="text-xs uppercase text-secondary font-semibold block mb-1.5">Zona Operasional:</span>
              <div class="flex flex-wrap gap-1.5 text-xs font-semibold">
                <a href="{{ route('admin.statistik', ['time_filter' => $timeFilter, 'kategori' => $kategori, 'zona' => 'all']) }}" class="px-2.5 py-1 rounded transition-colors {{ $zonaFilter === 'all' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                  Semua Zona
                </a>
                <a href="{{ route('admin.statistik', ['time_filter' => $timeFilter, 'kategori' => $kategori, 'zona' => 'runway']) }}" class="px-2.5 py-1 rounded transition-colors {{ $zonaFilter === 'runway' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                  Runway Strip
                </a>
                <a href="{{ route('admin.statistik', ['time_filter' => $timeFilter, 'kategori' => $kategori, 'zona' => 'taxiway']) }}" class="px-2.5 py-1 rounded transition-colors {{ $zonaFilter === 'taxiway' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                  Taxiway
                </a>
                <a href="{{ route('admin.statistik', ['time_filter' => $timeFilter, 'kategori' => $kategori, 'zona' => 'apron']) }}" class="px-2.5 py-1 rounded transition-colors {{ $zonaFilter === 'apron' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                  Apron &amp; Hanggar
                </a>
                <a href="{{ route('admin.statistik', ['time_filter' => $timeFilter, 'kategori' => $kategori, 'zona' => 'perimeter']) }}" class="px-2.5 py-1 rounded transition-colors {{ $zonaFilter === 'perimeter' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                  Perimeter Luar
                </a>
              </div>
            </div>
          </div>
        </div>

      </div>

    </div>

    <!-- 3. BOTTOM SECTION: ACTIVE INCIDENTS AUDIT TABLE & TEMPORAL DISPERSAL LOG -->
    <div class="bg-card-bg rounded-xl border border-border-subtle shadow-sm p-4 sm:p-5 mb-6">
      
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-border-subtle mb-4">
        <div>
          <h2 class="text-base sm:text-lg font-bold text-slate-navy">
            Daftar Log Koordinat Grid Aktif &amp; Pemantauan Intervensi
          </h2>
          <p class="text-xs text-secondary mt-0.5">
            Integrasi langsung dengan Master Logbook Sisi Udara dan Berita Acara Ditjen Perhubungan Udara.
          </p>
        </div>

        <div class="flex items-center gap-2">
          <div class="relative">
            <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-secondary text-sm">search</span>
            <input id="gridTableSearch" onkeyup="searchGridTable()" class="pl-8 pr-3 py-1.5 text-xs border border-border-subtle rounded-lg focus:outline-none focus:border-injourney-teal bg-canvas-light w-56" placeholder="Cari Grid / Satwa..." type="text"/>
          </div>
        </div>
      </div>

      <!-- High-Density Responsive Table -->
      <div class="overflow-x-auto">
        <table id="tblGridAudit" class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-surface-container-low text-secondary text-xs uppercase font-semibold border-b border-border-subtle">
              <th class="py-2.5 px-3">Kode Grid</th>
              <th class="py-2.5 px-3">Zona Lokasi</th>
              <th class="py-2.5 px-3">Spesies Dominan</th>
              <th class="py-2.5 px-3">Tingkat Risiko</th>
              <th class="py-2.5 px-3">Jumlah Laporan</th>
              <th class="py-2.5 px-3">Aksi Mitigasi Terakhir</th>
              <th class="py-2.5 px-3">Status Terkini</th>
              <th class="py-2.5 px-3 text-right">Tindakan</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border-subtle text-xs">
            @forelse($tableGrids as $gRow)
              <tr onclick="selectGrid('{{ $gRow['grid'] }}')" class="hover:bg-surface-container-low/60 transition-colors cursor-pointer group">
                <td class="py-3 px-3">
                  <span class="font-code-coordinate font-bold text-warning-rose bg-red-50 border border-red-200 px-2 py-0.5 rounded">
                    Grid {{ $gRow['grid'] }}
                  </span>
                </td>
                <td class="py-3 px-3 font-medium text-slate-navy">{{ $gRow['zone_name'] }}</td>
                <td class="py-3 px-3">
                  <span class="font-semibold text-slate-navy">{{ $gRow['dominant_species'] }}</span>
                </td>
                <td class="py-3 px-3">
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold {{ $gRow['risk_badge_class'] }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $gRow['risk_level'] === 'Kritis' ? 'bg-warning-rose' : ($gRow['risk_level'] === 'Sedang' ? 'bg-alert-amber' : 'bg-injourney-teal') }}"></span>
                    {{ $gRow['risk_category'] }}
                  </span>
                </td>
                <td class="py-3 px-3 font-code-coordinate font-bold text-slate-navy">{{ $gRow['count'] }} Kejadian</td>
                <td class="py-3 px-3 text-secondary">{{ $gRow['last_mitigation'] }}</td>
                <td class="py-3 px-3">
                  @if($gRow['status'] === 'Belum Ditangani')
                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-800 bg-amber-100 px-2 py-0.5 rounded-full">
                      <span class="w-1.5 h-1.5 rounded-full bg-alert-amber"></span>
                      Belum Ditangani
                    </span>
                  @else
                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-full">
                      <span class="w-1.5 h-1.5 rounded-full bg-resolved-emerald"></span>
                      Telah Ditangani
                    </span>
                  @endif
                </td>
                <td class="py-3 px-3 text-right">
                  <a href="{{ route('admin.dashboard', ['search' => $gRow['grid']]) }}" class="text-injourney-teal hover:text-injourney-dark-teal font-bold hover:underline">
                    Buka Log
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center py-6 text-secondary">Tidak ada data grid yang cocok dengan filter.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

    </div>

  </main>

  <!-- OPERATIONAL COMPLIANCE FOOTER STRIP -->
  <footer class="mt-auto bg-card-bg border-t border-border-subtle py-3.5 px-4 sm:px-6 lg:px-8 text-xs text-secondary">
    <div class="max-w-[1920px] mx-auto flex flex-col md:flex-row items-center justify-between gap-2">
      <div class="flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-resolved-emerald"></span>
        <span>Konektivitas Telemetri Grid Sisi Udara: <strong>Terhubung (AOCC Server Node 01)</strong></span>
        <span class="text-border-subtle">|</span>
        <span>Sinkronisasi Terakhir: <strong>{{ now()->format('H:i:s') }} WIB</strong></span>
      </div>
      <div class="text-[11px] font-code-coordinate text-slate-500">
        PT Angkasa Pura Indonesia © {{ date('Y') }} • Wildlife Hazard Management System • Ditjen Perhubungan Udara KP 283 / 2020
      </div>
    </div>
  </footer>

  <!-- FLOATING TOAST CONTAINER -->
  <div id="gridToast" class="fixed bottom-5 right-5 z-50 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none bg-slate-900 text-white px-4 py-3 rounded-xl shadow-xl flex items-center gap-3">
    <span class="material-symbols-outlined text-resolved-emerald" id="gridToastIcon">check_circle</span>
    <span class="text-xs font-medium" id="gridToastMsg">Perintah dispersal berhasil diterbitkan.</span>
  </div>

  <!-- CLIENT-SIDE SCRIPT FOR INTERACTIVE MAP & DETAILS -->
  <script>
    // Grid Data Aggregation Dictionary
    const gridData = @json($gridAgg);

    // Zoom level state
    let currentZoom = 1.0;

    function zoomMap(delta) {
      currentZoom = Math.min(Math.max(0.7, currentZoom + delta), 1.5);
      const container = document.getElementById('gridMatrixContainer');
      container.style.transform = `scale(${currentZoom})`;
    }

    function resetMap() {
      currentZoom = 1.0;
      const container = document.getElementById('gridMatrixContainer');
      container.style.transform = 'scale(1)';
    }

    // Dynamic Grid Selection on Click
    function selectGrid(code) {
      const data = gridData[code] || {
        grid: code,
        zone_name: 'Zona ' + code + ' (Steril)',
        desc: 'Area sisi udara steril tanpa laporan satwa liar dalam periode pengamatan terpilih.',
        count: 0,
        dominant_species: 'Tidak Ada',
        status: 'Steril Aman',
        risk_category: 'Steril / Terkendali',
        peak_hours: 'Kondisi steril terpantau normal 24 jam.',
        mitigation_sop: [
          'Pertahankan inspeksi patroli berkala regu AMC.',
          'Pemotongan rumput berkala sesuai batas standar ICAO 35m.',
          'Pastikan tidak ada genangan air terbuka.'
        ]
      };

      // Update badge & title
      document.getElementById('detailGridBadge').innerText = 'GRID ' + data.grid;
      document.getElementById('detailRiskBadge').innerText = data.risk_category || 'Kategori Terkendali';
      document.getElementById('detailZoneName').innerText = data.zone_name || ('Zona ' + data.grid);
      document.getElementById('detailDesc').innerText = data.desc || 'Area terpantau aman.';
      document.getElementById('detailCount').innerText = (data.count || 0) + ' Kejadian';
      document.getElementById('detailSpecies').innerText = data.dominant_species || 'Tidak Ada';
      document.getElementById('detailStatus').innerText = data.status || 'Telah Ditangani';
      document.getElementById('detailHours').innerText = data.peak_hours || 'Terpantau normal.';

      // Update SOP list
      const sopContainer = document.getElementById('detailSopList');
      sopContainer.innerHTML = '';
      const sops = data.mitigation_sop || ['Inspeksi patroli berkala regu AMC.'];
      sops.forEach(s => {
        const li = document.createElement('li');
        li.innerText = s;
        sopContainer.appendChild(li);
      });

      // Update View Log Link
      const btnViewLog = document.getElementById('btnViewLog');
      btnViewLog.href = "{{ route('admin.dashboard') }}?search=" + encodeURIComponent(data.grid);

      // Smooth highlight effect
      const card = document.getElementById('detailCard');
      card.classList.add('ring-4', 'ring-injourney-teal/30');
      setTimeout(() => {
        card.classList.remove('ring-4', 'ring-injourney-teal/30');
      }, 500);
    }

    function searchGridTable() {
      const input = document.getElementById('gridTableSearch').value.toLowerCase();
      const table = document.getElementById('tblGridAudit');
      const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

      for (let r of rows) {
        const text = r.innerText.toLowerCase();
        r.style.display = text.includes(input) ? '' : 'none';
      }
    }

    function triggerDispersalAction() {
      const activeGrid = document.getElementById('detailGridBadge').innerText;
      showToast(`Perintah unit dispersal lapangan diterbitkan untuk ${activeGrid}. Regu jaga AMC bergerak.`);
    }

    function showToast(message) {
      const toast = document.getElementById('gridToast');
      const toastMsg = document.getElementById('gridToastMsg');
      toastMsg.innerText = message;
      toast.classList.remove('translate-y-20', 'opacity-0');
      setTimeout(() => {
        toast.classList.add('translate-y-20', 'opacity-0');
      }, 3500);
    }
  </script>

</body>
</html>
