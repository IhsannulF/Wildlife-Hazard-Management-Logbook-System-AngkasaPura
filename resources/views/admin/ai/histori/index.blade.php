<!DOCTYPE html>
<html lang="id" class="h-full bg-canvas-light text-on-surface">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Histori Insiden Birdstrike - InJourney Airports</title>

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

    .logbook-table-wrapper {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
    }
    .logbook-table-wrapper table {
      min-width: 1080px;
    }
  </style>
</head>
<body class="min-h-full bg-canvas-light text-on-surface font-sans antialiased selection:bg-injourney-teal selection:text-white flex flex-col">

  <!-- SIDEBAR NAVIGATION -->
  @include('partials.navbar', ['activePage' => 'ai_histori'])

  <!-- SUB-HEADER & COMMAND STRIP -->
  @include('partials.navbar-ai', [
    'title' => 'Histori Insiden Birdstrike',
    'subtitle' => 'Basis data insiden lampau untuk pemodelan preseden dan pembelajaran probabilitas AI Gemini.',
    'icon' => 'history_edu',
    'actions' => '
      <a href="' . route('admin.ai.histori.template') . '" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg border border-slate-200/90 bg-white text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs shrink-0 whitespace-nowrap">
        <span class="material-symbols-outlined text-base text-slate-500">download</span>
        <span>Template CSV</span>
      </a>
      <button type="button" onclick="openModal(\'modalImportHistori\')" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg border border-cyan-200 bg-cyan-50/80 text-[#007fa3] hover:bg-cyan-100 transition-colors shadow-2xs shrink-0 whitespace-nowrap cursor-pointer">
        <span class="material-symbols-outlined text-base">upload_file</span>
        <span>Impor CSV</span>
      </button>
      <button type="button" onclick="openCreateModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-lg bg-gradient-to-r from-[#00A9C1] to-[#007fa3] hover:from-[#007fa3] hover:to-[#00607d] text-white shadow-xs transition-all active:scale-[0.98] shrink-0 whitespace-nowrap cursor-pointer">
        <span class="material-symbols-outlined text-base">add_circle</span>
        <span>Tambah Insiden</span>
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
      <span class="text-slate-800 font-semibold">Histori Insiden Birdstrike</span>
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

    <!-- 2. SUMMARY KPI BENTO CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex items-center gap-3.5 hover:border-cyan-200 transition-colors">
        <div class="w-11 h-11 rounded-xl bg-cyan-50 text-[#007fa3] border border-cyan-100 flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-2xl">database</span>
        </div>
        <div class="min-w-0">
          <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Basis Histori</div>
          <div class="text-xl font-extrabold text-slate-800 mt-0.5">
            {{ number_format($stats['total']) }}
            <span class="text-xs font-medium text-slate-400">insiden</span>
          </div>
        </div>
      </div>

      <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex items-center gap-3.5 hover:border-emerald-200 transition-colors">
        <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-2xl">calendar_today</span>
        </div>
        <div class="min-w-0">
          <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Insiden Tahun {{ date('Y') }}</div>
          <div class="text-xl font-extrabold text-slate-800 mt-0.5">
            {{ number_format($stats['tahun_ini']) }}
            <span class="text-xs font-medium text-slate-400">kejadian</span>
          </div>
        </div>
      </div>

      <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex items-center gap-3.5 hover:border-rose-200 transition-colors">
        <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-700 border border-rose-100 flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-2xl">warning</span>
        </div>
        <div class="min-w-0">
          <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kerusakan Mayor / Fatal</div>
          <div class="text-xl font-extrabold text-rose-700 mt-0.5">
            {{ number_format($stats['fatal_substansial']) }}
            <span class="text-xs font-medium text-rose-400">kejadian</span>
          </div>
        </div>
      </div>
    </div>

    <!-- 3. TABLE CARD: HISTORI BIRDSTRIKE -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
      <!-- Toolbar Filter & Search -->
      <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-2.5">
          <div class="w-9 h-9 rounded-xl bg-cyan-50 text-[#00A9C1] flex items-center justify-center border border-cyan-100 shrink-0">
            <span class="material-symbols-outlined text-xl">folder</span>
          </div>
          <div>
            <h2 class="text-sm sm:text-base font-bold text-slate-800">Catatan Insiden Birdstrike</h2>
            <p class="text-[11px] text-slate-500">Basis data riwayat tabrakan satwa dengan pesawat udara.</p>
          </div>
        </div>

        <!-- Filter Controls -->
        <form method="GET" action="{{ route('admin.ai.histori.index') }}" class="flex flex-wrap items-center gap-2">
          <select name="runway" class="px-2.5 py-1.5 text-xs font-medium rounded-lg border border-slate-200/90 focus:outline-none focus:border-[#00A9C1] bg-slate-50/80 text-slate-700">
            <option value="">Semua Runway</option>
            <option value="10" {{ request('runway') == '10' ? 'selected' : '' }}>Runway 10</option>
            <option value="28" {{ request('runway') == '28' ? 'selected' : '' }}>Runway 28</option>
          </select>

          <select name="kerusakan" class="px-2.5 py-1.5 text-xs font-medium rounded-lg border border-slate-200/90 focus:outline-none focus:border-[#00A9C1] bg-slate-50/80 text-slate-700">
            <option value="">Semua Kerusakan</option>
            <option value="nihil" {{ request('kerusakan') == 'nihil' ? 'selected' : '' }}>Nihil</option>
            <option value="minor" {{ request('kerusakan') == 'minor' ? 'selected' : '' }}>Minor</option>
            <option value="substansial" {{ request('kerusakan') == 'substansial' ? 'selected' : '' }}>Substansial</option>
            <option value="fatal" {{ request('kerusakan') == 'fatal' ? 'selected' : '' }}>Fatal</option>
          </select>

          <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold transition-colors cursor-pointer shadow-2xs">
            Filter
          </button>
          @if(request()->anyFilled(['runway', 'kerusakan']))
            <a href="{{ route('admin.ai.histori.index') }}" class="px-2.5 py-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 hover:underline">
              Reset
            </a>
          @endif
        </form>
      </div>

      <!-- Table Wrapper -->
      <div class="logbook-table-wrapper">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-slate-50/80 text-slate-500 text-[11px] uppercase font-bold tracking-wider border-b border-slate-200/80">
              <th class="py-3 px-4 min-w-[130px]">Tanggal &amp; Jam</th>
              <th class="py-3 px-4 min-w-[140px]">Pesawat &amp; Fase</th>
              <th class="py-3 px-4 min-w-[120px]">Lokasi Runway</th>
              <th class="py-3 px-4 min-w-[140px]">Spesies Satwa</th>
              <th class="py-3 px-4 min-w-[120px]">Bagian Pesawat</th>
              <th class="py-3 px-4 min-w-[120px]">Tingkat Kerusakan</th>
              <th class="py-3 px-4 text-center min-w-[100px]">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs">
            @forelse($historiList as $item)
              @php
                $badgeKerusakan = match($item->tingkat_kerusakan) {
                  'fatal' => 'bg-rose-100 text-rose-800 border-rose-200',
                  'substansial' => 'bg-amber-100 text-amber-800 border-amber-200',
                  'minor' => 'bg-cyan-100 text-[#007fa3] border-cyan-200',
                  default => 'bg-emerald-100 text-emerald-800 border-emerald-200'
                };
              @endphp
              <tr class="hover:bg-slate-50/70 transition-colors">
                <td class="py-3 px-4">
                  <div class="font-bold text-slate-800">{{ $item->tanggal_kejadian?->format('d M Y') }}</div>
                  <div class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $item->jam_kejadian ? substr($item->jam_kejadian, 0, 5) . ' WIB' : '-' }}</div>
                  @if($item->jenis_kejadian === 'near_miss')
                    <span class="inline-block mt-1 text-[10px] px-1.5 py-0.5 rounded border border-amber-200 bg-amber-50 text-amber-800 font-bold">NEAR-MISS</span>
                  @endif
                </td>
                <td class="py-3 px-4">
                  <div class="font-bold text-slate-800">{{ $item->jenis_pesawat }}</div>
                  <div class="text-[10px] text-slate-400 uppercase font-semibold mt-0.5">{{ str_replace('_', ' ', $item->fase_penerbangan) }}</div>
                </td>
                <td class="py-3 px-4 font-mono">
                  <div class="font-bold text-slate-700">RWY {{ $item->kode_runway }}</div>
                  <div class="text-[10px] text-slate-400 capitalize mt-0.5">{{ $item->titik_lokasi }}</div>
                </td>
                <td class="py-3 px-4">
                  <div class="font-bold text-slate-800">{{ $item->spesies_satwa }}</div>
                  <div class="text-[10px] text-slate-400 mt-0.5">{{ $item->jumlah_satwa }} ekor</div>
                </td>
                <td class="py-3 px-4 text-slate-600">
                  {{ $item->bagian_pesawat }}
                </td>
                <td class="py-3 px-4">
                  <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border {{ $badgeKerusakan }}">
                    {{ strtoupper($item->tingkat_kerusakan) }}
                  </span>
                </td>
                <td class="py-3 px-4 text-center">
                  <div class="inline-flex items-center gap-1">
                    <button type="button" onclick="openEditModal({{ json_encode($item) }})" class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-500 hover:text-[#00A9C1] hover:bg-cyan-50 transition-colors cursor-pointer" title="Edit Data">
                      <span class="material-symbols-outlined text-base">edit</span>
                    </button>
                    <form method="POST" action="{{ route('admin.ai.histori.destroy', $item->id) }}" onsubmit="return confirm('Hapus baris histori ini?')" class="inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer" title="Hapus Data">
                        <span class="material-symbols-outlined text-base">delete</span>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="py-12 text-center text-slate-400">
                  <span class="material-symbols-outlined text-4xl mb-2 text-slate-300">folder_off</span>
                  <p class="font-medium text-slate-500">Belum ada data histori birdstrike yang tersimpan.</p>
                  <p class="text-[11px] text-slate-400 mt-0.5">Tambahkan data insiden manual atau impor melalui CSV.</p>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Pagination Footer -->
      @if($historiList->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
          {{ $historiList->links() }}
        </div>
      @endif
    </div>

  </main>

  <!-- ================= MODAL TAMBAH / EDIT HISTORI ================= -->
  <div id="modalHistoriForm" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" role="dialog" aria-modal="true">
    <div class="bg-white rounded-2xl max-w-xl w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all p-5 sm:p-6">
      <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 mb-4">
        <h3 id="modalHistoriTitle" class="text-sm sm:text-base font-bold text-slate-800 flex items-center gap-2">
          <span class="material-symbols-outlined text-[#00A9C1]">history_edu</span>
          Tambah Insiden Birdstrike
        </h3>
        <button type="button" onclick="closeModal('modalHistoriForm')" class="text-slate-400 hover:text-slate-600 cursor-pointer">
          <span class="material-symbols-outlined text-xl">close</span>
        </button>
      </div>

      <form id="formHistori" method="POST" action="{{ route('admin.ai.histori.store') }}" class="space-y-3.5 text-xs">
        @csrf
        <input type="hidden" name="_method" id="historiMethod" value="POST">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-slate-700 mb-1">Tanggal Kejadian *</label>
            <input type="date" name="tanggal_kejadian" id="h_tanggal" max="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-[#00A9C1]">
          </div>
          <div>
            <label class="block font-semibold text-slate-700 mb-1">Jam Kejadian (WIB)</label>
            <input type="time" name="jam_kejadian" id="h_jam" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-[#00A9C1]">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label for="h_jenis" class="block font-semibold text-slate-700 mb-1">Jenis Kejadian *</label>
            <select name="jenis_kejadian" id="h_jenis" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-[#00A9C1]">
              @foreach(\App\Models\HistoriBirdstrike::JENIS_KEJADIAN as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label for="h_grid" class="block font-semibold text-slate-700 mb-1">Grid Lokasi</label>
            <input type="text" name="grid" id="h_grid" placeholder="K-10" class="w-full px-3 py-2 rounded-lg border border-slate-200 uppercase focus:outline-none focus:border-[#00A9C1]">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label for="h_ketinggian" class="block font-semibold text-slate-700 mb-1">Ketinggian (ft)</label>
            <input type="number" name="ketinggian_ft" id="h_ketinggian" min="0" max="20000" placeholder="Bila diketahui" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-[#00A9C1]">
          </div>
          <div>
            <label for="h_centerline" class="block font-semibold text-slate-700 mb-1">Jarak dari Centerline (m)</label>
            <input type="number" name="jarak_centerline_m" id="h_centerline" min="0" max="5000" placeholder="Bila diketahui" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-[#00A9C1]">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-slate-700 mb-1">Kode Runway *</label>
            <select name="kode_runway" id="h_runway" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-[#00A9C1]">
              <option value="10">Runway 10</option>
              <option value="28">Runway 28</option>
            </select>
          </div>
          <div>
            <label class="block font-semibold text-slate-700 mb-1">Titik Lokasi *</label>
            <select name="titik_lokasi" id="h_titik" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-[#00A9C1]">
              <option value="touchdown">Touchdown Zone</option>
              <option value="roll">Runway Roll</option>
              <option value="threshold">Threshold</option>
              <option value="grid">Grid Perimeter</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-slate-700 mb-1">Jenis Pesawat *</label>
            <input type="text" name="jenis_pesawat" id="h_pesawat" placeholder="B737-800, A320, dll" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-[#00A9C1]">
          </div>
          <div>
            <label class="block font-semibold text-slate-700 mb-1">Fase Penerbangan *</label>
            <select name="fase_penerbangan" id="h_fase" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-[#00A9C1]">
              <option value="take_off">Take Off</option>
              <option value="climb">Initial Climb</option>
              <option value="approach">Approach</option>
              <option value="landing">Landing</option>
              <option value="taxi">Taxiing</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div>
            <label class="block font-semibold text-slate-700 mb-1">Bagian Pesawat *</label>
            <input type="text" name="bagian_pesawat" id="h_bagian" placeholder="engine, windshield, wing" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-[#00A9C1]">
          </div>
          <div>
            <label class="block font-semibold text-slate-700 mb-1">Spesies Satwa *</label>
            <input type="text" name="spesies_satwa" id="h_spesies" placeholder="Kuntul Kerbau" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-[#00A9C1]">
          </div>
          <div>
            <label class="block font-semibold text-slate-700 mb-1">Jumlah (Ekor) *</label>
            <input type="number" name="jumlah_satwa" id="h_jumlah" value="1" min="1" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-[#00A9C1]">
          </div>
        </div>

        <div>
          <label class="block font-semibold text-slate-700 mb-1">Tingkat Kerusakan *</label>
          <select name="tingkat_kerusakan" id="h_kerusakan" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-[#00A9C1]">
            <option value="nihil">Nihil (Tidak Ada Kerusakan)</option>
            <option value="minor">Minor (Bisa Segera Diperbaiki / Terjadwal)</option>
            <option value="substansial">Substansial (Pesawat Grounded)</option>
            <option value="fatal">Fatal (Kerusakan Struktural Parah)</option>
          </select>
        </div>

        <div>
          <label class="block font-semibold text-slate-700 mb-1">Keterangan Tambahan</label>
          <textarea name="keterangan" id="h_keterangan" rows="2" placeholder="Catatan temuan bangkai, inspeksi teknisi runway, dsb..." class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-[#00A9C1]"></textarea>
        </div>

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
          <button type="button" onclick="closeModal('modalHistoriForm')" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold cursor-pointer">Batal</button>
          <button type="submit" class="px-4 py-2 rounded-xl bg-gradient-to-r from-[#00A9C1] to-[#007fa3] text-white font-bold shadow-xs cursor-pointer">Simpan Data</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ================= MODAL IMPOR CSV ================= -->
  <div id="modalImportHistori" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" role="dialog" aria-modal="true">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 p-5 sm:p-6">
      <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 mb-4">
        <h3 class="text-sm sm:text-base font-bold text-slate-800 flex items-center gap-2">
          <span class="material-symbols-outlined text-[#00A9C1]">upload_file</span>
          Impor File CSV Histori Birdstrike
        </h3>
        <button type="button" onclick="closeModal('modalImportHistori')" class="text-slate-400 hover:text-slate-600 cursor-pointer">
          <span class="material-symbols-outlined text-xl">close</span>
        </button>
      </div>

      <form method="POST" action="{{ route('admin.ai.histori.import') }}" enctype="multipart/form-data" class="space-y-4 text-xs">
        @csrf
        <p class="text-slate-500 leading-relaxed">
          Unggah berkas CSV berisi catatan histori insiden birdstrike lampau. Sistem akan memvalidasi kolom secara otomatis dan menyimpan baris valid dalam satu transaksi.
        </p>

        <div class="border-2 border-dashed border-slate-200 rounded-xl p-4 text-center hover:border-[#00A9C1] transition-colors">
          <span class="material-symbols-outlined text-3xl text-slate-400 mb-1">file_upload</span>
          <input type="file" name="csv_file" accept=".csv,text/csv,text/plain" required class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-cyan-50 file:text-[#007fa3] hover:file:bg-cyan-100 cursor-pointer">
          <div class="text-[10px] text-slate-400 mt-2">Maksimal 5 MB (.csv)</div>
        </div>

        <div class="flex items-center justify-between pt-3 border-t border-slate-100">
          <a href="{{ route('admin.ai.histori.template') }}" class="text-[11px] text-[#007fa3] hover:underline flex items-center gap-1">
            <span class="material-symbols-outlined text-xs">download</span> Unduh contoh CSV
          </a>
          <div class="flex items-center gap-2">
            <button type="button" onclick="closeModal('modalImportHistori')" class="px-3 py-1.5 rounded-lg text-slate-600 hover:bg-slate-100 font-semibold cursor-pointer">Batal</button>
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

    function openCreateModal() {
      document.getElementById('modalHistoriTitle').innerHTML = '<span class="material-symbols-outlined text-[#00A9C1]">add_circle</span> Tambah Insiden Birdstrike';
      const form = document.getElementById('formHistori');
      form.action = "{{ route('admin.ai.histori.store') }}";
      document.getElementById('historiMethod').value = 'POST';
      form.reset();
      document.getElementById('h_tanggal').value = "{{ date('Y-m-d') }}";
      openModal('modalHistoriForm');
    }

    function openEditModal(item) {
      document.getElementById('modalHistoriTitle').innerHTML = '<span class="material-symbols-outlined text-[#00A9C1]">edit</span> Edit Data Insiden Birdstrike';
      const form = document.getElementById('formHistori');
      form.action = `/admin/ai/histori/${item.id}`;
      document.getElementById('historiMethod').value = 'PUT';

      document.getElementById('h_tanggal').value = item.tanggal_kejadian ? item.tanggal_kejadian.substring(0, 10) : '';
      document.getElementById('h_jam').value = item.jam_kejadian ? item.jam_kejadian.substring(0, 5) : '';
      document.getElementById('h_jenis').value = item.jenis_kejadian || 'strike';
      document.getElementById('h_grid').value = item.grid || '';
      document.getElementById('h_ketinggian').value = item.ketinggian_ft ?? '';
      document.getElementById('h_centerline').value = item.jarak_centerline_m ?? '';
      document.getElementById('h_runway').value = item.kode_runway || '10';
      document.getElementById('h_titik').value = item.titik_lokasi || 'touchdown';
      document.getElementById('h_pesawat').value = item.jenis_pesawat || '';
      document.getElementById('h_fase').value = item.fase_penerbangan || 'take_off';
      document.getElementById('h_bagian').value = item.bagian_pesawat || '';
      document.getElementById('h_spesies').value = item.spesies_satwa || '';
      document.getElementById('h_jumlah').value = item.jumlah_satwa || 1;
      document.getElementById('h_kerusakan').value = item.tingkat_kerusakan || 'nihil';
      document.getElementById('h_keterangan').value = item.keterangan || '';

      openModal('modalHistoriForm');
    }
  </script>
</body>
</html>
