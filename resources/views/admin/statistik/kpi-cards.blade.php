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
