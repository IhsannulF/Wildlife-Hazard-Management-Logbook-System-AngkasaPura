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
    .juanda-grid-row {
      display: grid;
      grid-template-columns: 32px repeat(29, minmax(32px, 1fr));
      gap: 2px;
      align-items: center;
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

  <!-- SIDEBAR NAVIGATION -->
  @include('partials.navbar', ['activePage' => 'statistic'])

  <!-- SUB-HEADER & BREADCRUMB COMMAND STRIP -->
  <section class="bg-card-bg border-b border-border-subtle px-4 sm:px-6 lg:px-8 py-3.5">
    <div class="max-w-[1920px] mx-auto flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
      
      <!-- Page Title & Short Purpose -->
      <div class="min-w-0">
        <h1 class="text-base sm:text-lg font-bold text-slate-navy tracking-tight">
          Peta Sebaran Grid &amp; Heatmap Bahaya Satwa Sisi Udara
        </h1>
        <h2 class="text-[11px] text-secondary font-normal mt-0.5">
          Monitoring sebaran hotspot satwa dan pemantauan zona bahaya sisi udara bandara.
        </h2>
      </div>

      <!-- Quick Action Controls & Time Toggles (Single Row) -->
      <div class="flex items-center flex-nowrap gap-2 shrink-0 self-start lg:self-center overflow-x-auto max-w-full">
        <!-- Time Filter Pills -->
        <div id="timeFilterPills" class="flex items-center shrink-0 bg-surface-container-low rounded-lg p-1 border border-border-subtle text-[11px] whitespace-nowrap">
          <a href="{{ route('admin.statistik', ['time_filter' => 'month', 'kategori' => $kategori, 'zona' => $zonaFilter]) }}" class="time-filter-link px-2.5 py-1 rounded font-bold transition-all {{ $timeFilter === 'month' ? 'bg-card-bg text-injourney-teal shadow-xs' : 'text-secondary hover:text-on-surface' }}">
            Bulan Ini
          </a>
          <a href="{{ route('admin.statistik', ['time_filter' => 'quarter', 'kategori' => $kategori, 'zona' => $zonaFilter]) }}" class="time-filter-link px-2.5 py-1 rounded font-bold transition-all {{ $timeFilter === 'quarter' ? 'bg-card-bg text-injourney-teal shadow-xs' : 'text-secondary hover:text-on-surface' }}">
            3 Bulan Terakhir
          </a>
          <a href="{{ route('admin.statistik', ['time_filter' => 'year', 'tahun' => $tahun, 'kategori' => $kategori, 'zona' => $zonaFilter]) }}" class="time-filter-link px-2.5 py-1 rounded font-bold transition-all {{ $timeFilter === 'year' ? 'bg-card-bg text-injourney-teal shadow-xs' : 'text-secondary hover:text-on-surface' }}">
            Tahun {{ $tahun }}
          </a>
        </div>

        <!-- Export Excel -->
        <a href="{{ route('admin.export.excel', ['tahun' => $tahun]) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 border border-border-subtle rounded-lg bg-card-bg text-on-surface hover:bg-surface-container-low text-[11px] font-semibold transition-colors shadow-2xs shrink-0 whitespace-nowrap">
          <span class="material-symbols-outlined text-sm text-secondary">file_download</span>
          <span>Export Excel</span>
        </a>

        <!-- Print Operational PDF Button -->
        <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 border border-border-subtle rounded-lg bg-card-bg text-on-surface hover:bg-surface-container-low text-[11px] font-semibold transition-colors shadow-2xs shrink-0 whitespace-nowrap">
          <span class="material-symbols-outlined text-sm text-secondary">print</span>
          <span>Cetak Peta PDF</span>
        </button>
      </div>

    </div>
  </section>

  <!-- MAIN OPERATIONAL CONTAINER -->
  <main class="flex-1 px-4 sm:px-6 lg:px-8 py-5 max-w-[1920px] mx-auto w-full flex flex-col gap-5">

    <!-- 1. KPI SUMMARY BAR (4 CARDS) -->
    <div id="kpiSummaryGrid" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3.5">
      
      <!-- KPI 1: Grid Terpadat -->
      <div class="bg-card-bg rounded-xl border border-border-subtle p-3.5 shadow-sm relative overflow-hidden flex flex-col justify-between">
        <div class="absolute top-0 left-0 right-0 h-[3px] bg-warning-rose"></div>
        <div>
          <div class="flex items-center justify-between mb-1">
            <span class="text-[10px] sm:text-[11px] uppercase text-secondary font-bold tracking-wider">Grid Terpadat (Hotspot Utama)</span>
            <span class="inline-flex items-center gap-1 text-[10px] px-1.5 py-0.5 rounded-full bg-error-container text-error font-bold">
              <span class="w-1.5 h-1.5 rounded-full bg-warning-rose animate-ping"></span>
              Level Kritis
            </span>
          </div>
          <div class="text-lg sm:text-xl font-bold text-slate-navy tracking-tight mt-1">Grid {{ $gridTerpadatTitle }}</div>
        </div>
        <div class="mt-2 pt-2 border-t border-border-subtle flex items-center justify-between text-[11px] text-secondary">
          <span>{{ $gridTerpadatCount }} Temuan / {{ $gridTerpadatPct }}% insiden</span>
          <span class="font-code-coordinate text-warning-rose font-bold">Bahu RWY 25R</span>
        </div>
      </div>

      <!-- KPI 2: Total Titik Terpetakan -->
      <div class="bg-card-bg rounded-xl border border-border-subtle p-3.5 shadow-sm relative overflow-hidden flex flex-col justify-between">
        <div class="absolute top-0 left-0 right-0 h-[3px] bg-injourney-teal"></div>
        <div>
          <div class="flex items-center justify-between mb-1">
            <span class="text-[10px] sm:text-[11px] uppercase text-secondary font-bold tracking-wider">Total Titik Insiden Terpetakan</span>
            <span class="inline-flex items-center text-[10px] px-1.5 py-0.5 rounded-full bg-surface-container-high text-injourney-teal font-semibold">
              Aktif Terpantau
            </span>
          </div>
          <div class="text-lg sm:text-xl font-bold text-slate-navy tracking-tight mt-1">{{ $totalTitikTerpetakan }} Titik</div>
        </div>
        <div class="mt-2 pt-2 border-t border-border-subtle flex items-center justify-between text-[11px] text-secondary">
          <span>Tersebar di {{ $totalSelAktif }} sel grid aktif</span>
          <span class="font-code-coordinate text-injourney-teal font-bold">15 Kolom x 12 Baris</span>
        </div>
      </div>

      <!-- KPI 3: Zona Kritis Runway Strip -->
      <div class="bg-card-bg rounded-xl border border-border-subtle p-3.5 shadow-sm relative overflow-hidden flex flex-col justify-between">
        <div class="absolute top-0 left-0 right-0 h-[3px] bg-alert-amber"></div>
        <div>
          <div class="flex items-center justify-between mb-1">
            <span class="text-[10px] sm:text-[11px] uppercase text-secondary font-bold tracking-wider">Status Zona Kritis Strip 35M</span>
            <span class="inline-flex items-center gap-1 text-[10px] px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-900 font-semibold">
              Perlu Patroli
            </span>
          </div>
          <div class="text-lg sm:text-xl font-bold text-slate-navy tracking-tight mt-1">{{ $runwayActiveCount }} Kejadian Aktif</div>
        </div>
        <div class="mt-2 pt-2 border-t border-border-subtle flex items-center justify-between text-[11px] text-secondary">
          <span>Perlu Pemantauan Mobile AMC</span>
          <span class="font-code-coordinate text-alert-amber font-bold">Siaga Unit 02</span>
        </div>
      </div>

      <!-- KPI 4: Efektivitas Dispersal Spasial -->
      <div class="bg-card-bg rounded-xl border border-border-subtle p-3.5 shadow-sm relative overflow-hidden flex flex-col justify-between">
        <div class="absolute top-0 left-0 right-0 h-[3px] bg-resolved-emerald"></div>
        <div>
          <div class="flex items-center justify-between mb-1">
            <span class="text-[10px] sm:text-[11px] uppercase text-secondary font-bold tracking-wider">Efektivitas Dispersal Spasial</span>
            <span class="inline-flex items-center text-[10px] px-1.5 py-0.5 rounded-full bg-emerald-100 text-emerald-900 font-semibold">
              Target Terlampaui
            </span>
          </div>
          <div class="text-lg sm:text-xl font-bold text-slate-navy tracking-tight mt-1">{{ $efektivitasDispersal }}% Area Steril</div>
        </div>
        <div class="mt-2 pt-2 border-t border-border-subtle flex items-center justify-between text-[11px] text-secondary">
          <span>Pasca intervensi regu jaga AMC</span>
          <span class="font-code-coordinate text-resolved-emerald font-bold">{{ $totalHandledAll }}/{{ $totalTitikTerpetakan }} Ditangani</span>
        </div>
      </div>

    </div>

    <!-- 2. PANORAMIC AIRSIDE RUNWAY GRID MAP (FULL WIDTH - EDGE TO EDGE) -->
    <div class="w-full bg-card-bg rounded-xl border border-border-subtle p-3.5 sm:p-4 shadow-sm flex flex-col relative">
      
      <!-- Airside Map Top Controls & Heading Strip -->
      <div class="flex flex-wrap items-center justify-between gap-2.5 pb-2.5 border-b border-border-subtle mb-3.5">
        <div class="flex items-center gap-2.5">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-injourney-teal text-lg">grid_4x4</span>
            <span class="text-sm sm:text-base font-bold text-slate-navy">Denah Sektoral Grid Sisi Udara — Bandara Juanda</span>
          </div>
          <div class="hidden sm:flex items-center gap-1 text-[10px] font-code-coordinate bg-surface-container-low px-2 py-0.5 rounded border border-border-subtle text-secondary font-semibold">
            <span>RUNWAY 10/28 (3.000m × 45m) • 29 KOLOM × 12 BARIS</span>
          </div>
        </div>
        
        @php
          $rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
          $x0 = 41.0;
          $y0 = 121.0;
          $cw = 941.0 / 29.0; // 32.448275862068965 px per column
          $rh = 395.0 / 12.0; // 32.916666666666664 px per row

          // Pre-index grid data with both dashed (G-19) and compact (G19) keys
          $normGrid = [];
          foreach ($gridAgg as $k => $v) {
              $normGrid[$k] = $v;
              $clean = strtoupper(str_replace(['-', ' ', '='], '', $k));
              $normGrid[$clean] = $v;
              if (preg_match('/^([A-L])(\d+)$/', $clean, $m)) {
                  $normGrid[$m[1] . '-' . (int)$m[2]] = $v;
                  $normGrid[$m[1] . (int)$m[2]] = $v;
              }
          }
        @endphp

        <!-- Operational GIS View Mode & Layer Controls -->
        <div class="flex flex-wrap items-center justify-between gap-2.5 bg-surface-container-low p-1.5 rounded-lg border border-border-subtle w-full">
          <!-- View Mode Switcher -->
          <div class="flex items-center gap-1">
            <button type="button" id="btnModeGis" onclick="setMapMode('gis')" class="text-[11px] px-2.5 py-1 rounded-md bg-injourney-teal text-white font-bold flex items-center gap-1 shadow-2xs transition-all cursor-pointer" title="Base map Juanda dengan overlay presisi sistem grid A-L & 1-29">
              <span class="material-symbols-outlined text-xs">layers</span>
              <span>Overlay GIS Juanda</span>
            </button>
            <button type="button" id="btnModeTactical" onclick="setMapMode('tactical')" class="text-[11px] px-2.5 py-1 rounded-md bg-surface-container-low text-secondary hover:text-slate-navy hover:bg-surface-container font-medium flex items-center gap-1 transition-all cursor-pointer" title="Tabel matriks data 29 kolom">
              <span class="material-symbols-outlined text-xs">grid_on</span>
              <span>Matriks 29 Kolom</span>
            </button>
          </div>

          <!-- Granular Layer Toggles (Overlay GIS) with Google Material Symbols -->
          <div id="gisLayerToggles" class="flex flex-wrap items-center gap-2 text-[11px]">
            <label class="inline-flex items-center gap-1.5 font-semibold text-slate-700 hover:text-slate-900 cursor-pointer select-none bg-card-bg px-2 py-1 rounded border border-border-subtle shadow-2xs transition-colors">
              <input type="checkbox" id="toggleLayerHotspot" checked onchange="toggleGisLayer('layerHotspots', this.checked)" class="rounded text-injourney-teal focus:ring-injourney-teal text-xs w-3.5 h-3.5">
              <span class="material-symbols-outlined text-[15px] text-warning-rose leading-none">pets</span>
              <span>Hotspot Satwa</span>
            </label>
            <label class="inline-flex items-center gap-1.5 font-semibold text-slate-700 hover:text-slate-900 cursor-pointer select-none bg-card-bg px-2 py-1 rounded border border-border-subtle shadow-2xs transition-colors">
              <input type="checkbox" id="toggleLayerFacilities" checked onchange="toggleGisLayer('layerFacilities', this.checked)" class="rounded text-injourney-teal focus:ring-injourney-teal text-xs w-3.5 h-3.5">
              <span class="material-symbols-outlined text-[15px] text-sky-600 leading-none">flight</span>
              <span>Fasilitas Bandara</span>
            </label>
            <label class="inline-flex items-center gap-1.5 font-semibold text-slate-700 hover:text-slate-900 cursor-pointer select-none bg-card-bg px-2 py-1 rounded border border-border-subtle shadow-2xs transition-colors">
              <input type="checkbox" id="toggleLayerGridLabels" checked onchange="toggleGisLayer('layerGridLabels', this.checked)" class="rounded text-injourney-teal focus:ring-injourney-teal text-xs w-3.5 h-3.5">
              <span class="material-symbols-outlined text-[15px] text-slate-600 leading-none">grid_4x4</span>
              <span>Label Grid (A1–L29)</span>
            </label>
            <label class="inline-flex items-center gap-1.5 font-semibold text-slate-700 hover:text-slate-900 cursor-pointer select-none bg-card-bg px-2 py-1 rounded border border-border-subtle shadow-2xs transition-colors">
              <input type="checkbox" id="toggleLayerDispersal" checked onchange="toggleGisLayer('layerDispersal', this.checked)" class="rounded text-injourney-teal focus:ring-injourney-teal text-xs w-3.5 h-3.5">
              <span class="material-symbols-outlined text-[15px] text-amber-500 leading-none">alt_route</span>
              <span>Rute Dispersal</span>
            </label>

            <!-- Opacity Slider -->
            <div class="inline-flex items-center gap-1.5 pl-2 border-l border-border-subtle">
              <span class="material-symbols-outlined text-[15px] text-secondary leading-none">opacity</span>
              <span class="text-secondary text-[10px] font-bold">Transparansi:</span>
              <input type="range" id="overlayOpacityRange" min="20" max="100" value="75" oninput="setOverlayOpacity(this.value)" class="w-16 h-1.5 bg-slate-300 rounded-lg appearance-none cursor-pointer accent-injourney-teal" title="Atur transparansi layer overlay">
              <span id="opacityValText" class="font-code-coordinate text-[10px] font-bold text-slate-700 w-6">75%</span>
            </div>
          </div>
        </div>
      </div>

      <!-- AIRSIDE INTERACTIVE SPATIAL CANVAS (FULL WIDTH, ZERO DEAD SPACE ON EDGES) -->
      <div class="relative w-full overflow-hidden bg-slate-950 rounded-xl border border-slate-700 select-none shadow-md">
        
        <!-- Floating Map Tools (Overlay Controls) -->
        <div class="absolute top-3 right-3 z-30 flex flex-col gap-1.5 bg-slate-navy/90 backdrop-blur-md p-1.5 rounded-lg border border-runway-grid shadow-lg">
          <button type="button" onclick="zoomMap(0.1)" class="w-8 h-8 rounded flex items-center justify-center text-white hover:bg-slate-700 transition-colors active:scale-95 cursor-pointer" title="Perbesar (Zoom In)">
            <span class="material-symbols-outlined text-base">add</span>
          </button>
          <button type="button" onclick="zoomMap(-0.1)" class="w-8 h-8 rounded flex items-center justify-center text-white hover:bg-slate-700 transition-colors active:scale-95 cursor-pointer" title="Perkecil (Zoom Out)">
            <span class="material-symbols-outlined text-base">remove</span>
          </button>
          <button type="button" onclick="resetMap()" class="w-8 h-8 rounded flex items-center justify-center text-white hover:bg-slate-700 transition-colors active:scale-95 cursor-pointer" title="Reset Skala & Posisi (1:1)">
            <span class="material-symbols-outlined text-base">restart_alt</span>
          </button>
          <div class="w-full h-px bg-runway-grid my-0.5"></div>
          <!-- Wind Compass Heading Indicator -->
          <div class="w-8 h-8 rounded flex items-center justify-center text-injourney-teal" title="Orientasi Runway 10/28 WNW 276°">
            <span class="material-symbols-outlined text-base transform rotate-[86deg]">navigation</span>
          </div>
        </div>

        <!-- Real-Time Cursor HUD Strip (Top-Left) -->
        <div id="gisCursorHud" class="absolute top-3 left-3 z-30 flex items-center gap-2 bg-slate-navy/90 backdrop-blur-md px-3 py-1.5 rounded-lg border border-runway-grid text-xs text-slate-200 pointer-events-none">
          <span class="w-2 h-2 rounded-full bg-injourney-teal animate-pulse"></span>
          <span id="gisHudCoord" class="font-code-coordinate font-bold text-cyan-300">KOORDINAT: K-10 (K10)</span>
          <span class="text-slate-500">•</span>
          <span id="gisHudInfo" class="text-slate-300 truncate max-w-xs sm:max-w-md">Bahu Runway 25R &amp; Kanal Selatan (38 Kejadian)</span>
        </div>

        <!-- ================= LAYER 1: GIS OPERATIONAL MAP CONTAINER (BASE MAP + EXACT SVG OVERLAY) ================= -->
        <div id="gisMapLayerWrapper" class="w-full overflow-hidden transition-transform duration-200 origin-top-left cursor-grab active:cursor-grabbing">
          <div id="gisCanvasInner" class="relative w-full overflow-hidden" style="aspect-ratio: 1024/723; width: 100%;">
            
            <!-- 1. BASE MAP ASLI (Gambar Peta Juanda Resmi Tanpa Crop / Stretch / Rotasi - Flush Full Edge) -->
            <img id="baseMapImg" src="{{ asset('images/gridmap_juanda.jpg') }}" alt="Gridmap Bandar Udara Internasional Juanda" class="w-full h-full object-fill block select-none pointer-events-none" onerror="this.src='{{ asset('images/gridmap_injourney.jpeg') }}'">
            
            <!-- 2. GIS OVERLAY MAPPING LAYER (SVG 1024 x 723 ViewBox - Presisi 1:1 Terhadap Pixel Grid Asli) -->
            <svg id="gisOverlaySvg" viewBox="0 0 1024 723" preserveAspectRatio="none" class="absolute inset-0 w-full h-full pointer-events-auto select-none">
                <defs>
                  <!-- Glow filter for Critical Hotspots -->
                  <filter id="glowRed" x="-20%" y="-20%" width="140%" height="140%">
                    <feGaussianBlur stdDeviation="3" result="blur" />
                    <feComposite in="SourceGraphic" in2="blur" operator="over" />
                  </filter>
                  <filter id="glowTeal" x="-20%" y="-20%" width="140%" height="140%">
                    <feGaussianBlur stdDeviation="2.5" result="blur" />
                    <feComposite in="SourceGraphic" in2="blur" operator="over" />
                  </filter>
                  <!-- Pattern for Isolated Parking -->
                  <pattern id="isolatedParkingPattern" width="8" height="8" patternTransform="rotate(45 0 0)" patternUnits="userSpaceOnUse">
                    <line x1="0" y1="0" x2="0" y2="8" stroke="rgba(239, 68, 68, 0.45)" stroke-width="1.5" />
                  </pattern>
                </defs>

                <!-- ================= OVERLAY MAPPING GROUP (CONTROLLED BY OPACITY SLIDER) ================= -->
                <g id="mainOverlayGroup" style="opacity: 0.75; transition: opacity 0.2s ease;">

                  <!-- SUB-LAYER A: FASILITAS SISI UDARA (RUNWAY, APRON, REGULATING POND, TERMINAL) -->
                  <g id="layerFacilities">
                    <!-- Runway 10/28 Strip Buffer (G5-G25 & H5-H25: x: 170.8 s/d 852.2, y: 318.5 s/d 384.3) -->
                    <rect x="170.79" y="318.50" width="681.41" height="65.83" fill="rgba(245, 158, 11, 0.16)" stroke="rgba(245, 158, 11, 0.75)" stroke-width="1.3" stroke-dasharray="4,3" rx="2" />
                    
                    <!-- Runway 10/28 Asphalt Surface (3000m x 45m: x: 170.8 s/d 852.2, y: 344 s/d 359) -->
                    <rect x="170.79" y="344.00" width="681.41" height="14.80" fill="rgba(15, 23, 42, 0.35)" stroke="rgba(255, 255, 255, 0.7)" stroke-width="1" rx="1" />
                    <line x1="185" y1="351.42" x2="838" y2="351.42" stroke="#FFFFFF" stroke-width="1.5" stroke-dasharray="8,6" />
                    
                    <!-- Runway Threshold Headings & Label -->
                    <text x="175" y="354" font-size="7" font-weight="900" fill="#FFFFFF" font-family="'DM Sans', monospace">◄ RWY 10</text>
                    <text x="822" y="354" font-size="7" font-weight="900" fill="#FFFFFF" font-family="'DM Sans', monospace">RWY 28 ►</text>
                    <text x="511.5" y="339" font-size="7.5" font-weight="800" fill="#F8FAFC" text-anchor="middle" font-family="'DM Sans', monospace" letter-spacing="0.8">RUNWAY ( 3000 M X 45 M )</text>

                    <!-- Apron B (Row E, Col 17..22: x: 560.2 s/d 754.9, y: 252.7 s/d 285.6) -->
                    <rect x="560.17" y="252.67" width="194.69" height="32.92" fill="rgba(56, 189, 248, 0.18)" stroke="#38BDF8" stroke-width="1" stroke-dasharray="3,2" rx="2" />
                    <text x="657.5" y="271.5" font-size="8" font-weight="800" fill="#0284C7" text-anchor="middle" font-family="'DM Sans', sans-serif">APRON B</text>

                    <!-- Apron C (Row E..F, Col 12..14: x: 397.9 s/d 495.3, y: 252.7 s/d 318.5) -->
                    <rect x="397.93" y="252.67" width="97.34" height="65.83" fill="rgba(56, 189, 248, 0.18)" stroke="#38BDF8" stroke-width="1" stroke-dasharray="3,2" rx="2" />
                    <text x="446.6" y="288" font-size="8" font-weight="800" fill="#0284C7" text-anchor="middle" font-family="'DM Sans', sans-serif">APRON C</text>

                    <!-- Apron A / Terminal 2 (Row J, Col 8..13: x: 268.1 s/d 462.8, y: 417.2 s/d 450.2) -->
                    <rect x="268.14" y="417.25" width="194.69" height="32.92" fill="rgba(56, 189, 248, 0.18)" stroke="#38BDF8" stroke-width="1" stroke-dasharray="3,2" rx="2" />
                    <text x="365.5" y="436" font-size="8" font-weight="800" fill="#0284C7" text-anchor="middle" font-family="'DM Sans', sans-serif">APRON A</text>

                    <!-- Terminal 1 (Row D, Col 18..22: x: 592.6 s/d 754.9, y: 219.8 s/d 252.7) -->
                    <rect x="592.62" y="219.75" width="162.24" height="32.92" fill="rgba(99, 102, 241, 0.18)" stroke="#6366F1" stroke-width="1" rx="2" />
                    <text x="673.7" y="238.5" font-size="8" font-weight="800" fill="#4338CA" text-anchor="middle" font-family="'DM Sans', sans-serif">TERMINAL 1</text>

                    <!-- Terminal 2 (Row J, Col 8..11: x: 268.1 s/d 397.9, y: 417.2 s/d 450.2) -->
                    <rect x="268.14" y="417.25" width="129.79" height="32.92" fill="rgba(99, 102, 241, 0.18)" stroke="#6366F1" stroke-width="1" rx="2" />
                    <text x="333.0" y="443" font-size="7.5" font-weight="800" fill="#4338CA" text-anchor="middle" font-family="'DM Sans', sans-serif">TERMINAL 2</text>

                    <!-- Regulating Pond 1 (Water Body, Habitat Burung Air: Row F, Col 9..11: x: 300.6 s/d 397.9, y: 285.6 s/d 318.5) -->
                    <rect x="300.59" y="285.58" width="97.34" height="32.92" fill="rgba(14, 165, 233, 0.28)" stroke="#0284C7" stroke-width="1.2" rx="2" />
                    <text x="349.2" y="304" font-size="7" font-weight="800" fill="#0369A1" text-anchor="middle" font-family="'DM Sans', sans-serif">REGULATING POND 1</text>

                    <!-- Regulating Pond 2 (Row C, Col 16..18: x: 527.7 s/d 625.1, y: 186.8 s/d 219.8) -->
                    <rect x="527.72" y="186.83" width="97.34" height="32.92" fill="rgba(14, 165, 233, 0.28)" stroke="#0284C7" stroke-width="1.2" rx="2" />
                    <text x="576.4" y="205" font-size="7" font-weight="800" fill="#0369A1" text-anchor="middle" font-family="'DM Sans', sans-serif">REGULATING POND 2</text>

                    <!-- Regulating Pond 3 (Row F, Col 23..24: x: 754.9 s/d 819.8, y: 285.6 s/d 318.5) -->
                    <rect x="754.86" y="285.58" width="64.90" height="32.92" fill="rgba(14, 165, 233, 0.28)" stroke="#0284C7" stroke-width="1.2" rx="2" />
                    <text x="787.3" y="304" font-size="7" font-weight="800" fill="#0369A1" text-anchor="middle" font-family="'DM Sans', sans-serif">REGULATING POND 3</text>

                    <!-- Fasilitas PKP-PK / ARFF 1 (Row E, Col 15: x: 511.5, y: 269.1) -->
                    <g transform="translate(504, 262)">
                      <rect width="15" height="15" rx="3" fill="#10B981" stroke="#FFFFFF" stroke-width="1" />
                      <text x="7.5" y="10.5" font-size="6" font-weight="900" fill="#FFFFFF" text-anchor="middle" font-family="'DM Sans', monospace">PKP</text>
                    </g>

                    <!-- Fasilitas PKP-PK / ARFF 2 (Row J, Col 14: x: 479.1, y: 433.7) -->
                    <g transform="translate(471.5, 426)">
                      <rect width="15" height="15" rx="3" fill="#10B981" stroke="#FFFFFF" stroke-width="1" />
                      <text x="7.5" y="10.5" font-size="6" font-weight="900" fill="#FFFFFF" text-anchor="middle" font-family="'DM Sans', monospace">PKP</text>
                    </g>

                    <!-- Emergency Operation Center (EOC, Row D, Col 15: x: 479.1, y: 236.2) -->
                    <g transform="translate(479.1, 236.2)">
                      <polygon points="0,-8 8,0 0,8 -8,0" fill="#EAB308" stroke="#FFFFFF" stroke-width="1" />
                      <text x="0" y="2.5" font-size="5" font-weight="900" fill="#713F12" text-anchor="middle" font-family="'DM Sans', monospace">EOC</text>
                    </g>

                    <!-- Rendezvous Point 1 (Row D, Col 11: x: 381.7, y: 236.2) -->
                    <g transform="translate(381.7, 236.2)">
                      <polygon points="0,-7 7,7 -7,7" fill="#FACC15" stroke="#FFFFFF" stroke-width="1" />
                      <text x="0" y="5" font-size="5" font-weight="900" fill="#713F12" text-anchor="middle" font-family="'DM Sans', monospace">RP1</text>
                    </g>

                    <!-- Rendezvous Point 2 (Row C, Col 22: x: 738.7, y: 203.3) -->
                    <g transform="translate(738.7, 203.3)">
                      <polygon points="0,-7 7,7 -7,7" fill="#FACC15" stroke="#FFFFFF" stroke-width="1" />
                      <text x="0" y="5" font-size="5" font-weight="900" fill="#713F12" text-anchor="middle" font-family="'DM Sans', monospace">RP2</text>
                    </g>

                    <!-- Isolated Parking Area (Row F, Col 4: x: 154.6, y: 302.1) -->
                    <rect x="144" y="291" width="21" height="21" rx="2" fill="url(#isolatedParkingPattern)" stroke="#EF4444" stroke-width="1" />
                    <text x="154.5" y="318" font-size="5.5" font-weight="800" fill="#EF4444" text-anchor="middle" font-family="'DM Sans', monospace">ISOLATED</text>
                  </g>

                  <!-- SUB-LAYER B: RUTE PATROLI & MITIGASI DISPERSAL AMC -->
                  <g id="layerDispersal">
                    <line x1="170.79" y1="324" x2="852.2" y2="324" stroke="#10B981" stroke-width="1.2" stroke-dasharray="4,4" opacity="0.8" />
                    <line x1="170.79" y1="378" x2="852.2" y2="378" stroke="#10B981" stroke-width="1.2" stroke-dasharray="4,4" opacity="0.8" />
                    <circle cx="235.7" cy="324" r="3" fill="#10B981" stroke="#FFFFFF" stroke-width="0.8" />
                    <circle cx="511.5" cy="324" r="3" fill="#10B981" stroke="#FFFFFF" stroke-width="0.8" />
                    <circle cx="754.9" cy="324" r="3" fill="#10B981" stroke="#FFFFFF" stroke-width="0.8" />
                  </g>

                  <!-- SUB-LAYER C: HOTSPOT BAHAYA SATWA LIAR (MAPPING PRESI PADA SETIAP SEL GRID) -->
                  <g id="layerHotspots">
                    @foreach($rows as $rIdx => $rChar)
                      @for($cNum = 1; $cNum <= 29; $cNum++)
                        @php
                          $codeKey1 = $rChar . '-' . $cNum;
                          $codeKey2 = $rChar . $cNum;
                          $itemData = $normGrid[$codeKey1] ?? ($normGrid[$codeKey2] ?? null);
                          $cellLeft = round($x0 + ($cNum - 1) * $cw, 2);
                          $cellTop  = round($y0 + $rIdx * $rh, 2);
                          $cellW    = round($cw, 2);
                          $cellH    = round($rh, 2);
                        @endphp
                        @if($itemData)
                          @php
                            $cCount = $itemData['count'];
                            $isKritis = $cCount >= 30;
                            $isSedang = $cCount >= 15 && $cCount < 30;
                            $fillColor = $isKritis ? 'rgba(239, 68, 68, 0.55)' : ($isSedang ? 'rgba(245, 158, 11, 0.50)' : 'rgba(0, 163, 180, 0.45)');
                            $strokeColor = $isKritis ? '#EF4444' : ($isSedang ? '#F59E0B' : '#00A3B4');
                          @endphp
                          <!-- Hotspot Cell {{ $codeKey1 }} -->
                          <rect x="{{ $cellLeft }}" y="{{ $cellTop }}" width="{{ $cellW }}" height="{{ $cellH }}" fill="{{ $fillColor }}" stroke="{{ $strokeColor }}" stroke-width="1.8" rx="1.5" />
                          <!-- Incident Count Badge in Cell Center -->
                          <rect x="{{ round($cellLeft + ($cellW - 17)/2, 2) }}" y="{{ round($cellTop + ($cellH - 12)/2, 2) }}" width="17" height="12" rx="3" fill="#0F172A" fill-opacity="0.88" stroke="#FFFFFF" stroke-width="0.8" />
                          <text x="{{ round($cellLeft + $cellW/2, 2) }}" y="{{ round($cellTop + $cellH/2 + 3.2, 2) }}" font-size="7" font-weight="900" fill="#FFFFFF" text-anchor="middle" font-family="'DM Sans', monospace">{{ $cCount }}</text>
                        @endif
                      @endfor
                    @endforeach
                  </g>

                  <!-- SUB-LAYER D: LABEL KODING GRID KOORDINAT A1 - L29 -->
                  <g id="layerGridLabels">
                    @foreach($rows as $rIdx => $rChar)
                      @for($cNum = 1; $cNum <= 29; $cNum++)
                        @php
                          $cellLeft = round($x0 + ($cNum - 1) * $cw, 2);
                          $cellTop  = round($y0 + $rIdx * $rh, 2);
                        @endphp
                        <text x="{{ $cellLeft + 2.5 }}" y="{{ $cellTop + 7.5 }}" font-size="5.5" font-weight="700" fill="#E2E8F0" fill-opacity="0.75" font-family="'DM Sans', monospace" pointer-events="none">{{ $rChar . $cNum }}</text>
                      @endfor
                    @endforeach
                  </g>

                </g>

                <!-- ================= INTERACTIVE HIT BOX LAYER (SEL A1 s/d L29 - HOVER & CLICK) ================= -->
                <g id="layerInteractiveHitBoxes">
                  @foreach($rows as $rIdx => $rChar)
                    @for($cNum = 1; $cNum <= 29; $cNum++)
                      @php
                        $codeKey1 = $rChar . '-' . $cNum;
                        $codeKey2 = $rChar . $cNum;
                        $cellLeft = round($x0 + ($cNum - 1) * $cw, 2);
                        $cellTop  = round($y0 + $rIdx * $rh, 2);
                        $cellW    = round($cw, 2);
                        $cellH    = round($rh, 2);
                        $hasData  = isset($normGrid[$codeKey1]) || isset($normGrid[$codeKey2]);
                      @endphp
                      <rect id="hitCell_{{ $rChar }}_{{ $cNum }}" x="{{ $cellLeft }}" y="{{ $cellTop }}" width="{{ $cellW }}" height="{{ $cellH }}" fill="transparent" class="grid-hit-cell cursor-pointer transition-colors" data-grid="{{ $codeKey1 }}" data-grid-compact="{{ $codeKey2 }}" data-row="{{ $rChar }}" data-col="{{ $cNum }}" data-has-data="{{ $hasData ? '1' : '0' }}" onmouseenter="onHoverGrid('{{ $codeKey1 }}', '{{ $rChar }}', {{ $cNum }})" onmouseleave="onLeaveGrid()" onclick="selectGrid('{{ $codeKey1 }}')" />
                    @endfor
                  @endforeach
                </g>

                <!-- ================= ACTIVE SELECTION RETICLE (ANIMATED HIGHLIGHT) ================= -->
                <g id="activeSelectionGroup">
                  <rect id="activeSelectionBox" x="0" y="0" width="32.45" height="32.92" rx="2" fill="none" stroke="#38BDF8" stroke-width="2.5" class="hidden transition-all duration-300 pointer-events-none" filter="url(#glowTeal)" />
                </g>

              </svg>
            </div>
          </div>

          <!-- ================= LAYER 2: TACTICAL 29-COLUMN MATRIX LAYER (FALLBACK ALTERNATIF) ================= -->
          <div id="mapTacticalLayer" class="hidden min-w-[960px] w-full p-4 overflow-x-auto transition-transform duration-200 origin-top-left">
            
            <!-- Column Numbers Axis (01 - 29) -->
            <div class="juanda-grid-row mb-1 text-center font-code-coordinate text-[11px] text-slate-400 font-bold">
              <div class="py-1 text-slate-500 text-[10px]">GRID</div>
              @for($c = 1; $c <= 29; $c++)
                <div class="py-1">{{ str_pad($c, 2, '0', STR_PAD_LEFT) }}</div>
              @endfor
            </div>

            @foreach($rows as $r)
              @if($r === 'G')
                <!-- ROW G & H (PRIMARY RUNWAY 10 / 28) -->
                <div class="relative py-1.5 my-1 bg-slate-950 rounded-lg border-2 border-slate-600 shadow-2xl">
                  <div class="juanda-grid-row mb-1">
                    <div class="text-center font-code-coordinate font-bold text-amber-400 text-xs">G</div>
                    @for($col = 1; $col <= 29; $col++)
                      @php
                        $code = 'G-' . $col;
                        $hasData = isset($gridAgg[$code]) || isset($gridAgg['G' . $col]);
                        $item = $hasData ? ($gridAgg[$code] ?? $gridAgg['G' . $col]) : null;
                        $isRunway = ($col >= 5 && $col <= 26);
                      @endphp
                      @if($hasData)
                        <div onclick="selectGrid('{{ $code }}')" class="h-10 rounded border-2 {{ $item['risk_border'] }} {{ $item['risk_bg'] }} {{ $item['risk_level'] === 'Kritis' ? 'grid-cell-pulse' : '' }} hover:border-white transition-all flex flex-col items-center justify-center p-0.5 cursor-pointer relative z-10" title="Grid {{ $code }}: {{ $item['dominant_species'] }} ({{ $item['count'] }} temuan)">
                          <span class="text-[10px] font-code-coordinate {{ $item['risk_text'] }} font-bold leading-none">{{ $code }}</span>
                          <span class="text-[9px] font-bold bg-white text-slate-navy px-1 rounded mt-0.5 leading-none">{{ $item['count'] }}</span>
                        </div>
                      @elseif($isRunway)
                        <div onclick="selectGrid('{{ $code }}')" class="h-10 rounded border border-slate-700/80 hover:border-injourney-teal bg-slate-900/90 flex flex-col items-center justify-center cursor-pointer text-slate-400 hover:text-white text-[10px] font-code-coordinate transition-colors" title="Grid {{ $code }} (Runway 10/28 Steril)">
                          <span class="text-[9px] opacity-70">{{ $col }}</span>
                          <div class="w-3 h-0.5 bg-white/30 mt-0.5"></div>
                        </div>
                      @else
                        <div onclick="selectGrid('{{ $code }}')" class="h-10 rounded border border-slate-800 bg-slate-950/60 flex items-center justify-center cursor-pointer text-slate-600 hover:text-slate-400 text-[10px] font-code-coordinate" title="Grid {{ $code }} (Steril)">
                          {{ $col }}
                        </div>
                      @endif
                    @endfor
                  </div>

                  <div class="h-1 my-0.5 flex items-center justify-between px-8 pointer-events-none opacity-60">
                    <span class="text-[9px] font-code-coordinate font-bold text-white tracking-widest pl-4">◄ RWY 10</span>
                    <div class="flex-1 border-t-2 border-dashed border-white mx-4"></div>
                    <span class="text-[9px] font-code-coordinate font-bold text-white tracking-widest pr-4">RWY 28 ►</span>
                  </div>

                  <div class="juanda-grid-row mt-1">
                    <div class="text-center font-code-coordinate font-bold text-amber-400 text-xs">H</div>
                    @for($col = 1; $col <= 29; $col++)
                      @php
                        $code = 'H-' . $col;
                        $hasData = isset($gridAgg[$code]) || isset($gridAgg['H' . $col]);
                        $item = $hasData ? ($gridAgg[$code] ?? $gridAgg['H' . $col]) : null;
                        $isRunway = ($col >= 5 && $col <= 26);
                      @endphp
                      @if($hasData)
                        <div onclick="selectGrid('{{ $code }}')" class="h-10 rounded border-2 {{ $item['risk_border'] }} {{ $item['risk_bg'] }} {{ $item['risk_level'] === 'Kritis' ? 'grid-cell-pulse' : '' }} hover:border-white transition-all flex flex-col items-center justify-center p-0.5 cursor-pointer relative z-10" title="Grid {{ $code }}: {{ $item['dominant_species'] }} ({{ $item['count'] }} temuan)">
                          <span class="text-[10px] font-code-coordinate {{ $item['risk_text'] }} font-bold leading-none">{{ $code }}</span>
                          <span class="text-[9px] font-bold bg-white text-slate-navy px-1 rounded mt-0.5 leading-none">{{ $item['count'] }}</span>
                        </div>
                      @elseif($isRunway)
                        <div onclick="selectGrid('{{ $code }}')" class="h-10 rounded border border-slate-700/80 hover:border-injourney-teal bg-slate-900/90 flex flex-col items-center justify-center cursor-pointer text-slate-400 hover:text-white text-[10px] font-code-coordinate transition-colors" title="Grid {{ $code }} (Runway 10/28 Steril)">
                          <span class="text-[9px] opacity-70">{{ $col }}</span>
                          <div class="w-3 h-0.5 bg-white/30 mt-0.5"></div>
                        </div>
                      @else
                        <div onclick="selectGrid('{{ $code }}')" class="h-10 rounded border border-slate-800 bg-slate-950/60 flex items-center justify-center cursor-pointer text-slate-600 hover:text-slate-400 text-[10px] font-code-coordinate" title="Grid {{ $code }} (Steril)">
                          {{ $col }}
                        </div>
                      @endif
                    @endfor
                  </div>
                </div>
              @elseif($r === 'H')
                {{-- Row H is rendered inside Runway above --}}
              @else
                <div class="juanda-grid-row mb-1">
                  <div class="text-center font-code-coordinate font-bold {{ in_array($r, ['E', 'D']) ? 'text-emerald-400' : (in_array($r, ['J', 'K']) ? 'text-sky-400' : 'text-slate-400') }} text-xs">{{ $r }}</div>
                  @for($col = 1; $col <= 29; $col++)
                    @php
                      $code = $r . '-' . $col;
                      $hasData = isset($gridAgg[$code]) || isset($gridAgg[$r . $col]);
                      $item = $hasData ? ($gridAgg[$code] ?? $gridAgg[$r . $col]) : null;
                    @endphp
                    @if($hasData)
                      <div onclick="selectGrid('{{ $code }}')" class="h-9 rounded border-2 {{ $item['risk_border'] }} {{ $item['risk_bg'] }} {{ $item['risk_level'] === 'Kritis' ? 'grid-cell-pulse' : '' }} hover:border-white transition-all flex flex-col items-center justify-center p-0.5 cursor-pointer relative z-10" title="Grid {{ $code }}: {{ $item['dominant_species'] }} ({{ $item['count'] }} temuan)">
                        <span class="text-[10px] font-code-coordinate {{ $item['risk_text'] }} font-semibold leading-none">{{ $code }}</span>
                        <span class="text-[9px] font-bold bg-white/20 text-white px-1 rounded mt-0.5 leading-none">{{ $item['count'] }}</span>
                      </div>
                    @else
                      <div onclick="selectGrid('{{ $code }}')" class="h-9 rounded border border-slate-700/60 bg-slate-800/40 hover:border-injourney-teal transition-all flex items-center justify-center cursor-pointer text-slate-600 hover:text-slate-400 text-[10px] font-code-coordinate" title="Grid {{ $code }} (Steril)">
                        {{ $col }}
                      </div>
                    @endif
                  @endfor
                </div>
              @endif
            @endforeach

          </div>

        </div>

        <!-- BOTTOM MAP FOOTER: RISK LEVEL LEGEND -->
        <div class="mt-3.5 pt-3 border-t border-border-subtle flex flex-wrap items-center justify-between gap-3 text-xs">
          <div id="mapRiskLegend" class="flex items-center gap-4 flex-wrap">
            <span class="text-secondary font-bold uppercase tracking-wider text-[11px]">Legenda Tingkat Risiko ICAO:</span>
            <div class="flex items-center gap-1.5">
              <span class="w-3 h-3 rounded bg-warning-rose"></span>
              <span class="text-slate-navy font-semibold text-[11px]">Kritis / Kategori 4 (&gt;30 Temuan)</span>
            </div>
            <div class="flex items-center gap-1.5">
              <span class="w-3 h-3 rounded bg-amber-500"></span>
              <span class="text-slate-navy font-semibold text-[11px]">Sedang / Flocking (15-30)</span>
            </div>
            <div class="flex items-center gap-1.5">
              <span class="w-3 h-3 rounded bg-sky-500"></span>
              <span class="text-slate-navy font-semibold text-[11px]">Rendah / Terpantau (&lt;15)</span>
            </div>
            <div class="flex items-center gap-1.5">
              <span class="w-3 h-3 rounded border border-slate-300 bg-surface-container-low"></span>
              <span class="text-secondary text-[11px]">Steril / Tanpa Laporan</span>
            </div>
          </div>
          <div class="text-secondary font-code-coordinate text-xs">
            Bandara Internasional Juanda (SUB/WARR) • Sisi Udara 29 Kolom × 12 Baris (Sumbu A–L)
          </div>
        </div>

      </div>

      <!-- 3. DETAIL & AUDIT ROW BELOW WIDE AIRSIDE MAP (PRACTICAL COMBINED 2-PANEL LAYOUT) -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-stretch">
        
        <!-- COMBINED CARD: DETAIL GRID TERPILIH & FILTER CEPAT (SPAN 2 COLUMNS) -->
        <div id="detailCard" class="lg:col-span-2 bg-card-bg rounded-xl border-2 border-warning-rose/30 p-3.5 sm:p-4 shadow-sm relative overflow-hidden transition-all duration-200 flex flex-col justify-between">
          <div id="detailTopBar" class="absolute top-0 left-0 right-0 h-1 bg-warning-rose"></div>
          
          <div class="grid grid-cols-1 md:grid-cols-12 gap-5 h-full">
            
            <!-- LEFT SUB-PANEL: DETAIL GRID TERPILIH (MD:COL-SPAN-7) -->
            <div class="md:col-span-7 flex flex-col justify-between">
              <div>
                <div class="flex items-center justify-between mb-2">
                  <div class="flex items-center gap-2">
                    <span id="detailGridBadge" class="font-code-coordinate text-xs font-bold bg-error-container text-error px-2 py-0.5 rounded">
                      GRID {{ $selectedGrid['grid'] ?? 'K-10' }}
                    </span>
                    <span id="detailRiskBadge" class="text-[10px] px-2 py-0.5 rounded-full bg-red-100 text-red-800 font-bold">
                      {{ $selectedGrid['risk_category'] ?? 'Kategori 4 - Kritis' }}
                    </span>
                  </div>
                  <span class="text-[11px] text-secondary font-medium">Terpilih Aktif</span>
                </div>

                <h2 id="detailZoneName" class="text-sm sm:text-base font-bold text-slate-navy">
                  {{ $selectedGrid['zone_name'] ?? 'Bahu Runway 25R & Kanal Selatan' }}
                </h2>
                <p id="detailDesc" class="text-[11px] text-secondary mt-0.5 mb-2.5 leading-relaxed">
                  {{ $selectedGrid['desc'] ?? 'Zona perimeter strip aktif runway 25R berdekatan dengan jalur drainase induk sisi barat.' }}
                </p>

                <!-- Detail Metrics Grid -->
                <div class="grid grid-cols-2 gap-2.5 mb-2.5">
                  <div class="bg-surface-container-low p-2.5 rounded-lg border border-border-subtle">
                    <span class="text-[10px] uppercase text-secondary font-bold block">Total Temuan</span>
                    <span id="detailCount" class="text-sm sm:text-base font-bold text-slate-navy mt-0.5 block">{{ $selectedGrid['count'] ?? 38 }} Kejadian</span>
                    <span id="detailFreq" class="text-[10px] text-warning-rose font-semibold mt-0.5 block">Hotspot Utama</span>
                  </div>
                  <div class="bg-surface-container-low p-2.5 rounded-lg border border-border-subtle">
                    <span class="text-[10px] uppercase text-secondary font-bold block">Spesies Dominan</span>
                    <span id="detailSpecies" class="text-xs sm:text-sm font-bold text-slate-navy truncate mt-0.5 block">{{ $selectedGrid['dominant_species'] ?? 'Biawak Air' }}</span>
                    <span id="detailStatus" class="text-[10px] text-secondary mt-0.5 block">{{ $selectedGrid['status'] ?? 'Telah Ditangani' }}</span>
                  </div>
                </div>

                <!-- Operational Time Pattern -->
                <div class="mb-2.5 bg-surface-container-low/70 p-2.5 rounded-lg border border-border-subtle text-[11px]">
                  <div class="flex items-center gap-1.5 font-bold text-slate-navy mb-1">
                    <span class="material-symbols-outlined text-xs text-injourney-teal">schedule</span>
                    <span>Puncak Frekuensi Waktu Muncul:</span>
                  </div>
                  <p id="detailHours" class="text-secondary leading-relaxed">
                    {{ $selectedGrid['peak_hours'] ?? 'Paling sering teridentifikasi pukul 06:30 - 08:30 WIB & 16:00 - 17:30 WIB saat pasang air kanal drainase dan kelembaban rumput shoulder meningkat.' }}
                  </p>
                </div>

                <!-- Mitigation SOP Recommendation -->
                <div class="mb-3 p-2.5 rounded-lg bg-amber-50/80 border border-amber-200 text-[11px]">
                  <div class="flex items-center gap-1.5 font-bold text-amber-900 mb-1">
                    <span class="material-symbols-outlined text-xs text-alert-amber">warning</span>
                    <span>Rekomendasi Tindakan Unit AMC:</span>
                  </div>
                  <ul id="detailSopList" class="text-amber-950 space-y-1 list-disc list-inside leading-relaxed text-[11px]">
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
              </div>

              <!-- Quick Action Buttons -->
              <div class="flex items-center gap-2 pt-1">
                <a id="btnViewLog" href="{{ route('admin.dashboard', ['search' => $selectedGrid['grid'] ?? 'K-10']) }}" class="flex-1 py-1.5 px-2.5 border border-border-subtle rounded-lg bg-card-bg hover:bg-surface-container-low text-on-surface text-xs font-semibold transition-colors text-center shadow-2xs">
                  Lihat Log Terkait
                </a>
                <button type="button" onclick="triggerDispersalAction()" class="flex-1 py-1.5 px-2.5 rounded-lg bg-injourney-teal hover:bg-injourney-dark-teal text-white text-xs font-semibold transition-colors text-center shadow-xs">
                  Perintah Dispersal
                </button>
              </div>
            </div>

            <!-- RIGHT SUB-PANEL: FILTER CEPAT KATEGORI & ZONA (MD:COL-SPAN-5) -->
            <div class="md:col-span-5 md:border-l md:border-border-subtle md:pl-5 flex flex-col justify-between">
              <div id="quickFilterCard" class="flex flex-col justify-between h-full">
                <div>
                  <div class="flex items-center justify-between mb-3 pb-2 border-b border-border-subtle">
                    <div class="flex items-center gap-1.5">
                      <span class="material-symbols-outlined text-injourney-teal text-base">tune</span>
                      <h3 class="text-xs sm:text-sm font-bold text-slate-navy">
                        Filter Cepat Kategori Satwa &amp; Zona
                      </h3>
                    </div>
                    <span id="filterLoadingIndicator" class="hidden items-center gap-1 text-[10px] text-injourney-teal font-semibold">
                      <span class="w-2 h-2 rounded-full bg-injourney-teal animate-ping"></span>
                      Memperbarui...
                    </span>
                  </div>

                  <div class="space-y-3">
                    <!-- Kategori Fauna -->
                    <div>
                      <span class="text-[10px] uppercase text-secondary font-bold block mb-1.5">Kategori Fauna:</span>
                      <div class="flex flex-wrap gap-1.5 text-[11px] font-semibold">
                        <a href="{{ route('admin.statistik', ['time_filter' => $timeFilter, 'kategori' => 'all', 'zona' => $zonaFilter]) }}" class="quick-filter-link px-2.5 py-1 rounded-lg transition-colors {{ $kategori === 'all' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                          Semua Satwa
                        </a>
                        <a href="{{ route('admin.statistik', ['time_filter' => $timeFilter, 'kategori' => 'burung', 'zona' => $zonaFilter]) }}" class="quick-filter-link px-2.5 py-1 rounded-lg transition-colors {{ $kategori === 'burung' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                          Avian / Burung
                        </a>
                        <a href="{{ route('admin.statistik', ['time_filter' => $timeFilter, 'kategori' => 'reptil', 'zona' => $zonaFilter]) }}" class="quick-filter-link px-2.5 py-1 rounded-lg transition-colors {{ $kategori === 'reptil' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                          Reptil
                        </a>
                        <a href="{{ route('admin.statistik', ['time_filter' => $timeFilter, 'kategori' => 'mamalia', 'zona' => $zonaFilter]) }}" class="quick-filter-link px-2.5 py-1 rounded-lg transition-colors {{ $kategori === 'mamalia' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                          Mamalia
                        </a>
                      </div>
                    </div>

                    <!-- Zona Operasional -->
                    <div class="pt-2.5 border-t border-border-subtle">
                      <span class="text-[10px] uppercase text-secondary font-bold block mb-1.5">Zona Operasional:</span>
                      <div class="flex flex-wrap gap-1.5 text-[11px] font-semibold">
                        <a href="{{ route('admin.statistik', ['time_filter' => $timeFilter, 'kategori' => $kategori, 'zona' => 'all']) }}" class="quick-filter-link px-2.5 py-1 rounded-lg transition-colors {{ $zonaFilter === 'all' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                          Semua Zona
                        </a>
                        <a href="{{ route('admin.statistik', ['time_filter' => $timeFilter, 'kategori' => $kategori, 'zona' => 'runway']) }}" class="quick-filter-link px-2.5 py-1 rounded-lg transition-colors {{ $zonaFilter === 'runway' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                          Runway Strip
                        </a>
                        <a href="{{ route('admin.statistik', ['time_filter' => $timeFilter, 'kategori' => $kategori, 'zona' => 'taxiway']) }}" class="quick-filter-link px-2.5 py-1 rounded-lg transition-colors {{ $zonaFilter === 'taxiway' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                          Taxiway
                        </a>
                        <a href="{{ route('admin.statistik', ['time_filter' => $timeFilter, 'kategori' => $kategori, 'zona' => 'apron']) }}" class="quick-filter-link px-2.5 py-1 rounded-lg transition-colors {{ $zonaFilter === 'apron' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                          Apron &amp; Hanggar
                        </a>
                        <a href="{{ route('admin.statistik', ['time_filter' => $timeFilter, 'kategori' => $kategori, 'zona' => 'perimeter']) }}" class="quick-filter-link px-2.5 py-1 rounded-lg transition-colors {{ $zonaFilter === 'perimeter' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                          Perimeter Luar
                        </a>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Guidance & Context Tip -->
                <div class="mt-4 p-2.5 bg-surface-container-low rounded-lg border border-border-subtle text-[11px] text-secondary leading-relaxed">
                  <div class="flex items-center gap-1.5 font-bold text-slate-navy mb-0.5">
                    <span class="material-symbols-outlined text-xs text-injourney-teal">touch_app</span>
                    <span>Interaktivitas Denah Sisi Udara:</span>
                  </div>
                  <p>Klik sel koordinat mana saja pada denah runway 10/28 atau daftar hotspot untuk memperbarui detail sektor secara instan.</p>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- CARD 2: TOP 5 AIRSIDE HAZARD ZONES RANKING (SPAN 1 COLUMN) -->
        <div id="top5Card" class="lg:col-span-1 bg-card-bg rounded-xl border border-border-subtle p-3.5 sm:p-4 shadow-sm flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between mb-3">
              <h2 class="text-xs sm:text-sm font-bold text-slate-navy">
                Peringkat 5 Grid Hotspot Teratas
              </h2>
              <span class="text-[11px] text-injourney-teal font-semibold">Bulan Berjalan</span>
            </div>

            <div class="space-y-2.5">
              @forelse($top5Grids as $top)
                <div onclick="selectGrid('{{ $top['grid'] }}')" class="cursor-pointer group">
                  <div class="flex justify-between items-center text-[11px] mb-1">
                    <span class="font-bold text-slate-navy group-hover:text-injourney-teal transition-colors flex items-center gap-1.5">
                      <span class="w-3.5 h-3.5 rounded-full bg-slate-100 text-slate-700 text-center text-[9px] font-black inline-flex items-center justify-center">
                        {{ $top['rank'] }}
                      </span>
                      Grid {{ $top['grid'] }} ({{ $top['name'] }})
                    </span>
                    <span class="font-code-coordinate font-bold text-slate-700">{{ $top['count'] }} Laporan</span>
                  </div>
                  <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="{{ $top['color'] }} h-1.5 rounded-full transition-all duration-500" style="width: {{ $top['bar_width'] }}%"></div>
                  </div>
                </div>
              @empty
                <div class="text-center py-6 text-xs text-secondary">
                  Tidak ada data hotspot untuk filter terpilih.
                </div>
              @endforelse
            </div>
          </div>
        </div>

      </div>

      <!-- 4. BOTTOM SECTION: ACTIVE INCIDENTS AUDIT TABLE & TEMPORAL DISPERSAL LOG -->
      <div class="bg-card-bg rounded-xl border border-border-subtle shadow-sm p-3.5 sm:p-4 mb-6">
      
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-2.5 pb-2.5 border-b border-border-subtle mb-3.5">
        <div>
          <h2 class="text-sm sm:text-base font-bold text-slate-navy">
            Daftar Log Koordinat Grid Aktif &amp; Pemantauan Intervensi
          </h2>
          <p class="text-[11px] text-secondary mt-0.5">
            Integrasi langsung dengan Master Logbook Sisi Udara dan Berita Acara Ditjen Perhubungan Udara.
          </p>
        </div>

        <div class="flex items-center gap-2">
          <div class="relative">
            <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-secondary text-sm">search</span>
            <input id="gridTableSearch" onkeyup="searchGridTable()" class="pl-8 pr-2.5 py-1 text-[11px] border border-border-subtle rounded-lg focus:outline-none focus:border-injourney-teal bg-canvas-light w-52" placeholder="Cari Grid / Satwa..." type="text"/>
          </div>
        </div>
      </div>

      <!-- High-Density Responsive Table Container with Pagination -->
      <div id="tblGridAuditContainer">
        <div class="overflow-x-auto">
          <table id="tblGridAudit" class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-surface-container-low text-secondary text-[10px] uppercase font-bold tracking-wider border-b border-border-subtle">
                <th class="py-2.5 px-2.5 sm:px-3 w-[85px] whitespace-nowrap">Kode Grid</th>
                <th class="py-2.5 px-2.5 sm:px-3 min-w-[130px]">Zona Lokasi</th>
                <th class="py-2.5 px-2.5 sm:px-3 min-w-[110px]">Spesies Dominan</th>
                <th class="py-2.5 px-2.5 sm:px-3 min-w-[115px] whitespace-nowrap">Tingkat Risiko</th>
                <th class="py-2.5 px-2.5 sm:px-3 min-w-[95px] whitespace-nowrap">Jumlah Laporan</th>
                <th class="py-2.5 px-2.5 sm:px-3 min-w-[140px]">Aksi Mitigasi Terakhir</th>
                <th class="py-2.5 px-2.5 sm:px-3 min-w-[105px] whitespace-nowrap">Status Terkini</th>
                <th class="py-2.5 px-2.5 sm:px-3 text-right min-w-[80px] whitespace-nowrap">Tindakan</th>
              </tr>
            </thead>
            <tbody id="tblGridAuditBody" class="divide-y divide-border-subtle text-[11px]">
              @forelse($tableGrids as $gRow)
                <tr onclick="selectGrid('{{ $gRow['grid'] }}')" class="audit-row hover:bg-surface-container-low/60 transition-colors cursor-pointer group">
                  <td class="py-2.5 px-2.5 sm:px-3 whitespace-nowrap">
                    <span class="inline-flex items-center justify-center font-code-coordinate font-bold text-rose-600 bg-rose-50 border border-rose-200/90 px-2 py-0.5 rounded text-[11px] tracking-tight whitespace-nowrap shadow-2xs">
                      Grid&nbsp;{{ $gRow['grid'] }}
                    </span>
                  </td>
                  <td class="py-2.5 px-2.5 sm:px-3 font-medium text-slate-navy leading-snug">{{ $gRow['zone_name'] }}</td>
                  <td class="py-2.5 px-2.5 sm:px-3 leading-snug">
                    <span class="font-semibold text-slate-navy">{{ $gRow['dominant_species'] }}</span>
                  </td>
                  <td class="py-2.5 px-2.5 sm:px-3 whitespace-nowrap">
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold {{ $gRow['risk_badge_class'] }}">
                      <span class="w-1.5 h-1.5 rounded-full {{ $gRow['risk_level'] === 'Kritis' ? 'bg-warning-rose animate-pulse' : ($gRow['risk_level'] === 'Sedang' ? 'bg-alert-amber' : 'bg-injourney-teal') }}"></span>
                      {{ $gRow['risk_category'] }}
                    </span>
                  </td>
                  <td class="py-2.5 px-2.5 sm:px-3 font-code-coordinate font-bold text-slate-navy whitespace-nowrap">{{ $gRow['count'] }} Kejadian</td>
                  <td class="py-2.5 px-2.5 sm:px-3 text-secondary leading-snug">{{ $gRow['last_mitigation'] }}</td>
                  <td class="py-2.5 px-2.5 sm:px-3 whitespace-nowrap">
                    @if($gRow['status'] === 'Belum Ditangani')
                      <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-800 bg-amber-100 px-2 py-0.5 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-alert-amber"></span>
                        Belum Ditangani
                      </span>
                    @else
                      <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-resolved-emerald"></span>
                        Telah Ditangani
                      </span>
                    @endif
                  </td>
                  <td class="py-2.5 px-2.5 sm:px-3 text-right whitespace-nowrap">
                    <a href="{{ route('admin.dashboard', ['search' => $gRow['grid']]) }}" class="inline-flex items-center gap-1 text-injourney-teal hover:text-injourney-dark-teal font-bold hover:underline text-xs">
                      <span>Buka Log</span>
                      <span class="material-symbols-outlined text-xs">arrow_forward</span>
                    </a>
                  </td>
                </tr>
              @empty
                <tr id="auditEmptyRow">
                  <td colspan="8" class="text-center py-6 text-secondary">Tidak ada data grid yang cocok dengan filter.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <!-- Pagination Controls for Table (10 per page) -->
        <div id="tablePaginationBar" class="mt-3.5 pt-3 border-t border-border-subtle flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
          <div class="text-secondary text-[11px]">
            Menampilkan <span id="auditPageStart" class="font-bold text-slate-navy">1</span> - <span id="auditPageEnd" class="font-bold text-slate-navy">10</span> dari <span id="auditTotalRows" class="font-bold text-slate-navy">{{ count($tableGrids) }}</span> data grid aktif
          </div>
          
          <div class="flex items-center gap-1.5">
            <!-- Previous Button -->
            <button type="button" id="btnAuditPrev" onclick="changeAuditPage(-1)" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-border-subtle bg-card-bg hover:bg-surface-container-low text-slate-navy disabled:opacity-40 disabled:cursor-not-allowed font-medium text-xs transition-colors shadow-2xs">
              <span class="material-symbols-outlined text-sm">chevron_left</span>
              <span>Sebelumnya</span>
            </button>

            <!-- Dynamic Page Number Buttons -->
            <div id="auditPageNumbers" class="flex items-center gap-1">
            </div>

            <!-- Next Button -->
            <button type="button" id="btnAuditNext" onclick="changeAuditPage(1)" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-border-subtle bg-card-bg hover:bg-surface-container-low text-slate-navy disabled:opacity-40 disabled:cursor-not-allowed font-medium text-xs transition-colors shadow-2xs">
              <span>Selanjutnya</span>
              <span class="material-symbols-outlined text-sm">chevron_right</span>
            </button>
          </div>
        </div>
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

  <!-- RAW DATA STORE (JSON) -->
  <script id="rawGridDataJson" type="application/json">@json($gridAgg)</script>

  <!-- CLIENT-SIDE SCRIPT FOR INTERACTIVE MAP & DETAILS -->
  <script>
    // Grid Data Aggregation Dictionary
    let rawGridData = JSON.parse(document.getElementById('rawGridDataJson').textContent);
    
    // Normalization dictionary for instant O(1) lookup
    const gridData = {};
    const rowLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
    const X0 = 41.0;
    const Y0 = 121.0;
    const CW = 941.0 / 29.0;
    const RH = 395.0 / 12.0;

    function rebuildGridData(newRaw) {
      rawGridData = newRaw;
      Object.keys(gridData).forEach(k => delete gridData[k]);
      Object.keys(rawGridData).forEach(k => {
        gridData[k] = rawGridData[k];
        const clean = k.replace(/[-_ =]/g, '').toUpperCase();
        gridData[clean] = rawGridData[k];
        const m = clean.match(/^([A-L])(\d+)$/);
        if (m) {
          gridData[`${m[1]}-${parseInt(m[2], 10)}`] = rawGridData[k];
          gridData[`${m[1]}${parseInt(m[2], 10)}`] = rawGridData[k];
        }
      });
    }

    rebuildGridData(rawGridData);

    // Zoom and pan state
    let currentZoom = 1.0;
    let panX = 0;
    let panY = 0;
    let isPanning = false;
    let startX = 0;
    let startY = 0;
    let activeSelectedCode = '{{ $selectedGrid['grid'] ?? 'K-10' }}';

    function setMapMode(mode) {
      const gisWrapper = document.getElementById('gisMapLayerWrapper');
      const tacLayer = document.getElementById('mapTacticalLayer');
      const togglesBar = document.getElementById('gisLayerToggles');

      const btnGis = document.getElementById('btnModeGis');
      const btnTac = document.getElementById('btnModeTactical');

      const activeCls = 'text-[11px] px-2.5 py-1 rounded-md bg-injourney-teal text-white font-bold flex items-center gap-1 shadow-2xs transition-all cursor-pointer';
      const inactiveCls = 'text-[11px] px-2.5 py-1 rounded-md bg-surface-container-low text-secondary hover:text-slate-navy hover:bg-surface-container font-medium flex items-center gap-1 transition-all cursor-pointer';

      if (btnGis) btnGis.className = (mode === 'gis') ? activeCls : inactiveCls;
      if (btnTac) btnTac.className = (mode === 'tactical') ? activeCls : inactiveCls;

      if (mode === 'gis') {
        if (gisWrapper) gisWrapper.classList.remove('hidden');
        if (tacLayer) tacLayer.classList.add('hidden');
        if (togglesBar) togglesBar.classList.remove('hidden');
      } else {
        if (gisWrapper) gisWrapper.classList.add('hidden');
        if (tacLayer) tacLayer.classList.remove('hidden');
        if (togglesBar) togglesBar.classList.add('hidden');
      }
      resetMap();
    }

    function toggleGisLayer(layerId, isVisible) {
      const el = document.getElementById(layerId);
      if (el) {
        el.style.display = isVisible ? '' : 'none';
      }
    }

    function setOverlayOpacity(val) {
      const group = document.getElementById('mainOverlayGroup');
      const text = document.getElementById('opacityValText');
      if (group) group.style.opacity = (val / 100);
      if (text) text.innerText = val + '%';
    }

    function applyTransform() {
      const canvas = document.getElementById('gisCanvasInner');
      if (canvas) {
        canvas.style.transform = `scale(${currentZoom}) translate(${panX}px, ${panY}px)`;
      }
    }

    function zoomMap(delta) {
      currentZoom = Math.min(Math.max(0.75, currentZoom + delta), 2.2);
      applyTransform();
    }

    function resetMap() {
      currentZoom = 1.0;
      panX = 0;
      panY = 0;
      applyTransform();
    }

    // Pan interaction
    const mapWrapper = document.getElementById('gisMapLayerWrapper');
    if (mapWrapper) {
      mapWrapper.addEventListener('mousedown', (e) => {
        if (e.target.tagName.toLowerCase() === 'input' || e.target.tagName.toLowerCase() === 'button') return;
        isPanning = true;
        startX = e.clientX - panX;
        startY = e.clientY - panY;
      });

      window.addEventListener('mousemove', (e) => {
        if (!isPanning || currentZoom <= 1.0) return;
        panX = e.clientX - startX;
        panY = e.clientY - startY;
        applyTransform();
      });

      window.addEventListener('mouseup', () => {
        isPanning = false;
      });
    }

    // Real-Time HUD Hover Handler
    function onHoverGrid(code, row, col) {
      const clean = code.replace(/[-_ =]/g, '').toUpperCase();
      const compact = row + col;
      const data = gridData[code] || gridData[compact];

      const hudCoord = document.getElementById('gisHudCoord');
      const hudInfo  = document.getElementById('gisHudInfo');

      if (hudCoord) hudCoord.innerText = `KOORDINAT: ${compact} (${row}-${col})`;
      if (hudInfo) {
        if (data && data.count > 0) {
          hudInfo.innerText = `${data.zone_name} • ${data.count} Laporan (${data.dominant_species})`;
        } else {
          hudInfo.innerText = `Area Steril Sisi Udara • Sumbu Horisontal ${col}, Sumbu Vertikal ${row}`;
        }
      }

      // Highlight hit cell
      const hitEl = document.getElementById(`hitCell_${row}_${col}`);
      if (hitEl) {
        hitEl.setAttribute('stroke', '#00A3B4');
        hitEl.setAttribute('stroke-width', '1.5');
        hitEl.setAttribute('fill', 'rgba(0, 163, 180, 0.15)');
      }
    }

    function onLeaveGrid() {
      // Clear all hover fills on hit cells
      document.querySelectorAll('.grid-hit-cell').forEach(el => {
        el.setAttribute('stroke', 'transparent');
        el.setAttribute('fill', 'transparent');
      });

      // Restore HUD to active selected grid
      selectGrid(activeSelectedCode, false);
    }

    // Dynamic Grid Selection on Click
    function selectGrid(code, updateActive = true) {
      if (updateActive) {
        activeSelectedCode = code;
      }

      const clean = code.replace(/[-_ =]/g, '').toUpperCase();
      const m = clean.match(/^([A-L])(\d+)$/);
      let rowChar = 'K';
      let colNum  = 10;
      let compactCode = code;
      let standardCode = code;

      if (m) {
        rowChar = m[1];
        colNum  = parseInt(m[2], 10);
        compactCode = rowChar + colNum;
        standardCode = rowChar + '-' + colNum;
      }

      const data = gridData[standardCode] || gridData[compactCode] || {
        grid: standardCode,
        zone_name: 'Zona ' + compactCode + ' (Area Sisi Udara)',
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

      // Position Active Selection Box Reticle in SVG
      const rIdx = rowLetters.indexOf(rowChar);
      const cIdx = colNum - 1;
      if (rIdx >= 0 && cIdx >= 0 && cIdx < 29) {
        const boxX = (X0 + cIdx * CW).toFixed(2);
        const boxY = (Y0 + rIdx * RH).toFixed(2);
        const boxW = CW.toFixed(2);
        const boxH = RH.toFixed(2);

        const selBox = document.getElementById('activeSelectionBox');
        if (selBox) {
          selBox.setAttribute('x', boxX);
          selBox.setAttribute('y', boxY);
          selBox.setAttribute('width', boxW);
          selBox.setAttribute('height', boxH);
          selBox.classList.remove('hidden');
        }
      }

      // Update HUD
      const hudCoord = document.getElementById('gisHudCoord');
      const hudInfo  = document.getElementById('gisHudInfo');
      if (hudCoord) hudCoord.innerText = `TERPILIH: ${compactCode} (${standardCode})`;
      if (hudInfo) hudInfo.innerText = `${data.zone_name} • ${data.count} Laporan (${data.dominant_species})`;

      // Update Sidebar Card Details
      const badgeEl = document.getElementById('detailGridBadge');
      if (badgeEl) badgeEl.innerText = 'GRID ' + compactCode;
      
      const riskEl = document.getElementById('detailRiskBadge');
      if (riskEl) riskEl.innerText = data.risk_category || 'Kategori Terkendali';
      
      const zoneEl = document.getElementById('detailZoneName');
      if (zoneEl) zoneEl.innerText = data.zone_name || ('Zona ' + compactCode);
      
      const descEl = document.getElementById('detailDesc');
      if (descEl) descEl.innerText = data.desc || 'Area terpantau aman.';
      
      const countEl = document.getElementById('detailCount');
      if (countEl) countEl.innerText = (data.count || 0) + ' Kejadian';
      
      const speciesEl = document.getElementById('detailSpecies');
      if (speciesEl) speciesEl.innerText = data.dominant_species || 'Tidak Ada';
      
      const statusEl = document.getElementById('detailStatus');
      if (statusEl) statusEl.innerText = data.status || 'Telah Ditangani';
      
      const hoursEl = document.getElementById('detailHours');
      if (hoursEl) hoursEl.innerText = data.peak_hours || 'Terpantau normal.';

      // Update SOP list
      const sopContainer = document.getElementById('detailSopList');
      if (sopContainer) {
        sopContainer.innerHTML = '';
        const sops = data.mitigation_sop || ['Inspeksi patroli berkala regu AMC.'];
        sops.forEach(s => {
          const li = document.createElement('li');
          li.innerText = s;
          sopContainer.appendChild(li);
        });
      }

      // Update View Log Link
      const btnViewLog = document.getElementById('btnViewLog');
      if (btnViewLog) {
        btnViewLog.href = "{{ route('admin.dashboard') }}?search=" + encodeURIComponent(standardCode);
      }

      // Smooth highlight effect on sidebar card
      const card = document.getElementById('detailCard');
      if (card && updateActive) {
        card.classList.add('ring-4', 'ring-injourney-teal/30');
        setTimeout(() => {
          card.classList.remove('ring-4', 'ring-injourney-teal/30');
        }, 500);
      }
    }

    let currentAuditPage = 1;
    const AUDIT_PAGE_SIZE = 10;

    function renderAuditTablePagination() {
      const searchInput = document.getElementById('gridTableSearch');
      const filterQuery = (searchInput ? searchInput.value : '').toLowerCase().trim();
      const tbody = document.getElementById('tblGridAuditBody');
      if (!tbody) return;

      const rows = Array.from(tbody.querySelectorAll('tr.audit-row'));
      if (rows.length === 0) {
        const pagBar = document.getElementById('tablePaginationBar');
        if (pagBar) pagBar.style.display = 'none';
        return;
      }

      // Filter rows based on search input
      const matchedRows = rows.filter(r => {
        if (!filterQuery) return true;
        const text = r.innerText.toLowerCase();
        return text.includes(filterQuery);
      });

      const totalMatches = matchedRows.length;
      const totalPages = Math.max(1, Math.ceil(totalMatches / AUDIT_PAGE_SIZE));

      if (currentAuditPage > totalPages) {
        currentAuditPage = totalPages;
      }
      if (currentAuditPage < 1) {
        currentAuditPage = 1;
      }

      const startIdx = (currentAuditPage - 1) * AUDIT_PAGE_SIZE;
      const endIdx = Math.min(startIdx + AUDIT_PAGE_SIZE, totalMatches);

      // Hide all rows first
      rows.forEach(r => r.style.display = 'none');

      // Show only rows in current page
      for (let i = startIdx; i < endIdx; i++) {
        matchedRows[i].style.display = '';
      }

      // Handle empty search result message
      let emptyRow = document.getElementById('auditEmptySearchRow');
      if (totalMatches === 0) {
        if (!emptyRow) {
          emptyRow = document.createElement('tr');
          emptyRow.id = 'auditEmptySearchRow';
          emptyRow.innerHTML = '<td colspan="8" class="text-center py-6 text-secondary">Tidak ada data grid yang cocok dengan pencarian.</td>';
          tbody.appendChild(emptyRow);
        }
        emptyRow.style.display = '';
      } else if (emptyRow) {
        emptyRow.style.display = 'none';
      }

      // Update counters
      const startEl = document.getElementById('auditPageStart');
      const endEl = document.getElementById('auditPageEnd');
      const totalEl = document.getElementById('auditTotalRows');
      if (startEl) startEl.innerText = totalMatches > 0 ? (startIdx + 1) : 0;
      if (endEl) endEl.innerText = endIdx;
      if (totalEl) totalEl.innerText = totalMatches;

      // Update buttons
      const btnPrev = document.getElementById('btnAuditPrev');
      const btnNext = document.getElementById('btnAuditNext');
      if (btnPrev) btnPrev.disabled = (currentAuditPage <= 1);
      if (btnNext) btnNext.disabled = (currentAuditPage >= totalPages || totalMatches === 0);

      // Render page numbers
      const pageNumbersContainer = document.getElementById('auditPageNumbers');
      if (pageNumbersContainer) {
        pageNumbersContainer.innerHTML = '';
        if (totalPages > 1) {
          for (let p = 1; p <= totalPages; p++) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.innerText = p;
            if (p === currentAuditPage) {
              btn.className = 'w-7 h-7 flex items-center justify-center rounded-lg bg-injourney-teal text-white font-bold text-xs shadow-xs';
            } else {
              btn.className = 'w-7 h-7 flex items-center justify-center rounded-lg border border-border-subtle bg-card-bg hover:bg-surface-container-low text-secondary hover:text-slate-navy font-semibold text-xs transition-colors';
              btn.onclick = () => goToAuditPage(p);
            }
            pageNumbersContainer.appendChild(btn);
          }
        }
      }

      const pagBar = document.getElementById('tablePaginationBar');
      if (pagBar) {
        pagBar.style.display = totalMatches === 0 && !filterQuery ? 'none' : 'flex';
      }
    }

    function changeAuditPage(delta) {
      currentAuditPage += delta;
      renderAuditTablePagination();
    }

    function goToAuditPage(page) {
      currentAuditPage = page;
      renderAuditTablePagination();
    }

    function searchGridTable() {
      currentAuditPage = 1;
      renderAuditTablePagination();
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

    // Auto-initialize selection on load
    document.addEventListener('DOMContentLoaded', () => {
      selectGrid(activeSelectedCode, false);
      renderAuditTablePagination();
    });

    // Realtime Filter Handler (Prevents page refresh, maintains exact scroll position)
    async function filterRealtime(url) {
      const scrollPos = window.scrollY; // Preserve exact scroll position
      
      const indicator = document.getElementById('filterLoadingIndicator');
      if (indicator) {
        indicator.classList.remove('hidden');
        indicator.classList.add('inline-flex');
      }

      try {
        const response = await fetch(url, {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!response.ok) throw new Error('Network response not ok');
        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        // 1. Update KPI Summary Grid
        const newKpi = doc.getElementById('kpiSummaryGrid');
        const currKpi = document.getElementById('kpiSummaryGrid');
        if (newKpi && currKpi) currKpi.innerHTML = newKpi.innerHTML;

        // 2. Update Hotspots in SVG Layer
        const newHotspots = doc.getElementById('layerHotspots');
        const currHotspots = document.getElementById('layerHotspots');
        if (newHotspots && currHotspots) currHotspots.innerHTML = newHotspots.innerHTML;

        // 3. Update Tactical Matrix Layer
        const newTac = doc.getElementById('mapTacticalLayer');
        const currTac = document.getElementById('mapTacticalLayer');
        if (newTac && currTac) currTac.innerHTML = newTac.innerHTML;

        // 4. Update Top 5 Card
        const newTop5 = doc.getElementById('top5Card');
        const currTop5 = document.getElementById('top5Card');
        if (newTop5 && currTop5) currTop5.innerHTML = newTop5.innerHTML;

        // 5. Update Quick Filter Card
        const newFilter = doc.getElementById('quickFilterCard');
        const currFilter = document.getElementById('quickFilterCard');
        if (newFilter && currFilter) currFilter.innerHTML = newFilter.innerHTML;

        // 6. Update Time Filter Pills
        const newTime = doc.getElementById('timeFilterPills');
        const currTime = document.getElementById('timeFilterPills');
        if (newTime && currTime) currTime.innerHTML = newTime.innerHTML;

        // 7. Update Table
        const newTable = doc.getElementById('tblGridAuditContainer');
        const currTable = document.getElementById('tblGridAuditContainer');
        if (newTable && currTable) {
          currTable.innerHTML = newTable.innerHTML;
          currentAuditPage = 1;
          renderAuditTablePagination();
        }

        // 8. Update gridData dictionary from new JSON store
        const newJsonEl = doc.getElementById('rawGridDataJson');
        if (newJsonEl) {
          try {
            const parsed = JSON.parse(newJsonEl.textContent);
            rebuildGridData(parsed);
          } catch(e) {
            console.error('Failed parsing new grid JSON', e);
          }
        }

        // 9. Update Active Selected Grid Card
        if (gridData[activeSelectedCode]) {
          selectGrid(activeSelectedCode, false);
        } else {
          const firstKey = Object.keys(gridData)[0] || 'K-10';
          selectGrid(firstKey, true);
        }

        // 10. Push URL state without page reload
        history.pushState(null, '', url);

      } catch (err) {
        console.error('Error applying realtime filter:', err);
        window.location.href = url;
      } finally {
        const ind = document.getElementById('filterLoadingIndicator');
        if (ind) {
          ind.classList.remove('inline-flex');
          ind.classList.add('hidden');
        }
        // Force scroll position preservation immediately and in next frame
        window.scrollTo({ top: scrollPos, behavior: 'instant' });
        requestAnimationFrame(() => {
          window.scrollTo({ top: scrollPos, behavior: 'instant' });
        });
      }
    }

    // Intercept clicks on quick filter links & time filter links
    document.addEventListener('click', function(e) {
      const link = e.target.closest('.quick-filter-link, .time-filter-link');
      if (link && link.href) {
        e.preventDefault();
        filterRealtime(link.href);
      }
    });

    // Browser navigation (Back / Forward)
    window.addEventListener('popstate', function() {
      filterRealtime(window.location.href);
    });
  </script>

</body>
</html>
