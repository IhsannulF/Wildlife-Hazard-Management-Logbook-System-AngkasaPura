@extends('layouts.app')

@section('title', 'Dashboard Riwayat Laporan — InJourney Airports')

@section('content')
<div class="min-h-screen bg-slate-50 text-slate-800 antialiased font-sans flex flex-col">

  <!-- Shared Unified Side Navigation Bar -->
  @include('partials.navbar', ['activePage' => 'dashboard'])

  <!-- TOP UNIFIED PAGE HEADER -->
  @include('partials.page-header', [
    'title' => 'Dashboard Logbook Pegawai',
    'subtitle' => 'Pusat kendali riwayat pelaporan satwa, tracking status verifikasi, dan arsip berita acara dinas.',
    'icon' => 'space_dashboard',
    'actions' => '
      <div class="flex items-center gap-2">
        <a href="' . route('user.dashboard') . '" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-white hover:bg-cyan-50/80 text-slate-600 hover:text-[#00A9C1] border border-slate-200/90 hover:border-cyan-200 shadow-2xs hover:shadow-xs flex items-center justify-center cursor-pointer transition-all shrink-0" title="Muat Ulang Halaman">
          <span class="material-symbols-outlined text-lg">sync</span>
        </a>
        <a href="' . route('laporan.create') . '" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-gradient-to-r from-[#00A9C1] to-[#007fa3] hover:from-[#007fa3] hover:to-[#005f7a] text-white font-semibold text-xs sm:text-sm shadow-xs hover:shadow-cyan-500/20 active:scale-[0.98] transition-all">
          <span class="material-symbols-outlined text-base">add_circle</span>
          <span>Buat Laporan Baru</span>
        </a>
      </div>
    '
  ])

  <!-- MAIN VIEWPORT CONTAINER -->
  <main class="max-w-[1680px] w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 flex-1">

    <!-- Flash Alert Notification -->
    @if(session('sukses'))
      <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2.5">
          <span class="material-symbols-outlined text-emerald-600 text-xl shrink-0">check_circle</span>
          <span class="text-xs sm:text-sm font-semibold">{{ session('sukses') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="p-1 text-emerald-600 hover:text-emerald-900 rounded-lg hover:bg-emerald-100 transition-colors cursor-pointer">
          <span class="material-symbols-outlined text-base">close</span>
        </button>
      </div>
    @endif

    @if(session('error'))
      <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2.5">
          <span class="material-symbols-outlined text-rose-600 text-xl shrink-0">error</span>
          <span class="text-xs sm:text-sm font-semibold">{{ session('error') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="p-1 text-rose-600 hover:text-rose-900 rounded-lg hover:bg-rose-100 transition-colors cursor-pointer">
          <span class="material-symbols-outlined text-base">close</span>
        </button>
      </div>
    @endif

    <!-- 1. HERO BENTO STAT METRIC CARDS (3 PERSONAL METRICS) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 lg:gap-5">
      <!-- Card 1: Total Laporan Saya -->
      <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-200/90 p-5 shadow-2xs hover:shadow-md transition-all duration-300 group">
        <div class="flex items-center justify-between gap-3">
          <div>
            <p class="text-[11px] font-bold tracking-wider uppercase text-slate-400 select-none">Total Laporan Saya</p>
            <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-800 mt-1 tracking-tight">{{ number_format($totalLaporanSaya) }}</h3>
          </div>
          <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold shadow-inner shrink-0 group-hover:scale-105 transition-transform">
            <span class="material-symbols-outlined text-2xl">inventory_2</span>
          </div>
        </div>
        <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
          <span>Seluruh berkas diajukan akun Anda</span>
          <span class="font-semibold text-slate-700">Akun Resmi</span>
        </div>
      </div>

      <!-- Card 2: Menunggu Validasi -->
      <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-200/90 p-5 shadow-2xs hover:shadow-md transition-all duration-300 group">
        <div class="flex items-center justify-between gap-3">
          <div>
            <p class="text-[11px] font-bold tracking-wider uppercase text-rose-500 select-none">Menunggu Verifikasi</p>
            <h3 class="text-2xl sm:text-3xl font-extrabold text-rose-600 mt-1 tracking-tight">{{ number_format($menungguValidasi) }}</h3>
          </div>
          <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center font-bold shadow-inner shrink-0 group-hover:scale-105 transition-transform">
            <span class="material-symbols-outlined text-2xl">pending_actions</span>
          </div>
        </div>
        <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
          <span>Antrean evaluasi Safety Manager</span>
          <span class="inline-flex items-center gap-1 font-semibold text-rose-600">
            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
            Proses Review
          </span>
        </div>
      </div>

      <!-- Card 3: Selesai / Ditangani -->
      <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-200/90 p-5 shadow-2xs hover:shadow-md transition-all duration-300 group">
        <div class="flex items-center justify-between gap-3">
          <div>
            <p class="text-[11px] font-bold tracking-wider uppercase text-emerald-600 select-none">Selesai / Terverifikasi</p>
            <h3 class="text-2xl sm:text-3xl font-extrabold text-emerald-700 mt-1 tracking-tight">{{ number_format($sudahDitangani) }}</h3>
          </div>
          <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center font-bold shadow-inner shrink-0 group-hover:scale-105 transition-transform">
            <span class="material-symbols-outlined text-2xl">verified</span>
          </div>
        </div>
        <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
          <span>Telah divalidasi dan ditindaklanjuti</span>
          <span class="font-semibold text-emerald-700">Tervalidasi</span>
        </div>
      </div>
    </div>

    <!-- 2. FILTER & SEARCH CONTROL BAR -->
    <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs space-y-3.5">
      <!-- Status Tabs Navigation -->
      <div class="flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-1.5 p-1 rounded-xl bg-slate-100/90 border border-slate-200/70 text-xs overflow-x-auto max-w-full">
          <!-- Tab All -->
          <a href="{{ route('user.dashboard', array_filter(['start_date' => $startDate, 'end_date' => $endDate, 'search' => $search, 'per_page' => $perPage])) }}" 
             class="px-3.5 py-1.5 rounded-lg font-semibold transition-all flex items-center gap-1.5 whitespace-nowrap {{ empty($statusFilter) ? 'bg-white text-[#007fa3] shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
            <span>Semua Laporan</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ empty($statusFilter) ? 'bg-cyan-100 text-[#007fa3] font-bold' : 'bg-slate-200 text-slate-600' }}">
              {{ $totalLaporanSaya }}
            </span>
          </a>

          <!-- Tab Menunggu -->
          <a href="{{ route('user.dashboard', array_filter(['status' => 'belum', 'start_date' => $startDate, 'end_date' => $endDate, 'search' => $search, 'per_page' => $perPage])) }}" 
             class="px-3.5 py-1.5 rounded-lg font-semibold transition-all flex items-center gap-1.5 whitespace-nowrap {{ $statusFilter === 'belum' ? 'bg-white text-rose-600 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
            <span>Menunggu Verifikasi</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $statusFilter === 'belum' ? 'bg-rose-100 text-rose-700 font-bold' : 'bg-slate-200 text-slate-600' }}">
              {{ $menungguValidasi }}
            </span>
          </a>

          <!-- Tab Selesai -->
          <a href="{{ route('user.dashboard', array_filter(['status' => 'sudah', 'start_date' => $startDate, 'end_date' => $endDate, 'search' => $search, 'per_page' => $perPage])) }}" 
             class="px-3.5 py-1.5 rounded-lg font-semibold transition-all flex items-center gap-1.5 whitespace-nowrap {{ $statusFilter === 'sudah' ? 'bg-white text-emerald-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
            <span>Selesai Ditangani</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $statusFilter === 'sudah' ? 'bg-emerald-100 text-emerald-800 font-bold' : 'bg-slate-200 text-slate-600' }}">
              {{ $sudahDitangani }}
            </span>
          </a>
        </div>

        <!-- Per Page Selector -->
        <div class="flex items-center gap-2 text-xs text-slate-500">
          <label for="perPageSelector" class="font-medium whitespace-nowrap">Tampilkan:</label>
          <select id="perPageSelector" 
                  onchange="applyPerPage(this.value)" 
                  class="h-8.5 px-2.5 py-1 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#00A9C1] cursor-pointer shadow-2xs">
            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10 baris</option>
            <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20 baris</option>
            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 baris</option>
            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 baris</option>
          </select>
        </div>
      </div>

      <!-- Search & Date Filter Inputs -->
      <form method="GET" action="{{ route('user.dashboard') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 pt-2 border-t border-slate-100">
        @if(!empty($statusFilter))
          <input type="hidden" name="status" value="{{ $statusFilter }}">
        @endif
        <input type="hidden" name="per_page" value="{{ $perPage }}">

        <!-- Search Input -->
        <div class="sm:col-span-5 relative">
          <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-lg pointer-events-none">search</span>
          <input type="text" 
                 name="search" 
                 value="{{ $search }}" 
                 placeholder="Cari No. Berita Acara, satwa, area, atau grid..." 
                 class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-slate-200/90 focus:outline-none focus:ring-2 focus:ring-[#00A9C1] text-slate-800 placeholder-slate-400 bg-slate-50/50">
        </div>

        <!-- Date Range: Tanggal Awal -->
        <div class="sm:col-span-3">
          <input type="date" 
                 name="start_date" 
                 value="{{ $startDate }}" 
                 class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200/90 focus:outline-none focus:ring-2 focus:ring-[#00A9C1] text-slate-700 bg-slate-50/50"
                 title="Tanggal Awal">
        </div>

        <!-- Date Range: Tanggal Akhir -->
        <div class="sm:col-span-3">
          <input type="date" 
                 name="end_date" 
                 value="{{ $endDate }}" 
                 class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200/90 focus:outline-none focus:ring-2 focus:ring-[#00A9C1] text-slate-700 bg-slate-50/50"
                 title="Tanggal Akhir">
        </div>

        <!-- Submit & Reset Action Buttons -->
        <div class="sm:col-span-1 flex items-center gap-1.5">
          <button type="submit" 
                  class="w-full h-9 flex items-center justify-center rounded-xl bg-[#00A9C1] hover:bg-[#007fa3] text-white font-semibold text-xs shadow-2xs transition-colors cursor-pointer" 
                  title="Terapkan Filter">
            <span class="material-symbols-outlined text-base">filter_alt</span>
          </button>
          @if(!empty($search) || !empty($startDate) || !empty($endDate) || !empty($statusFilter))
            <a href="{{ route('user.dashboard') }}" 
               class="h-9 px-2 flex items-center justify-center rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs transition-colors cursor-pointer shrink-0" 
               title="Reset Semua Filter">
              <span class="material-symbols-outlined text-base">filter_alt_off</span>
            </a>
          @endif
        </div>
      </form>
    </div>

    <!-- 3. TABEL RIWAYAT LAPORAN PEGAWAI -->
    <div class="bg-white rounded-2xl border border-slate-200/90 overflow-hidden shadow-2xs">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-700 border-collapse">
          <thead>
            <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider select-none">
              <th scope="col" class="py-3.5 px-4 w-12 text-center">No</th>
              <th scope="col" class="py-3.5 px-4">No. Berita Acara</th>
              <th scope="col" class="py-3.5 px-4">Waktu & Tanggal</th>
              <th scope="col" class="py-3.5 px-4">Area & Grid</th>
              <th scope="col" class="py-3.5 px-4">Temuan Satwa</th>
              <th scope="col" class="py-3.5 px-4 text-center">Tingkat Risiko</th>
              <th scope="col" class="py-3.5 px-4 text-center">Status Verifikasi</th>
              <th scope="col" class="py-3.5 px-4 text-center w-36">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 font-medium">
            @forelse($laporans as $index => $lap)
              @php
                $noReg = $lap->no_laporan ?: \App\Models\Laporan::generateNoLaporan($lap->unit_kerja ?? 'AMC', $lap->tanggal?->format('Y-m-d'), $lap->id);
                $isResolved = ($lap->status === 'sudah');
                $totalSatwaEkor = $lap->detailSatwa->sum('jumlah');
                $spesiesList = $lap->detailSatwa->pluck('nama_satwa')->unique()->filter()->values();
                $firstSatwa = $lap->detailSatwa->first();
                $firstFoto = $firstSatwa?->fotoLaporan?->first() ?? $lap->fotoLaporan->first();
                $riskLevel = strtolower($lap->risk_level ?? 'rendah');
              @endphp
              <tr onclick="openDetailModal({{ $lap->id }})" class="hover:bg-cyan-50/40 transition-colors cursor-pointer group" title="Klik baris untuk membuka rincian Berita Acara #{{ $noReg }}">
                <!-- No Urut -->
                <td class="py-3.5 px-4 text-center text-slate-400 font-mono text-[11px]">
                  {{ $laporans->firstItem() + $index }}
                </td>

                <!-- No Berita Acara -->
                <td class="py-3.5 px-4">
                  <div class="font-mono font-bold text-slate-900 group-hover:text-[#007fa3] text-xs transition-colors flex items-center gap-1">
                    <span>{{ $noReg }}</span>
                    <span class="material-symbols-outlined text-[13px] opacity-0 group-hover:opacity-100 text-[#00A9C1] transition-opacity">open_in_new</span>
                  </div>
                  <div class="text-[10px] text-slate-500 font-normal mt-0.5 flex items-center gap-1">
                    <span class="material-symbols-outlined text-xs text-slate-400">badge</span>
                    <span>{{ $lap->unit_kerja }}</span>
                  </div>
                </td>

                <!-- Waktu & Tanggal -->
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <div class="font-semibold text-slate-800">{{ $lap->tanggal->translatedFormat('d M Y') }}</div>
                  <div class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                    <span class="material-symbols-outlined text-xs text-slate-400">schedule</span>
                    <span>{{ $lap->jam ? substr($lap->jam, 0, 5) . ' WIB' : ($lap->created_at ? $lap->created_at->format('H:i') . ' WIB' : '-') }}</span>
                  </div>
                </td>

                <!-- Area & Grid -->
                <td class="py-3.5 px-4">
                  <div class="font-semibold text-slate-800">{{ $lap->area_inspeksi }}</div>
                  <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200">
                      Grid: {{ $lap->grid_lokasi ?: '-' }}
                    </span>
                    @if($lap->kondisi_cuaca)
                      <span class="text-[10px] text-slate-500">&bull; {{ $lap->kondisi_cuaca }}</span>
                    @endif
                  </div>
                </td>

                <!-- Temuan Satwa -->
                <td class="py-3.5 px-4">
                  <div class="flex items-center gap-2.5">
                    @if($firstFoto && !empty($firstFoto->nama_file))
                      <img src="{{ $firstFoto->url }}" 
                           alt="Foto Bukti" 
                           class="w-10 h-10 rounded-lg object-cover border border-slate-200 shadow-2xs shrink-0 bg-slate-100"
                           onerror="this.onerror=null; this.src='{{ asset('images/logo_login.png') }}';">
                    @else
                      <div class="w-10 h-10 rounded-lg bg-cyan-50 border border-cyan-100 flex items-center justify-center text-[#00A9C1] shrink-0">
                        <span class="material-symbols-outlined text-xl">pets</span>
                      </div>
                    @endif
                    <div class="min-w-0">
                      <div class="font-bold text-slate-800 truncate max-w-[180px]">
                        {{ $spesiesList->first() ?: 'Satwa Liar Lapangan' }}
                        @if($spesiesList->count() > 1)
                          <span class="text-[10px] font-normal text-slate-400">(+{{ $spesiesList->count() - 1 }})</span>
                        @endif
                      </div>
                      <div class="text-[11px] text-slate-500 mt-0.5">
                        <strong class="text-slate-700 font-semibold">{{ $totalSatwaEkor ?: 1 }}</strong> ekor terpantau
                      </div>
                    </div>
                  </div>
                </td>

                <!-- Tingkat Risiko ICAO -->
                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                  @if($riskLevel === 'kritis')
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-rose-600 text-white shadow-xs ring-2 ring-rose-300 animate-pulse">
                      <span class="material-symbols-outlined text-xs">warning</span>
                      Kritis ({{ $lap->risk_score ?? 12 }})
                    </span>
                  @elseif($riskLevel === 'tinggi')
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-600 text-white shadow-xs ring-1 ring-amber-300">
                      <span class="material-symbols-outlined text-xs">priority_high</span>
                      Tinggi ({{ $lap->risk_score ?? 8 }})
                    </span>
                  @elseif($riskLevel === 'sedang')
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300">
                      Sedang ({{ $lap->risk_score ?? 4 }})
                    </span>
                  @else
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                      Rendah ({{ $lap->risk_score ?? 1 }})
                    </span>
                  @endif
                </td>

                <!-- Status Verifikasi -->
                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                  @if($isResolved)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs">
                      <span class="material-symbols-outlined text-xs">verified</span>
                      <span>Terverifikasi</span>
                    </span>
                  @else
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 shadow-2xs">
                      <span class="material-symbols-outlined text-xs">schedule</span>
                      <span>Menunggu</span>
                    </span>
                  @endif
                </td>

                <!-- Aksi Tombol: Pratinjau BA & Unduh PDF Resmi -->
                <td class="py-3.5 px-4 text-center whitespace-nowrap" onclick="event.stopPropagation()">
                  <div class="inline-flex items-center justify-center gap-1.5">
                    <a href="{{ route('laporan.cetak', $lap->id) }}" 
                       target="_blank" 
                       class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-white hover:bg-cyan-50 text-slate-600 hover:text-[#007fa3] border border-slate-200/90 hover:border-cyan-200 text-xs font-semibold transition-all shadow-2xs hover:shadow-xs cursor-pointer" 
                       title="Pratinjau Cetak Berita Acara">
                      <span class="material-symbols-outlined text-sm text-[#00A9C1]">print</span>
                      <span>Cetak BA</span>
                    </a>
                    <a href="{{ route('laporan.pdf', $lap->id) }}" 
                       class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs font-semibold transition-all shadow-2xs cursor-pointer" 
                       title="Unduh PDF Resmi">
                      <span class="material-symbols-outlined text-sm">picture_as_pdf</span>
                      <span>PDF</span>
                    </a>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="py-12 px-4 text-center">
                  <div class="max-w-sm mx-auto text-center space-y-3">
                    <div class="w-14 h-14 rounded-2xl bg-cyan-50 text-[#00A9C1] border border-cyan-100 flex items-center justify-center mx-auto shadow-inner">
                      <span class="material-symbols-outlined text-3xl">fact_check</span>
                    </div>
                    <div class="space-y-1">
                      <h4 class="text-sm font-bold text-slate-800">Belum Ada Riwayat Laporan</h4>
                      <p class="text-xs text-slate-500">
                        @if(!empty($search) || !empty($startDate) || !empty($endDate) || !empty($statusFilter))
                          Tidak ditemukan laporan yang cocok dengan kriteria filter saat ini.
                        @else
                          Anda belum pernah mengajukan laporan temuan satwa liar sisi udara.
                        @endif
                      </p>
                    </div>
                    <div class="pt-1">
                      @if(!empty($search) || !empty($startDate) || !empty($endDate) || !empty($statusFilter))
                        <a href="{{ route('user.dashboard') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors">
                          <span class="material-symbols-outlined text-sm">filter_alt_off</span>
                          <span>Bersihkan Filter</span>
                        </a>
                      @else
                        <a href="{{ route('laporan.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#00A9C1] hover:bg-[#007fa3] text-white font-semibold text-xs shadow-xs transition-all">
                          <span class="material-symbols-outlined text-sm">add</span>
                          <span>Buat Laporan Baru Sekarang</span>
                        </a>
                      @endif
                    </div>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Pagination Footer -->
      @if($laporans->hasPages())
        <div class="p-4 border-t border-slate-100 flex items-center justify-between flex-wrap gap-3 text-xs text-slate-500">
          <div>
            Menampilkan <strong class="text-slate-800 font-semibold">{{ $laporans->firstItem() }} - {{ $laporans->lastItem() }}</strong> dari <strong class="text-slate-800 font-semibold">{{ $laporans->total() }}</strong> berkas
          </div>
          <div>
            {{ $laporans->links() }}
          </div>
        </div>
      @endif
    </div>

  </main>
</div>

<!-- ================= MODAL PRATINJAU DETAIL BERITA ACARA & TANGGAPAN ================= -->
<div id="detailModal" 
     class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs overflow-y-auto p-4 sm:p-6 lg:p-8 flex items-center justify-center transition-all duration-300 opacity-0"
     role="dialog" 
     aria-modal="true" 
     aria-labelledby="modalTitle">
  
  <div class="relative w-full max-w-4xl bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all duration-300 scale-95 flex flex-col max-h-[90vh]">
    
    <!-- Modal Header -->
    <div class="px-6 py-4.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/70 shrink-0">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-cyan-50 border border-cyan-100 text-[#00A9C1] flex items-center justify-center shrink-0">
          <span class="material-symbols-outlined text-xl">description</span>
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h3 id="modalTitle" class="text-sm sm:text-base font-extrabold text-slate-800 tracking-tight font-mono">
              Loading...
            </h3>
            <span id="modalStatusBadge" class="px-2 py-0.5 rounded-full text-[10px] font-bold"></span>
          </div>
          <p class="text-[11px] text-slate-500 font-normal">Lembar Pratinjau Berita Acara & Catatan Tindak Lanjut Dinas</p>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <a id="modalDownloadPdfBtn" href="#" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-[11px] font-bold transition-colors" title="Unduh PDF Resmi">
          <span class="material-symbols-outlined text-sm">picture_as_pdf</span>
          <span>Unduh PDF</span>
        </a>
        <a id="modalPrintBtn" href="#" target="_blank" class="p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-slate-200 transition-colors" title="Cetak Berita Acara">
          <span class="material-symbols-outlined text-lg">print</span>
        </a>
        <button type="button" onclick="closeDetailModal()" class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-200 transition-colors cursor-pointer" aria-label="Tutup Modal">
          <span class="material-symbols-outlined text-xl">close</span>
        </button>
      </div>
    </div>

    <!-- Modal Scrollable Body -->
    <div class="p-6 overflow-y-auto space-y-6 flex-1 text-xs">
      <!-- Loading State Spinner -->
      <div id="modalLoading" class="py-16 text-center space-y-3">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-cyan-500 border-t-transparent"></div>
        <p class="text-xs text-slate-500 font-medium">Memuat rincian berita acara...</p>
      </div>

      <!-- Modal Content Body -->
      <div id="modalBody" class="hidden space-y-6">
        
        <!-- 1. Metadata Ringkasan Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50/80 p-4 rounded-2xl border border-slate-200/80">
          <div>
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Petugas Pelapor</span>
            <div id="mPetugas" class="font-bold text-slate-800 text-xs mt-0.5">-</div>
          </div>
          <div>
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Unit Kerja</span>
            <div id="mUnit" class="font-bold text-slate-800 text-xs mt-0.5">-</div>
          </div>
          <div>
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Waktu / Tanggal</span>
            <div id="mWaktu" class="font-bold text-slate-800 text-xs mt-0.5">-</div>
          </div>
          <div>
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Cuaca / Kondisi</span>
            <div id="mCuaca" class="font-bold text-slate-800 text-xs mt-0.5">-</div>
          </div>
        </div>

        <!-- 2. Area, Grid & Lokasi GPS + Risiko ICAO -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
          <div class="p-3.5 rounded-2xl border border-slate-200/90 bg-white">
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Area Inspeksi</span>
            <div id="mArea" class="font-bold text-slate-800 text-sm mt-1">-</div>
          </div>
          <div class="p-3.5 rounded-2xl border border-slate-200/90 bg-white">
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Grid Lokasi Primer</span>
            <div id="mGrid" class="font-mono font-extrabold text-[#007fa3] text-sm mt-1">-</div>
          </div>
          <div class="p-3.5 rounded-2xl border border-slate-200/90 bg-white">
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Koordinat GPS</span>
            <div id="mGps" class="font-mono font-semibold text-slate-700 text-xs mt-1">-</div>
          </div>
          <div class="p-3.5 rounded-2xl border border-slate-200/90 bg-white">
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Matriks Risiko ICAO</span>
            <div id="mRiskScoreBadge" class="mt-1 font-bold text-xs">
              -
            </div>
          </div>
        </div>

        <!-- 3. Rincian Satwa Yang Ditemukan -->
        <div class="space-y-2">
          <h4 class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
            <span class="material-symbols-outlined text-base text-[#00A9C1]">pets</span>
            <span>Rincian Spesies Satwa Liar</span>
          </h4>
          <div id="mSatwaContainer" class="divide-y divide-slate-100 rounded-2xl border border-slate-200/90 overflow-hidden bg-white">
            <!-- Dynamic Injection -->
          </div>
        </div>

        <!-- 4. Kondisi, Morfologi & Tindak Lanjut -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="space-y-3 bg-slate-50/60 p-4 rounded-2xl border border-slate-200/80">
            <div>
              <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Kondisi Satwa</span>
              <p id="mKondisiApron" class="font-medium text-slate-700 mt-0.5">-</p>
            </div>
            <div>
              <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Ciri & Ukuran Morfologi</span>
              <p id="mCiri" class="font-medium text-slate-700 mt-0.5">-</p>
            </div>
            <div>
              <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Aktivitas Saat Ditemukan</span>
              <p id="mAktivitas" class="font-medium text-slate-700 mt-0.5">-</p>
            </div>
          </div>

          <div class="space-y-3 bg-slate-50/60 p-4 rounded-2xl border border-slate-200/80">
            <div>
              <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Tindak Lanjut Lapangan</span>
              <p id="mTindakLanjut" class="font-semibold text-slate-800 mt-0.5">-</p>
            </div>
            <div>
              <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Metode Pengusiran</span>
              <p id="mPengusiran" class="font-medium text-slate-700 mt-0.5">-</p>
            </div>
            <div>
              <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Tanda Tangan Digital Pelapor</span>
              <div class="mt-1">
                <img id="mTandaTangan" src="" alt="Tanda Tangan" class="h-14 w-auto object-contain border border-slate-200 rounded-lg p-1 bg-white">
              </div>
            </div>
          </div>
        </div>

        <!-- 5. Foto Dokumentasi Tambahan (Jika Ada) -->
        <div id="mFotoExtraSection" class="space-y-2 hidden">
          <h4 class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
            <span class="material-symbols-outlined text-base text-[#00A9C1]">photo_library</span>
            <span>Dokumentasi Tambahan Lapangan</span>
          </h4>
          <div id="mFotoExtraGallery" class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
            <!-- Dynamic Injection -->
          </div>
        </div>

        <!-- 6. THREAD TANGGAPAN & INSTRUKSI RESMI SAFETY MANAGER -->
        <div class="space-y-3 pt-3 border-t border-slate-200">
          <div class="flex items-center justify-between">
            <h4 class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
              <span class="material-symbols-outlined text-base text-[#007fa3]">forum</span>
              <span>Instruksi & Tanggapan Safety Unit</span>
            </h4>
            <span class="text-[10px] text-slate-400">Komunikasi Resmi Safety Management</span>
          </div>

          <div id="mTanggapanContainer" class="space-y-2.5">
            <!-- Dynamic Injection -->
          </div>

          <!-- FORM BALAS TANGGAPAN DINAS (PEGAWAI) -->
          <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/90 space-y-3 mt-3" id="mReplySection">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base text-[#00A9C1]">reply</span>
                <span>Balas Tanggapan / Konfirmasi Tindakan Lapangan</span>
              </span>
              <span class="text-[10px] text-slate-500">Respon Resmi Pelapor Lapangan</span>
            </div>
            <form id="userReplyForm" onsubmit="submitUserReply(event)" class="space-y-2.5">
              <textarea id="userReplyIsi" rows="3" required placeholder="Tuliskan konfirmasi tindakan pengusiran lanjutan, amunisi tambahan yang digunakan, atau penjelasan klarifikasi..." class="w-full text-xs rounded-xl border border-slate-200 bg-white text-slate-800 p-2.5 focus:border-[#00A9C1] focus:ring-1 focus:ring-[#00A9C1] placeholder:text-slate-400 leading-relaxed"></textarea>
              <div class="flex items-center justify-between pt-1">
                <span class="text-[10px] text-slate-400 italic">Balasan akan otomatis tersimpan dalam Berita Acara resmi.</span>
                <button type="submit" id="btnSubmitUserReply" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-[#00A9C1] to-[#007fa3] hover:from-[#007fa3] hover:to-[#005f7a] text-white font-semibold text-xs shadow-xs transition-all cursor-pointer">
                  <span class="material-symbols-outlined text-sm">send</span>
                  <span>Kirim Balasan Dinas</span>
                </button>
              </div>
            </form>
          </div>
        </div>

      </div>
    </div>

    <!-- Modal Footer -->
    <div class="px-6 py-3.5 border-t border-slate-100 bg-slate-50 flex items-center justify-between shrink-0">
      <span class="text-[11px] text-slate-400">Sistem Logbook Satwa Liar &bull; PT Angkasa Pura (Persero)</span>
      <button type="button" 
              onclick="closeDetailModal()" 
              class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-xs transition-colors cursor-pointer">
        Tutup
      </button>
    </div>

  </div>
</div>

@endsection

@section('scripts')
<script>
  let currentActiveLaporanId = null;

  function applyPerPage(val) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', val);
    url.searchParams.delete('page');
    window.location.href = url.toString();
  }

  function openDetailModal(id) {
    currentActiveLaporanId = id;
    const modal = document.getElementById('detailModal');
    const loading = document.getElementById('modalLoading');
    const body = document.getElementById('modalBody');
    const title = document.getElementById('modalTitle');
    const statusBadge = document.getElementById('modalStatusBadge');
    const printBtn = document.getElementById('modalPrintBtn');
    const pdfBtn = document.getElementById('modalDownloadPdfBtn');

    if (!modal) return;

    modal.classList.remove('hidden');
    setTimeout(() => {
      modal.classList.remove('opacity-0');
      modal.querySelector('div').classList.remove('scale-95');
      modal.querySelector('div').classList.add('scale-100');
    }, 10);

    loading.classList.remove('hidden');
    body.classList.add('hidden');
    title.textContent = 'Memuat Data...';
    statusBadge.textContent = '';
    statusBadge.className = 'hidden';

    if (printBtn) {
      printBtn.href = `/laporan/${id}/cetak`;
    }
    if (pdfBtn) {
      pdfBtn.href = `/laporan/${id}/pdf`;
    }

    fetch(`/laporan/${id}/detail`)
      .then(res => {
        if (!res.ok) throw new Error('Gagal mengambil data detail laporan.');
        return res.json();
      })
      .then(response => {
        if (!response.success || !response.data) {
          throw new Error('Format data tidak sesuai.');
        }
        populateModalData(response.data);
      })
      .catch(err => {
        loading.innerHTML = `
          <div class="text-rose-500 font-semibold py-8 space-y-2">
            <span class="material-symbols-outlined text-3xl">error</span>
            <p>${err.message}</p>
          </div>
        `;
      });
  }

  function populateModalData(data) {
    const loading = document.getElementById('modalLoading');
    const body = document.getElementById('modalBody');
    const title = document.getElementById('modalTitle');
    const statusBadge = document.getElementById('modalStatusBadge');

    const noLaporan = data.no_laporan || `BA-WHMS/AMC/${String(data.id).padStart(4, '0')}`;
    title.textContent = noLaporan;

    if (data.status === 'sudah') {
      statusBadge.textContent = 'Terverifikasi';
      statusBadge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200';
    } else {
      statusBadge.textContent = 'Menunggu Verifikasi';
      statusBadge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200';
    }

    document.getElementById('mPetugas').textContent = data.nama_petugas || '-';
    document.getElementById('mUnit').textContent = data.unit_kerja || '-';

    const dateFormatted = data.tanggal ? new Date(data.tanggal).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-';
    const jamFormatted = data.jam ? data.jam.substring(0, 5) + ' WIB' : '-';
    document.getElementById('mWaktu').textContent = `${dateFormatted} (${jamFormatted})`;

    document.getElementById('mCuaca').textContent = data.kondisi_cuaca || '-';
    document.getElementById('mArea').textContent = data.area_inspeksi || '-';
    document.getElementById('mGrid').textContent = data.grid_lokasi || '-';

    if (data.gps_lat && data.gps_lng) {
      document.getElementById('mGps').textContent = `${data.gps_lat}, ${data.gps_lng}`;
    } else {
      document.getElementById('mGps').textContent = 'Tidak tersimpan';
    }

    // Render Satwa List
    const satwaBox = document.getElementById('mSatwaContainer');
    satwaBox.innerHTML = '';
    if (data.detail_satwa && data.detail_satwa.length > 0) {
      data.detail_satwa.forEach(item => {
        let fotoHtml = `
          <div class="w-11 h-11 rounded-lg bg-cyan-50 border border-cyan-100 flex items-center justify-center text-[#00A9C1] shrink-0">
            <span class="material-symbols-outlined text-xl">pets</span>
          </div>
        `;
        if (item.foto_laporan && item.foto_laporan.length > 0 && item.foto_laporan[0].nama_file) {
          const fotoDir = item.foto_laporan[0].tipe === 'extra' ? 'extra' : 'satwa';
          fotoHtml = `
            <img src="/storage/uploads/${fotoDir}/${item.foto_laporan[0].nama_file}" alt="Satwa" class="w-11 h-11 rounded-lg object-cover border border-slate-200 shrink-0 bg-slate-100" onerror="this.onerror=null; this.src='/images/logo_login.png';">
          `;
        }

        const row = document.createElement('div');
        row.className = 'p-3 flex items-center justify-between gap-3 hover:bg-slate-50/50';
        row.innerHTML = `
          <div class="flex items-center gap-3 min-w-0">
            ${fotoHtml}
            <div>
              <div class="font-bold text-slate-800 text-xs">${item.nama_satwa}</div>
              <div class="text-[11px] text-slate-500 mt-0.5">Grid Lokasi Spesifik: <strong class="text-slate-700">${item.grid || data.grid_lokasi || '-'}</strong></div>
            </div>
          </div>
          <div class="text-right shrink-0">
            <span class="px-2.5 py-1 rounded-lg bg-cyan-50 border border-cyan-100 text-[#007fa3] font-bold text-xs">
              ${item.jumlah || 1} ekor
            </span>
          </div>
        `;
        satwaBox.appendChild(row);
      });
    } else {
      satwaBox.innerHTML = '<div class="p-4 text-center text-slate-400 text-xs">Tidak ada data rincian satwa.</div>';
    }

    document.getElementById('mKondisiApron').textContent = data.kondisi_apron || '-';
    document.getElementById('mCiri').textContent = data.ciri_ukuran || '-';
    document.getElementById('mAktivitas').textContent = data.aktivitas_satwa || '-';
    document.getElementById('mTindakLanjut').textContent = data.tindak_lanjut || '-';
    document.getElementById('mPengusiran').textContent = data.detail_pengusiran || '-';

    const ttdImg = document.getElementById('mTandaTangan');
    if (data.tanda_tangan && data.tanda_tangan.length > 10) {
      ttdImg.src = data.tanda_tangan;
      ttdImg.classList.remove('hidden');
    } else {
      ttdImg.classList.add('hidden');
    }

    // Render Foto Extra
    const extraSection = document.getElementById('mFotoExtraSection');
    const extraGallery = document.getElementById('mFotoExtraGallery');
    extraGallery.innerHTML = '';
    const extraPhotos = (data.foto_laporan || []).filter(f => f.tipe === 'extra');

    if (extraPhotos.length > 0) {
      extraSection.classList.remove('hidden');
      extraPhotos.forEach(p => {
        const item = document.createElement('div');
        item.className = 'rounded-xl overflow-hidden border border-slate-200 bg-slate-50';
        item.innerHTML = `
          <a href="/storage/uploads/extra/${p.nama_file}" target="_blank">
            <img src="/storage/uploads/extra/${p.nama_file}" alt="Foto Tambahan" class="w-full h-24 object-cover hover:scale-105 transition-transform duration-200">
          </a>
        `;
        extraGallery.appendChild(item);
      });
    } else {
      extraSection.classList.add('hidden');
    }

    // Render ICAO Risk Score Badge
    const rLvl = (data.risk_level || 'rendah').toLowerCase();
    const rScore = data.risk_score || 1;
    const riskBadgeEl = document.getElementById('mRiskScoreBadge');
    if (riskBadgeEl) {
      if (rLvl === 'kritis') {
        riskBadgeEl.innerHTML = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-600 text-white shadow-xs ring-2 ring-rose-300 animate-pulse"><span class="material-symbols-outlined text-xs">warning</span> Kritis (${rScore}/12)</span>`;
      } else if (rLvl === 'tinggi') {
        riskBadgeEl.innerHTML = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-600 text-white shadow-xs ring-1 ring-amber-300"><span class="material-symbols-outlined text-xs">priority_high</span> Tinggi (${rScore}/12)</span>`;
      } else if (rLvl === 'sedang') {
        riskBadgeEl.innerHTML = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300">Sedang (${rScore}/12)</span>`;
      } else {
        riskBadgeEl.innerHTML = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">Rendah (${rScore}/12)</span>`;
      }
    }

    // Render Tanggapan Thread
    renderUserThread(data.tanggapan || []);

    loading.classList.add('hidden');
    body.classList.remove('hidden');
  }

  function renderUserThread(threadList) {
    const tanggapanBox = document.getElementById('mTanggapanContainer');
    if (!tanggapanBox) return;
    tanggapanBox.innerHTML = '';

    if (threadList && threadList.length > 0) {
      threadList.forEach(t => {
        const tDate = t.created_at_human || (t.created_at ? new Date(t.created_at).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) + ' WIB' : '-');
        const author = t.author_name || (t.user ? (t.user.namalengkap || t.user.username) : 'Safety Officer');
        const role = t.author_role || (t.user ? (t.user.jabatan || (t.user.role === 'admin' ? 'Safety Manager' : 'Petugas Lapangan')) : 'Validator');
        const isAdmin = t.is_admin || (t.user ? t.user.role === 'admin' : false);
        const kategori = t.kategori_tanggapan || 'instruksi';

        let badgeHtml = '';
        if (kategori === 'penutupan') {
          badgeHtml = '<span class="px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Penutupan Laporan</span>';
        } else if (kategori === 'evaluasi') {
          badgeHtml = '<span class="px-2 py-0.5 rounded text-[9px] font-bold bg-blue-100 text-blue-800 border border-blue-200">Evaluasi Tindakan</span>';
        } else if (kategori === 'klarifikasi') {
          badgeHtml = '<span class="px-2 py-0.5 rounded text-[9px] font-bold bg-purple-100 text-purple-800 border border-purple-200">Klarifikasi Lapangan</span>';
        } else {
          badgeHtml = '<span class="px-2 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-900 border border-amber-200">Instruksi Lanjutan</span>';
        }

        const card = document.createElement('div');
        card.className = `p-3.5 rounded-2xl border ${isAdmin ? 'bg-cyan-50/70 border-cyan-100/90 shadow-2xs' : 'bg-slate-50 border-slate-200'} space-y-1.5`;
        card.innerHTML = `
          <div class="flex items-center justify-between text-[11px] gap-2 flex-wrap">
            <div class="flex items-center gap-1.5 font-bold ${isAdmin ? 'text-[#007fa3]' : 'text-slate-700'}">
              <span class="material-symbols-outlined text-base">${isAdmin ? 'support_agent' : 'person'}</span>
              <span>${author} (${role})</span>
            </div>
            <div class="flex items-center gap-2">
              ${badgeHtml}
              <span class="text-slate-400 text-[10px] font-mono">${tDate}</span>
            </div>
          </div>
          <p class="text-xs text-slate-700 leading-relaxed font-normal whitespace-pre-line">${t.isi}</p>
        `;
        tanggapanBox.appendChild(card);
      });
    } else {
      tanggapanBox.innerHTML = `
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 text-slate-500 text-xs text-center flex items-center justify-center gap-2">
          <span class="material-symbols-outlined text-slate-400 text-base">info</span>
          <span>Belum ada catatan atau instruksi lanjutan dari Safety Manager. Laporan berada dalam antrean evaluasi.</span>
        </div>
      `;
    }
  }

  // Kirim Balasan Tanggapan dari Pegawai Lapangan via AJAX
  function submitUserReply(e) {
    e.preventDefault();
    if (!currentActiveLaporanId) return;

    const isiInput = document.getElementById('userReplyIsi');
    const submitBtn = document.getElementById('btnSubmitUserReply');
    const isi = isiInput.value.trim();

    if (!isi) {
      alert('Harap tuliskan balasan tindakan dinas.');
      return;
    }

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="inline-block animate-spin rounded-full h-3.5 w-3.5 border-2 border-white border-t-transparent"></span> <span>Mengirim...</span>';

    fetch(`/laporan/${currentActiveLaporanId}/tanggapan`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        kategori_tanggapan: 'klarifikasi',
        isi: isi
      })
    })
    .then(res => {
      if (!res.ok) throw new Error('Gagal mengirim balasan');
      return res.json();
    })
    .then(data => {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<span class="material-symbols-outlined text-sm">send</span> <span>Kirim Balasan Dinas</span>';

      if (data.success) {
        isiInput.value = '';
        // Ambil ulang detail thread untuk me-refresh
        fetch(`/laporan/${currentActiveLaporanId}/detail`)
          .then(r => r.json())
          .then(res => {
            if (res.success && res.data) {
              renderUserThread(res.data.tanggapan || []);
            }
          });
      }
    })
    .catch(err => {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<span class="material-symbols-outlined text-sm">send</span> <span>Kirim Balasan Dinas</span>';
      alert(err.message || 'Terjadi kesalahan saat mengirim balasan.');
    });
  }

  function closeDetailModal() {
    const modal = document.getElementById('detailModal');
    if (!modal) return;

    modal.classList.add('opacity-0');
    modal.querySelector('div').classList.add('scale-95');
    modal.querySelector('div').classList.remove('scale-100');
    setTimeout(() => {
      modal.classList.add('hidden');
    }, 250);
  }

  // Close on Escape Key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeDetailModal();
    }
  });

  // Close on Backdrop Click
  document.getElementById('detailModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
      closeDetailModal();
    }
  });
</script>
@endsection
