<!DOCTYPE html>
<html class="h-full bg-canvas-light" lang="id">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Katalog Master Satwa Liar Sisi Udara - InJourney Airports WHMS</title>

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
            "primary": "#006874",
            "primary-fixed": "#97f0ff",
            "alert-amber": "#F59E0B",
            "tertiary": "#006780",
            "secondary-fixed-dim": "#bec6e0",
            "secondary": "#565e74",
            "tertiary-fixed": "#b7eaff",
            "outline": "#6d797c",
            "primary-fixed-dim": "#5dd7e9",
            "on-secondary-fixed": "#131b2e",
            "injourney-teal": "#00A3B4",
            "background": "#f8f9ff",
            "tertiary-container": "#2ca0c1",
            "surface-container-high": "#dce9ff",
            "slate-navy": "#0F172A",
            "secondary-fixed": "#dae2fd",
            "runway-grid": "#334155",
            "error-container": "#ffdad6",
            "injourney-dark-teal": "#0891B2",
            "on-secondary-fixed-variant": "#3f465c",
            "on-error": "#ffffff",
            "surface-container-lowest": "#ffffff",
            "surface-bright": "#f8f9ff",
            "inverse-on-surface": "#eaf1ff",
            "border-subtle": "#E2E8F0",
            "canvas-light": "#F8FAFC",
            "surface-container": "#e5eeff",
            "error": "#ba1a1a",
            "surface": "#f8f9ff",
            "on-secondary-container": "#5c647a",
            "on-background": "#0b1c30",
            "on-tertiary-container": "#00313f",
            "resolved-emerald": "#10B981",
            "surface-container-low": "#eff4ff",
            "inverse-primary": "#5dd7e9",
            "on-primary-fixed-variant": "#004f58",
            "tertiary-fixed-dim": "#6cd3f7",
            "slate-surface": "#1E293B",
            "secondary-container": "#dae2fd",
            "inverse-surface": "#213145",
            "on-primary-fixed": "#001f24",
            "on-tertiary": "#ffffff",
            "on-primary-container": "#003238",
            "on-surface": "#0b1c30",
            "primary-container": "#00a3b4",
            "on-surface-variant": "#3d494b",
            "on-primary": "#ffffff",
            "warning-rose": "#EF4444",
            "surface-variant": "#d3e4fe",
            "card-bg": "#FFFFFF",
            "on-tertiary-fixed-variant": "#004e61",
            "on-secondary": "#ffffff",
            "outline-variant": "#bcc9cb",
            "surface-dim": "#cbdbf5",
            "surface-container-highest": "#d3e4fe",
            "on-tertiary-fixed": "#001f28",
            "surface-tint": "#006874",
            "on-error-container": "#93000a"
          },
          "borderRadius": {
            "DEFAULT": "0.25rem",
            "lg": "0.5rem",
            "xl": "0.75rem",
            "full": "9999px"
          },
          "fontFamily": {
            "label-sm": ["DM Sans"],
            "body-md": ["DM Sans"],
            "headline-sm": ["DM Sans"],
            "headline-xl-mobile": ["DM Sans"],
            "body-sm": ["DM Sans"],
            "label-md": ["DM Sans"],
            "body-lg": ["DM Sans"],
            "headline-md": ["DM Sans"],
            "code-coordinate": ["DM Sans"],
            "label-lg": ["DM Sans"],
            "headline-xl": ["DM Sans"],
            "headline-lg": ["DM Sans"]
          }
        }
      }
    }
  </script>
  <style>
    .material-symbols-outlined {
      font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
      display: inline-block;
      vertical-align: middle;
      line-height: 1;
    }
    .badge-pulse {
      animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: .45; }
    }
    /* Custom scrollbar */
    ::-webkit-scrollbar {
      width: 6px;
      height: 6px;
    }
    ::-webkit-scrollbar-track {
      background: #F1F5F9;
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
<body class="min-h-full bg-canvas-light text-on-surface font-body-md antialiased selection:bg-injourney-teal selection:text-white flex flex-col">

  <!-- TOP APP BAR (Unified Shared Navbar) -->
  @include('partials.navbar', ['activePage' => 'manajemen'])

  <!-- ================= MAIN OPERATIONAL CONTENT CONTAINER ================= -->
  <main class="flex-1 max-w-[1680px] w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">
    
    <!-- 1. BREADCRUMB & PAGE TITLE SECTION -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 pb-2 border-b border-border-subtle">
      <div>
        <div class="flex items-center gap-2 text-secondary text-xs mb-1.5 font-medium">
          <span>Sistem WHMS</span>
          <span class="material-symbols-outlined text-xs">chevron_right</span>
          <span>Manajemen Master Data</span>
          <span class="material-symbols-outlined text-xs">chevron_right</span>
          <span class="text-injourney-teal font-semibold">Katalog Master Jenis Satwa</span>
        </div>
        <h1 class="text-xl sm:text-2xl font-bold text-on-surface tracking-tight">
          Katalog Master Satwa Liar Sisi Udara <span class="text-secondary font-normal text-sm sm:text-base block sm:inline sm:ml-2">(Airside Wildlife Database)</span>
        </h1>
        <p class="text-xs sm:text-sm text-secondary mt-1 max-w-4xl">
          Basis data taksonomi operasional satwa liar, tingkat risiko tabrakan pesawat (<span class="font-medium text-slate-navy">Wildlife Strike Risk</span>), pola habitat, dan rekomendasi metode pengusiran resmi ICAO Doc 9137 Part 3.
        </p>
      </div>

      <!-- Action Cluster Bar -->
      <div class="flex flex-wrap items-center gap-2 sm:gap-2.5 self-start lg:self-center">
        <!-- Sub-tabs for full PRD REQ-MGT-005 capability -->
        <div class="flex items-center bg-surface-container rounded-lg p-1 border border-border-subtle text-xs font-semibold mr-2">
          <a href="{{ route('admin.manajemen', ['tab' => 'satwa']) }}" class="px-3 py-1.5 rounded transition-all {{ $tab === 'satwa' ? 'bg-white text-injourney-teal font-bold shadow-2xs' : 'text-secondary hover:text-slate-navy' }}">
            Katalog Satwa
          </a>
          <a href="{{ route('admin.manajemen', ['tab' => 'fields']) }}" class="px-3 py-1.5 rounded transition-all {{ $tab === 'fields' ? 'bg-white text-injourney-teal font-bold shadow-2xs' : 'text-secondary hover:text-slate-navy' }}">
            Field Formulir
          </a>
          <a href="{{ route('admin.manajemen', ['tab' => 'users']) }}" class="px-3 py-1.5 rounded transition-all {{ $tab === 'users' ? 'bg-white text-injourney-teal font-bold shadow-2xs' : 'text-secondary hover:text-slate-navy' }}">
            Manajemen User
          </a>
        </div>

        @if($tab === 'satwa')
          <a href="{{ route('admin.export.excel') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg border border-border-subtle bg-white text-slate-navy hover:bg-surface-container-low transition-colors shadow-2xs">
            <span class="material-symbols-outlined text-sm">download</span>
            <span>Export Excel</span>
          </a>
          <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg border border-border-subtle bg-white text-slate-navy hover:bg-surface-container-low transition-colors shadow-2xs">
            <span class="material-symbols-outlined text-sm">print</span>
            <span>Cetak PDF</span>
          </button>
          <button type="button" onclick="openModal('addSpeciesModal')" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-lg bg-injourney-teal hover:bg-injourney-dark-teal text-white shadow-xs transition-all active:scale-[0.98]">
            <span class="material-symbols-outlined text-sm">add</span>
            <span>+ Tambah Spesies</span>
          </button>
        @endif
      </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('sukses'))
      <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-xs font-semibold flex items-center justify-between">
        <span class="flex items-center gap-2">
          <span class="material-symbols-outlined text-sm text-resolved-emerald">check_circle</span>
          {{ session('sukses') }}
        </span>
        <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold">&times;</button>
      </div>
    @endif
    @if(session('error'))
      <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-xs font-semibold flex items-center justify-between">
        <span class="flex items-center gap-2">
          <span class="material-symbols-outlined text-sm text-warning-rose">error</span>
          {{ session('error') }}
        </span>
        <button onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 font-bold">&times;</button>
      </div>
    @endif

    @if($tab === 'satwa')
      <!-- ================= TAB: KATALOG MASTER SATWA ================= -->

      <!-- 2. SUMMARY METRIC CARDS (ICAO AVIATION STAT CARDS) -->
      <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Card 1: Total Spesies -->
        <div class="bg-card-bg border border-border-subtle rounded-xl p-4 shadow-sm relative overflow-hidden flex flex-col justify-between">
          <div class="absolute top-0 left-0 right-0 h-1 bg-injourney-teal"></div>
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs uppercase text-secondary font-semibold tracking-wider">Total Spesies Terdaftar</span>
            <div class="w-8 h-8 rounded-lg bg-surface-container flex items-center justify-center text-injourney-teal">
              <span class="material-symbols-outlined text-lg">database</span>
            </div>
          </div>
          <div class="flex items-baseline gap-2">
            <span class="text-2xl sm:text-3xl font-bold text-slate-navy">{{ $totalSpesies }}</span>
            <span class="text-xs text-secondary font-medium">Spesies Terdata</span>
          </div>
          <div class="mt-3 pt-2 border-t border-border-subtle flex items-center justify-between text-xs text-secondary">
            <span>Avian: {{ $avianCount }} | Reptil: {{ $reptilCount }} | Mamalia: {{ $mamaliaCount }}</span>
            <span class="text-resolved-emerald font-semibold">100% Aktif</span>
          </div>
        </div>

        <!-- Card 2: Kategori Kritis -->
        <div class="bg-card-bg border border-border-subtle rounded-xl p-4 shadow-sm relative overflow-hidden flex flex-col justify-between">
          <div class="absolute top-0 left-0 right-0 h-1 bg-warning-rose"></div>
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs uppercase text-secondary font-semibold tracking-wider">Spesies Risiko Kritis</span>
            <div class="w-8 h-8 rounded-lg bg-error-container/50 flex items-center justify-center text-warning-rose">
              <span class="material-symbols-outlined text-lg badge-pulse">warning</span>
            </div>
          </div>
          <div class="flex items-baseline gap-2">
            <span class="text-2xl sm:text-3xl font-bold text-slate-navy">{{ $kritisCount }}</span>
            <span class="text-[11px] px-2 py-0.5 rounded-full bg-red-100 text-red-800 font-bold">Kategori 4 - Tinggi</span>
          </div>
          <div class="mt-3 pt-2 border-t border-border-subtle flex items-center text-xs text-secondary truncate" title="Biawak Air, Kera, Anjing Liar, Burung Elang Tikus, Ular Sanca">
            <span class="truncate">Biawak Air, Kera, Anjing Liar, Burung Elang...</span>
          </div>
        </div>

        <!-- Card 3: Kategori Risiko Sedang -->
        <div class="bg-card-bg border border-border-subtle rounded-xl p-4 shadow-sm relative overflow-hidden flex flex-col justify-between">
          <div class="absolute top-0 left-0 right-0 h-1 bg-alert-amber"></div>
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs uppercase text-secondary font-semibold tracking-wider">Kategori Risiko Sedang</span>
            <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-alert-amber">
              <span class="material-symbols-outlined text-lg">flight_takeoff</span>
            </div>
          </div>
          <div class="flex items-baseline gap-2">
            <span class="text-2xl sm:text-3xl font-bold text-slate-navy">{{ $sedangCount }}</span>
            <span class="text-xs text-secondary font-medium">Spesies (Flocking Threat)</span>
          </div>
          <div class="mt-3 pt-2 border-t border-border-subtle flex items-center text-xs text-secondary">
            <span>Mayoritas: Kawanan Burung Air / Wading</span>
          </div>
        </div>

        <!-- Card 4: Frekuensi Temuan Tertinggi -->
        <div class="bg-card-bg border border-border-subtle rounded-xl p-4 shadow-sm relative overflow-hidden flex flex-col justify-between">
          <div class="absolute top-0 left-0 right-0 h-1 bg-injourney-dark-teal"></div>
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs uppercase text-secondary font-semibold tracking-wider">Frekuensi Temuan Tertinggi</span>
            <div class="w-8 h-8 rounded-lg bg-cyan-50 flex items-center justify-center text-injourney-teal">
              <span class="material-symbols-outlined text-lg">near_me</span>
            </div>
          </div>
          <div class="flex items-baseline gap-1.5 truncate">
            <span class="text-xl font-bold text-slate-navy truncate">Biawak &amp; Blekok</span>
          </div>
          <div class="mt-3 pt-2 border-t border-border-subtle flex items-center justify-between text-xs text-secondary">
            <span class="text-slate-navy font-medium">Hotspot:</span>
            <div class="flex gap-1.5">
              <span class="px-1.5 py-0.5 rounded bg-surface-container font-code-coordinate text-injourney-teal font-bold text-[11px]">Grid K-10</span>
              <span class="px-1.5 py-0.5 rounded bg-surface-container font-code-coordinate text-injourney-teal font-bold text-[11px]">Grid D-9</span>
            </div>
          </div>
        </div>

      </section>

      <!-- 3. FILTER & CONTROLS TOOLBAR -->
      <section class="bg-card-bg border border-border-subtle rounded-xl p-4 shadow-sm space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
          
          <!-- Search Input -->
          <div class="relative flex-1 max-w-xl">
            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-secondary text-sm">search</span>
            <input class="w-full pl-9 pr-4 py-2 bg-white border border-border-subtle rounded-lg text-xs font-medium text-slate-navy placeholder:text-secondary/60 focus:border-injourney-teal focus:ring-2 focus:ring-injourney-teal/20 transition-all outline-none" id="speciesSearch" onkeyup="filterCatalog()" placeholder="Cari nama satwa, klasifikasi, tingkat risiko, atau zona habitat..." type="text"/>
          </div>

          <!-- Filter Selects & View Toggles -->
          <div class="flex flex-wrap items-center gap-2.5">
            <!-- Filter Tingkat Risiko -->
            <div class="relative min-w-[170px]">
              <select class="w-full appearance-none bg-white border border-border-subtle rounded-lg px-3 py-2 text-xs font-semibold text-slate-navy focus:border-injourney-teal outline-none pr-8 cursor-pointer" id="riskFilter" onchange="filterCatalog()">
                <option value="all">Semua Tingkat Risiko</option>
                <option value="kritis">Risiko Kritis (Kategori 4)</option>
                <option value="sedang">Risiko Sedang (Kategori 3)</option>
                <option value="rendah">Risiko Rendah (Kategori 2)</option>
              </select>
              <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-secondary pointer-events-none text-sm">expand_more</span>
            </div>

            <!-- Filter Zona Habitat -->
            <div class="relative min-w-[180px]">
              <select class="w-full appearance-none bg-white border border-border-subtle rounded-lg px-3 py-2 text-xs font-semibold text-slate-navy focus:border-injourney-teal outline-none pr-8 cursor-pointer" id="habitatFilter" onchange="filterCatalog()">
                <option value="all">Semua Zona Habitat</option>
                <option value="runway">Runway / Strip Buffer</option>
                <option value="perimeter">Perimeter &amp; Kanal Saluran</option>
                <option value="apron">Area Apron / Taxiway</option>
              </select>
              <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-secondary pointer-events-none text-sm">expand_more</span>
            </div>

            <!-- Layout View Switcher -->
            <div class="flex items-center bg-surface-container rounded-lg p-0.5 border border-border-subtle ml-auto lg:ml-0">
              <button type="button" class="p-1.5 rounded-md bg-white text-injourney-teal shadow-xs transition-all" id="viewGridBtn" onclick="setViewMode('grid')" title="Tampilan Kartu Grid">
                <span class="material-symbols-outlined text-base block">grid_view</span>
              </button>
              <button type="button" class="p-1.5 rounded-md text-secondary hover:text-slate-navy transition-all" id="viewTableBtn" onclick="setViewMode('table')" title="Tampilan Tabel Detail">
                <span class="material-symbols-outlined text-base block">table_rows</span>
              </button>
            </div>

          </div>
        </div>

        <!-- Category Filter Chips -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs font-semibold">
          <span class="text-secondary mr-1 hidden sm:inline">Kategori:</span>
          <button type="button" class="category-chip px-3 py-1.5 rounded-lg bg-injourney-teal text-white transition-all shadow-xs flex items-center gap-1.5" onclick="setCategoryFilter('all', this)">
            <span class="material-symbols-outlined text-sm">apps</span>
            Semua Satwa ({{ $totalSpesies }})
          </button>
          <button type="button" class="category-chip px-3 py-1.5 rounded-lg bg-surface-container text-slate-navy hover:bg-surface-container-high transition-all flex items-center gap-1.5" onclick="setCategoryFilter('burung', this)">
            <span class="material-symbols-outlined text-sm">flight</span>
            Burung / Avian ({{ $avianCount }})
          </button>
          <button type="button" class="category-chip px-3 py-1.5 rounded-lg bg-surface-container text-slate-navy hover:bg-surface-container-high transition-all flex items-center gap-1.5" onclick="setCategoryFilter('reptil', this)">
            <span class="material-symbols-outlined text-sm">pest_control</span>
            Reptil ({{ $reptilCount }})
          </button>
          <button type="button" class="category-chip px-3 py-1.5 rounded-lg bg-surface-container text-slate-navy hover:bg-surface-container-high transition-all flex items-center gap-1.5" onclick="setCategoryFilter('mamalia', this)">
            <span class="material-symbols-outlined text-sm">pets</span>
            Mamalia ({{ $mamaliaCount }})
          </button>
        </div>
      </section>

      <!-- 4. GRID KATALOG KARTU SATWA INTERAKTIF -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5" id="catalogGridView">
        @foreach($satwaList as $satwa)
          @php
            $cat = strtolower($satwa->kategori ?: (str_contains($satwa->nama, 'Burung') ? 'burung' : (str_contains($satwa->nama, 'Biawak') || str_contains($satwa->nama, 'Ular') ? 'reptil' : 'mamalia')));
            $risk = strtolower($satwa->tingkat_risiko ?: 'sedang');
            $habitat = strtolower($satwa->grid_hotspot ?: 'runway');
            $idCode = 'WHMS-' . strtoupper(substr($cat, 0, 3)) . '-' . str_pad($satwa->id, 3, '0', STR_PAD_LEFT);
            $gridDisplay = $satwa->grid_hotspot ?: 'K-10';
            $descText = $satwa->deskripsi ?: 'Pemantauan intensif di area perimeter keselamatan penerbangan bandara.';
            $sopLines = array_filter(array_map('trim', explode("\n", $satwa->sop_pengusiran ?: "Sirene Patroli AMC\nBio-Akustik / Repellent")));
          @endphp

          <div class="species-card bg-card-bg border border-border-subtle rounded-xl overflow-hidden shadow-sm hover:shadow-md hover:border-injourney-teal/60 transition-all duration-200 flex flex-col justify-between"
               data-category="{{ $cat }}"
               data-habitat="{{ $habitat }}"
               data-name="{{ strtolower($satwa->nama) }}"
               data-risk="{{ $risk }}">
            <div>
              <!-- Visual Photo Container with Badges -->
              <div class="relative h-48 w-full bg-slate-900 overflow-hidden group">
                <img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" src="{{ $satwa->foto_url }}" alt="{{ $satwa->nama }}" onerror="this.src='{{ asset('images/biawak.jpeg') }}'"/>
                <div class="absolute inset-0 bg-gradient-to-t from-slate-navy/80 via-transparent to-black/30"></div>
                
                <!-- Category Tag -->
                <span class="absolute top-3 left-3 px-2.5 py-1 rounded-md bg-slate-navy/80 backdrop-blur-md text-white text-[11px] font-semibold uppercase tracking-wider flex items-center gap-1 border border-white/10">
                  <span class="material-symbols-outlined text-xs text-cyan-300">
                    {{ $cat === 'burung' ? 'flight' : ($cat === 'reptil' ? 'pest_control' : 'pets') }}
                  </span>
                  {{ $satwa->kategori_label }}
                </span>

                <!-- Risk Badge -->
                @if($risk === 'kritis')
                  <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full bg-rose-500 text-white text-[11px] font-bold tracking-wide flex items-center gap-1 shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-white badge-pulse"></span>
                    Risiko Kritis (Kategori 4)
                  </span>
                @elseif($risk === 'rendah')
                  <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full bg-emerald-500 text-white text-[11px] font-bold tracking-wide flex items-center gap-1 shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-white"></span>
                    Risiko Rendah (Kategori 2)
                  </span>
                @else
                  <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full bg-amber-500 text-white text-[11px] font-bold tracking-wide flex items-center gap-1 shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-white"></span>
                    Risiko Sedang (Kategori 3)
                  </span>
                @endif

                <div class="absolute bottom-2.5 left-3 right-3 flex items-center justify-between text-white">
                  <span class="text-xs font-code-coordinate text-cyan-300 bg-slate-navy/60 px-2 py-0.5 rounded backdrop-blur-xs font-bold">{{ $idCode }}</span>
                  <span class="text-xs flex items-center gap-1 text-slate-200">
                    <span class="material-symbols-outlined text-xs">pin_drop</span> Grid {{ $gridDisplay }}
                  </span>
                </div>
              </div>

              <!-- Card Content Body -->
              <div class="p-4 space-y-3">
                <div>
                  <h3 class="text-base font-bold text-slate-navy hover:text-injourney-teal transition-colors">{{ $satwa->nama }}</h3>
                  <p class="text-xs text-secondary mt-0.5 line-clamp-2 leading-relaxed">
                    {{ $descText }}
                  </p>
                </div>

                <!-- Habitat & Hotspot Tag -->
                <div class="bg-surface-container-low rounded-lg p-2.5 border border-border-subtle">
                  <div class="flex items-center justify-between text-xs mb-1">
                    <span class="text-secondary text-[10px] uppercase font-semibold">Hotspot &amp; Habitat Utama</span>
                    <span class="px-1.5 py-0.2 rounded bg-injourney-teal/10 text-injourney-teal font-code-coordinate text-[11px] font-bold">Grid {{ $gridDisplay }}</span>
                  </div>
                  <p class="text-xs font-medium text-slate-navy flex items-center gap-1.5 truncate">
                    <span class="material-symbols-outlined text-sm text-injourney-teal">location_on</span>
                    {{ $satwa->grid_hotspot ?: 'Runway 07L/25R & Perimeter' }}
                  </p>
                </div>

                <!-- SOP Dispersal Badges -->
                <div>
                  <span class="text-[10px] font-semibold uppercase text-secondary block mb-1.5">Metode Pengusiran Resmi (ICAO):</span>
                  <div class="flex flex-wrap gap-1.5">
                    @foreach(array_slice($sopLines, 0, 2) as $sopLine)
                      <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-slate-100 text-slate-navy text-[11px] font-medium border border-slate-200">
                        <span class="material-symbols-outlined text-xs text-injourney-teal">shield</span>
                        {{ $sopLine }}
                      </span>
                    @endforeach
                  </div>
                </div>
              </div>
            </div>

            <!-- Card Footer Actions -->
            <div class="p-4 pt-0">
              <div class="pt-3 border-t border-border-subtle flex items-center justify-between gap-2">
                <button type="button" class="flex-1 py-1.5 px-3 rounded-lg bg-injourney-teal/10 hover:bg-injourney-teal hover:text-white text-injourney-teal text-xs font-semibold text-center transition-colors"
                        onclick="openSpeciesDetail('{{ addslashes($satwa->nama) }}', '{{ addslashes($satwa->kategori_label) }}', '{{ addslashes($satwa->risiko_label) }}', '{{ addslashes($gridDisplay) }}', '{{ addslashes($descText) }}', '{{ addslashes($satwa->jam_puncak ?: '08:00 - 15:00 WIB') }}', '{{ addslashes($satwa->bobot_rata_rata ?: 'FOD Ringan - Sedang') }}')">
                  Detail Profil &amp; SOP
                </button>
                <button type="button" onclick="openEditSpeciesModal({{ $satwa->id }}, '{{ addslashes($satwa->nama) }}', '{{ $cat }}', '{{ $risk }}', '{{ addslashes($gridDisplay) }}', '{{ addslashes($descText) }}', '{{ addslashes($satwa->sop_pengusiran ?: '') }}')" class="p-1.5 text-secondary hover:text-injourney-teal hover:bg-surface-container rounded-lg transition-colors" title="Edit Spesies">
                  <span class="material-symbols-outlined text-base">edit</span>
                </button>
                <form action="{{ route('admin.manajemen.satwa.delete', $satwa->id) }}" method="POST" class="inline m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data spesies {{ addslashes($satwa->nama) }}?');">
                  @csrf
                  <button type="submit" class="p-1.5 text-secondary hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Spesies">
                    <span class="material-symbols-outlined text-base">delete</span>
                  </button>
                </form>
              </div>
            </div>
          </div>
        @endforeach
      </div>

      <!-- TABLE VIEW (Alternative Operational Mode) -->
      <div class="hidden bg-card-bg border border-border-subtle rounded-xl overflow-hidden shadow-sm" id="catalogTableView">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-surface-container text-slate-navy uppercase tracking-wider font-semibold border-b border-border-subtle">
              <tr>
                <th class="py-3 px-4">Spesies (Nama Umum)</th>
                <th class="py-3 px-4">Klasifikasi</th>
                <th class="py-3 px-4">Level Risiko (ICAO Strike)</th>
                <th class="py-3 px-4">Hotspot Grid</th>
                <th class="py-3 px-4">Aktivitas &amp; Karakter Bahaya</th>
                <th class="py-3 px-4">SOP Pengusiran Utama</th>
                <th class="py-3 px-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-border-subtle">
              @foreach($satwaList as $satwa)
                @php
                  $cat = strtolower($satwa->kategori ?: 'burung');
                  $risk = strtolower($satwa->tingkat_risiko ?: 'sedang');
                  $gridDisplay = $satwa->grid_hotspot ?: 'K-10';
                @endphp
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="py-3.5 px-4 font-bold text-slate-navy flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full {{ $risk === 'kritis' ? 'bg-rose-500' : ($risk === 'rendah' ? 'bg-emerald-500' : 'bg-amber-500') }}"></span>
                    {{ $satwa->nama }}
                  </td>
                  <td class="py-3.5 px-4"><span class="px-2 py-0.5 rounded bg-surface-container text-[11px] font-semibold">{{ $satwa->kategori_label }}</span></td>
                  <td class="py-3.5 px-4">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $risk === 'kritis' ? 'bg-red-100 text-red-800' : ($risk === 'rendah' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800') }}">
                      {{ $satwa->risiko_label }}
                    </span>
                  </td>
                  <td class="py-3.5 px-4 font-code-coordinate text-injourney-teal font-bold">Grid {{ $gridDisplay }}</td>
                  <td class="py-3.5 px-4 text-secondary max-w-xs truncate">{{ $satwa->deskripsi ?: 'Pemantauan rutin area sisi udara.' }}</td>
                  <td class="py-3.5 px-4 text-slate-navy max-w-xs truncate">{{ $satwa->sop_pengusiran ?: 'Sirene patroli & repellent trap' }}</td>
                  <td class="py-3.5 px-4 text-right whitespace-nowrap">
                    <button type="button" class="text-injourney-teal hover:underline font-bold mr-2"
                            onclick="openSpeciesDetail('{{ addslashes($satwa->nama) }}', '{{ addslashes($satwa->kategori_label) }}', '{{ addslashes($satwa->risiko_label) }}', '{{ addslashes($gridDisplay) }}', '{{ addslashes($satwa->deskripsi ?: '') }}', '{{ addslashes($satwa->jam_puncak ?: '') }}', '{{ addslashes($satwa->bobot_rata_rata ?: '') }}')">
                      Detail SOP
                    </button>
                    <button type="button" onclick="openEditSpeciesModal({{ $satwa->id }}, '{{ addslashes($satwa->nama) }}', '{{ $cat }}', '{{ $risk }}', '{{ addslashes($gridDisplay) }}', '{{ addslashes($satwa->deskripsi ?: '') }}', '{{ addslashes($satwa->sop_pengusiran ?: '') }}')" class="text-secondary hover:text-slate-navy font-bold">
                      Edit
                    </button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>

      <!-- 5. AIRSIDE RUNWAY GRID MAP TELEMETRY PREVIEW -->
      <section class="bg-card-bg border border-border-subtle rounded-xl p-5 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-border-subtle">
          <div>
            <h2 class="text-base font-bold text-slate-navy flex items-center gap-2">
              <span class="material-symbols-outlined text-injourney-teal">grid_4x4</span>
              Matriks Sebaran Spasial Runway &amp; Perimeter (Airside Hazard Heatmap)
            </h2>
            <p class="text-xs text-secondary mt-0.5">
              Integrasi zonasi grid operasi ICAO Annex 14 dengan konsentrasi populasi satwa liar aktif
            </p>
          </div>
          <div class="flex items-center gap-3 text-xs">
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-rose-500"></span> Zona Kritis (K-10, F-5)</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-amber-400"></span> Zona Sedang (D-9, E-12)</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-injourney-teal"></span> Terpantau Aman</span>
          </div>
        </div>

        <!-- Compact Visual Grid representation -->
        <div class="grid grid-cols-6 sm:grid-cols-12 gap-2 mt-4 text-center font-code-coordinate text-xs font-bold">
          <div class="p-2.5 rounded border border-border-subtle bg-surface-container-low text-secondary">A-1</div>
          <div class="p-2.5 rounded border border-injourney-teal bg-cyan-50 text-injourney-teal">A-2 (Apron)</div>
          <div class="p-2.5 rounded border border-border-subtle bg-surface-container-low text-secondary">B-3</div>
          <div class="p-2.5 rounded border border-rose-300 bg-red-50 text-rose-700">B-4 (Anjing)</div>
          <div class="p-2.5 rounded border border-border-subtle bg-surface-container-low text-secondary">C-5</div>
          <div class="p-2.5 rounded border border-rose-300 bg-red-50 text-rose-700">C-6 (Sanca)</div>
          <div class="p-2.5 rounded border border-amber-300 bg-amber-50 text-amber-700">D-9 (Blekok)</div>
          <div class="p-2.5 rounded border border-border-subtle bg-surface-container-low text-secondary">E-10</div>
          <div class="p-2.5 rounded border border-amber-300 bg-amber-50 text-amber-700">E-12 (Kuntul)</div>
          <div class="p-2.5 rounded border border-rose-400 bg-red-100 text-rose-800 font-extrabold shadow-xs">F-5 (Kera)</div>
          <div class="p-2.5 rounded border border-rose-400 bg-red-100 text-rose-800 font-extrabold shadow-xs ring-2 ring-rose-400/40">K-10 (Biawak)</div>
          <div class="p-2.5 rounded border border-border-subtle bg-surface-container-low text-secondary">Z-20</div>
        </div>
      </section>

    @elseif($tab === 'fields')
      <!-- ================= TAB: FORM FIELD BUILDER ================= -->
      <div class="bg-card-bg rounded-xl border border-border-subtle p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4 mb-6 flex-wrap">
          <div>
            <h3 class="text-lg font-bold text-slate-navy">Pengaturan Field Form Pelaporan Dinamis</h3>
            <p class="text-xs text-secondary mt-0.5">Atur input formulir pelaporan petugas lapangan, tipe data, dan opsi dropdown.</p>
          </div>
          <button type="button" onclick="openModal('modalAddField')" class="px-4 py-2 bg-injourney-teal hover:bg-injourney-dark-teal text-white text-xs font-bold rounded-lg shadow-xs transition-all">
            + Tambah Field Baru
          </button>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-surface-container text-slate-navy uppercase font-semibold border-b border-border-subtle">
                <th class="py-3 px-4">Urutan</th>
                <th class="py-3 px-4">Label Field</th>
                <th class="py-3 px-4">Tipe Input</th>
                <th class="py-3 px-4">Status Wajib</th>
                <th class="py-3 px-4">Status Aktif</th>
                <th class="py-3 px-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-border-subtle">
              @foreach($fields as $f)
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="py-3 px-4 font-bold text-slate-navy">{{ $f->urutan }}</td>
                  <td class="py-3 px-4 font-semibold text-slate-navy">{{ $f->label }}</td>
                  <td class="py-3 px-4"><span class="px-2 py-0.5 rounded bg-surface-container font-code-coordinate uppercase">{{ $f->tipe }}</span></td>
                  <td class="py-3 px-4">{{ $f->wajib ? 'Ya (Required)' : 'Opsional' }}</td>
                  <td class="py-3 px-4">
                    <span class="px-2 py-0.5 rounded-full font-bold {{ $f->aktif ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                      {{ $f->aktif ? 'Aktif' : 'Nonaktif' }}
                    </span>
                  </td>
                  <td class="py-3 px-4 text-right">
                    <form action="{{ route('admin.manajemen.fields.toggle', $f->id) }}" method="POST" class="inline">
                      @csrf
                      <button type="submit" class="text-xs text-injourney-teal hover:underline font-bold mr-2">
                        {{ $f->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                      </button>
                    </form>
                    <form action="{{ route('admin.manajemen.fields.delete', $f->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus field ini?');">
                      @csrf
                      <button type="submit" class="text-xs text-rose-600 hover:underline font-bold">Hapus</button>
                    </form>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>

    @elseif($tab === 'users')
      <!-- ================= TAB: MANAJEMEN USER ================= -->
      <div class="bg-card-bg rounded-xl border border-border-subtle p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4 mb-6 flex-wrap">
          <div>
            <h3 class="text-lg font-bold text-slate-navy">Manajemen Akun Petugas &amp; Personil</h3>
            <p class="text-xs text-secondary mt-0.5">Kelola kredensial akun dinas petugas lapangan AMC, ARFF, Avsec, dan Safety Section.</p>
          </div>
          <button type="button" onclick="openModal('modalAddUser')" class="px-4 py-2 bg-injourney-teal hover:bg-injourney-dark-teal text-white text-xs font-bold rounded-lg shadow-xs transition-all">
            + Tambah User Baru
          </button>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-surface-container text-slate-navy uppercase font-semibold border-b border-border-subtle">
                <th class="py-3 px-4">Nama Lengkap</th>
                <th class="py-3 px-4">Username</th>
                <th class="py-3 px-4">Jabatan / Posko</th>
                <th class="py-3 px-4">Hak Akses Role</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-border-subtle">
              @foreach($users as $u)
                <tr class="hover:bg-surface-container-low transition-colors">
                  <td class="py-3 px-4 font-bold text-slate-navy">{{ $u->namalengkap }}</td>
                  <td class="py-3 px-4 font-code-coordinate text-injourney-teal">{{ $u->username }}</td>
                  <td class="py-3 px-4 text-secondary">{{ $u->jabatan }}</td>
                  <td class="py-3 px-4">
                    <span class="px-2.5 py-0.5 rounded-full font-bold uppercase {{ $u->role === 'admin' ? 'bg-cyan-100 text-cyan-800' : 'bg-slate-100 text-slate-700' }}">
                      {{ $u->role }}
                    </span>
                  </td>
                  <td class="py-3 px-4">
                    <span class="px-2 py-0.5 rounded-full font-bold {{ $u->aktif ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                      {{ $u->aktif ? 'Aktif' : 'Nonaktif' }}
                    </span>
                  </td>
                  <td class="py-3 px-4 text-right">
                    @if($u->id !== auth()->id())
                      <form action="{{ route('admin.manajemen.users.delete', $u->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus user {{ $u->username }}?');">
                        @csrf
                        <button type="submit" class="text-xs text-rose-600 hover:underline font-bold">Hapus</button>
                      </form>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    @endif

  </main>

  <!-- ================= MODAL DETAIL PROFIL SATWA ================= -->
  <div class="fixed inset-0 z-50 hidden bg-slate-navy/60 backdrop-blur-xs flex items-center justify-center p-4" id="speciesDetailModal">
    <div class="bg-card-bg border border-border-subtle rounded-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto shadow-2xl transition-all">
      <div class="p-5 border-b border-border-subtle flex items-center justify-between sticky top-0 bg-white z-10">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-injourney-teal/10 text-injourney-teal flex items-center justify-center font-bold">
            <span class="material-symbols-outlined text-2xl">verified</span>
          </div>
          <div>
            <span class="text-[11px] uppercase tracking-wider text-secondary font-semibold">Profil Standar Operasional Prosedur (SOP)</span>
            <h2 class="text-lg font-bold text-slate-navy" id="modalSpeciesName">Biawak Air</h2>
          </div>
        </div>
        <button class="p-2 text-secondary hover:text-slate-navy rounded-lg hover:bg-surface-container" onclick="closeModal('speciesDetailModal')">
          <span class="material-symbols-outlined text-lg">close</span>
        </button>
      </div>

      <div class="p-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="bg-surface-container-low p-4 rounded-xl border border-border-subtle">
            <span class="text-xs uppercase text-secondary font-semibold block">Klasifikasi Operasional</span>
            <span class="text-base font-bold text-slate-navy mt-1 block" id="modalSpeciesCategory">Reptil</span>
          </div>
          <div class="bg-surface-container-low p-4 rounded-xl border border-border-subtle">
            <span class="text-xs uppercase text-secondary font-semibold block">Tingkat Risiko Tabrakan</span>
            <span class="text-base font-bold text-rose-600 mt-1 block" id="modalSpeciesRisk">Kategori 4 - Kritis</span>
          </div>
          <div class="bg-surface-container-low p-4 rounded-xl border border-border-subtle">
            <span class="text-xs uppercase text-secondary font-semibold block">Titik Hotspot Utama</span>
            <span class="text-base font-bold text-injourney-teal font-code-coordinate mt-1 block" id="modalSpeciesGrid">Grid K-10</span>
          </div>
        </div>

        <div class="space-y-3">
          <h3 class="text-sm font-bold text-slate-navy flex items-center gap-2">
            <span class="material-symbols-outlined text-injourney-teal text-base">info</span>
            Karakteristik Lapangan &amp; Jam Aktivitas Puncak
          </h3>
          <p class="text-xs text-slate-navy bg-slate-50 p-4 rounded-xl border border-border-subtle leading-relaxed" id="modalSpeciesDesc">
            Deskripsi karakteristik lapangan.
          </p>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
            <div class="flex items-center gap-2 text-slate-navy">
              <span class="material-symbols-outlined text-injourney-teal text-base">schedule</span>
              <span><strong>Jam Puncak:</strong> <span id="modalSpeciesHours">09:00 - 14:00 WIB</span></span>
            </div>
            <div class="flex items-center gap-2 text-slate-navy">
              <span class="material-symbols-outlined text-injourney-teal text-base">scale</span>
              <span><strong>Bobot Rata-rata:</strong> <span id="modalSpeciesWeight">8 - 15 Kilogram</span></span>
            </div>
          </div>
        </div>

        <div class="space-y-3">
          <h3 class="text-sm font-bold text-slate-navy flex items-center gap-2">
            <span class="material-symbols-outlined text-injourney-teal text-base">security</span>
            SOP Khusus Intervensi Sisi Udara (ICAO Doc 9137 Compliant)
          </h3>
          <ol class="space-y-2 text-xs text-slate-navy list-decimal list-inside bg-surface-container-low p-4 rounded-xl border border-border-subtle leading-relaxed">
            <li class="pl-1"><strong>Langkah 1:</strong> Petugas Patroli AMC memberi tahu ATC Tower (TWR) mengenai temuan satwa di grid aktif untuk koordinasi pergerakan.</li>
            <li class="pl-1"><strong>Langkah 2:</strong> Dekati lokasi satwa menggunakan kendaraan dinas pemantau satwa dengan menyalakan lampu hazard rotari kuning.</li>
            <li class="pl-1"><strong>Langkah 3:</strong> Terapkan jaring tangkap atau perangkap kandang. DILARANG menggunakan proyektil tajam di runway buffer area.</li>
            <li class="pl-1"><strong>Langkah 4:</strong> Ambil foto dokumentasi, tentukan koordinat akurat pada logbook WHMS, dan lakukan tanda tangan basah digital pelaporan.</li>
          </ol>
        </div>
      </div>

      <div class="p-4 border-t border-border-subtle bg-surface-container-low rounded-b-2xl flex items-center justify-between">
        <button type="button" class="px-4 py-2 text-xs font-semibold rounded-lg border border-border-subtle bg-white text-slate-navy hover:bg-slate-50 transition-colors" onclick="closeModal('speciesDetailModal')">
          Tutup
        </button>
        <div class="flex gap-2">
          <a id="modalBtnViewLog" href="{{ route('admin.dashboard') }}" class="px-4 py-2 text-xs font-semibold rounded-lg border border-border-subtle bg-white text-slate-navy hover:bg-slate-50 transition-colors flex items-center gap-1.5">
            <span class="material-symbols-outlined text-sm">history</span>
            Lihat Log Temuan Satwa Ini
          </a>
          <button type="button" class="px-4 py-2 text-xs font-bold rounded-lg bg-injourney-teal hover:bg-injourney-dark-teal text-white transition-colors" onclick="window.print()">
            Cetak Kartu SOP (A4)
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ================= MODAL TAMBAH SPESIES BARU ================= -->
  <div class="fixed inset-0 z-50 hidden bg-slate-navy/60 backdrop-blur-xs flex items-center justify-center p-4" id="addSpeciesModal">
    <div class="bg-card-bg border border-border-subtle rounded-2xl max-w-xl w-full max-h-[90vh] overflow-y-auto shadow-2xl transition-all">
      <div class="p-5 border-b border-border-subtle flex items-center justify-between sticky top-0 bg-white z-10">
        <div>
          <span class="text-[11px] uppercase tracking-wider text-secondary font-semibold">Manajemen Master Data</span>
          <h2 class="text-base font-bold text-slate-navy">Tambah Master Spesies Baru</h2>
        </div>
        <button class="p-2 text-secondary hover:text-slate-navy rounded-lg hover:bg-surface-container" onclick="closeModal('addSpeciesModal')">
          <span class="material-symbols-outlined text-lg">close</span>
        </button>
      </div>

      <form action="{{ route('admin.manajemen.satwa.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
        @csrf
        <div>
          <label class="block text-xs font-semibold text-slate-navy mb-1">
            Nama Satwa (Nama Umum Operasional) <span class="text-rose-500">*</span>
          </label>
          <input name="nama" class="w-full px-3.5 py-2.5 bg-white border border-border-subtle rounded-lg text-xs font-medium text-slate-navy focus:border-injourney-teal focus:ring-2 focus:ring-injourney-teal/20 outline-none" placeholder="Contoh: Burung Gagak Hutan, Kucing Liar, dsb." required type="text"/>
          <p class="text-[11px] text-secondary mt-1">Gunakan nama umum lapangan yang mudah dipahami petugas dinas (tanpa nama latin).</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-navy mb-1">
              Kategori Taksonomi <span class="text-rose-500">*</span>
            </label>
            <select name="kategori" class="w-full bg-white border border-border-subtle rounded-lg px-3 py-2 text-xs font-medium text-slate-navy focus:border-injourney-teal outline-none" required>
              <option value="burung">Burung / Avian</option>
              <option value="reptil">Reptil</option>
              <option value="mamalia">Mamalia</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-navy mb-1">
              Tingkat Risiko Tabrakan <span class="text-rose-500">*</span>
            </label>
            <select name="tingkat_risiko" class="w-full bg-white border border-border-subtle rounded-lg px-3 py-2 text-xs font-medium text-slate-navy focus:border-injourney-teal outline-none" required>
              <option value="kritis">Risiko Kritis (Kategori 4)</option>
              <option value="sedang" selected>Risiko Sedang (Kategori 3)</option>
              <option value="rendah">Risiko Rendah (Kategori 2)</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-navy mb-1">
            Zona Grid Hotspot Acuan
          </label>
          <input name="grid_hotspot" class="w-full px-3.5 py-2 bg-white border border-border-subtle rounded-lg text-xs font-medium text-slate-navy focus:border-injourney-teal focus:ring-2 focus:ring-injourney-teal/20 outline-none" placeholder="Contoh: K-10, Kanal Perimeter Selatan" type="text"/>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-navy mb-1">
            Deskripsi Karakter &amp; Bahaya di Sisi Udara
          </label>
          <textarea name="deskripsi" class="w-full px-3.5 py-2 bg-white border border-border-subtle rounded-lg text-xs text-slate-navy focus:border-injourney-teal focus:ring-2 focus:ring-injourney-teal/20 outline-none" placeholder="Jelaskan pola terbang, kebiasaan mencari makan, atau potensi benturan terhadap pesawat..." rows="3"></textarea>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-navy mb-1">
            Metode Pengusiran Resmi (SOP)
          </label>
          <textarea name="sop_pengusiran" class="w-full px-3.5 py-2 bg-white border border-border-subtle rounded-lg text-xs text-slate-navy focus:border-injourney-teal focus:ring-2 focus:ring-injourney-teal/20 outline-none" placeholder="Tuliskan metode pengusiran resmi, satu per baris..." rows="2"></textarea>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-navy mb-1">
            Unggah Foto Dokumentasi Referensi (Maks 5MB)
          </label>
          <input name="foto" type="file" accept="image/*" class="w-full text-xs text-secondary file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-surface-container file:text-injourney-teal hover:file:bg-surface-container-high cursor-pointer"/>
        </div>

        <div class="pt-4 border-t border-border-subtle flex items-center justify-end gap-2">
          <button class="px-4 py-2 text-xs font-semibold rounded-lg border border-border-subtle bg-white text-slate-navy hover:bg-slate-50" onclick="closeModal('addSpeciesModal')" type="button">
            Batal
          </button>
          <button class="px-5 py-2 text-xs font-bold rounded-lg bg-injourney-teal hover:bg-injourney-dark-teal text-white shadow-xs" type="submit">
            Simpan ke Master Database
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- ================= MODAL EDIT SPESIES ================= -->
  <div class="fixed inset-0 z-50 hidden bg-slate-navy/60 backdrop-blur-xs flex items-center justify-center p-4" id="editSpeciesModal">
    <div class="bg-card-bg border border-border-subtle rounded-2xl max-w-xl w-full max-h-[90vh] overflow-y-auto shadow-2xl transition-all">
      <div class="p-5 border-b border-border-subtle flex items-center justify-between sticky top-0 bg-white z-10">
        <div>
          <span class="text-[11px] uppercase tracking-wider text-secondary font-semibold">Ubah Data Satwa</span>
          <h2 class="text-base font-bold text-slate-navy">Edit Master Spesies</h2>
        </div>
        <button class="p-2 text-secondary hover:text-slate-navy rounded-lg hover:bg-surface-container" onclick="closeModal('editSpeciesModal')">
          <span class="material-symbols-outlined text-lg">close</span>
        </button>
      </div>

      <form id="editSpeciesForm" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
        @csrf
        <div>
          <label class="block text-xs font-semibold text-slate-navy mb-1">
            Nama Satwa (Nama Umum) <span class="text-rose-500">*</span>
          </label>
          <input id="editNama" name="nama" class="w-full px-3.5 py-2 bg-white border border-border-subtle rounded-lg text-xs font-medium text-slate-navy focus:border-injourney-teal outline-none" required type="text"/>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-navy mb-1">
              Kategori Taksonomi <span class="text-rose-500">*</span>
            </label>
            <select id="editKategori" name="kategori" class="w-full bg-white border border-border-subtle rounded-lg px-3 py-2 text-xs font-medium text-slate-navy focus:border-injourney-teal outline-none">
              <option value="burung">Burung / Avian</option>
              <option value="reptil">Reptil</option>
              <option value="mamalia">Mamalia</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-navy mb-1">
              Tingkat Risiko Tabrakan <span class="text-rose-500">*</span>
            </label>
            <select id="editRisiko" name="tingkat_risiko" class="w-full bg-white border border-border-subtle rounded-lg px-3 py-2 text-xs font-medium text-slate-navy focus:border-injourney-teal outline-none">
              <option value="kritis">Risiko Kritis (Kategori 4)</option>
              <option value="sedang">Risiko Sedang (Kategori 3)</option>
              <option value="rendah">Risiko Rendah (Kategori 2)</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-navy mb-1">
            Zona Grid Hotspot Acuan
          </label>
          <input id="editGrid" name="grid_hotspot" class="w-full px-3.5 py-2 bg-white border border-border-subtle rounded-lg text-xs font-medium text-slate-navy focus:border-injourney-teal outline-none" type="text"/>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-navy mb-1">
            Deskripsi Karakter &amp; Bahaya
          </label>
          <textarea id="editDeskripsi" name="deskripsi" class="w-full px-3.5 py-2 bg-white border border-border-subtle rounded-lg text-xs text-slate-navy focus:border-injourney-teal outline-none" rows="3"></textarea>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-navy mb-1">
            Metode Pengusiran Resmi (SOP)
          </label>
          <textarea id="editSop" name="sop_pengusiran" class="w-full px-3.5 py-2 bg-white border border-border-subtle rounded-lg text-xs text-slate-navy focus:border-injourney-teal outline-none" rows="2"></textarea>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-navy mb-1">
            Perbarui Foto Dokumentasi (Opsional)
          </label>
          <input name="foto" type="file" accept="image/*" class="w-full text-xs text-secondary file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-surface-container file:text-injourney-teal hover:file:bg-surface-container-high cursor-pointer"/>
        </div>

        <div class="pt-4 border-t border-border-subtle flex items-center justify-end gap-2">
          <button class="px-4 py-2 text-xs font-semibold rounded-lg border border-border-subtle bg-white text-slate-navy hover:bg-slate-50" onclick="closeModal('editSpeciesModal')" type="button">
            Batal
          </button>
          <button class="px-5 py-2 text-xs font-bold rounded-lg bg-injourney-teal hover:bg-injourney-dark-teal text-white shadow-xs" type="submit">
            Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- ================= MODAL TAMBAH FIELD (PRD REQ-MGT-005) ================= -->
  <div class="fixed inset-0 z-50 hidden bg-slate-navy/60 backdrop-blur-xs flex items-center justify-center p-4" id="modalAddField">
    <div class="bg-card-bg border border-border-subtle rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl p-6">
      <div class="flex items-center justify-between pb-4 border-b border-border-subtle mb-4">
        <h3 class="text-base font-bold text-slate-navy">Tambah Field Baru</h3>
        <button class="p-1.5 text-secondary hover:text-slate-navy rounded-lg" onclick="closeModal('modalAddField')">
          <span class="material-symbols-outlined text-lg">close</span>
        </button>
      </div>

      <form action="{{ route('admin.manajemen.fields.store') }}" method="POST" class="space-y-4 text-xs">
        @csrf
        <div>
          <label class="block font-semibold text-slate-navy mb-1">Label Input <span class="text-rose-500">*</span></label>
          <input name="label" required placeholder="Contoh: Titik Ketinggian Satwa" class="w-full px-3 py-2 border border-border-subtle rounded-lg focus:border-injourney-teal outline-none"/>
        </div>
        <div>
          <label class="block font-semibold text-slate-navy mb-1">Tipe Input <span class="text-rose-500">*</span></label>
          <select name="tipe" id="addSelectTipe" onchange="toggleAddOpsi(this.value)" class="w-full px-3 py-2 border border-border-subtle rounded-lg focus:border-injourney-teal outline-none">
            <option value="text">Teks Pendek</option>
            <option value="textarea">Teks Panjang (Textarea)</option>
            <option value="dropdown">Dropdown Pilihan</option>
            <option value="date">Tanggal</option>
            <option value="number">Angka</option>
          </select>
        </div>
        <div id="addOpsiContainer" class="hidden">
          <label class="block font-semibold text-slate-navy mb-1">Opsi Dropdown (Satu per baris)</label>
          <textarea name="opsi" rows="3" placeholder="Pilihan 1&#10;Pilihan 2" class="w-full px-3 py-2 border border-border-subtle rounded-lg focus:border-injourney-teal outline-none"></textarea>
        </div>
        <div class="flex items-center gap-2">
          <input type="checkbox" name="wajib" id="addWajib" value="1" class="rounded text-injourney-teal focus:ring-injourney-teal"/>
          <label for="addWajib" class="font-semibold text-slate-navy">Wajib Diisi (Required)</label>
        </div>
        <div class="pt-4 border-t border-border-subtle flex items-center justify-end gap-2">
          <button type="button" class="px-4 py-2 border border-border-subtle rounded-lg bg-white" onclick="closeModal('modalAddField')">Batal</button>
          <button type="submit" class="px-5 py-2 bg-injourney-teal hover:bg-injourney-dark-teal text-white font-bold rounded-lg shadow-xs">Simpan Field</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ================= MODAL TAMBAH USER (PRD REQ-MGT-005) ================= -->
  <div class="fixed inset-0 z-50 hidden bg-slate-navy/60 backdrop-blur-xs flex items-center justify-center p-4" id="modalAddUser">
    <div class="bg-card-bg border border-border-subtle rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl p-6">
      <div class="flex items-center justify-between pb-4 border-b border-border-subtle mb-4">
        <h3 class="text-base font-bold text-slate-navy">Tambah Akun Petugas Baru</h3>
        <button class="p-1.5 text-secondary hover:text-slate-navy rounded-lg" onclick="closeModal('modalAddUser')">
          <span class="material-symbols-outlined text-lg">close</span>
        </button>
      </div>

      <form action="{{ route('admin.manajemen.users.store') }}" method="POST" class="space-y-4 text-xs">
        @csrf
        <div>
          <label class="block font-semibold text-slate-navy mb-1">Nama Lengkap Petugas <span class="text-rose-500">*</span></label>
          <input name="namalengkap" required placeholder="Contoh: Budi Santoso, S.T." class="w-full px-3 py-2 border border-border-subtle rounded-lg focus:border-injourney-teal outline-none"/>
        </div>
        <div>
          <label class="block font-semibold text-slate-navy mb-1">Username Login <span class="text-rose-500">*</span></label>
          <input name="username" required placeholder="Contoh: amc.budisantoso" class="w-full px-3 py-2 border border-border-subtle rounded-lg focus:border-injourney-teal outline-none"/>
        </div>
        <div>
          <label class="block font-semibold text-slate-navy mb-1">Password Baru <span class="text-rose-500">*</span></label>
          <input name="password" type="password" required minlength="6" placeholder="Minimal 6 karakter" class="w-full px-3 py-2 border border-border-subtle rounded-lg focus:border-injourney-teal outline-none"/>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-slate-navy mb-1">Role Akses <span class="text-rose-500">*</span></label>
            <select name="role" class="w-full px-3 py-2 border border-border-subtle rounded-lg focus:border-injourney-teal outline-none">
              <option value="pegawai">Pegawai / Petugas Lapangan</option>
              <option value="admin">Administrator / Safety Manager</option>
            </select>
          </div>
          <div>
            <label class="block font-semibold text-slate-navy mb-1">Jabatan / Posko</label>
            <input name="jabatan" placeholder="Contoh: AMC Airside Supervisor" class="w-full px-3 py-2 border border-border-subtle rounded-lg focus:border-injourney-teal outline-none"/>
          </div>
        </div>
        <div class="pt-4 border-t border-border-subtle flex items-center justify-end gap-2">
          <button type="button" class="px-4 py-2 border border-border-subtle rounded-lg bg-white" onclick="closeModal('modalAddUser')">Batal</button>
          <button type="submit" class="px-5 py-2 bg-injourney-teal hover:bg-injourney-dark-teal text-white font-bold rounded-lg shadow-xs">Simpan User</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ================= FOOTER STANDAR INJOURNEY ================= -->
  <footer class="bg-card-bg border-t border-border-subtle mt-auto py-4 px-4 sm:px-6 lg:px-8">
    <div class="max-w-[1680px] mx-auto flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-secondary">
      <div class="flex items-center gap-3">
        <span class="font-bold text-slate-navy">PT Angkasa Pura Indonesia</span>
        <span>•</span>
        <span>InJourney Airports WHMS Portal v2.0</span>
      </div>
      <div class="flex items-center gap-3 text-[11px]">
        <span class="inline-flex items-center gap-1 text-injourney-teal">
          <span class="material-symbols-outlined text-sm">verified</span>
          ICAO Annex 14 Aerodrome Safety Certified
        </span>
        <span>•</span>
        <span>Ditjen Perhubungan Udara Compliance</span>
      </div>
    </div>
  </footer>

  <!-- ================= INLINE INTERACTIVITY SCRIPT ================= -->
  <script>
    // Tab & View Switching (Grid vs Table)
    function setViewMode(mode) {
      const gridView = document.getElementById('catalogGridView');
      const tableView = document.getElementById('catalogTableView');
      const gridBtn = document.getElementById('viewGridBtn');
      const tableBtn = document.getElementById('viewTableBtn');

      if (!gridView || !tableView) return;

      if (mode === 'grid') {
        gridView.classList.remove('hidden');
        tableView.classList.add('hidden');
        gridBtn.className = 'p-1.5 rounded-md bg-white text-injourney-teal shadow-xs transition-all';
        tableBtn.className = 'p-1.5 rounded-md text-secondary hover:text-slate-navy transition-all';
      } else {
        gridView.classList.add('hidden');
        tableView.classList.remove('hidden');
        tableBtn.className = 'p-1.5 rounded-md bg-white text-injourney-teal shadow-xs transition-all';
        gridBtn.className = 'p-1.5 rounded-md text-secondary hover:text-slate-navy transition-all';
      }
    }

    // Category Filter Chips handler
    let currentCategory = 'all';
    function setCategoryFilter(category, buttonEl) {
      currentCategory = category;
      document.querySelectorAll('.category-chip').forEach(btn => {
        btn.className = 'category-chip px-3 py-1.5 rounded-lg bg-surface-container text-slate-navy hover:bg-surface-container-high transition-all flex items-center gap-1.5';
      });
      buttonEl.className = 'category-chip px-3 py-1.5 rounded-lg bg-injourney-teal text-white transition-all shadow-xs flex items-center gap-1.5';
      filterCatalog();
    }

    // Comprehensive Dynamic Catalog Search & Filter
    function filterCatalog() {
      const searchInput = document.getElementById('speciesSearch');
      if (!searchInput) return;

      const searchTerm = searchInput.value.toLowerCase();
      const riskValue = document.getElementById('riskFilter').value;
      const habitatValue = document.getElementById('habitatFilter').value;
      const cards = document.querySelectorAll('.species-card');

      cards.forEach(card => {
        const name = (card.getAttribute('data-name') || '').toLowerCase();
        const cat = card.getAttribute('data-category') || '';
        const risk = card.getAttribute('data-risk') || '';
        const habitat = card.getAttribute('data-habitat') || '';

        const matchesSearch = name.includes(searchTerm);
        const matchesCategory = (currentCategory === 'all' || cat === currentCategory);
        const matchesRisk = (riskValue === 'all' || risk === riskValue);
        const matchesHabitat = (habitatValue === 'all' || habitat.includes(habitatValue));

        if (matchesSearch && matchesCategory && matchesRisk && matchesHabitat) {
          card.style.display = 'flex';
        } else {
          card.style.display = 'none';
        }
      });
    }

    // Modal Handlers
    function openModal(modalId) {
      const modal = document.getElementById(modalId);
      if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
      }
    }

    function closeModal(modalId) {
      const modal = document.getElementById(modalId);
      if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
      }
    }

    // Detailed Species Profiling Modal
    function openSpeciesDetail(name, cat, risk, grid, desc, hours, weight) {
      document.getElementById('modalSpeciesName').innerText = name;
      document.getElementById('modalSpeciesCategory').innerText = cat;
      document.getElementById('modalSpeciesRisk').innerText = risk;
      document.getElementById('modalSpeciesGrid').innerText = 'Grid ' + grid;
      document.getElementById('modalSpeciesDesc').innerText = desc || 'Karakteristik lapangan terpantau normal.';
      document.getElementById('modalSpeciesHours').innerText = hours || '08:00 - 15:00 WIB';
      document.getElementById('modalSpeciesWeight').innerText = weight || 'FOD Ringan - Sedang';
      
      const btnLog = document.getElementById('modalBtnViewLog');
      if (btnLog) {
        btnLog.href = "{{ route('admin.dashboard') }}?search=" + encodeURIComponent(name);
      }

      openModal('speciesDetailModal');
    }

    function openEditSpeciesModal(id, nama, kategori, risiko, grid, deskripsi, sop) {
      document.getElementById('editNama').value = nama;
      document.getElementById('editKategori').value = kategori;
      document.getElementById('editRisiko').value = risiko;
      document.getElementById('editGrid').value = grid;
      document.getElementById('editDeskripsi').value = deskripsi;
      document.getElementById('editSop').value = sop;

      const form = document.getElementById('editSpeciesForm');
      form.action = "{{ url('admin/manajemen/satwa') }}/" + id;

      openModal('editSpeciesModal');
    }

    function toggleAddOpsi(type) {
      const container = document.getElementById('addOpsiContainer');
      if (type === 'dropdown') {
        container.classList.remove('hidden');
      } else {
        container.classList.add('hidden');
      }
    }

    // Close modal on escape key
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        closeModal('speciesDetailModal');
        closeModal('addSpeciesModal');
        closeModal('editSpeciesModal');
        closeModal('modalAddField');
        closeModal('modalAddUser');
      }
    });
  </script>

</body>
</html>
