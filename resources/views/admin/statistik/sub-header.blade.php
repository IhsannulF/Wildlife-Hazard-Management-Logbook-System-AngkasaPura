<!-- SUB-HEADER & BREADCRUMB COMMAND STRIP -->
<section class="bg-card-bg border-b border-border-subtle px-4 sm:px-6 lg:px-8 py-3.5">
  <div class="max-w-[1920px] mx-auto flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
    
    <!-- Page Title & Short Purpose -->
    <div class="min-w-0">
      <h1 class="text-base sm:text-lg font-bold text-slate-navy tracking-tight">
        Peta Sebaran Grid &amp; Heatmap Bahaya Satwa Sisi Udara
      </h1>
      <h2 class="text-[11px] text-secondary font-normal mt-0.5">
        Monitoring sebaran hotspot satwa dan pemantauan zona bahaya sisi udara bandara.
      </h2>
    </div>

    <!-- Quick Action Controls & Time Toggles (Single Row) -->
    <div class="flex items-center flex-nowrap gap-2 shrink-0 self-start lg:self-center overflow-x-auto max-w-full">
      <!-- Time Filter Pills -->
      <div id="timeFilterPills" class="flex items-center shrink-0 bg-surface-container-low rounded-lg p-1 border border-border-subtle text-[11px] whitespace-nowrap">
        <a href="{{ route('admin.statistik', ['time_filter' => 'month', 'kategori' => $kategori, 'zona' => $zonaFilter]) }}" class="time-filter-link px-2.5 py-1 rounded font-bold transition-all {{ $timeFilter === 'month' ? 'bg-card-bg text-injourney-teal shadow-xs' : 'text-secondary hover:text-on-surface' }}">
          Bulan Ini
        </a>
        <a href="{{ route('admin.statistik', ['time_filter' => 'quarter', 'kategori' => $kategori, 'zona' => $zonaFilter]) }}" class="time-filter-link px-2.5 py-1 rounded font-bold transition-all {{ $timeFilter === 'quarter' ? 'bg-card-bg text-injourney-teal shadow-xs' : 'text-secondary hover:text-on-surface' }}">
          3 Bulan Terakhir
        </a>
        <a href="{{ route('admin.statistik', ['time_filter' => 'year', 'tahun' => $tahun, 'kategori' => $kategori, 'zona' => $zonaFilter]) }}" class="time-filter-link px-2.5 py-1 rounded font-bold transition-all {{ $timeFilter === 'year' ? 'bg-card-bg text-injourney-teal shadow-xs' : 'text-secondary hover:text-on-surface' }}">
          Tahun {{ $tahun }}
        </a>
      </div>

      <!-- Export Excel -->
      <a href="{{ route('admin.export.excel', ['tahun' => $tahun]) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 border border-border-subtle rounded-lg bg-card-bg text-on-surface hover:bg-surface-container-low text-[11px] font-semibold transition-colors shadow-2xs shrink-0 whitespace-nowrap">
        <span class="material-symbols-outlined text-sm text-secondary">file_download</span>
        <span>Export Excel</span>
      </a>

      <!-- Print Operational PDF Button -->
      <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 border border-border-subtle rounded-lg bg-card-bg text-on-surface hover:bg-surface-container-low text-[11px] font-semibold transition-colors shadow-2xs shrink-0 whitespace-nowrap cursor-pointer">
        <span class="material-symbols-outlined text-sm text-secondary">print</span>
        <span>Cetak Peta PDF</span>
      </button>
    </div>

  </div>
</section>
