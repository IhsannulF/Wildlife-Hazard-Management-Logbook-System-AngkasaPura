<!-- CARD 2: TOP 5 AIRSIDE HAZARD ZONES RANKING (SPAN 1 COLUMN) -->
<div id="top5Card" class="lg:col-span-1 bg-card-bg rounded-xl border border-border-subtle p-3.5 sm:p-4 shadow-sm flex flex-col justify-between">
  <div>
    <div class="flex items-center justify-between mb-3">
      <h2 class="text-xs sm:text-sm font-bold text-slate-navy">
        Peringkat 5 Grid Hotspot Teratas
      </h2>
      <span class="text-[11px] text-injourney-teal font-semibold">Bulan Berjalan</span>
    </div>

    <div class="space-y-2.5">
      @forelse($top5Grids as $top)
        <div onclick="selectGrid('{{ $top['grid'] }}')" class="cursor-pointer group">
          <div class="flex justify-between items-center text-[11px] mb-1">
            <span class="font-bold text-slate-navy group-hover:text-injourney-teal transition-colors flex items-center gap-1.5">
              <span class="w-3.5 h-3.5 rounded-full bg-slate-100 text-slate-700 text-center text-[9px] font-black inline-flex items-center justify-center">
                {{ $top['rank'] }}
              </span>
              Grid {{ $top['grid'] }} ({{ $top['name'] }})
            </span>
            <span class="font-code-coordinate font-bold text-slate-700">{{ $top['count'] }} Laporan</span>
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
  </div>
</div>
