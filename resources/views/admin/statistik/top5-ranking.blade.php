<!-- CARD 2: TOP 5 AIRSIDE HAZARD & BIRD RANKING (SPAN 1 COLUMN) -->
<div id="top5Card" class="lg:col-span-1 bg-card-bg rounded-xl border border-border-subtle p-3.5 sm:p-4 shadow-sm flex flex-col justify-between">
  <div>
    <!-- Header with Tab Switcher -->
    <div class="flex items-center justify-between mb-3 pb-2 border-b border-border-subtle">
      <div class="flex items-center gap-1 bg-surface-container-low p-1 rounded-lg border border-border-subtle">
        <button type="button" id="btnTabHotspot" onclick="switchRankingTab('hotspot')" class="text-[11px] px-2.5 py-1 rounded-md bg-injourney-teal text-white font-bold transition-all cursor-pointer shadow-2xs">
          Grid Hotspot
        </button>
        <button type="button" id="btnTabBurung" onclick="switchRankingTab('burung')" class="text-[11px] px-2.5 py-1 rounded-md text-secondary hover:text-slate-navy font-semibold transition-all cursor-pointer flex items-center gap-1">
          <span class="material-symbols-outlined text-xs">flutter</span>
          <span>Burung Terbanyak</span>
        </button>
      </div>
      <span class="text-[10px] text-injourney-teal font-bold uppercase tracking-wider font-code-coordinate">Top 5</span>
    </div>

    <!-- TAB 1: TOP 5 GRID HOTSPOT -->
    <div id="tabContentHotspot" class="space-y-2.5">
      <div class="flex items-center justify-between text-[10px] text-slate-500 font-semibold mb-1">
        <h2 class="uppercase">Peringkat 5 Grid Hotspot Teratas</h2>
        <span class="text-injourney-teal font-bold">Bulan Berjalan</span>
      </div>
      @forelse($top5Grids as $top)
        <div onclick="selectGrid('{{ $top['grid'] }}')" class="cursor-pointer group">
          <div class="flex justify-between items-center text-[11px] mb-1">
            <span class="font-bold text-slate-navy group-hover:text-injourney-teal transition-colors flex items-center gap-1.5 truncate mr-2">
              <span class="w-3.5 h-3.5 rounded-full bg-slate-100 text-slate-700 text-center text-[9px] font-black inline-flex items-center justify-center shrink-0">
                {{ $top['rank'] }}
              </span>
              <span class="truncate">Grid {{ $top['grid'] }} ({{ $top['name'] }})</span>
            </span>
            <span class="font-code-coordinate font-bold text-slate-700 whitespace-nowrap">{{ $top['count'] }} Laporan</span>
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

    <!-- TAB 2: TOP 5 PENGUMPULAN SPESIES BURUNG TERBANYAK -->
    <div id="tabContentBurung" class="hidden space-y-2.5">
      <div class="flex items-center justify-between text-[10px] text-slate-500 font-semibold mb-1">
        <span>TOTAL PENGUMPULAN:</span>
        <span class="font-code-coordinate font-bold text-[#00A9C1]">{{ $totalBurungGlobal ?? 0 }} Ekor ({{ $totalLaporanBurungGlobal ?? 0 }} BA)</span>
      </div>
      @forelse($topBurungGlobal ?? [] as $bTop)
        <div class="p-2 rounded-lg bg-surface-container-low/70 border border-border-subtle group hover:border-[#00A9C1] transition-colors">
          <div class="flex justify-between items-center text-[11px] mb-1">
            <span class="font-bold text-slate-navy flex items-center gap-1.5 truncate mr-2">
              <span class="w-3.5 h-3.5 rounded-full bg-cyan-100 text-[#007fa3] text-center text-[9px] font-black inline-flex items-center justify-center shrink-0">
                {{ $bTop['rank'] }}
              </span>
              <span class="truncate">{{ $bTop['nama'] }}</span>
            </span>
            <span class="font-code-coordinate font-bold text-slate-800 whitespace-nowrap">{{ $bTop['count'] }} Ekor ({{ $bTop['percentage'] }}%)</span>
          </div>
          <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
            <div class="{{ $bTop['color'] }} h-1.5 rounded-full transition-all duration-500" style="width: {{ $bTop['bar_width'] }}%"></div>
          </div>
        </div>
      @empty
        <div class="text-center py-6 text-xs text-secondary">
          Tidak ada catatan pengumpulan burung pada periode ini.
        </div>
      @endforelse
    </div>
  </div>

  <div class="mt-3 pt-2.5 border-t border-border-subtle flex items-center justify-between text-[10px] text-secondary">
    <span>Data terintegrasi logbook AMC</span>
    <span class="font-code-coordinate font-bold text-injourney-teal">ICAO Doc 9137 Part 3</span>
  </div>
</div>
