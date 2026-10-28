<!-- ================= UNIFIED PAGE HEADER: PETA SEBARAN GRID ================= -->
@component('partials.page-header', [
  'title' => 'Peta Sebaran Grid & Heatmap Bahaya Satwa Sisi Udara',
  'subtitle' => 'Monitoring sebaran hotspot satwa dan pemantauan zona bahaya sisi udara bandara.',
  'icon' => 'grid_view',
  'maxWidth' => 'max-w-[1920px]',
])
  <!-- Quick Action Controls & Time Toggles -->
  <div class="flex items-center flex-nowrap gap-2 shrink-0 self-start md:self-center overflow-x-auto max-w-full">
    <!-- Time Filter Pills -->
    <div id="timeFilterPills" class="flex items-center shrink-0 bg-slate-100/80 rounded-lg p-1 border border-slate-200/80 text-[11px] whitespace-nowrap">
      <a href="{{ route('admin.statistik', ['time_filter' => 'month', 'kategori' => $kategori, 'zona' => $zonaFilter]) }}" class="time-filter-link px-2.5 py-1 rounded font-bold transition-all {{ $timeFilter === 'month' ? 'bg-white text-[#00A9C1] shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}">
        Bulan Ini
      </a>
      <a href="{{ route('admin.statistik', ['time_filter' => 'quarter', 'kategori' => $kategori, 'zona' => $zonaFilter]) }}" class="time-filter-link px-2.5 py-1 rounded font-bold transition-all {{ $timeFilter === 'quarter' ? 'bg-white text-[#00A9C1] shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}">
        3 Bulan Terakhir
      </a>
      <a href="{{ route('admin.statistik', ['time_filter' => 'year', 'tahun' => $tahun, 'kategori' => $kategori, 'zona' => $zonaFilter]) }}" class="time-filter-link px-2.5 py-1 rounded font-bold transition-all {{ $timeFilter === 'year' ? 'bg-white text-[#00A9C1] shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}">
        Tahun {{ $tahun }}
      </a>
    </div>

    <!-- Export Excel -->
    <a href="{{ route('admin.export.excel', ['tahun' => $tahun]) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 border border-slate-200/90 rounded-lg bg-white text-slate-700 hover:bg-slate-50 text-[11px] font-semibold transition-colors shadow-2xs shrink-0 whitespace-nowrap">
      <span class="material-symbols-outlined text-sm text-slate-500">file_download</span>
      <span>Export Excel</span>
    </a>

    <!-- Print Operational PDF Button -->
    <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 border border-slate-200/90 rounded-lg bg-white text-slate-700 hover:bg-slate-50 text-[11px] font-semibold transition-colors shadow-2xs shrink-0 whitespace-nowrap cursor-pointer">
      <span class="material-symbols-outlined text-sm text-slate-500">print</span>
      <span>Cetak Peta PDF</span>
    </button>
  </div>
@endcomponent

