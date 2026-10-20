<!-- ================= QUICK OPERATIONAL ACTION / SUMMARY CARDS (Dashboard Utama) ================= -->
<section class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
  
  <!-- Card 1: Verifikasi Berita Acara (BA) -->
  <div class="bg-card-bg rounded-xl border border-border-subtle p-4 shadow-sm flex flex-col justify-between hover:border-injourney-teal/60 transition-all">
    <div class="space-y-2">
      <div class="flex items-center justify-between">
        <div class="w-9 h-9 rounded-lg bg-cyan-50 border border-cyan-200 flex items-center justify-center text-[#007fa3]">
          <span class="material-symbols-outlined text-xl">fact_check</span>
        </div>
        @if($belum > 0)
          <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-warning-rose border border-rose-200 flex items-center gap-1">
            <span class="w-1.5 h-1.5 rounded-full bg-warning-rose animate-ping"></span>
            {{ $belum }} Menunggu Verifikasi
          </span>
        @else
          <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            Semua Terverifikasi
          </span>
        @endif
      </div>
      <div>
        <h3 class="text-xs sm:text-sm font-bold text-slate-800">Verifikasi Berita Acara (BA)</h3>
        <p class="text-[11px] text-secondary mt-0.5">
          Kelola antrean validasi laporan kejadian satwa liar sisi udara dan terbitkan dokumen Berita Acara resmi ICAO / DKPPU.
        </p>
      </div>
    </div>
    <div class="mt-4 pt-3 border-t border-border-subtle flex items-center justify-between">
      <span class="text-[11px] text-secondary font-medium">{{ $totalLaporan }} Total Register Laporan</span>
      <a href="{{ route('admin.dashboard', ['tab' => 'verifikasi']) }}" class="inline-flex items-center gap-1 text-xs font-bold text-injourney-teal hover:underline group">
        <span>Buka Verifikasi BA</span>
        <span class="material-symbols-outlined text-sm transition-transform group-hover:translate-x-0.5">arrow_forward</span>
      </a>
    </div>
  </div>

  <!-- Card 2: Peta Sebaran Grid Runway -->
  <div class="bg-card-bg rounded-xl border border-border-subtle p-4 shadow-sm flex flex-col justify-between hover:border-injourney-teal/60 transition-all">
    <div class="space-y-2">
      <div class="flex items-center justify-between">
        <div class="w-9 h-9 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-700">
          <span class="material-symbols-outlined text-xl">grid_view</span>
        </div>
        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-alert-amber border border-amber-200">
          Hotspot: {{ $hotspotGrid }}
        </span>
      </div>
      <div>
        <h3 class="text-xs sm:text-sm font-bold text-slate-800">Peta Sebaran Grid Runway</h3>
        <p class="text-[11px] text-secondary mt-0.5">
          Analisis spasial konsentrasi satwa di sepanjang runway 07L/25R, taxiway, kanal terbuka, dan perimeter bandara.
        </p>
      </div>
    </div>
    <div class="mt-4 pt-3 border-t border-border-subtle flex items-center justify-between">
      <span class="text-[11px] text-secondary font-medium">Matriks Risiko Spasial ICAO</span>
      <a href="{{ route('admin.statistik') }}" class="inline-flex items-center gap-1 text-xs font-bold text-injourney-teal hover:underline group">
        <span>Buka Peta Grid</span>
        <span class="material-symbols-outlined text-sm transition-transform group-hover:translate-x-0.5">arrow_forward</span>
      </a>
    </div>
  </div>

  <!-- Card 3: Katalog Satwa & Rekap DKPPU -->
  <div class="bg-card-bg rounded-xl border border-border-subtle p-4 shadow-sm flex flex-col justify-between hover:border-injourney-teal/60 transition-all">
    <div class="space-y-2">
      <div class="flex items-center justify-between">
        <div class="w-9 h-9 rounded-lg bg-purple-50 border border-purple-200 flex items-center justify-center text-purple-700">
          <span class="material-symbols-outlined text-xl">pets</span>
        </div>
        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
          {{ $totalSatwa ?? 0 }} Spesies Terdata
        </span>
      </div>
      <div>
        <h3 class="text-xs sm:text-sm font-bold text-slate-800">Katalog Satwa &amp; Rekap DKPPU</h3>
        <p class="text-[11px] text-secondary mt-0.5">
          Kelola master data taksonomi satwa atau unduh rekap berkala logbook satwa format spreadsheet resmi DKPPU.
        </p>
      </div>
    </div>
    <div class="mt-4 pt-3 border-t border-border-subtle flex items-center justify-between">
      <a href="{{ route('admin.export.excel') }}" class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-600 hover:text-slate-900 transition-colors">
        <span class="material-symbols-outlined text-xs">file_download</span>
        <span>Export Excel</span>
      </a>
      <a href="{{ route('admin.manajemen') }}" class="inline-flex items-center gap-1 text-xs font-bold text-injourney-teal hover:underline group">
        <span>Katalog Master</span>
        <span class="material-symbols-outlined text-sm transition-transform group-hover:translate-x-0.5">arrow_forward</span>
      </a>
    </div>
  </div>

</section>
