<!DOCTYPE html>
<html lang="id" class="h-full bg-canvas-light text-on-surface">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Kepadatan Penerbangan - InJourney Airports</title>

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

  <!-- Tailwind CSS CDN with configuration -->
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
            "resolved-emerald": "#10B981",
            "warning-rose": "#EF4444",
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
    ::-webkit-scrollbar-track { background: #F8FAFC; }
    ::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 9999px; }
    ::-webkit-scrollbar-thumb:hover { background: #94A3B8; }
  </style>
</head>
<body class="min-h-full bg-canvas-light text-on-surface font-sans antialiased selection:bg-injourney-teal selection:text-white flex flex-col">

  <!-- SIDEBAR NAVIGATION -->
  @include('partials.navbar', ['activePage' => 'ai_lalu_lintas'])

  <!-- SUB-HEADER & COMMAND STRIP -->
  @include('partials.navbar-ai', [
    'title' => 'Kepadatan Lalu Lintas Penerbangan',
    'subtitle' => 'Input pergerakan pesawat per jam (24 jam) untuk korelasi jam rawan birdstrike dengan jam sibuk bandara.',
    'icon' => 'flight_takeoff',
    'actions' => '
      <a href="' . route('admin.ai.lalu-lintas.template') . '" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg border border-slate-200/90 bg-white text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs shrink-0 whitespace-nowrap">
        <span class="material-symbols-outlined text-base text-slate-500">download</span>
        <span>Template CSV</span>
      </a>
      <button type="button" onclick="openModal(\'modalImportTraffic\')" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg border border-cyan-200 bg-cyan-50/80 text-[#007fa3] hover:bg-cyan-100 transition-colors shadow-2xs shrink-0 whitespace-nowrap cursor-pointer">
        <span class="material-symbols-outlined text-base">upload_file</span>
        <span>Impor CSV</span>
      </button>
      <button type="submit" form="formTraffic24h" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-lg bg-gradient-to-r from-[#00A9C1] to-[#007fa3] hover:from-[#007fa3] hover:to-[#00607d] text-white shadow-xs transition-all active:scale-[0.98] shrink-0 whitespace-nowrap cursor-pointer">
        <span class="material-symbols-outlined text-base">save</span>
        <span>Simpan Data</span>
      </button>
    '
  ])

  <!-- ================= MAIN OPERATIONAL CONTENT CONTAINER ================= -->
  <main class="flex-1 max-w-[1680px] w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- 1. BREADCRUMB -->
    <nav class="flex items-center gap-2 text-xs text-slate-500 font-medium">
      <a href="{{ route('admin.dashboard') }}" class="hover:text-[#00A9C1] transition-colors flex items-center gap-1">
        <span class="material-symbols-outlined text-sm">home</span>
        Beranda
      </a>
      <span>/</span>
      <span class="text-slate-400">Kecerdasan Buatan (AI)</span>
      <span>/</span>
      <span class="text-slate-800 font-semibold">Kepadatan Lalu Lintas Penerbangan</span>
    </nav>

    <!-- Flash Alert Messages -->
    @if(session('success'))
      <div class="bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-xl p-3.5 flex items-center justify-between text-xs font-medium shadow-2xs">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-emerald-600 text-lg">check_circle</span>
          <span>{{ session('success') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 cursor-pointer">
          <span class="material-symbols-outlined text-base">close</span>
        </button>
      </div>
    @endif

    @if(session('error'))
      <div class="bg-rose-50 text-rose-800 border border-rose-200 rounded-xl p-3.5 flex items-center justify-between text-xs font-medium shadow-2xs">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-rose-600 text-lg">error</span>
          <span>{{ session('error') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800 cursor-pointer">
          <span class="material-symbols-outlined text-base">close</span>
        </button>
      </div>
    @endif

    <!-- 2. DATE SELECTION & KPI BENTO CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <!-- Date Picker Card -->
      <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex flex-col justify-between hover:border-cyan-200 transition-colors">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pilih Tanggal Operasional</span>
          <span class="material-symbols-outlined text-slate-400 text-lg">calendar_month</span>
        </div>
        <form method="GET" action="{{ route('admin.ai.lalu-lintas.index') }}" class="flex items-center gap-2 mt-2">
          <input type="date" name="tanggal" value="{{ $selectedDate }}" onchange="this.form.submit()" class="w-full px-3 py-2 text-xs font-bold rounded-xl border border-slate-200/90 focus:outline-none focus:border-[#00A9C1] bg-slate-50/80 text-slate-800 shadow-2xs">
          <noscript>
            <button type="submit" class="px-3 py-2 text-xs font-bold bg-slate-800 text-white rounded-xl shadow-2xs">Pilih</button>
          </noscript>
        </form>
      </div>

      <!-- Total Movements Card -->
      <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex items-center gap-3.5 hover:border-cyan-200 transition-colors">
        <div class="w-11 h-11 rounded-xl bg-cyan-50 text-[#007fa3] border border-cyan-100 flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-2xl">connecting_airports</span>
        </div>
        <div class="min-w-0">
          <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Pergerakan (24 Jam)</div>
          <div class="text-xl font-extrabold text-slate-800 mt-0.5">
            {{ number_format($totalMovements) }}
            <span class="text-xs font-medium text-slate-400">pergerakan</span>
          </div>
          <div class="text-[11px] text-slate-500 mt-0.5">
            <span class="font-semibold text-slate-700">{{ number_format($totalTakeoff) }}</span> take-off ·
            <span class="font-semibold text-slate-700">{{ number_format($totalLanding) }}</span> landing
          </div>
        </div>
      </div>

      <!-- Peak Hour Card -->
      <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex items-center gap-3.5 hover:border-amber-200 transition-colors">
        <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-700 border border-amber-100 flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-2xl">alarm</span>
        </div>
        <div class="min-w-0">
          <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Jam Paling Sibuk (Peak)</div>
          <div class="text-xl font-extrabold text-amber-700 mt-0.5">
            {{ $peakHour ? $peakHour . ' WIB' : 'Belum Terdata' }}
          </div>
        </div>
      </div>
    </div>

    <!-- 3. 24-HOUR INPUT FORM PANEL -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
      <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-2.5">
          <div class="w-9 h-9 rounded-xl bg-cyan-50 text-[#00A9C1] flex items-center justify-center border border-cyan-100 shrink-0">
            <span class="material-symbols-outlined text-xl">schedule</span>
          </div>
          <div>
            <h2 class="text-sm sm:text-base font-bold text-slate-800">
              Rincian Pergerakan Pesawat 24 Jam
            </h2>
            <p class="text-[11px] text-slate-500">
              Tanggal Operasional: <span class="font-bold text-slate-700">{{ Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y') }}</span>
            </p>
          </div>
        </div>

        <button type="submit" form="formTraffic24h" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-[#00A9C1] to-[#007fa3] hover:from-[#007fa3] hover:to-[#00607d] shadow-xs shadow-cyan-500/20 cursor-pointer self-start md:self-auto transition-all active:scale-[0.98]">
          <span class="material-symbols-outlined text-base">save</span>
          <span>Simpan Pergerakan</span>
        </button>
      </div>

      <form id="formTraffic24h" method="POST" action="{{ route('admin.ai.lalu-lintas.store') }}" class="p-4 sm:p-6">
        @csrf
        <input type="hidden" name="tanggal" value="{{ $selectedDate }}">

        @if($unsplitHours > 0)
          <div class="mb-4 p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-[11px]">
            {{ $unsplitHours }} jam pada tanggal ini masih berisi total lama tanpa rincian take-off/landing. Isi kedua kolomnya untuk memisahkan; bila dibiarkan kosong, total lama tetap disimpan.
          </div>
        @endif

        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3 text-xs">
          @for($h = 0; $h <= 23; $h++)
            @php
              $slot = $hoursData[$h];
              $val = $slot['total'];
              $isPeak = ($val > 0 && sprintf('%02d:00', $h) === $peakHour);
            @endphp
            <div class="p-3 rounded-2xl border {{ $isPeak ? 'border-amber-300 bg-amber-50/60 shadow-2xs ring-1 ring-amber-200' : 'border-slate-200/80 bg-slate-50/60 hover:bg-slate-50 hover:border-slate-300' }} flex flex-col justify-between transition-all">
              <div class="flex items-center justify-between mb-2">
                <span class="font-bold font-mono text-xs {{ $isPeak ? 'text-amber-900 font-extrabold' : 'text-slate-700' }}">
                  {{ sprintf('%02d:00', $h) }}
                </span>
                @if($isPeak)
                  <span class="px-1.5 py-0.5 rounded-full text-[9px] font-extrabold bg-amber-200 text-amber-900 border border-amber-300">Peak</span>
                @endif
              </div>
              <div class="space-y-1.5">
                <label class="flex items-center gap-1.5">
                  <span class="w-7 shrink-0 text-[10px] font-bold text-slate-500" title="Take-off">TO</span>
                  <input type="number" name="takeoff[{{ $h }}]" value="{{ $slot['takeoff'] }}" min="0" max="200" placeholder="–" aria-label="Take-off pukul {{ sprintf('%02d:00', $h) }}" class="w-full px-1.5 py-1.5 text-sm text-center font-bold text-slate-800 rounded-lg border border-slate-200/90 focus:outline-none focus:border-[#00A9C1] bg-white shadow-2xs">
                </label>
                <label class="flex items-center gap-1.5">
                  <span class="w-7 shrink-0 text-[10px] font-bold text-slate-500" title="Landing">LDG</span>
                  <input type="number" name="landing[{{ $h }}]" value="{{ $slot['landing'] }}" min="0" max="200" placeholder="–" aria-label="Landing pukul {{ sprintf('%02d:00', $h) }}" class="w-full px-1.5 py-1.5 text-sm text-center font-bold text-slate-800 rounded-lg border border-slate-200/90 focus:outline-none focus:border-[#00A9C1] bg-white shadow-2xs">
                </label>
                <span class="block text-[10px] text-center mt-1 font-medium {{ $slot['terpisah'] ? 'text-slate-400' : 'text-amber-700' }}">
                  {{ $slot['terpisah'] ? 'Total '.$val : 'Total lama '.$val.' (belum dipisah)' }}
                </span>
              </div>
            </div>
          @endfor
        </div>

        <div class="mt-6 pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
          <span class="flex items-center gap-1.5 text-slate-400 text-[11px]">
            <span class="material-symbols-outlined text-sm">info</span>
            Kolom kosong berarti tidak diubah; isi 0 bila memang tidak ada pergerakan. Total dihitung otomatis dari take-off + landing.
          </span>
          <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#00A9C1] hover:bg-[#007fa3] text-white font-bold transition-all shadow-xs active:scale-[0.98] cursor-pointer">
            Simpan Seluruh Data 24 Jam
          </button>
        </div>
      </form>
    </div>

  </main>

  <!-- ================= MODAL IMPOR CSV ================= -->
  <div id="modalImportTraffic" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" role="dialog" aria-modal="true">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 p-5 sm:p-6">
      <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 mb-4">
        <h3 class="text-sm sm:text-base font-bold text-slate-800 flex items-center gap-2">
          <span class="material-symbols-outlined text-[#00A9C1]">upload_file</span>
          Impor CSV Kepadatan Penerbangan
        </h3>
        <button type="button" onclick="closeModal('modalImportTraffic')" class="text-slate-400 hover:text-slate-600 cursor-pointer">
          <span class="material-symbols-outlined text-xl">close</span>
        </button>
      </div>

      <form method="POST" action="{{ route('admin.ai.lalu-lintas.import') }}" enctype="multipart/form-data" class="space-y-4 text-xs">
        @csrf
        <p class="text-slate-500 leading-relaxed">
          Format kolom file CSV: <code>tanggal</code> (YYYY-MM-DD), <code>jam</code> (0-23), <code>jumlah_takeoff</code>, <code>jumlah_landing</code>.
          File format lama dengan kolom <code>jumlah_pergerakan</code> (total saja) tetap diterima.
        </p>

        <div class="border-2 border-dashed border-slate-200 rounded-xl p-4 text-center hover:border-[#00A9C1] transition-colors">
          <span class="material-symbols-outlined text-3xl text-slate-400 mb-1">file_upload</span>
          <input type="file" name="csv_file" accept=".csv,text/csv,text/plain" required class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-cyan-50 file:text-[#007fa3] hover:file:bg-cyan-100 cursor-pointer">
          <div class="text-[10px] text-slate-400 mt-2">Maksimal 5 MB (.csv)</div>
        </div>

        <div class="flex items-center justify-between pt-3 border-t border-slate-100">
          <a href="{{ route('admin.ai.lalu-lintas.template') }}" class="text-[11px] text-[#007fa3] hover:underline flex items-center gap-1">
            <span class="material-symbols-outlined text-xs">download</span> Unduh contoh CSV
          </a>
          <div class="flex items-center gap-2">
            <button type="button" onclick="closeModal('modalImportTraffic')" class="px-3 py-1.5 rounded-lg text-slate-600 hover:bg-slate-100 font-semibold cursor-pointer">Batal</button>
            <button type="submit" class="px-4 py-1.5 rounded-lg bg-gradient-to-r from-[#00A9C1] to-[#007fa3] text-white font-bold shadow-xs cursor-pointer">Mulai Impor</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openModal(id) {
      const el = document.getElementById(id);
      if (el) {
        el.classList.remove('hidden');
        el.classList.add('flex');
      }
    }

    function closeModal(id) {
      const el = document.getElementById(id);
      if (el) {
        el.classList.add('hidden');
        el.classList.remove('flex');
      }
    }
  </script>
</body>
</html>
