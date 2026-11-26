<!-- ================= UNIFIED PAGE HEADER: PETA SEBARAN GRID ================= -->
@component('partials.page-header', [
  'title' => 'Peta Sebaran Grid & Heatmap Bahaya Satwa Sisi Udara',
  'subtitle' => 'Monitoring sebaran hotspot satwa dan pemantauan zona bahaya sisi udara bandara.',
  'icon' => 'grid_view',
  'maxWidth' => 'max-w-[1920px]',
])
  @php
    $bulanIni = now(config('whms_risk.zona_waktu'))->format('Y-m');
    $pillClass = fn (bool $active) => 'time-filter-link px-2.5 py-1 rounded font-bold transition-all ' . ($active ? 'bg-white text-[#00A9C1] shadow-2xs' : 'text-slate-600 hover:text-slate-900');
  @endphp
  <!-- Quick Action Controls & Time Toggles (diganti utuh saat filter realtime) -->
  <div id="periodeFilterBar" class="flex flex-col items-start md:items-end gap-1.5 shrink-0 self-start md:self-center max-w-full">
    <div class="flex items-center flex-wrap gap-2 max-w-full">
      <!-- Time Filter Pills -->
      <div id="timeFilterPills" class="flex items-center shrink-0 bg-slate-100/80 rounded-lg p-1 border border-slate-200/80 text-[11px] whitespace-nowrap overflow-x-auto max-w-full">
        <a href="{{ route('admin.statistik', ['time_filter' => 'month', 'kategori' => $kategori, 'zona' => $zonaFilter]) }}" class="{{ $pillClass($timeFilter === 'month') }}">Bulan Ini</a>
        <a href="{{ route('admin.statistik', ['time_filter' => 'quarter', 'kategori' => $kategori, 'zona' => $zonaFilter]) }}" class="{{ $pillClass($timeFilter === 'quarter') }}">3 Bulan Terakhir</a>
        <a href="{{ route('admin.statistik', ['time_filter' => '12m', 'kategori' => $kategori, 'zona' => $zonaFilter]) }}" class="{{ $pillClass($timeFilter === '12m') }}">12 Bulan Terakhir</a>
        <a href="{{ route('admin.statistik', ['time_filter' => 'year', 'tahun' => $tahun, 'kategori' => $kategori, 'zona' => $zonaFilter]) }}" class="{{ $pillClass($timeFilter === 'year') }}">Tahun {{ $tahun }}</a>
        <a href="{{ route('admin.statistik', ['time_filter' => 'all', 'kategori' => $kategori, 'zona' => $zonaFilter]) }}" class="{{ $pillClass($timeFilter === 'all') }}">Semua Data</a>
      </div>

      <!-- Periode pilihan sendiri: rentang bulan (1 bulan, 2 bulan, dst.) -->
      <form id="customPeriodForm" method="GET" action="{{ route('admin.statistik') }}" class="flex items-center gap-1.5 text-[11px] rounded-lg p-1 border {{ $timeFilter === 'custom' ? 'border-[#00A9C1] bg-cyan-50/60' : 'border-slate-200/80 bg-slate-100/80' }}">
        <input type="hidden" name="time_filter" value="custom">
        <input type="hidden" name="kategori" value="{{ $kategori }}">
        <input type="hidden" name="zona" value="{{ $zonaFilter }}">
        <label for="periodeDari" class="text-slate-600 font-semibold pl-1">Dari</label>
        <input id="periodeDari" type="month" name="dari" value="{{ $periodeDari ?: $bulanIni }}" min="{{ $bulanPertamaData }}" max="{{ $bulanIni }}" required class="rounded border border-slate-300 bg-white px-1.5 py-0.5 text-[11px] text-slate-800">
        <label for="periodeSampai" class="text-slate-600 font-semibold">s/d</label>
        <input id="periodeSampai" type="month" name="sampai" value="{{ $periodeSampai ?: $bulanIni }}" min="{{ $bulanPertamaData }}" max="{{ $bulanIni }}" required class="rounded border border-slate-300 bg-white px-1.5 py-0.5 text-[11px] text-slate-800">
        <button type="submit" class="rounded bg-[#00A9C1] hover:bg-[#0891B2] text-white font-bold px-2.5 py-1 cursor-pointer transition-colors">Terapkan</button>
      </form>
    </div>

    <p id="periodeAktifLabel" class="text-[11px] text-slate-500">
      Periode ditampilkan: <strong class="text-slate-800">{{ $periodeLabel }}</strong>
    </p>
  </div>
@endcomponent
