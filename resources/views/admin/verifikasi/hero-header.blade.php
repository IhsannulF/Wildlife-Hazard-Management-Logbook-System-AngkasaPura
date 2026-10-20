<!-- PAGE HERO & EXECUTIVE METRIC BENTO CARDS (VERIFIKASI BERITA ACARA) -->
<section class="space-y-space-md">
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
    <div>
      <h1 class="text-base sm:text-lg font-bold text-on-surface tracking-tight flex items-center gap-2">
        <span class="material-symbols-outlined text-[#007fa3] text-2xl">fact_check</span>
        Verifikasi Berita Acara (BA)
      </h1>
      <p class="text-[11px] sm:text-xs text-secondary mt-0.5">
        Antrean verifikasi laporan kejadian dan intervensi bahaya satwa sisi udara.
      </p>
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
        <div class="w-8 h-8 rounded-lg bg-surface-container-low flex items-center justify-center text-injourney-teal">
          <span class="material-symbols-outlined text-lg">pets</span>
        </div>
      </div>
      <div class="mt-2.5 pt-2.5 border-t border-border-subtle flex items-center justify-between text-[11px] text-secondary">
        <span>Burung: <strong>{{ $pctBurung ?? 62 }}%</strong></span>
        <span>Reptil: <strong>{{ $pctReptil ?? 24 }}%</strong></span>
        <span>Mamalia: <strong>{{ $pctMamalia ?? 14 }}%</strong></span>
      </div>
    </div>

    <!-- Stat Card 2: Belum Ditangani / High Risk -->
    <div class="bg-card-bg rounded-xl border border-border-subtle p-3.5 shadow-sm relative overflow-hidden flex flex-col justify-between">
      <div class="absolute top-0 left-0 right-0 h-1 bg-warning-rose"></div>
      <div class="flex items-start justify-between">
        <div>
          <p class="text-[10px] sm:text-[11px] uppercase text-secondary tracking-wider font-bold">Belum Ditangani (Kritis)</p>
          <div class="flex items-baseline gap-2 mt-1">
            <span class="text-lg sm:text-xl font-bold text-warning-rose">{{ $belum }}</span>
            <span class="text-[10px] text-rose-700 bg-rose-50 px-1.5 py-0.5 rounded font-semibold flex items-center">
              <span class="material-symbols-outlined text-xs">priority_high</span> Prioritas
            </span>
          </div>
        </div>
        <div class="w-8 h-8 rounded-lg bg-rose-50 flex items-center justify-center text-warning-rose">
          <span class="material-symbols-outlined text-lg">warning</span>
        </div>
      </div>
      <div class="mt-2.5 pt-2.5 border-t border-border-subtle flex items-center justify-between text-[11px] text-secondary">
        <span>Runway 07L: <strong class="text-warning-rose font-bold">{{ $belumRunway ?? 0 }} Log</strong></span>
        <span>Perimeter Luar: <strong>{{ $belumPerimeter ?? 0 }} Log</strong></span>
      </div>
    </div>

    <!-- Stat Card 3: Sudah Ditangani / Mitigasi Selesai -->
    <div class="bg-card-bg rounded-xl border border-border-subtle p-3.5 shadow-sm relative overflow-hidden flex flex-col justify-between">
      <div class="absolute top-0 left-0 right-0 h-1 bg-resolved-emerald"></div>
      <div class="flex items-start justify-between">
        <div>
          <p class="text-[10px] sm:text-[11px] uppercase text-secondary tracking-wider font-bold">Sudah Ditangani (Mitigasi)</p>
          <div class="flex items-baseline gap-2 mt-1">
            <span class="text-lg sm:text-xl font-bold text-on-surface">{{ $ditangani }}</span>
            <span class="text-[10px] text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded font-semibold flex items-center">
              <span class="material-symbols-outlined text-xs">verified</span> Terkendali
            </span>
          </div>
        </div>
        <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-resolved-emerald">
          <span class="material-symbols-outlined text-lg">check_circle</span>
        </div>
      </div>
      <div class="mt-2.5 pt-2.5 border-t border-border-subtle flex items-center justify-between text-[11px] text-secondary">
        @php
          $rateSelesai = $totalLaporan > 0 ? round(($ditangani / $totalLaporan) * 100) : 100;
        @endphp
        <span>Tingkat Selesai: <strong class="text-emerald-700 font-bold">{{ $rateSelesai }}%</strong></span>
        <span>ICAO Compliance: <strong>100%</strong></span>
      </div>
    </div>

    <!-- Stat Card 4: Hotspot Area Kritis -->
    <div class="bg-card-bg rounded-xl border border-border-subtle p-3.5 shadow-sm relative overflow-hidden flex flex-col justify-between">
      <div class="absolute top-0 left-0 right-0 h-1 bg-alert-amber"></div>
      <div class="flex items-start justify-between">
        <div>
          <p class="text-[10px] sm:text-[11px] uppercase text-secondary tracking-wider font-bold">Hotspot Area Kritis</p>
          <div class="flex items-baseline gap-2 mt-1">
            <span class="text-lg sm:text-xl font-bold text-on-surface font-code-coordinate">{{ $hotspotGrid ?? 'K-10 & D-9' }}</span>
            <span class="text-[10px] text-amber-800 bg-amber-50 px-1.5 py-0.5 rounded font-semibold flex items-center">
              <span class="material-symbols-outlined text-xs">crisis_alert</span> High Alert
            </span>
          </div>
        </div>
        <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-alert-amber">
          <span class="material-symbols-outlined text-lg">radar</span>
        </div>
      </div>
      <div class="mt-2.5 pt-2.5 border-t border-border-subtle flex items-center justify-between text-[11px] text-secondary">
        <span>Zona Inti: <strong>Runway 07L</strong></span>
        <span>Patroli AMC: <strong class="text-injourney-teal font-semibold">Intensif</strong></span>
      </div>
    </div>

  </div>
</section>
