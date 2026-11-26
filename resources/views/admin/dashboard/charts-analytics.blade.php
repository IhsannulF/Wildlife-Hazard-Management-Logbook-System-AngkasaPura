@php
  $fmt = fn ($value, int $decimals = 0) => number_format((float) $value, $decimals, ',', '.');
  $kpi = $statistik['kpi'];
  $div = $statistik['keanekaragaman'];
  $grid = $statistik['grid'];
  $korelasi = $statistik['korelasi'];
  $kejadian = $statistik['kejadian'];

  $kelasAktivitas = [
    'rendah' => ['label' => 'Rendah', 'batas' => '≤ 50 ekor', 'dot' => 'bg-[#16A34A]', 'badge' => 'bg-emerald-50 text-emerald-800 border-emerald-200'],
    'sedang' => ['label' => 'Sedang', 'batas' => '51–150 ekor', 'dot' => 'bg-[#EAB308]', 'badge' => 'bg-yellow-50 text-yellow-800 border-yellow-200'],
    'tinggi' => ['label' => 'Tinggi', 'batas' => '151–300 ekor', 'dot' => 'bg-[#F97316]', 'badge' => 'bg-orange-50 text-orange-800 border-orange-200'],
    'sangat_tinggi' => ['label' => 'Sangat Tinggi', 'batas' => '> 300 ekor', 'dot' => 'bg-[#DC2626]', 'badge' => 'bg-red-50 text-red-800 border-red-200'],
  ];

  $tafsirShannon = match (true) {
    $div['total'] === 0 => 'Belum ada data',
    $div['shannon'] < 1 => 'Keanekaragaman rendah',
    $div['shannon'] <= 3 => 'Keanekaragaman sedang',
    default => 'Keanekaragaman tinggi',
  };

  $tafsirKorelasi = null;
  if ($korelasi['r'] !== null) {
    $kekuatan = abs($korelasi['r']) < 0.3 ? 'lemah' : (abs($korelasi['r']) < 0.7 ? 'sedang' : 'kuat');
    $tafsirKorelasi = 'Korelasi ' . ($korelasi['r'] >= 0 ? 'positif ' : 'negatif ') . $kekuatan;
  }

  $bulanDenganData = collect($statistik['tren']['burung'])->filter()->count();
  $bulanPuncak = collect($statistik['tren']['burung'])->search(max($statistik['tren']['burung']));
  $periodeGrid = \Carbon\Carbon::parse($grid['mulai'])->translatedFormat('j M Y') . ' – ' . \Carbon\Carbon::parse($grid['akhir'])->translatedFormat('j M Y');
@endphp

<!-- ================= STATISTIK & INFOGRAFIS POPULASI BURUNG (PRD F6) ================= -->
<section id="statistikBurung" class="space-y-3.5">

  <!-- Header & Filter Tahun -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-1 border-b border-border-subtle">
    <div class="flex items-center gap-2">
      <div class="w-7 h-7 rounded-lg bg-cyan-50 border border-cyan-200 flex items-center justify-center text-injourney-teal">
        <span class="material-symbols-outlined text-base">insights</span>
      </div>
      <div>
        <h2 class="text-sm sm:text-base font-bold text-slate-navy">Statistik &amp; Infografis Populasi Burung</h2>
        <p class="text-[11px] text-secondary">Dihitung otomatis dari logbook inspeksi tahun {{ $statistik['tahun'] }} — tanpa data contoh.</p>
      </div>
    </div>

    <div class="flex items-center gap-2 self-start sm:self-center">
      <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-1.5">
        <label for="statistikTahun" class="text-[11px] font-semibold text-secondary">Tahun</label>
        <select id="statistikTahun" name="tahun" onchange="this.form.submit()" class="text-[11px] font-semibold rounded-lg border border-border-subtle bg-white px-2 py-1 text-slate-navy focus:outline-none focus:ring-2 focus:ring-injourney-teal/40">
          @forelse($statistikTahunTersedia as $tahunOpsi)
            <option value="{{ $tahunOpsi }}" @selected($tahunOpsi === $statistikTahun)>{{ $tahunOpsi }}</option>
          @empty
            <option value="{{ $statistikTahun }}">{{ $statistikTahun }}</option>
          @endforelse
        </select>
      </form>
      <a href="{{ route('admin.statistik') }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-border-subtle bg-white hover:bg-surface-container-low text-[11px] font-semibold text-injourney-teal transition-colors shadow-2xs">
        <span>Peta Grid Heatmap</span>
        <span class="material-symbols-outlined text-xs">arrow_forward</span>
      </a>
    </div>
  </div>

  <!-- KPI Tiles -->
  <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-3">
    @foreach([
      ['label' => 'Total Observasi', 'value' => $fmt($kpi['observasi']), 'unit' => 'laporan', 'icon' => 'travel_explore', 'accent' => 'text-injourney-teal'],
      ['label' => 'Temuan Burung', 'value' => $fmt($kpi['burung']), 'unit' => 'ekor', 'icon' => 'flutter_dash', 'accent' => 'text-emerald-600'],
      ['label' => 'Jenis Burung', 'value' => $fmt($kpi['jenis']), 'unit' => 'spesies', 'icon' => 'category', 'accent' => 'text-indigo-600'],
      ['label' => 'Kejadian Bird Strike', 'value' => $fmt($kpi['kejadian']), 'unit' => 'insiden', 'icon' => 'flight', 'accent' => 'text-rose-600'],
      ['label' => 'Grid Aktivitas Tinggi', 'value' => $fmt($kpi['grid_risiko_tinggi']), 'unit' => 'grid', 'icon' => 'warning', 'accent' => 'text-orange-600'],
    ] as $tile)
      <div class="bg-card-bg rounded-xl border border-border-subtle p-3 shadow-sm">
        <div class="flex items-center justify-between">
          <p class="text-[10px] uppercase tracking-wider font-bold text-secondary">{{ $tile['label'] }}</p>
          <span class="material-symbols-outlined text-base {{ $tile['accent'] }}">{{ $tile['icon'] }}</span>
        </div>
        <div class="mt-1 flex items-baseline gap-1.5">
          <span class="text-xl font-bold text-slate-navy">{{ $tile['value'] }}</span>
          <span class="text-[11px] text-secondary">{{ $tile['unit'] }}</span>
        </div>
      </div>
    @endforeach
  </div>

  <!-- ROW 1: Tren Bulanan & Top 10 Jenis -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-3.5 items-stretch">

    <div class="lg:col-span-7 bg-card-bg rounded-xl border border-border-subtle p-3.5 sm:p-4 shadow-sm flex flex-col">
      <div class="flex flex-wrap items-start justify-between gap-2 mb-2.5">
        <div>
          <h3 class="text-xs sm:text-sm font-bold text-slate-navy flex items-center gap-1.5">
            <span class="material-symbols-outlined text-sm text-injourney-teal">trending_up</span>
            Tren Populasi Burung Bulanan
          </h3>
          <p class="text-[11px] text-secondary mt-0.5">Total ekor burung yang tercatat per bulan, {{ $statistik['tahun'] }}. Arahkan kursor untuk jumlah observasi.</p>
        </div>
        @if($bulanDenganData > 0)
          <span class="text-[10px] px-2 py-0.5 rounded-md bg-cyan-50 text-cyan-900 border border-cyan-200 font-bold">
            Puncak: {{ $statistik['tren']['labels'][$bulanPuncak] }} ({{ $fmt($statistik['tren']['burung'][$bulanPuncak]) }} ekor)
          </span>
        @endif
      </div>
      <div class="relative w-full h-56 sm:h-64">
        <canvas id="trendChart" role="img" aria-label="Tren populasi burung bulanan"></canvas>
      </div>
      @if($bulanDenganData < 3)
        <p class="mt-2 text-[11px] text-amber-800 bg-amber-50 border border-amber-200 rounded-lg px-2.5 py-1.5">Baru {{ $bulanDenganData }} bulan berisi data — tren naik/turun belum dapat disimpulkan.</p>
      @endif
    </div>

    <div class="lg:col-span-5 bg-card-bg rounded-xl border border-border-subtle p-3.5 sm:p-4 shadow-sm flex flex-col">
      <h3 class="text-xs sm:text-sm font-bold text-slate-navy flex items-center gap-1.5">
        <span class="material-symbols-outlined text-sm text-injourney-teal">format_list_numbered</span>
        Top 10 Jenis Burung
      </h3>
      <p class="text-[11px] text-secondary mt-0.5 mb-2">Jumlah ekor dan porsi dari total temuan burung.</p>
      @if(count($statistik['top_jenis']) > 0)
        <div class="relative w-full" style="height: {{ max(160, count($statistik['top_jenis']) * 30) }}px">
          <canvas id="speciesBarChart" role="img" aria-label="Top 10 jenis burung"></canvas>
        </div>
      @else
        <p class="my-auto text-center text-xs text-secondary py-10">Belum ada temuan burung pada tahun ini.</p>
      @endif
    </div>
  </div>

  <!-- ROW 2: Keanekaragaman, Aktivitas Grid, Jam Temuan -->
  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3.5">

    <div class="bg-card-bg rounded-xl border border-border-subtle p-3.5 sm:p-4 shadow-sm flex flex-col">
      <h3 class="text-xs sm:text-sm font-bold text-slate-navy flex items-center gap-1.5">
        <span class="material-symbols-outlined text-sm text-indigo-600">diversity_3</span>
        Indeks Keanekaragaman
      </h3>
      <p class="text-[11px] text-secondary mt-0.5 mb-2.5">{{ $tafsirShannon }} · dari {{ $fmt($div['total']) }} ekor, {{ $div['richness'] }} jenis.</p>
      <dl class="grid grid-cols-2 gap-2">
        @foreach([
          ['label' => "Shannon-Wiener (H')", 'value' => $div['total'] > 0 ? $fmt($div['shannon'], 2) : '–', 'hint' => '< 1 rendah · 1–3 sedang · > 3 tinggi'],
          ['label' => 'Simpson (1 − D)', 'value' => $div['total'] > 0 ? $fmt($div['simpson'], 2) : '–', 'hint' => 'Peluang 2 ekor acak beda jenis'],
          ['label' => 'Kemerataan Pielou (E)', 'value' => $div['evenness'] !== null ? $fmt($div['evenness'], 2) : '–', 'hint' => 'Mendekati 1 = merata'],
          ['label' => 'Kekayaan Jenis (S)', 'value' => $fmt($div['richness']), 'hint' => 'Jumlah jenis burung'],
        ] as $indeks)
          <div class="bg-surface-container-low rounded-lg border border-border-subtle p-2">
            <dt class="text-[10px] font-semibold text-secondary">{{ $indeks['label'] }}</dt>
            <dd class="text-base font-bold text-slate-navy">{{ $indeks['value'] }}</dd>
            <dd class="text-[9.5px] text-secondary leading-tight">{{ $indeks['hint'] }}</dd>
          </div>
        @endforeach
      </dl>
      <p class="text-[10px] font-semibold text-secondary mt-3 mb-1">H' per bulan</p>
      <div class="relative w-full h-28">
        <canvas id="diversityChart" role="img" aria-label="Indeks Shannon per bulan"></canvas>
      </div>
    </div>

    <div class="bg-card-bg rounded-xl border border-border-subtle p-3.5 sm:p-4 shadow-sm flex flex-col">
      <h3 class="text-xs sm:text-sm font-bold text-slate-navy flex items-center gap-1.5">
        <span class="material-symbols-outlined text-sm text-orange-600">grid_on</span>
        Distribusi Aktivitas Grid
      </h3>
      <p class="text-[11px] text-secondary mt-0.5 mb-2">Total ekor burung per grid, {{ $periodeGrid }} (6 bulan).</p>
      <div class="flex items-center gap-3">
        <div class="w-32 h-32 relative shrink-0">
          <canvas id="gridDonutChart" role="img" aria-label="Distribusi kelas aktivitas grid"></canvas>
          <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
            <span class="text-lg font-bold text-slate-navy">{{ array_sum($grid['distribusi']) }}</span>
            <span class="text-[9px] text-secondary uppercase font-semibold">Grid</span>
          </div>
        </div>
        <ul class="flex-1 space-y-1 text-[11px]">
          @foreach($kelasAktivitas as $kelas => $meta)
            <li class="flex items-center justify-between gap-2">
              <span class="flex items-center gap-1.5 text-slate-700"><span class="w-2.5 h-2.5 rounded-sm {{ $meta['dot'] }}"></span>{{ $meta['label'] }} <span class="text-secondary">({{ $meta['batas'] }})</span></span>
              <strong class="text-slate-navy">{{ $grid['distribusi'][$kelas] }}</strong>
            </li>
          @endforeach
        </ul>
      </div>
      <div class="mt-3 pt-2 border-t border-border-subtle">
        <p class="text-[10px] font-semibold text-secondary mb-1">5 grid teraktif</p>
        @forelse($grid['teratas'] as $baris)
          <div class="flex items-center justify-between text-[11px] py-0.5">
            <span class="font-code-coordinate font-bold text-slate-navy">{{ $baris['grid'] }}</span>
            <span class="text-slate-700">{{ $fmt($baris['jumlah']) }} ekor</span>
            <span class="px-1.5 py-0.5 rounded border text-[10px] font-bold {{ $kelasAktivitas[$baris['kelas']]['badge'] }}">{{ $kelasAktivitas[$baris['kelas']]['label'] }}</span>
          </div>
        @empty
          <p class="text-[11px] text-secondary">Belum ada temuan berkode grid dalam periode ini.</p>
        @endforelse
      </div>
    </div>

    <div class="bg-card-bg rounded-xl border border-border-subtle p-3.5 sm:p-4 shadow-sm flex flex-col md:col-span-2 xl:col-span-1">
      <h3 class="text-xs sm:text-sm font-bold text-slate-navy flex items-center gap-1.5">
        <span class="material-symbols-outlined text-sm text-alert-amber">schedule</span>
        Jam Temuan Burung
      </h3>
      <p class="text-[11px] text-secondary mt-0.5 mb-2">Total ekor per jam pengamatan (WIB).</p>
      <div class="relative w-full h-48 sm:h-52">
        <canvas id="hourlyBarChart" role="img" aria-label="Jumlah burung per jam"></canvas>
      </div>
      @if($statistik['jam']['tanpa_jam'] > 0)
        <p class="mt-2 text-[11px] text-amber-800 bg-amber-50 border border-amber-200 rounded-lg px-2.5 py-1.5">
          {{ $fmt($statistik['jam']['tanpa_jam']) }} dari {{ $fmt($kpi['burung']) }} ekor tercatat tanpa jam dan tidak ikut dihitung.
        </p>
      @endif
    </div>
  </div>

  <!-- ROW 3: Kejadian Bird Strike & Korelasi Traffic -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-3.5">

    <div class="bg-card-bg rounded-xl border border-border-subtle p-3.5 sm:p-4 shadow-sm flex flex-col">
      <div class="flex items-start justify-between gap-2">
        <div>
          <h3 class="text-xs sm:text-sm font-bold text-slate-navy flex items-center gap-1.5">
            <span class="material-symbols-outlined text-sm text-rose-600">flight_land</span>
            Kejadian Bird Strike
          </h3>
          <p class="text-[11px] text-secondary mt-0.5 mb-2">Seluruh histori ({{ $fmt($kejadian['total']) }} insiden) menurut fase penerbangan dan waktu.</p>
        </div>
        <a href="{{ route('admin.ai.histori.index') }}" class="text-[11px] font-semibold text-injourney-teal hover:underline shrink-0">Kelola histori</a>
      </div>
      @if($kejadian['total'] > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div class="relative w-full h-44"><canvas id="phaseBarChart" role="img" aria-label="Kejadian per fase penerbangan"></canvas></div>
          <div class="relative w-full h-44"><canvas id="timeBarChart" role="img" aria-label="Kejadian per waktu"></canvas></div>
        </div>
        @if($kejadian['total'] < 10)
          <p class="mt-2 text-[11px] text-secondary">Data insiden masih sedikit; pola belum representatif.</p>
        @endif
      @else
        <p class="my-auto text-center text-xs text-secondary py-10">Belum ada histori kejadian bird strike.</p>
      @endif
    </div>

    <div class="bg-card-bg rounded-xl border border-border-subtle p-3.5 sm:p-4 shadow-sm flex flex-col">
      <div class="flex items-start justify-between gap-2">
        <div>
          <h3 class="text-xs sm:text-sm font-bold text-slate-navy flex items-center gap-1.5">
            <span class="material-symbols-outlined text-sm text-injourney-teal">scatter_plot</span>
            Korelasi Traffic Pesawat vs Jumlah Burung
          </h3>
          <p class="text-[11px] text-secondary mt-0.5 mb-2">Per hari: total pergerakan pesawat (sumbu X) dan total ekor burung (sumbu Y).</p>
        </div>
        @if($korelasi['r'] !== null)
          <div class="text-right shrink-0">
            <div class="text-base font-bold text-slate-navy">r = {{ $fmt($korelasi['r'], 2) }}</div>
            <div class="text-[10px] text-secondary">{{ $tafsirKorelasi }} · n = {{ $korelasi['n'] }} hari</div>
          </div>
        @endif
      </div>
      @if($korelasi['n'] > 0)
        <div class="relative w-full h-52"><canvas id="correlationChart" role="img" aria-label="Sebaran traffic pesawat dan jumlah burung per hari"></canvas></div>
        @if(! $korelasi['cukup'])
          <p class="mt-2 text-[11px] text-amber-800 bg-amber-50 border border-amber-200 rounded-lg px-2.5 py-1.5">Data belum cukup: baru {{ $korelasi['n'] }} hari yang memiliki data traffic dan temuan burung (minimal {{ \App\Services\Statistics\BirdStatisticsService::MIN_CORRELATION_SAMPLES }} hari).</p>
        @endif
      @else
        <div class="my-auto text-center py-8">
          <p class="text-xs text-secondary">Belum ada hari yang memiliki data traffic pesawat sekaligus temuan burung.</p>
          <a href="{{ route('admin.ai.lalu-lintas.index') }}" class="inline-flex items-center gap-1 mt-2 text-[11px] font-semibold text-injourney-teal hover:underline">
            <span class="material-symbols-outlined text-xs">upload</span> Import data traffic
          </a>
        </div>
      @endif
    </div>
  </div>

</section>
