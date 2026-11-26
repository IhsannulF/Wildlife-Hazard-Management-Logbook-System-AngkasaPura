<!DOCTYPE html>
<html lang="id" class="h-full bg-canvas-light text-on-surface">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Log Pengusiran &amp; Master Alat - InJourney Airports</title>

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
      min-width: 960px;
    }
  </style>
</head>
<body class="min-h-full bg-canvas-light text-on-surface font-sans antialiased selection:bg-injourney-teal selection:text-white flex flex-col">

  <!-- SIDEBAR NAVIGATION -->
  @include('partials.navbar', ['activePage' => 'ai_pengusiran'])

  <!-- SUB-HEADER & COMMAND STRIP -->
  @include('partials.navbar-ai', [
    'title' => 'Log Pengusiran & Master Alat',
    'subtitle' => 'Seluruh tindakan pengusiran satwa per waktu dan grid, sebagai variabel pengusiran pada prediksi bird strike.',
    'icon' => 'campaign',
    'actions' => '
      <button type="button" id="btnTambahAlat" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-lg bg-gradient-to-r from-[#00A9C1] to-[#007fa3] hover:from-[#007fa3] hover:to-[#00607d] text-white shadow-xs transition-all active:scale-[0.98] shrink-0 whitespace-nowrap cursor-pointer">
        <span class="material-symbols-outlined text-base">add_circle</span>
        <span>Tambah Alat</span>
      </button>
    '
  ])

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
      <span class="text-slate-800 font-semibold">Log Pengusiran</span>
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

    @if($errors->any())
      <div class="bg-rose-50 text-rose-800 border border-rose-200 rounded-xl p-3.5 text-xs font-medium shadow-2xs">
        <ul class="list-disc list-inside space-y-0.5">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <!-- 2. SUMMARY KPI CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
      <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-cyan-50 text-[#007fa3] border border-cyan-100 flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-2xl">campaign</span>
        </div>
        <div class="min-w-0">
          <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pengusiran (sesuai filter)</div>
          <div class="text-xl font-extrabold text-slate-800 mt-0.5">{{ number_format($stats['total'], 0, ',', '.') }} <span class="text-xs font-medium text-slate-400">tindakan</span></div>
        </div>
      </div>
      <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-2xl">calendar_month</span>
        </div>
        <div class="min-w-0">
          <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">30 Hari Terakhir</div>
          <div class="text-xl font-extrabold text-slate-800 mt-0.5">{{ number_format($stats['tiga_puluh_hari'], 0, ',', '.') }} <span class="text-xs font-medium text-slate-400">tindakan</span></div>
        </div>
      </div>
      <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-700 border border-amber-100 flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-2xl">construction</span>
        </div>
        <div class="min-w-0">
          <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Alat Paling Sering</div>
          <div class="text-sm font-extrabold text-slate-800 mt-0.5 truncate">{{ $stats['alat_terbanyak']?->alat?->nama_alat ?? '–' }}</div>
          @if($stats['alat_terbanyak'])
            <div class="text-[11px] text-slate-400">{{ $stats['alat_terbanyak']->total }} kali</div>
          @endif
        </div>
      </div>
      <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-700 border border-rose-100 flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-2xl">grid_on</span>
        </div>
        <div class="min-w-0">
          <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Grid Paling Sering</div>
          <div class="text-sm font-extrabold text-slate-800 mt-0.5">{{ $stats['grid_terbanyak']?->grid ?? '–' }}</div>
          @if($stats['grid_terbanyak'])
            <div class="text-[11px] text-slate-400">{{ $stats['grid_terbanyak']->total }} kali</div>
          @endif
        </div>
      </div>
    </div>

    <!-- 3. TABLE: LOG PENGUSIRAN -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
      <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
        <div class="flex items-center gap-2.5">
          <div class="w-9 h-9 rounded-xl bg-cyan-50 text-[#00A9C1] flex items-center justify-center border border-cyan-100 shrink-0">
            <span class="material-symbols-outlined text-xl">list_alt</span>
          </div>
          <div>
            <h2 class="text-sm sm:text-base font-bold text-slate-800">Log Tindakan Pengusiran</h2>
            <p class="text-[11px] text-slate-500">Dari form laporan inspeksi dan tombol "Catat Pengusiran" petugas.</p>
          </div>
        </div>

        <form method="GET" action="{{ route('admin.ai.pengusiran.index') }}" class="flex flex-wrap items-center gap-2">
          <input type="date" name="dari" value="{{ $filters['dari'] ?? '' }}" aria-label="Dari tanggal" class="px-2.5 py-1.5 text-xs rounded-lg border border-slate-200/90 bg-slate-50/80 text-slate-700 focus:outline-none focus:border-[#00A9C1]">
          <span class="text-xs text-slate-400">s.d.</span>
          <input type="date" name="sampai" value="{{ $filters['sampai'] ?? '' }}" aria-label="Sampai tanggal" class="px-2.5 py-1.5 text-xs rounded-lg border border-slate-200/90 bg-slate-50/80 text-slate-700 focus:outline-none focus:border-[#00A9C1]">
          <select name="alat_id" aria-label="Filter alat" class="px-2.5 py-1.5 text-xs font-medium rounded-lg border border-slate-200/90 bg-slate-50/80 text-slate-700 focus:outline-none focus:border-[#00A9C1]">
            <option value="">Semua Alat</option>
            @foreach($alatList as $alat)
              <option value="{{ $alat->id }}" @selected((string) ($filters['alat_id'] ?? '') === (string) $alat->id)>{{ $alat->nama_alat }}</option>
            @endforeach
          </select>
          <select name="hasil" aria-label="Filter hasil" class="px-2.5 py-1.5 text-xs font-medium rounded-lg border border-slate-200/90 bg-slate-50/80 text-slate-700 focus:outline-none focus:border-[#00A9C1]">
            <option value="">Semua Hasil</option>
            @foreach(\App\Models\TindakanPengusiran::HASIL as $value => $label)
              <option value="{{ $value }}" @selected(($filters['hasil'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
          </select>
          <input type="text" name="grid" value="{{ $filters['grid'] ?? '' }}" placeholder="Grid, mis. K-10" aria-label="Filter grid" class="w-28 px-2.5 py-1.5 text-xs rounded-lg border border-slate-200/90 bg-slate-50/80 text-slate-700 uppercase focus:outline-none focus:border-[#00A9C1]">
          <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold transition-colors cursor-pointer shadow-2xs">Filter</button>
          @if(request()->anyFilled(['dari', 'sampai', 'alat_id', 'hasil', 'grid']))
            <a href="{{ route('admin.ai.pengusiran.index') }}" class="px-2.5 py-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 hover:underline">Reset</a>
          @endif
        </form>
      </div>

      <div class="logbook-table-wrapper">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-slate-50/80 text-slate-500 text-[11px] uppercase font-bold tracking-wider border-b border-slate-200/80">
              <th class="py-3 px-4">Waktu</th>
              <th class="py-3 px-4">Grid</th>
              <th class="py-3 px-4">Alat / Metode</th>
              <th class="py-3 px-4 text-right">Jumlah</th>
              <th class="py-3 px-4">Hasil</th>
              <th class="py-3 px-4">Petugas</th>
              <th class="py-3 px-4">Sumber</th>
              <th class="py-3 px-4">Keterangan</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse($tindakanList as $tindakan)
              @php
                $badgeHasil = match($tindakan->hasil) {
                  'berhasil' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                  'sebagian' => 'bg-amber-50 text-amber-800 border-amber-200',
                  'tidak_berhasil' => 'bg-rose-50 text-rose-800 border-rose-200',
                  default => 'bg-slate-50 text-slate-500 border-slate-200',
                };
              @endphp
              <tr class="hover:bg-slate-50/70 transition-colors">
                <td class="py-3 px-4">
                  <div class="font-bold text-slate-800">{{ $tindakan->waktu->translatedFormat('d M Y') }}</div>
                  <div class="text-[11px] text-slate-400 font-mono">{{ $tindakan->waktu->format('H:i') }} WIB</div>
                </td>
                <td class="py-3 px-4 font-mono font-bold text-slate-700">{{ $tindakan->grid }}</td>
                <td class="py-3 px-4">
                  <div class="font-semibold text-slate-800">{{ $tindakan->alat?->nama_alat ?? '–' }}</div>
                  <div class="text-[10px] text-slate-400">{{ $tindakan->alat?->kategori }}</div>
                </td>
                <td class="py-3 px-4 text-right font-mono">{{ $tindakan->jumlah_digunakan }}</td>
                <td class="py-3 px-4">
                  <span class="inline-block px-1.5 py-0.5 rounded border text-[10px] font-bold {{ $badgeHasil }}">
                    {{ $tindakan->hasil ? \App\Models\TindakanPengusiran::HASIL[$tindakan->hasil] : 'Tidak dicatat' }}
                  </span>
                </td>
                <td class="py-3 px-4 text-slate-700">{{ $tindakan->user?->namalengkap ?: ($tindakan->user?->username ?? '–') }}</td>
                <td class="py-3 px-4">
                  @if($tindakan->laporan)
                    <span class="font-mono text-[10px] text-slate-600">{{ $tindakan->laporan->no_laporan }}</span>
                  @else
                    <span class="text-[10px] text-slate-400">Catat cepat</span>
                  @endif
                </td>
                <td class="py-3 px-4 text-slate-600 max-w-[260px]">{{ $tindakan->keterangan ?: '–' }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="py-10 px-4 text-center text-slate-400">
                  <span class="material-symbols-outlined text-3xl mb-1">campaign</span>
                  <p class="font-medium text-slate-500">Belum ada tindakan pengusiran yang sesuai filter.</p>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if($tindakanList->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
          {{ $tindakanList->links() }}
        </div>
      @endif
    </div>

    <!-- 4. MASTER ALAT DISPERSAL -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
      <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-cyan-50 text-[#00A9C1] flex items-center justify-center border border-cyan-100 shrink-0">
          <span class="material-symbols-outlined text-xl">inventory_2</span>
        </div>
        <div>
          <h2 class="text-sm sm:text-base font-bold text-slate-800">Master Alat Dispersal</h2>
          <p class="text-[11px] text-slate-500">Hanya alat aktif yang muncul di form petugas. Alat yang sudah tercatat di log tidak bisa dihapus, cukup dinonaktifkan.</p>
        </div>
      </div>

      <div class="logbook-table-wrapper">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-slate-50/80 text-slate-500 text-[11px] uppercase font-bold tracking-wider border-b border-slate-200/80">
              <th class="py-3 px-4">Nama Alat</th>
              <th class="py-3 px-4">Kategori</th>
              <th class="py-3 px-4">Satuan</th>
              <th class="py-3 px-4 text-right">Stok</th>
              <th class="py-3 px-4 text-right">Dipakai</th>
              <th class="py-3 px-4">Status</th>
              <th class="py-3 px-4 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @foreach($alatList as $alat)
              <tr class="hover:bg-slate-50/70 transition-colors {{ $alat->aktif ? '' : 'opacity-60' }}">
                <td class="py-3 px-4 font-semibold text-slate-800">{{ $alat->nama_alat }}</td>
                <td class="py-3 px-4 text-slate-600">{{ $alat->kategori }}</td>
                <td class="py-3 px-4 text-slate-600">{{ $alat->satuan }}</td>
                <td class="py-3 px-4 text-right font-mono">{{ number_format($alat->stok_tersedia, 0, ',', '.') }}</td>
                <td class="py-3 px-4 text-right font-mono">{{ $alat->tindakan_pengusiran_count }}×</td>
                <td class="py-3 px-4">
                  <span class="inline-block px-1.5 py-0.5 rounded border text-[10px] font-bold {{ $alat->aktif ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-200' }}">
                    {{ $alat->aktif ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
                <td class="py-3 px-4">
                  <div class="flex items-center justify-center gap-1">
                    <button type="button"
                            class="js-edit-alat p-1.5 rounded-lg text-slate-500 hover:text-[#007fa3] hover:bg-cyan-50 cursor-pointer"
                            data-id="{{ $alat->id }}"
                            data-nama="{{ $alat->nama_alat }}"
                            data-kategori="{{ $alat->kategori }}"
                            data-satuan="{{ $alat->satuan }}"
                            data-stok="{{ $alat->stok_tersedia }}"
                            title="Ubah alat" aria-label="Ubah {{ $alat->nama_alat }}">
                      <span class="material-symbols-outlined text-base">edit</span>
                    </button>
                    <form method="POST" action="{{ route('admin.ai.pengusiran.alat.toggle', $alat->id) }}" class="inline">
                      @csrf
                      <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-amber-700 hover:bg-amber-50 cursor-pointer" title="{{ $alat->aktif ? 'Nonaktifkan' : 'Aktifkan' }}" aria-label="{{ $alat->aktif ? 'Nonaktifkan' : 'Aktifkan' }} {{ $alat->nama_alat }}">
                        <span class="material-symbols-outlined text-base">{{ $alat->aktif ? 'toggle_on' : 'toggle_off' }}</span>
                      </button>
                    </form>
                    @if($alat->tindakan_pengusiran_count === 0)
                      <form method="POST" action="{{ route('admin.ai.pengusiran.alat.destroy', $alat->id) }}" class="inline js-confirm-delete" data-nama="{{ $alat->nama_alat }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-700 hover:bg-rose-50 cursor-pointer" title="Hapus alat" aria-label="Hapus {{ $alat->nama_alat }}">
                          <span class="material-symbols-outlined text-base">delete</span>
                        </button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

  </main>

  <!-- ================= MODAL TAMBAH / UBAH ALAT ================= -->
  <div id="modalAlat" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" role="dialog" aria-modal="true" aria-labelledby="modalAlatTitle">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 p-5 sm:p-6">
      <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 mb-4">
        <h3 id="modalAlatTitle" class="text-sm sm:text-base font-bold text-slate-800">Tambah Alat Dispersal</h3>
        <button type="button" class="js-close-alat text-slate-400 hover:text-slate-600 cursor-pointer" aria-label="Tutup">
          <span class="material-symbols-outlined text-xl">close</span>
        </button>
      </div>

      <form id="formAlat" method="POST" action="{{ route('admin.ai.pengusiran.alat.store') }}" data-store-action="{{ route('admin.ai.pengusiran.alat.store') }}" data-update-base="{{ url('admin/ai/pengusiran/alat') }}" class="space-y-3.5 text-xs">
        @csrf
        <input type="hidden" name="_method" id="alatMethod" value="POST">

        <div>
          <label for="alatNama" class="block font-semibold text-slate-700 mb-1">Nama Alat *</label>
          <input type="text" name="nama_alat" id="alatNama" maxlength="150" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-[#00A9C1]">
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label for="alatKategori" class="block font-semibold text-slate-700 mb-1">Kategori *</label>
            <select name="kategori" id="alatKategori" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-[#00A9C1]">
              @foreach(\App\Models\MasterAlatDispersal::KATEGORI as $kategori)
                <option value="{{ $kategori }}">{{ $kategori }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label for="alatSatuan" class="block font-semibold text-slate-700 mb-1">Satuan *</label>
            <input type="text" name="satuan" id="alatSatuan" maxlength="50" required placeholder="Butir, Kali, Menit" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-[#00A9C1]">
          </div>
        </div>
        <div>
          <label for="alatStok" class="block font-semibold text-slate-700 mb-1">Stok Tersedia</label>
          <input type="number" name="stok_tersedia" id="alatStok" min="0" value="0" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-[#00A9C1]">
        </div>

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
          <button type="button" class="js-close-alat px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold cursor-pointer">Batal</button>
          <button type="submit" class="px-4 py-2 rounded-xl bg-gradient-to-r from-[#00A9C1] to-[#007fa3] text-white font-bold shadow-xs cursor-pointer">Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    (() => {
      const modal = document.getElementById('modalAlat');
      const form = document.getElementById('formAlat');
      const field = (id) => document.getElementById(id);

      const show = (title) => {
        field('modalAlatTitle').textContent = title;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
      };
      const hide = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      };

      field('btnTambahAlat')?.addEventListener('click', () => {
        form.reset();
        form.action = form.dataset.storeAction;
        field('alatMethod').value = 'POST';
        show('Tambah Alat Dispersal');
      });

      document.querySelectorAll('.js-edit-alat').forEach((button) => {
        button.addEventListener('click', () => {
          form.action = form.dataset.updateBase + '/' + button.dataset.id;
          field('alatMethod').value = 'PUT';
          field('alatNama').value = button.dataset.nama;
          field('alatKategori').value = button.dataset.kategori;
          field('alatSatuan').value = button.dataset.satuan;
          field('alatStok').value = button.dataset.stok;
          show('Ubah Alat Dispersal');
        });
      });

      document.querySelectorAll('.js-close-alat').forEach((button) => button.addEventListener('click', hide));
      modal.addEventListener('click', (event) => {
        if (event.target === modal) {
          hide();
        }
      });

      document.querySelectorAll('.js-confirm-delete').forEach((deleteForm) => {
        deleteForm.addEventListener('submit', (event) => {
          if (!confirm('Hapus alat "' + deleteForm.dataset.nama + '"?')) {
            event.preventDefault();
          }
        });
      });
    })();
  </script>
</body>
</html>
