@extends('layouts.admin', ['activePage' => 'statistic'])

@section('title', 'Statistik Satwa Liar — InJourney Airports')

@section('content')
  <!-- Breadcrumb -->
  <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-6">
    <a href="{{ route('admin.dashboard') }}" class="hover:text-teal-600 transition-colors">Dashboard</a>
    <span>&rsaquo;</span>
    <span class="text-slate-700">Statistik & Visualisasi Satwa Liar</span>
  </div>

  <!-- FILTER CARD -->
  <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-7 mb-8 shadow-sm flex items-center justify-between gap-4 flex-wrap">
    <form method="GET" action="{{ route('admin.statistik') }}" class="flex items-center gap-3.5 flex-wrap m-0">
      <div class="flex items-center gap-2">
        <label class="text-xs font-bold text-slate-600">Tahun:</label>
        <select name="tahun" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 outline-none focus:ring-2 focus:ring-teal-500 shadow-2xs" onchange="this.form.submit()">
          @for($y = date('Y'); $y >= date('Y') - 4; $y--)
            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
          @endfor
        </select>
      </div>

      <div class="flex items-center gap-2">
        <label class="text-xs font-bold text-slate-600">Zona Grid:</label>
        <select name="zona" class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 outline-none focus:ring-2 focus:ring-teal-500 shadow-2xs" onchange="this.form.submit()">
          <option value="">Semua Zona Grid</option>
          @foreach($gridAda as $g)
            <option value="{{ $g }}" {{ $zona === $g ? 'selected' : '' }}>Grid {{ $g }}</option>
          @endforeach
        </select>
      </div>

      <div class="relative">
        <input type="text" name="search" value="{{ $search }}" placeholder="Cari jenis satwa..." class="w-48 sm:w-56 pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 placeholder:text-slate-400 outline-none focus:ring-2 focus:ring-teal-500 shadow-2xs">
        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      </div>

      <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-[#00A9C1] to-[#007fa3] text-white text-xs font-bold rounded-xl shadow-xs hover:opacity-95 cursor-pointer transition-all">
        Terapkan
      </button>

      @if($zona || $search)
        <a href="{{ route('admin.statistik', ['tahun' => $tahun]) }}" class="text-xs font-bold text-rose-500 hover:text-rose-700 px-2">Reset</a>
      @endif
    </form>

    <div class="flex items-center gap-3 flex-wrap">
      <a href="{{ route('admin.export.excel', ['tahun' => $tahun, 'zona' => $zona]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs hover:shadow transition-all cursor-pointer">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/></svg>
        <span>Export Excel</span>
      </a>

      <div class="text-xs font-bold text-teal-700 bg-teal-50 border border-teal-200 px-4 py-2.5 rounded-xl shadow-2xs">
        Total Terdata: <strong class="text-sm font-extrabold text-teal-900">{{ $grandTotal }}</strong> ekor
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
    <!-- CHART CONTAINER -->
    <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200/90 p-7 sm:p-8 shadow-sm">
      <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100">
        <div>
          <h3 class="text-base sm:text-lg font-extrabold text-slate-800 tracking-tight">Grafik Fluktuasi Satwa Bulanan (Tahun {{ $tahun }})</h3>
          <p class="text-xs text-slate-400 mt-1">
            Rekapitulasi jumlah temuan satwa per bulan{{ $zona ? " di Zona Grid $zona" : "" }}
          </p>
        </div>
      </div>
      <div class="h-96 relative">
        <canvas id="stackedBarChart"></canvas>
      </div>
    </div>

    <!-- SIDE PANELS -->
    <div class="space-y-6">
      <!-- TOP SPECIES -->
      <div class="bg-white rounded-3xl border border-slate-200/90 p-7 shadow-sm">
        <h3 class="text-sm font-extrabold text-slate-800 mb-5 flex items-center gap-2 tracking-tight">
          <svg class="w-4 h-4 text-amber-500" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <span>5 Spesies Terbanyak ({{ $tahun }})</span>
        </h3>
        <div class="space-y-2.5">
          @forelse($topSatwa as $i => $ts)
            <div class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 border border-slate-100 text-xs hover:bg-slate-100/70 transition-all">
              <span class="font-bold text-slate-800 flex items-center gap-2.5 truncate pr-2">
                <span class="w-6 h-6 rounded-lg bg-teal-50 text-teal-700 font-extrabold flex items-center justify-center text-[11px] shrink-0 border border-teal-200">#{{ $i + 1 }}</span>
                <span class="truncate">{{ $ts->nama }}</span>
              </span>
              <span class="font-extrabold text-teal-800 bg-teal-100/60 px-2.5 py-1 rounded-lg shrink-0 text-xs">{{ $ts->total }} ekor</span>
            </div>
          @empty
            <p class="text-xs text-slate-400 text-center py-6">Belum ada data tercatat.</p>
          @endforelse
        </div>
      </div>

      <!-- ZONA GRID DENGAN TEMUAN -->
      <div class="bg-white rounded-3xl border border-slate-200/90 p-7 shadow-sm">
        <h3 class="text-sm font-extrabold text-slate-800 mb-1 tracking-tight">Zona Grid Aktif ({{ count($gridAda) }})</h3>
        <p class="text-xs text-slate-400 mb-4">Klik kode grid untuk memfilter grafik secara spesifik</p>
        <div class="flex flex-wrap gap-2">
          @foreach($gridAda as $ga)
            <a href="{{ route('admin.statistik', ['tahun' => $tahun, 'zona' => $ga]) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ $zona === $ga ? 'bg-gradient-to-r from-[#00A9C1] to-[#007fa3] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
              {{ $ga }}
            </a>
          @endforeach
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('stackedBarChart').getContext('2d');
  
  const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  
  const rawDatasets = [
    @foreach($jenisList as $idx => $jenis)
      {
        label: "{{ $jenis }}",
        backgroundColor: "{{ $paletteHex[$idx % count($paletteHex)] }}",
        data: [
          @for($m = 1; $m <= 12; $m++)
            {{ $mapBar[$jenis][$m] ?? 0 }},
          @endfor
        ]
      },
    @endforeach
  ];

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: rawDatasets
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: { font: { family: 'DM Sans', size: 12 } }
        },
        tooltip: {
          mode: 'index',
          intersect: false
        }
      },
      scales: {
        x: {
          stacked: true,
          grid: { display: false }
        },
        y: {
          stacked: true,
          beginAtZero: true,
          ticks: { precision: 0 }
        }
      }
    }
  });
</script>
@endsection
