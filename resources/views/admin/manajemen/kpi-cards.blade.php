<!-- SUMMARY METRIC CARDS (ICAO AVIATION STAT CARDS) -->
<section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3.5">
  
  <!-- Card 1: Total Spesies -->
  <div class="bg-card-bg border border-border-subtle rounded-xl p-3.5 shadow-sm relative overflow-hidden flex flex-col justify-between">
    <div class="absolute top-0 left-0 right-0 h-1 bg-injourney-teal"></div>
    <div class="flex items-center justify-between mb-1.5">
      <span class="text-[10px] sm:text-[11px] uppercase text-secondary font-bold tracking-wider">Total Spesies Terdaftar</span>
      <div class="w-8 h-8 rounded-lg bg-surface-container flex items-center justify-center text-injourney-teal">
        <span class="material-symbols-outlined text-base">database</span>
      </div>
    </div>
    <div class="flex items-baseline gap-2">
      <span class="text-lg sm:text-xl font-bold text-slate-navy">{{ $totalSpesies }}</span>
      <span class="text-[11px] text-secondary font-medium">Spesies Terdata</span>
    </div>
    <div class="mt-2.5 pt-2 border-t border-border-subtle flex items-center justify-between text-[11px] text-secondary">
      <span>Avian: {{ $avianCount }} | Reptil: {{ $reptilCount }} | Mamalia: {{ $mamaliaCount }}</span>
      <span class="text-resolved-emerald font-semibold">100% Aktif</span>
    </div>
  </div>

  <!-- Card 2: Kategori Kritis -->
  <div class="bg-card-bg border border-border-subtle rounded-xl p-3.5 shadow-sm relative overflow-hidden flex flex-col justify-between">
    <div class="absolute top-0 left-0 right-0 h-1 bg-warning-rose"></div>
    <div class="flex items-center justify-between mb-1.5">
      <span class="text-[10px] sm:text-[11px] uppercase text-secondary font-bold tracking-wider">Spesies Risiko Kritis</span>
      <div class="w-8 h-8 rounded-lg bg-error-container/50 flex items-center justify-center text-warning-rose">
        <span class="material-symbols-outlined text-base badge-pulse">warning</span>
      </div>
    </div>
    <div class="flex items-baseline gap-2">
      <span class="text-lg sm:text-xl font-bold text-slate-navy">{{ $kritisCount }}</span>
      <span class="text-[10px] px-2 py-0.5 rounded-full bg-red-100 text-red-800 font-bold">Kategori 4 - Tinggi</span>
    </div>
    <div class="mt-2.5 pt-2 border-t border-border-subtle flex items-center text-[11px] text-secondary truncate" title="Biawak Air, Kera, Anjing Liar, Burung Elang Tikus, Ular Sanca">
      <span class="truncate">Biawak Air, Kera, Anjing Liar, Burung Elang...</span>
    </div>
  </div>

  <!-- Card 3: Kategori Risiko Sedang -->
  <div class="bg-card-bg border border-border-subtle rounded-xl p-3.5 shadow-sm relative overflow-hidden flex flex-col justify-between">
    <div class="absolute top-0 left-0 right-0 h-1 bg-alert-amber"></div>
    <div class="flex items-center justify-between mb-1.5">
      <span class="text-[10px] sm:text-[11px] uppercase text-secondary font-bold tracking-wider">Kategori Risiko Sedang</span>
      <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-alert-amber">
        <span class="material-symbols-outlined text-base">flight_takeoff</span>
      </div>
    </div>
    <div class="flex items-baseline gap-2">
      <span class="text-lg sm:text-xl font-bold text-slate-navy">{{ $sedangCount }}</span>
      <span class="text-[11px] text-secondary font-medium">Spesies (Flocking Threat)</span>
    </div>
    <div class="mt-2.5 pt-2 border-t border-border-subtle flex items-center text-[11px] text-secondary">
      <span>Mayoritas: Kawanan Burung Air / Wading</span>
    </div>
  </div>

  <!-- Card 4: Frekuensi Temuan Tertinggi -->
  <div class="bg-card-bg border border-border-subtle rounded-xl p-3.5 shadow-sm relative overflow-hidden flex flex-col justify-between">
    <div class="absolute top-0 left-0 right-0 h-1 bg-injourney-dark-teal"></div>
    <div class="flex items-center justify-between mb-1.5">
      <span class="text-[10px] sm:text-[11px] uppercase text-secondary font-bold tracking-wider">Frekuensi Temuan Tertinggi</span>
      <div class="w-8 h-8 rounded-lg bg-cyan-50 flex items-center justify-center text-injourney-teal">
        <span class="material-symbols-outlined text-base">near_me</span>
      </div>
    </div>
    <div class="flex items-baseline gap-1.5 truncate">
      <span class="text-base sm:text-lg font-bold text-slate-navy truncate">Biawak &amp; Blekok</span>
    </div>
    <div class="mt-2.5 pt-2 border-t border-border-subtle flex items-center justify-between text-[11px] text-secondary">
      <span class="text-slate-navy font-medium">Hotspot:</span>
      <div class="flex gap-1.5">
        <span class="px-1.5 py-0.5 rounded bg-surface-container font-code-coordinate text-injourney-teal font-bold text-[10px]">Grid K-10</span>
        <span class="px-1.5 py-0.5 rounded bg-surface-container font-code-coordinate text-injourney-teal font-bold text-[10px]">Grid D-9</span>
      </div>
    </div>
  </div>

</section>
