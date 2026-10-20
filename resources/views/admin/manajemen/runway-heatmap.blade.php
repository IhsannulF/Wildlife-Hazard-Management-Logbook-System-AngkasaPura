<!-- AIRSIDE RUNWAY GRID MAP TELEMETRY PREVIEW -->
<section class="bg-card-bg border border-border-subtle rounded-xl p-5 shadow-sm">
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-border-subtle">
    <div>
      <h2 class="text-base font-bold text-slate-navy flex items-center gap-2">
        <span class="material-symbols-outlined text-injourney-teal">grid_4x4</span>
        Matriks Sebaran Spasial Runway &amp; Perimeter (Airside Hazard Heatmap)
      </h2>
      <p class="text-xs text-secondary mt-0.5">
        Integrasi zonasi grid operasi ICAO Annex 14 dengan konsentrasi populasi satwa liar aktif
      </p>
    </div>
    <div class="flex flex-wrap items-center gap-3 text-xs">
      <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-rose-500"></span> Zona Kritis (K-10, F-5)</span>
      <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-amber-400"></span> Zona Sedang (D-9, E-12)</span>
      <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-injourney-teal"></span> Terpantau Aman</span>
    </div>
  </div>

  <!-- Compact Visual Grid representation -->
  <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 xl:grid-cols-12 gap-2 mt-4 text-center font-code-coordinate text-xs font-bold">
    <div class="p-2 sm:p-2.5 rounded border border-border-subtle bg-surface-container-low text-secondary truncate" title="A-1">A-1</div>
    <div class="p-2 sm:p-2.5 rounded border border-injourney-teal bg-cyan-50 text-injourney-teal truncate" title="A-2 (Apron)">A-2 (Apron)</div>
    <div class="p-2 sm:p-2.5 rounded border border-border-subtle bg-surface-container-low text-secondary truncate" title="B-3">B-3</div>
    <div class="p-2 sm:p-2.5 rounded border border-rose-300 bg-red-50 text-rose-700 truncate" title="B-4 (Anjing)">B-4 (Anjing)</div>
    <div class="p-2 sm:p-2.5 rounded border border-border-subtle bg-surface-container-low text-secondary truncate" title="C-5">C-5</div>
    <div class="p-2 sm:p-2.5 rounded border border-rose-300 bg-red-50 text-rose-700 truncate" title="C-6 (Sanca)">C-6 (Sanca)</div>
    <div class="p-2 sm:p-2.5 rounded border border-amber-300 bg-amber-50 text-amber-700 truncate" title="D-9 (Blekok)">D-9 (Blekok)</div>
    <div class="p-2 sm:p-2.5 rounded border border-border-subtle bg-surface-container-low text-secondary truncate" title="E-10">E-10</div>
    <div class="p-2 sm:p-2.5 rounded border border-amber-300 bg-amber-50 text-amber-700 truncate" title="E-12 (Kuntul)">E-12 (Kuntul)</div>
    <div class="p-2 sm:p-2.5 rounded border border-rose-400 bg-red-100 text-rose-800 font-extrabold shadow-xs truncate" title="F-5 (Kera)">F-5 (Kera)</div>
    <div class="p-2 sm:p-2.5 rounded border border-rose-400 bg-red-100 text-rose-800 font-extrabold shadow-xs ring-2 ring-rose-400/40 truncate" title="K-10 (Biawak)">K-10 (Biawak)</div>
    <div class="p-2 sm:p-2.5 rounded border border-border-subtle bg-surface-container-low text-secondary truncate" title="Z-20">Z-20</div>
  </div>
</section>
