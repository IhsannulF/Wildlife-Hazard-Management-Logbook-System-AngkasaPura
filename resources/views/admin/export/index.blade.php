<!DOCTYPE html>
<html class="h-full bg-canvas-light" lang="id">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Pusat Konfigurasi & Ekspor Excel - InJourney Airports WHMS</title>

  <!-- Favicon InJourney Airports -->
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
  <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

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
          colors: {
            "primary": "#006874",
            "primary-fixed": "#97f0ff",
            "alert-amber": "#F59E0B",
            "tertiary": "#006780",
            "secondary": "#565e74",
            "injourney-teal": "#00A3B4",
            "injourney-dark-teal": "#0891B2",
            "slate-navy": "#0F172A",
            "border-subtle": "#E2E8F0",
            "canvas-light": "#F8FAFC",
            "on-surface": "#0b1c30",
            "card-bg": "#FFFFFF",
          },
          fontFamily: {
            sans: ["DM Sans", "sans-serif"],
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
    /* Custom scrollbar */
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: #F1F5F9; }
    ::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 9999px; }
    ::-webkit-scrollbar-thumb:hover { background: #94A3B8; }
  </style>
</head>
<body class="min-h-full bg-[#f8fafc] text-slate-800 font-sans antialiased selection:bg-[#00A3B4] selection:text-white flex flex-col">

  <!-- SIDEBAR NAVIGATION -->
  @include('partials.navbar', ['activePage' => 'export_excel'])

  <!-- TOP UNIFIED PAGE HEADER -->
  @include('partials.page-header', [
    'title' => 'Pusat Konfigurasi & Ekspor Laporan Excel',
    'subtitle' => 'Pilih data apa saja yang ingin dimasukkan ke dalam laporan, sesuaikan rentang waktu, dan format audit.',
    'icon' => 'table_chart',
    'maxWidth' => 'max-w-[1680px]',
    'actions' => '
      <a href="' . route('admin.verifikasi') . '" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg border border-slate-200/90 bg-white text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs">
        <span class="material-symbols-outlined text-base">arrow_back</span>
        Kembali ke Logbook
      </a>
    '
  ])

  <!-- ================= MAIN CONTAINER ================= -->
  <main class="flex-1 max-w-[1680px] w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- 1. BREADCRUMB -->
    <nav class="flex items-center gap-2 text-xs text-slate-500 font-medium">
      <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors flex items-center gap-1">
        <span class="material-symbols-outlined text-sm">home</span>
        Beranda
      </a>
      <span>/</span>
      <a href="{{ route('admin.verifikasi') }}" class="hover:text-primary transition-colors">Logbook Operasional</a>
      <span>/</span>
      <span class="text-slate-800 font-semibold">Pusat Ekspor Spreadsheet</span>
    </nav>

    <!-- 1.5 BUNDEL LOGBOOK BULANAN PDF (REQ-REP-002) -->
    <section id="monthlyBundleSection" class="bg-gradient-to-br from-slate-900 via-[#003e56] to-[#005f80] text-white rounded-2xl border border-cyan-800/60 shadow-md p-5 sm:p-6 space-y-4">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 border-b border-white/15 pb-3.5">
        <div class="space-y-1">
          <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-cyan-400/20 border border-cyan-300/30 text-[10px] font-bold uppercase tracking-wider text-cyan-200">
            <span class="material-symbols-outlined text-xs">menu_book</span>
            Standar Audit Kelaikudaraan A4 (REQ-REP-002)
          </div>
          <h2 class="text-base sm:text-lg font-bold text-white flex items-center gap-2">
            <span class="material-symbols-outlined text-cyan-300 text-xl">picture_as_pdf</span>
            Unduh Buku Log Bulanan (Bundle PDF)
          </h2>
          <p class="text-xs text-cyan-100/80 max-w-3xl">
            Kompilasi satu berkas PDF A4 resmi berisi Halaman Cover, Kata Pengantar &amp; Indeks Kronologis, Statistik Temporal Bulanan, dan seluruh lembar Berita Acara dengan penomoran halaman berkelanjutan.
          </p>
        </div>
        <div class="bg-white/10 backdrop-blur-xs border border-white/15 rounded-xl px-3.5 py-2.5 text-left lg:text-right shrink-0">
          <div id="bundleCounterText" class="text-xs sm:text-sm font-bold text-white">Memeriksa arsip bulan terpilih...</div>
          <div id="bundleCounterSub" class="text-[11px] text-cyan-200 mt-0.5">Menyiapkan jumlah BA &amp; populasi satwa</div>
        </div>
      </div>

      <form id="monthlyBundleForm" action="{{ route('admin.export.bundle.pdf') }}" method="GET" target="_blank" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
        <input type="hidden" name="inline" value="1">
        <div class="space-y-1">
          <label for="bundleBulan" class="block text-[11px] font-bold text-cyan-100">Bulan Arsip</label>
          <select name="bulan" id="bundleBulan" class="bundle-filter-trigger w-full h-[36px] py-0 text-xs rounded-xl border-white/20 bg-white text-slate-900 font-semibold focus:border-cyan-400 focus:ring-cyan-400">
            @foreach($availableMonths as $mNum => $mName)
              <option value="{{ $mNum }}" {{ (int) $preSelectedBulan === (int) $mNum ? 'selected' : '' }}>{{ $mName }}</option>
            @endforeach
          </select>
        </div>

        <div class="space-y-1">
          <label for="bundleTahun" class="block text-[11px] font-bold text-cyan-100">Tahun</label>
          <select name="tahun" id="bundleTahun" class="bundle-filter-trigger w-full h-[36px] py-0 text-xs rounded-xl border-white/20 bg-white text-slate-900 font-semibold focus:border-cyan-400 focus:ring-cyan-400">
            @foreach($availableYears as $yr)
              <option value="{{ $yr }}" {{ (int) $preSelectedTahun === (int) $yr ? 'selected' : '' }}>{{ $yr }}</option>
            @endforeach
          </select>
        </div>

        <div class="space-y-1">
          <label for="bundleStatus" class="block text-[11px] font-bold text-cyan-100">Status Validasi</label>
          <select name="status" id="bundleStatus" class="bundle-filter-trigger w-full h-[36px] py-0 text-xs rounded-xl border-white/20 bg-white text-slate-900 font-semibold focus:border-cyan-400 focus:ring-cyan-400">
            <option value="all">Semua Status BA</option>
            <option value="sudah">Hanya Tervalidasi</option>
            <option value="belum">Menunggu Validasi</option>
          </select>
        </div>

        <div class="space-y-1">
          <label for="bundleUnit" class="block text-[11px] font-bold text-cyan-100">Unit Kerja</label>
          <select name="unit_kerja" id="bundleUnit" class="bundle-filter-trigger w-full h-[36px] py-0 text-xs rounded-xl border-white/20 bg-white text-slate-900 font-semibold focus:border-cyan-400 focus:ring-cyan-400">
            <option value="all">Semua Unit Kerja</option>
            @foreach($availableUnits as $unit)
              <option value="{{ $unit }}">{{ $unit }}</option>
            @endforeach
          </select>
        </div>

        <div class="sm:col-span-2 lg:col-span-1 flex items-center">
          <a id="btnPreviewBundlePdf" href="{{ route('admin.export.bundle.pdf', ['bulan' => $preSelectedBulan, 'tahun' => $preSelectedTahun, 'inline' => 1]) }}" target="_blank" rel="noopener noreferrer" class="w-full h-[36px] inline-flex items-center justify-center gap-1.5 px-4 rounded-xl border border-transparent bg-[#00A9C1] hover:bg-[#008ba0] text-white text-xs font-bold shadow-md shadow-cyan-950/40 transition-all cursor-pointer">
            <span class="material-symbols-outlined text-base leading-none">visibility</span>
            <span>Lihat Laporan</span>
          </a>
        </div>
      </form>
    </section>

    <!-- FORM UTAMA EKSPOR -->
    <form id="exportForm" action="{{ route('admin.export.excel.download') }}" method="POST" class="space-y-6">
      @csrf

      <!-- 2. PILIHAN PRESET CEPAT (QUICK TEMPLATES) -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
          <div>
            <h2 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
              <span class="material-symbols-outlined text-primary text-xl">auto_fix_high</span>
              Langkah 1: Pilih Preset Format Laporan
            </h2>
            <p class="text-xs text-slate-500">Pilih template standar yang telah terkonfigurasi, atau pilih Kustom untuk menentukan kolom sendiri.</p>
          </div>
          <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 self-start sm:self-auto">
            Otomatis Menyesuaikan Kolom
          </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
          @foreach($presets as $key => $preset)
            @php
              $isSelected = ($key === $preSelectedPreset);
            @endphp
            <label class="preset-card relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all {{ $isSelected ? 'border-primary bg-cyan-50/40 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white' }}" data-preset="{{ $key }}">
              <input type="radio" name="preset_choice" value="{{ $key }}" class="sr-only" {{ $isSelected ? 'checked' : '' }}>
              
              <div class="flex items-start justify-between gap-2 mb-2">
                <span class="p-1.5 rounded-lg {{ $isSelected ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600' }} preset-icon-box">
                  <span class="material-symbols-outlined text-lg">{{ $preset['icon'] }}</span>
                </span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wider {{ $key === 'dkppu' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                  {{ $preset['badge'] }}
                </span>
              </div>

              <span class="font-bold text-xs sm:text-sm text-slate-900 leading-snug">{{ $preset['name'] }}</span>
              <p class="text-[11px] text-slate-500 mt-1 flex-1 leading-relaxed">{{ $preset['description'] }}</p>

              <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] font-medium text-slate-600">
                <span class="flex items-center gap-1">
                  <span class="material-symbols-outlined text-sm text-primary">checklist</span>
                  {{ count($preset['columns']) > 0 ? count($preset['columns']) . ' Kolom' : 'Bebas Pilih' }}
                </span>
                <span class="text-[10px] text-slate-400 capitalize">{{ str_replace('_', ' ', $preset['mode_baris']) }}</span>
              </div>
            </label>
          @endforeach
        </div>
      </div>

      <!-- 3. FILTER DATA (PERIODE, UNIT, STATUS, GRID, SATWA) -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
          <div>
            <h2 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
              <span class="material-symbols-outlined text-primary text-xl">filter_alt</span>
              Langkah 2: Tentukan Filter Data yang Ingin Diekspor
            </h2>
            <p class="text-xs text-slate-500">Batasi data yang akan ditarik ke Excel berdasarkan tanggal, unit, status verifikasi, atau zona tertentu.</p>
          </div>
          <button type="button" id="btnResetFilter" class="text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors flex items-center gap-1 self-start sm:self-auto">
            <span class="material-symbols-outlined text-sm">restart_alt</span>
            Reset Filter
          </button>
        </div>

        <!-- Filter Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          
          <!-- Periode Mode -->
          <div class="space-y-1.5 lg:col-span-2">
            <label class="block text-xs font-bold text-slate-700">Periode Waktu Laporan</label>
            <div class="grid grid-cols-3 gap-2 p-1 bg-slate-100 rounded-xl text-xs font-semibold text-slate-600">
              <label class="flex items-center justify-center py-1.5 px-2 rounded-lg cursor-pointer transition-all has-[:checked]:bg-white has-[:checked]:text-primary has-[:checked]:shadow-2xs">
                <input type="radio" name="mode_periode" value="all" class="sr-only filter-trigger" checked>
                <span>Semua Data</span>
              </label>
              <label class="flex items-center justify-center py-1.5 px-2 rounded-lg cursor-pointer transition-all has-[:checked]:bg-white has-[:checked]:text-primary has-[:checked]:shadow-2xs">
                <input type="radio" name="mode_periode" value="tahun" class="sr-only filter-trigger" {{ $preSelectedTahun ? 'checked' : '' }}>
                <span>Per Tahun</span>
              </label>
              <label class="flex items-center justify-center py-1.5 px-2 rounded-lg cursor-pointer transition-all has-[:checked]:bg-white has-[:checked]:text-primary has-[:checked]:shadow-2xs">
                <input type="radio" name="mode_periode" value="range" class="sr-only filter-trigger">
                <span>Rentang Tanggal</span>
              </label>
            </div>

            <!-- Periode Controls: Tahun Container -->
            <div id="containerTahun" class="pt-2">
              <select name="tahun" id="filterTahun" class="w-full text-xs rounded-xl border-slate-200 focus:border-primary focus:ring-primary filter-trigger py-2">
                @foreach($availableYears as $yr)
                  <option value="{{ $yr }}" {{ (int)$preSelectedTahun === (int)$yr ? 'selected' : '' }}>Tahun {{ $yr }}</option>
                @endforeach
              </select>
            </div>

            <!-- Periode Controls: Date Range Container -->
            <div id="containerRange" class="hidden pt-2 grid grid-cols-2 gap-2">
              <div>
                <span class="text-[10px] text-slate-500 font-medium">Dari Tanggal</span>
                <input type="date" name="start_date" id="filterStartDate" class="w-full text-xs rounded-xl border-slate-200 focus:border-primary focus:ring-primary filter-trigger py-1.5">
              </div>
              <div>
                <span class="text-[10px] text-slate-500 font-medium">Sampai Tanggal</span>
                <input type="date" name="end_date" id="filterEndDate" class="w-full text-xs rounded-xl border-slate-200 focus:border-primary focus:ring-primary filter-trigger py-1.5">
              </div>
            </div>
          </div>

          <!-- Unit Kerja -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-700">Unit Kerja Pelapor</label>
            <select name="unit_kerja" id="filterUnit" class="w-full text-xs rounded-xl border-slate-200 focus:border-primary focus:ring-primary filter-trigger py-2">
              <option value="all">Semua Unit Dinas</option>
              @foreach($availableUnits as $unit)
                <option value="{{ $unit }}">{{ $unit }}</option>
              @endforeach
            </select>
            <span class="text-[10px] text-slate-400">AMC, ARFF, Avsec, SMS</span>
          </div>

          <!-- Status Penanganan -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-700">Status Penanganan</label>
            <select name="status" id="filterStatus" class="w-full text-xs rounded-xl border-slate-200 focus:border-primary focus:ring-primary filter-trigger py-2">
              <option value="all">Semua Status (Lengkap)</option>
              <option value="sudah">Telah Ditangani (Selesai)</option>
              <option value="belum">Belum Ditangani (Pending)</option>
            </select>
            <span class="text-[10px] text-slate-400">Filter hasil verifikasi lapangan</span>
          </div>

          <!-- Grid Lokasi -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-700">Grid / Zona Bandara</label>
            <select name="grid_lokasi" id="filterGrid" class="w-full text-xs rounded-xl border-slate-200 focus:border-primary focus:ring-primary filter-trigger py-2">
              <option value="all">Semua Grid Koordinat</option>
              @foreach($availableGrids as $grid)
                <option value="{{ $grid }}" {{ $preSelectedZona === $grid ? 'selected' : '' }}>Grid {{ $grid }}</option>
              @endforeach
            </select>
            <span class="text-[10px] text-slate-400">Area spesifik sisi udara</span>
          </div>

          <!-- Spesies Satwa -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-700">Jenis Spesies Satwa</label>
            <select name="nama_satwa" id="filterSatwa" class="w-full text-xs rounded-xl border-slate-200 focus:border-primary focus:ring-primary filter-trigger py-2">
              <option value="all">Semua Spesies Satwa</option>
              @foreach($availableSatwa as $satwa)
                <option value="{{ $satwa->nama }}">{{ $satwa->nama }}</option>
              @endforeach
            </select>
            <span class="text-[10px] text-slate-400">Filter berdasarkan jenis spesifik</span>
          </div>

        </div>
      </div>

      <!-- 4. PEMILIHAN KOLOM DATA (CUSTOM DATA SELECTION) -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
          <div>
            <h2 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
              <span class="material-symbols-outlined text-primary text-xl">view_column</span>
              Langkah 3: Pilih Kolom Data yang Ingin Diekspor
            </h2>
            <p class="text-xs text-slate-500">Centang atau hilangkan centang kolom di bawah sesuai kebutuhan pelaporan Anda.</p>
          </div>

          <!-- Quick Action Buttons -->
          <div class="flex items-center gap-2 flex-wrap">
            <span id="selectedCountBadge" class="text-xs font-bold px-2.5 py-1 rounded-lg bg-cyan-50 text-[#007fa3] border border-cyan-100">
              16 Kolom Terpilih
            </span>
            <button type="button" id="btnSelectAll" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">
              Pilih Semua
            </button>
            <button type="button" id="btnDeselectAll" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">
              Kosongkan
            </button>
            <button type="button" id="btnResetDkppu" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 transition-colors">
              Standar DKPPU
            </button>
          </div>
        </div>

        <!-- Column Checklist by Category -->
        <div class="space-y-5 pt-2">
          
          <!-- Grup A: Identitas & Stempel Waktu -->
          <div class="space-y-2">
            <div class="flex items-center gap-1.5 text-xs font-bold text-slate-600 uppercase tracking-wider">
              <span class="material-symbols-outlined text-sm text-slate-400">badge</span>
              A. Identitas & Stempel Waktu Laporan
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
              @foreach($columns as $key => $col)
                @if($col['category'] === 'identitas')
                  <label class="column-card flex items-start gap-2.5 p-2.5 rounded-xl border border-slate-200 hover:border-slate-300 hover:bg-slate-50/70 cursor-pointer transition-colors has-[:checked]:border-cyan-300 has-[:checked]:bg-cyan-50/40">
                    <input type="checkbox" name="columns[]" value="{{ $key }}" class="col-checkbox rounded border-slate-300 text-primary focus:ring-primary mt-0.5">
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center justify-between gap-1">
                        <span class="text-xs font-bold text-slate-900 truncate">{{ $col['label'] }}</span>
                        @if(in_array($key, $presets['dkppu']['columns']))
                          <span class="text-[9px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded shrink-0">DKPPU</span>
                        @endif
                      </div>
                      <p class="text-[10px] text-slate-500 truncate mt-0.5">{{ $col['description'] }}</p>
                    </div>
                  </label>
                @endif
              @endforeach
            </div>
          </div>

          <!-- Grup B: Lokasi & Lingkungan Sisi Udara -->
          <div class="space-y-2">
            <div class="flex items-center gap-1.5 text-xs font-bold text-slate-600 uppercase tracking-wider">
              <span class="material-symbols-outlined text-sm text-slate-400">location_on</span>
              B. Lokasi Spasial & Lingkungan Sisi Udara
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
              @foreach($columns as $key => $col)
                @if($col['category'] === 'lokasi')
                  <label class="column-card flex items-start gap-2.5 p-2.5 rounded-xl border border-slate-200 hover:border-slate-300 hover:bg-slate-50/70 cursor-pointer transition-colors has-[:checked]:border-cyan-300 has-[:checked]:bg-cyan-50/40">
                    <input type="checkbox" name="columns[]" value="{{ $key }}" class="col-checkbox rounded border-slate-300 text-primary focus:ring-primary mt-0.5">
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center justify-between gap-1">
                        <span class="text-xs font-bold text-slate-900 truncate">{{ $col['label'] }}</span>
                        @if(in_array($key, $presets['dkppu']['columns']))
                          <span class="text-[9px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded shrink-0">DKPPU</span>
                        @endif
                      </div>
                      <p class="text-[10px] text-slate-500 truncate mt-0.5">{{ $col['description'] }}</p>
                    </div>
                  </label>
                @endif
              @endforeach
            </div>
          </div>

          <!-- Grup C: Parameter Satwa Liar -->
          <div class="space-y-2">
            <div class="flex items-center gap-1.5 text-xs font-bold text-slate-600 uppercase tracking-wider">
              <span class="material-symbols-outlined text-sm text-slate-400">pets</span>
              C. Parameter Spesies Satwa Liar
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
              @foreach($columns as $key => $col)
                @if($col['category'] === 'satwa')
                  <label class="column-card flex items-start gap-2.5 p-2.5 rounded-xl border border-slate-200 hover:border-slate-300 hover:bg-slate-50/70 cursor-pointer transition-colors has-[:checked]:border-cyan-300 has-[:checked]:bg-cyan-50/40">
                    <input type="checkbox" name="columns[]" value="{{ $key }}" class="col-checkbox rounded border-slate-300 text-primary focus:ring-primary mt-0.5">
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center justify-between gap-1">
                        <span class="text-xs font-bold text-slate-900 truncate">{{ $col['label'] }}</span>
                        @if(in_array($key, $presets['dkppu']['columns']))
                          <span class="text-[9px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded shrink-0">DKPPU</span>
                        @endif
                      </div>
                      <p class="text-[10px] text-slate-500 truncate mt-0.5">{{ $col['description'] }}</p>
                    </div>
                  </label>
                @endif
              @endforeach
            </div>
          </div>

          <!-- Grup D: Tindakan Pengusiran & Status -->
          <div class="space-y-2">
            <div class="flex items-center gap-1.5 text-xs font-bold text-slate-600 uppercase tracking-wider">
              <span class="material-symbols-outlined text-sm text-slate-400">security</span>
              D. Pengendalian Satwa & Tindak Lanjut
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
              @foreach($columns as $key => $col)
                @if($col['category'] === 'tindakan')
                  <label class="column-card flex items-start gap-2.5 p-2.5 rounded-xl border border-slate-200 hover:border-slate-300 hover:bg-slate-50/70 cursor-pointer transition-colors has-[:checked]:border-cyan-300 has-[:checked]:bg-cyan-50/40">
                    <input type="checkbox" name="columns[]" value="{{ $key }}" class="col-checkbox rounded border-slate-300 text-primary focus:ring-primary mt-0.5">
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center justify-between gap-1">
                        <span class="text-xs font-bold text-slate-900 truncate">{{ $col['label'] }}</span>
                        @if(in_array($key, $presets['dkppu']['columns']))
                          <span class="text-[9px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded shrink-0">DKPPU</span>
                        @endif
                      </div>
                      <p class="text-[10px] text-slate-500 truncate mt-0.5">{{ $col['description'] }}</p>
                    </div>
                  </label>
                @endif
              @endforeach
            </div>
          </div>

        </div>
      </div>

      <!-- 5. TATA LETAK & OPSI BERKAS EKSPOR -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6 space-y-4">
        <div class="border-b border-slate-100 pb-3">
          <h2 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-xl">tune</span>
            Langkah 4: Tata Letak Baris & Format Berkas
          </h2>
          <p class="text-xs text-slate-500">Tentukan cara penyusunan baris data dan format dokumen yang dihasilkan.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          
          <!-- Mode Baris -->
          <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/50 space-y-2">
            <label class="block text-xs font-bold text-slate-800">Format Penyusunan Baris</label>
            <div class="space-y-2 text-xs">
              <label class="flex items-start gap-2 cursor-pointer">
                <input type="radio" name="mode_baris" value="per_satwa" id="modePerSatwa" class="mt-0.5 text-primary focus:ring-primary" checked>
                <div>
                  <span class="font-bold text-slate-800">Rincian Per Temuan Satwa</span>
                  <p class="text-[10px] text-slate-500">1 baris = 1 temuan satwa. Standar DKPPU & ideal untuk pivot tabel.</p>
                </div>
              </label>
              <label class="flex items-start gap-2 cursor-pointer">
                <input type="radio" name="mode_baris" value="per_laporan" id="modePerLaporan" class="mt-0.5 text-primary focus:ring-primary">
                <div>
                  <span class="font-bold text-slate-800">Ringkasan Per Laporan</span>
                  <p class="text-[10px] text-slate-500">1 baris = 1 laporan. Temuan satwa digabung dalam satu sel.</p>
                </div>
              </label>
            </div>
          </div>

          <!-- Formula Ringkasan -->
          <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/50 space-y-2">
            <label class="block text-xs font-bold text-slate-800">Formula Rekapitulasi</label>
            <label class="flex items-start gap-2 cursor-pointer text-xs mt-1">
              <input type="checkbox" name="include_summary" value="1" class="mt-0.5 rounded text-primary focus:ring-primary" checked>
              <div>
                <span class="font-bold text-slate-800">Sertakan Baris Total & Formula</span>
                <p class="text-[10px] text-slate-500">Menambahkan baris rekapitulasi di baris terbawah (Total Ekor, Amunisi, dan % Selesai).</p>
              </div>
            </label>
          </div>

          <!-- Format File -->
          <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/50 space-y-2">
            <label class="block text-xs font-bold text-slate-800">Tipe Berkas Unduhan</label>
            <div class="space-y-2 text-xs">
              <label class="flex items-start gap-2 cursor-pointer">
                <input type="radio" name="file_format" value="xlsx" class="mt-0.5 text-primary focus:ring-primary" checked>
                <div>
                  <span class="font-bold text-slate-800">Microsoft Excel (.xlsx)</span>
                  <p class="text-[10px] text-slate-500">Format Excel asli lengkap dengan warna kop dinas, lebar kolom otomatis, dan bebas peringatan.</p>
                </div>
              </label>
              <label class="flex items-start gap-2 cursor-pointer">
                <input type="radio" name="file_format" value="csv" class="mt-0.5 text-primary focus:ring-primary">
                <div>
                  <span class="font-bold text-slate-800">File CSV (Comma Separated)</span>
                  <p class="text-[10px] text-slate-500">Format ringan untuk integrasi data atau olah data script statistik.</p>
                </div>
              </label>
            </div>
          </div>

        </div>
      </div>

      <!-- 6. FLOATING BOTTOM ACTION BAR -->
      <div class="sticky bottom-5 z-40 bg-slate-900/95 backdrop-blur-md text-white rounded-2xl p-4 sm:p-5 shadow-2xl border border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
        
        <!-- Live Counter Telemetry -->
        <div class="flex items-center gap-3 w-full sm:w-auto">
          <span class="p-2.5 rounded-xl bg-white/10 text-[#00A9C1] flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-2xl">analytics</span>
          </span>
          <div>
            <div class="flex items-center gap-2">
              <span id="liveCounterText" class="text-xs sm:text-sm font-bold text-white">
                Memuat data...
              </span>
              <span id="counterSpinner" class="inline-block animate-spin text-slate-400">
                <span class="material-symbols-outlined text-xs">sync</span>
              </span>
            </div>
            <p id="liveCounterSub" class="text-[11px] text-slate-400">
              Menyesuaikan otomatis dengan filter yang aktif
            </p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end">
          <a href="{{ route('admin.dashboard') }}" class="px-4 py-2.5 rounded-xl border border-white/20 hover:bg-white/10 text-xs font-semibold text-slate-200 transition-colors">
            Batal
          </a>
          <button type="submit" id="btnDownload" class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-[#00A9C1] hover:bg-[#008ba0] text-white text-xs sm:text-sm font-bold shadow-md shadow-cyan-900/30 transition-all cursor-pointer">
            <span class="material-symbols-outlined text-lg">download</span>
            <span>Unduh Spreadsheet Sekarang</span>
          </button>
        </div>

      </div>

    </form>

  </main>

  <!-- JAVASCRIPT LOGIC UNTUK INTERAKTIVITAS HALAMAN EKSPOR -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Data Presets dari Server
      const presets = @json($presets);

      const form = document.getElementById('exportForm');
      const presetCards = document.querySelectorAll('.preset-card');
      const colCheckboxes = document.querySelectorAll('.col-checkbox');
      const selectedCountBadge = document.getElementById('selectedCountBadge');
      const btnSelectAll = document.getElementById('btnSelectAll');
      const btnDeselectAll = document.getElementById('btnDeselectAll');
      const btnResetDkppu = document.getElementById('btnResetDkppu');
      const btnResetFilter = document.getElementById('btnResetFilter');

      const modePeriodeRadios = document.querySelectorAll('input[name="mode_periode"]');
      const containerTahun = document.getElementById('containerTahun');
      const containerRange = document.getElementById('containerRange');

      const liveCounterText = document.getElementById('liveCounterText');
      const liveCounterSub = document.getElementById('liveCounterSub');
      const counterSpinner = document.getElementById('counterSpinner');
      const filterTriggers = document.querySelectorAll('.filter-trigger');

      // 1. Toggle Periode Controls
      function updatePeriodeDisplay() {
        const selectedMode = document.querySelector('input[name="mode_periode"]:checked')?.value || 'all';
        if (selectedMode === 'tahun') {
          containerTahun.classList.remove('hidden');
          containerRange.classList.add('hidden');
        } else if (selectedMode === 'range') {
          containerTahun.classList.add('hidden');
          containerRange.classList.remove('hidden');
        } else {
          containerTahun.classList.add('hidden');
          containerRange.classList.add('hidden');
        }
      }

      modePeriodeRadios.forEach(radio => {
        radio.addEventListener('change', () => {
          updatePeriodeDisplay();
          fetchLiveCount();
        });
      });
      updatePeriodeDisplay();

      // 2. Update Checklist Kolom dari Preset
      function applyPreset(presetKey) {
        const preset = presets[presetKey];
        if (!preset) return;

        // Update card styles
        presetCards.forEach(card => {
          const isThis = card.getAttribute('data-preset') === presetKey;
          const radio = card.querySelector('input[type="radio"]');
          radio.checked = isThis;
          const iconBox = card.querySelector('.preset-icon-box');

          if (isThis) {
            card.classList.add('border-primary', 'bg-cyan-50/40', 'shadow-xs');
            card.classList.remove('border-slate-200', 'bg-white');
            if (iconBox) {
              iconBox.classList.add('bg-primary', 'text-white');
              iconBox.classList.remove('bg-slate-100', 'text-slate-600');
            }
          } else {
            card.classList.remove('border-primary', 'bg-cyan-50/40', 'shadow-xs');
            card.classList.add('border-slate-200', 'bg-white');
            if (iconBox) {
              iconBox.classList.remove('bg-primary', 'text-white');
              iconBox.classList.add('bg-slate-100', 'text-slate-600');
            }
          }
        });

        // Jika bukan custom, sinkronkan checkbox
        if (presetKey !== 'custom') {
          const targetCols = preset.columns || [];
          colCheckboxes.forEach(cb => {
            cb.checked = targetCols.includes(cb.value);
          });

          // Set mode baris
          if (preset.mode_baris === 'per_satwa') {
            document.getElementById('modePerSatwa').checked = true;
          } else {
            document.getElementById('modePerLaporan').checked = true;
          }
        }

        updateSelectedCount();
      }

      presetCards.forEach(card => {
        card.addEventListener('click', function () {
          const key = this.getAttribute('data-preset');
          applyPreset(key);
        });
      });

      // 3. Update Count Kolom Terpilih
      function updateSelectedCount() {
        const checkedCount = document.querySelectorAll('.col-checkbox:checked').length;
        selectedCountBadge.textContent = `${checkedCount} Kolom Terpilih`;

        if (checkedCount === 0) {
          selectedCountBadge.className = 'text-xs font-bold px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 border border-rose-200';
        } else {
          selectedCountBadge.className = 'text-xs font-bold px-2.5 py-1 rounded-lg bg-cyan-50 text-[#007fa3] border border-cyan-100';
        }
      }

      // Jika user klik checkbox manual, otomatis switch preset ke custom
      colCheckboxes.forEach(cb => {
        cb.addEventListener('change', () => {
          updateSelectedCount();
          // Highlight preset custom
          presetCards.forEach(card => {
            const isCustom = card.getAttribute('data-preset') === 'custom';
            const radio = card.querySelector('input[type="radio"]');
            radio.checked = isCustom;
            if (isCustom) {
              card.classList.add('border-primary', 'bg-cyan-50/40');
              card.classList.remove('border-slate-200', 'bg-white');
            } else {
              card.classList.remove('border-primary', 'bg-cyan-50/40');
              card.classList.add('border-slate-200', 'bg-white');
            }
          });
        });
      });

      // Tombol Quick Actions Kolom
      btnSelectAll.addEventListener('click', () => {
        colCheckboxes.forEach(cb => cb.checked = true);
        applyPreset('lengkap');
      });

      btnDeselectAll.addEventListener('click', () => {
        colCheckboxes.forEach(cb => cb.checked = false);
        updateSelectedCount();
      });

      btnResetDkppu.addEventListener('click', () => {
        applyPreset('dkppu');
      });

      btnResetFilter.addEventListener('click', () => {
        document.querySelector('input[name="mode_periode"][value="all"]').checked = true;
        document.getElementById('filterUnit').value = 'all';
        document.getElementById('filterStatus').value = 'all';
        document.getElementById('filterGrid').value = 'all';
        document.getElementById('filterSatwa').value = 'all';
        updatePeriodeDisplay();
        fetchLiveCount();
      });

      // 4. Live Counter Fetch AJAX
      let debounceTimer = null;
      function fetchLiveCount() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
          counterSpinner.classList.remove('hidden');

          const formData = new FormData(form);
          const params = new URLSearchParams();

          params.append('mode_periode', formData.get('mode_periode') || 'all');
          if (formData.get('tahun')) params.append('tahun', formData.get('tahun'));
          if (formData.get('start_date')) params.append('start_date', formData.get('start_date'));
          if (formData.get('end_date')) params.append('end_date', formData.get('end_date'));
          if (formData.get('unit_kerja')) params.append('unit_kerja', formData.get('unit_kerja'));
          if (formData.get('status')) params.append('status', formData.get('status'));
          if (formData.get('grid_lokasi')) params.append('grid_lokasi', formData.get('grid_lokasi'));
          if (formData.get('nama_satwa')) params.append('nama_satwa', formData.get('nama_satwa'));

          fetch(`{{ route('admin.export.excel.count') }}?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
              counterSpinner.classList.add('hidden');
              if (data.status === 'success') {
                liveCounterText.textContent = `${data.total_laporan} Laporan Ditemukan (${data.total_ekor} Ekor Satwa)`;
                liveCounterSub.textContent = `Siap diekspor (~${data.total_satwa_rows} baris rincian data spreadsheet)`;
              }
            })
            .catch(() => {
              counterSpinner.classList.add('hidden');
              liveCounterText.textContent = `Data siap diekspor`;
            });
        }, 300);
      }

      filterTriggers.forEach(el => {
        el.addEventListener('change', fetchLiveCount);
        el.addEventListener('input', fetchLiveCount);
      });

      // Inisialisasi awal saat halaman dibuka
      applyPreset('{{ $preSelectedPreset ?: "dkppu" }}');
      fetchLiveCount();

      // 5. Panel Bundel Logbook Bulanan PDF (REQ-REP-002)
      const bundleBulan = document.getElementById('bundleBulan');
      const bundleTahun = document.getElementById('bundleTahun');
      const bundleStatus = document.getElementById('bundleStatus');
      const bundleUnit = document.getElementById('bundleUnit');
      const bundleCounterText = document.getElementById('bundleCounterText');
      const bundleCounterSub = document.getElementById('bundleCounterSub');
      const btnPreviewBundlePdf = document.getElementById('btnPreviewBundlePdf');
      const bundleTriggers = document.querySelectorAll('.bundle-filter-trigger');

      function buildBundleParams(includeInline = false) {
        const params = new URLSearchParams();
        params.append('bulan', bundleBulan?.value || '{{ $preSelectedBulan }}');
        params.append('tahun', bundleTahun?.value || '{{ $preSelectedTahun }}');
        params.append('status', bundleStatus?.value || 'all');
        params.append('unit_kerja', bundleUnit?.value || 'all');
        if (includeInline) {
          params.append('inline', '1');
        }
        return params;
      }

      function syncBundlePreviewLink() {
        if (!btnPreviewBundlePdf) return;
        const params = buildBundleParams(true);
        btnPreviewBundlePdf.href = `{{ route('admin.export.bundle.pdf') }}?${params.toString()}`;
      }

      let bundleCountTimer = null;
      function fetchBundleLiveCount() {
        syncBundlePreviewLink();
        if (!bundleCounterText || !bundleCounterSub) return;

        clearTimeout(bundleCountTimer);
        bundleCountTimer = setTimeout(() => {
          const params = buildBundleParams(false);
          fetch(`{{ route('admin.export.bundle.count') }}?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
              if (data.status === 'success') {
                if (data.total_laporan > 0) {
                  bundleCounterText.textContent = `${data.total_laporan} Berita Acara (${data.periode_label})`;
                  bundleCounterSub.textContent = `${data.total_tervalidasi} tervalidasi • ${data.total_ekor} ekor satwa`;
                } else {
                  bundleCounterText.textContent = `0 Berita Acara (${data.periode_label})`;
                  bundleCounterSub.textContent = `Akan diterbitkan Buku Log Nihil Resmi (Cover, Pengantar & Statistik)`;
                }
              }
            })
            .catch(() => {
              bundleCounterText.textContent = `Siap dikompilasi ke PDF A4`;
              bundleCounterSub.textContent = `Menyesuaikan bulan dan tahun arsip terpilih`;
            });
        }, 200);
      }

      bundleTriggers.forEach(el => {
        el.addEventListener('change', fetchBundleLiveCount);
      });
      fetchBundleLiveCount();
    });
  </script>

</body>
</html>
