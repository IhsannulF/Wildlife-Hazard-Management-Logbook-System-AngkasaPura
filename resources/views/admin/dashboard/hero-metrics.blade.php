<!-- PAGE HERO & EXECUTIVE METRIC BENTO CARDS -->
<section class="space-y-space-md">
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
    <div>
      @if($isVerifikasi)
        <h1 class="text-base sm:text-lg font-bold text-on-surface tracking-tight flex items-center gap-2">
          <span class="material-symbols-outlined text-[#007fa3] text-2xl">fact_check</span>
          Verifikasi Berita Acara (BA)
        </h1>
        <p class="text-[11px] sm:text-xs text-secondary mt-0.5">
          Antrean verifikasi laporan kejadian dan intervensi bahaya satwa sisi udara.
        </p>
      @else
        <h1 class="text-base sm:text-lg font-bold text-on-surface tracking-tight">
          Logbook Sisi Udara
        </h1>
        <p class="text-[11px] sm:text-xs text-secondary mt-0.5">
          Monitoring dan pengendalian bahaya satwa liar area sisi udara.
        </p>
      @endif
    </div>
    <div class="flex items-center gap-2">
      <span class="text-[11px] text-secondary font-medium">Pembaruan otomatis 60d</span>
      <button class="p-1.5 text-secondary hover:text-injourney-teal transition-colors rounded hover:bg-surface-container-low cursor-pointer" onclick="refreshData()" title="Muat Ulang Data">
        <span class="material-symbols-outlined text-sm">sync</span>
      </button>
    </div>
  </div>

  <!-- 4 Aviation Metric Summary Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
    
    <!-- Stat Card 1: Total Temuan Satwa -->
    <div class="bg-card-bg rounded-xl border border-border-subtle p-3.5 shadow-sm relative overflow-hidden flex flex-col justify-between">
      <div class="absolute top-0 left-0 right-0 h-1 bg-injourney-teal"></div>
      <div class="flex items-start justify-between">
        <div>
          <p class="text-[10px] sm:text-[11px] uppercase text-secondary tracking-wider font-bold">Total Temuan Bulan Ini</p>
          <div class="flex items-baseline gap-2 mt-1">
            <span class="text-lg sm:text-xl font-bold text-on-surface">{{ $totalLaporan }}</span>
            <span class="text-[10px] text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded font-semibold flex items-center">
              <span class="material-symbols-outlined text-xs">arrow_upward</span> Aktif
            </span>
          </div>
        </div>
        <a href="{{ route('admin.verifikasi') }}" class="w-8 h-8 rounded-lg bg-surface-container-low hover:bg-surface-container flex items-center justify-center text-injourney-teal transition-colors" title="Buka Register Laporan & Verifikasi BA">
          <span class="material-symbols-outlined text-lg">pets</span>
        </a>
      </div>
      <div class="mt-2.5 pt-2.5 border-t border-border-subtle flex items-center justify-between text-[11px] text-secondary">
        <span>Burung: <strong>{{ $pctBurung }}%</strong></span>
        <span>Reptil: <strong>{{ $pctReptil }}%</strong></span>
        <span>Mamalia: <strong>{{ $pctMamalia }}%</strong></span>
      </div>
    </div>

    <!-- Stat Card 2: Belum Ditangani / High Risk -->
    <div class="bg-card-bg rounded-xl border border-border-subtle p-3.5 shadow-sm relative overflow-hidden flex flex-col justify-between">
      <div class="absolute top-0 left-0 right-0 h-1 bg-warning-rose"></div>
      <div class="flex items-start justify-between">
        <div>
          <p class="text-[10px] sm:text-[11px] uppercase text-secondary tracking-wider font-bold">Belum Ditangani (Aktif)</p>
          <div class="flex items-baseline gap-2 mt-1">
            <span class="text-lg sm:text-xl font-bold text-warning-rose">{{ $belum }}</span>
            @if($belum > 0)
              <a href="{{ route('admin.dashboard', ['tab' => 'verifikasi', 'status' => 'belum']) }}" class="text-[10px] text-rose-700 bg-rose-50 hover:bg-rose-100 px-2 py-0.5 rounded-full font-bold transition-colors inline-flex items-center gap-0.5" title="Buka Antrean Verifikasi BA">
                <span>Verifikasi BA</span>
                <span class="material-symbols-outlined text-[10px]">arrow_forward</span>
              </a>
            @else
              <span class="text-[10px] text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full font-bold">
                Terkendali Aman
              </span>
            @endif
          </div>
        </div>
        <a href="{{ route('admin.dashboard', ['tab' => 'verifikasi', 'status' => 'belum']) }}" class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 flex items-center justify-center text-warning-rose transition-colors" title="Buka Antrean Verifikasi BA">
          <span class="material-symbols-outlined text-lg">crisis_alert</span>
        </a>
      </div>
      <div class="mt-2.5 pt-2.5 border-t border-border-subtle flex items-center justify-between text-[11px] text-secondary">
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
    <div class="bg-card-bg rounded-xl border border-border-subtle p-3.5 shadow-sm relative overflow-hidden flex flex-col justify-between">
      <div class="absolute top-0 left-0 right-0 h-1 bg-resolved-emerald"></div>
      <div class="flex items-start justify-between">
        <div>
          <p class="text-[10px] sm:text-[11px] uppercase text-secondary tracking-wider font-bold">Mitigasi Telah Ditangani</p>
          <div class="flex items-baseline gap-2 mt-1">
            <span class="text-lg sm:text-xl font-bold text-on-surface">{{ $ditangani }}</span>
            <span class="text-[10px] text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-full font-bold">
              {{ $rate }}% Rate
            </span>
          </div>
        </div>
        <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-resolved-emerald">
          <span class="material-symbols-outlined text-lg">task_alt</span>
        </div>
      </div>
      <div class="mt-2.5 pt-2.5 border-t border-border-subtle flex items-center justify-between text-[11px] text-secondary">
        <span>Rerata Respon: <strong>4.2 Menit</strong></span>
        <span class="text-emerald-700 font-medium">100% Dispersal BA Valid</span>
      </div>
    </div>

    <!-- Stat Card 4: Grid Rawan / Hotspot Utama -->
    <div class="bg-card-bg rounded-xl border border-border-subtle p-3.5 shadow-sm relative overflow-hidden flex flex-col justify-between">
      <div class="absolute top-0 left-0 right-0 h-1 bg-alert-amber"></div>
      <div class="flex items-start justify-between">
        <div>
          <p class="text-[10px] sm:text-[11px] uppercase text-secondary tracking-wider font-bold">Grid Hotspot Utama</p>
          <div class="flex items-baseline gap-2 mt-1">
            <span class="text-lg sm:text-xl font-bold text-on-surface">{{ $hotspotGrid }}</span>
          </div>
        </div>
        <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-alert-amber">
          <span class="material-symbols-outlined text-lg">share_location</span>
        </div>
      </div>
      <div class="mt-2.5 pt-2.5 border-t border-border-subtle flex items-center justify-between text-[11px] text-secondary">
        <span class="truncate pr-1">Perimeter Runway 25R</span>
        <span class="font-code-coordinate bg-surface-container-high px-1.5 py-0.5 rounded text-on-surface font-bold text-[10px]">{{ $totalLaporan }} Log</span>
      </div>
    </div>

  </div>

  @if($isVerifikasi)
  <!-- Alert / Notice Banner Verifikasi BA (Khusus Tab Verifikasi Tanpa Grafik) -->
  <div class="bg-cyan-50/80 border border-cyan-200/90 rounded-xl p-3.5 sm:p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-cyan-100 border border-cyan-200 text-[#007fa3] flex items-center justify-center shrink-0">
        <span class="material-symbols-outlined text-2xl">fact_check</span>
      </div>
      <div>
        <h2 class="text-xs sm:text-sm font-bold text-slate-800 flex items-center gap-1.5">
          <span>Mode Verifikasi Berita Acara (BA) &amp; Intervensi Satwa</span>
          <span class="px-2 py-0.5 rounded-full text-[10px] bg-[#007fa3] text-white font-bold">Resmi</span>
        </h2>
        <p class="text-[11px] text-slate-600 mt-0.5">
          Memvalidasi laporan kejadian satwa liar sisi udara, tindakan pengusiran (dispersal), dan penerbitan berkas Berita Acara (BA) standar ICAO / DKPPU.
        </p>
      </div>
    </div>
    <div class="flex items-center gap-2 shrink-0 self-start sm:self-auto">
      @if($belum > 0)
        <span class="px-3 py-1.5 rounded-lg bg-rose-50 text-warning-rose border border-rose-200 text-xs font-bold shadow-2xs flex items-center gap-1.5">
          <span class="w-2 h-2 rounded-full bg-warning-rose animate-ping"></span>
          {{ $belum }} Menunggu Verifikasi
        </span>
      @else
        <span class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold shadow-2xs flex items-center gap-1.5">
          <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
          Semua Laporan Terverifikasi
        </span>
      @endif
      <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 rounded-lg bg-white hover:bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700 shadow-2xs transition-colors flex items-center gap-1">
        <span class="material-symbols-outlined text-sm">space_dashboard</span>
        <span>Dashboard Utama</span>
      </a>
    </div>
  </div>
  @endif
</section>
