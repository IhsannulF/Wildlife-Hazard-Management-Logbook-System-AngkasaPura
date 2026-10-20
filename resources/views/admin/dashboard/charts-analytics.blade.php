<!-- ================= MULTI-CHART VISUAL ANALYTICS SUITE ================= -->
<section class="space-y-3.5">
  
  <!-- Visual Analytics Header Strip -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-1 border-b border-border-subtle">
    <div class="flex items-center gap-2">
      <div class="w-7 h-7 rounded-lg bg-cyan-50 border border-cyan-200 flex items-center justify-center text-injourney-teal">
        <span class="material-symbols-outlined text-base">insights</span>
      </div>
      <div>
        <h2 class="text-sm sm:text-base font-bold text-slate-navy">
          Visualisasi Analitik &amp; Tren Bahaya Satwa Sisi Udara
        </h2>
        <p class="text-[11px] text-secondary">
          Pemantauan pola temporal, komposisi taksonomi, jam aktivitas, dan sebaran zona pergerakan pesawat.
        </p>
      </div>
    </div>

    <div class="flex items-center gap-2 self-start sm:self-center">
      <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px] font-bold">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
        Telemetri Terpadu Live
      </span>
      <a href="{{ route('admin.statistik') }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-border-subtle bg-white hover:bg-surface-container-low text-[11px] font-semibold text-injourney-teal transition-colors shadow-2xs">
        <span>Buka Peta Grid Heatmap</span>
        <span class="material-symbols-outlined text-xs">arrow_forward</span>
      </a>
    </div>
  </div>

  <!-- ROW 1: TREN TEMPORAL BULANAN (8 Cols) & KOMPOSISI TAKSONOMI (4 Cols) -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-3.5 items-stretch">
    
    <!-- CHART 1: LINE & AREA CHART - FLUKTUASI TEMUAN SATWA & DISPERSAL -->
    <div class="lg:col-span-8 bg-card-bg rounded-xl border border-border-subtle p-3.5 sm:p-4 shadow-sm flex flex-col justify-between">
      <div class="flex flex-wrap items-center justify-between gap-2 mb-2.5">
        <div>
          <div class="flex items-center gap-2">
            <h3 class="text-xs sm:text-sm font-bold text-slate-navy flex items-center gap-1.5">
              <span class="material-symbols-outlined text-sm text-injourney-teal">trending_up</span>
              Tren Frekuensi Temuan Satwa &amp; Efektivitas Mitigasi
            </h3>
            <span class="text-[10px] px-1.5 py-0.5 rounded bg-surface-container font-semibold text-secondary">12 Bulan</span>
          </div>
          <p class="text-[11px] text-secondary mt-0.5">
            Pola fluktuasi temuan satwa liar per bulan vs aksi pengusiran AMC (Tahun {{ date('Y') }}).
          </p>
        </div>

        <!-- Custom Metric Pills -->
        <div class="flex items-center gap-1.5">
          <div class="flex items-center gap-1 text-[10px] bg-cyan-50 text-cyan-800 px-2 py-0.5 rounded-md border border-cyan-200 font-bold">
            <span>Rerata:</span>
            <span class="font-code-coordinate text-injourney-teal font-bold">19.3 / bln</span>
          </div>
          <div class="flex items-center gap-1 text-[10px] bg-emerald-50 text-emerald-800 px-2 py-0.5 rounded-md border border-emerald-200 font-bold">
            <span>Mitigasi:</span>
            <span class="font-code-coordinate text-resolved-emerald font-bold">94.8%</span>
          </div>
        </div>
      </div>

      <!-- Canvas Container -->
      <div class="relative w-full h-56 sm:h-64">
        <canvas id="trendChart"></canvas>
      </div>

      <!-- Legend Footer -->
      <div class="mt-2.5 pt-2 border-t border-border-subtle flex flex-wrap items-center justify-between gap-2 text-[11px] text-secondary">
        <div class="flex items-center gap-4 flex-wrap">
          <div class="flex items-center gap-1.5">
            <span class="w-3 h-1.5 rounded-sm bg-[#00A3B4]"></span>
            <span class="text-slate-800 font-medium">Total Temuan</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="w-3 h-1.5 rounded-sm bg-[#10B981]"></span>
            <span class="text-slate-800 font-medium">Berhasil Ditangani</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="w-3 h-1.5 rounded-sm bg-[#EF4444]"></span>
            <span class="text-slate-800 font-medium">Kejadian Kritis</span>
          </div>
        </div>
        <div class="text-[10px] text-secondary font-medium">
          <span>⚠️ Puncak Aktivitas: Q4 (Migrasi Burung Air &amp; Curah Hujan Tinggi)</span>
        </div>
      </div>
    </div>

    <!-- CHART 2: INTERACTIVE DOUGHNUT CHART - KOMPOSISI TAKSONOMI -->
    <div class="lg:col-span-4 bg-card-bg rounded-xl border border-border-subtle p-3.5 sm:p-4 shadow-sm flex flex-col justify-between">
      <div class="flex items-center justify-between mb-1">
        <h3 class="text-xs sm:text-sm font-bold text-slate-navy flex items-center gap-1.5">
          <span class="material-symbols-outlined text-sm text-injourney-teal">donut_large</span>
          Komposisi Taksonomi Satwa
        </h3>
        <span class="text-[10px] px-1.5 py-0.5 rounded bg-surface-container font-semibold text-secondary">Klasifikasi ICAO</span>
      </div>
      <p class="text-[11px] text-secondary mb-2">
        Perbandingan proporsi fauna berisiko di perimeter sisi udara.
      </p>

      <!-- Doughnut with Center Total Overlay -->
      <div class="relative flex items-center justify-center my-auto py-1">
        <div class="w-44 h-44 sm:w-48 sm:h-48 relative">
          <canvas id="categoryDonutChart"></canvas>
          <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
            <span class="text-lg sm:text-xl font-bold text-slate-navy">{{ $totalLaporan > 0 ? $totalLaporan : 199 }}</span>
            <span class="text-[10px] text-secondary uppercase font-semibold tracking-wider">Total Laporan</span>
          </div>
        </div>
      </div>

      <!-- Bottom Breakdown Chips -->
      <div class="mt-2.5 pt-2 border-t border-border-subtle grid grid-cols-3 gap-1.5 text-center">
        <div class="bg-surface-container-low p-1.5 rounded-lg border border-border-subtle">
          <span class="block text-[10px] text-secondary font-medium">Avian (Burung)</span>
          <span class="text-xs font-bold text-injourney-teal">{{ $pctBurung }}%</span>
          <span class="block text-[9px] text-secondary">{{ $categoryData['counts'][0] ?? 124 }} Log</span>
        </div>
        <div class="bg-surface-container-low p-1.5 rounded-lg border border-border-subtle">
          <span class="block text-[10px] text-secondary font-medium">Reptil</span>
          <span class="text-xs font-bold text-alert-amber">{{ $pctReptil }}%</span>
          <span class="block text-[9px] text-secondary">{{ $categoryData['counts'][1] ?? 48 }} Log</span>
        </div>
        <div class="bg-surface-container-low p-1.5 rounded-lg border border-border-subtle">
          <span class="block text-[10px] text-secondary font-medium">Mamalia</span>
          <span class="text-xs font-bold text-indigo-500">{{ $pctMamalia }}%</span>
          <span class="block text-[9px] text-secondary">{{ $categoryData['counts'][2] ?? 27 }} Log</span>
        </div>
      </div>
    </div>

  </div>

  <!-- ROW 2: 3 SPECIALIZED OPERATIONAL CHARTS -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
    
    <!-- CHART 3: HORIZONTAL BAR CHART - TOP 5 SPESIES PALING SERING TERIDENTIFIKASI -->
    <div class="bg-card-bg rounded-xl border border-border-subtle p-3.5 sm:p-4 shadow-sm flex flex-col justify-between">
      <div class="flex items-center justify-between mb-1">
        <h3 class="text-xs sm:text-sm font-bold text-slate-navy flex items-center gap-1.5">
          <span class="material-symbols-outlined text-sm text-warning-rose">bar_chart</span>
          Top 5 Spesies Bahaya Satwa
        </h3>
        <span class="text-[10px] px-1.5 py-0.5 rounded bg-red-100 text-red-800 font-bold">Risiko Operasional</span>
      </div>
      <p class="text-[11px] text-secondary mb-2">
        Paling sering teridentifikasi di jalur runway &amp; taxiway.
      </p>

      <div class="relative w-full h-48 sm:h-52">
        <canvas id="speciesBarChart"></canvas>
      </div>

      <div class="mt-2 pt-2 border-t border-border-subtle flex items-center justify-between text-[10px] text-secondary">
        <span>Biawak &amp; Blekok mendominasi 42% laporan</span>
        <span class="font-code-coordinate text-injourney-teal font-semibold">Bahu RWY 25R</span>
      </div>
    </div>

    <!-- CHART 4: VERTICAL BAR CHART - DISTRIBUSI JAM AKTIVITAS (SHIFT PATROLI) -->
    <div class="bg-card-bg rounded-xl border border-border-subtle p-3.5 sm:p-4 shadow-sm flex flex-col justify-between">
      <div class="flex items-center justify-between mb-1">
        <h3 class="text-xs sm:text-sm font-bold text-slate-navy flex items-center gap-1.5">
          <span class="material-symbols-outlined text-sm text-alert-amber">schedule</span>
          Distribusi Waktu Kemunculan
        </h3>
        <span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-100 text-amber-900 font-bold">Shift AMC</span>
      </div>
      <p class="text-[11px] text-secondary mb-2">
        Frekuensi kemunculan berdasarkan jendela jam operasional.
      </p>

      <div class="relative w-full h-48 sm:h-52">
        <canvas id="hourlyBarChart"></canvas>
      </div>

      <div class="mt-2 pt-2 border-t border-border-subtle flex items-center justify-between text-[10px] text-secondary">
        <span class="text-amber-800 font-medium">⚠️ Jam Siaga: 06-10 WIB &amp; 14-18 WIB</span>
        <span class="font-code-coordinate font-bold text-slate-navy">4 Shift</span>
      </div>
    </div>

    <!-- CHART 5: POLAR AREA / RADAR CHART - SEBARAN ZONA RISIKO SISI UDARA -->
    <div class="bg-card-bg rounded-xl border border-border-subtle p-3.5 sm:p-4 shadow-sm flex flex-col justify-between md:col-span-2 lg:col-span-1">
      <div class="flex items-center justify-between mb-1">
        <h3 class="text-xs sm:text-sm font-bold text-slate-navy flex items-center gap-1.5">
          <span class="material-symbols-outlined text-sm text-injourney-teal">radar</span>
          Sebaran Zona Operasional
        </h3>
        <span class="text-[10px] px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-900 font-bold">ICAO Buffer</span>
      </div>
      <p class="text-[11px] text-secondary mb-2">
        Konsentrasi temuan per sektor runway, taxiway, &amp; drainase.
      </p>

      <div class="relative w-full h-48 sm:h-52">
        <canvas id="zoneRadarChart"></canvas>
      </div>

      <div class="mt-2 pt-2 border-t border-border-subtle flex items-center justify-between text-[10px] text-secondary">
        <span class="text-slate-navy font-semibold">Runway 07L/25R: 40% Temuan</span>
        <span class="text-rose-600 font-bold">Zona Kritis 1</span>
      </div>
    </div>

  </div>

</section>
