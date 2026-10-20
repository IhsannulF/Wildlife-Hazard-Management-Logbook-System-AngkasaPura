<!-- FILTER & CONTROLS TOOLBAR -->
<section class="bg-card-bg border border-border-subtle rounded-xl p-4 shadow-sm space-y-4">
  <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
    
    <!-- Search Input -->
    <div class="relative flex-1 max-w-xl">
      <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-secondary text-sm">search</span>
      <input class="w-full pl-9 pr-4 py-2 bg-white border border-border-subtle rounded-lg text-xs font-medium text-slate-navy placeholder:text-secondary/60 focus:border-injourney-teal focus:ring-2 focus:ring-injourney-teal/20 transition-all outline-none" id="speciesSearch" onkeyup="filterCatalog()" placeholder="Cari nama satwa, klasifikasi, tingkat risiko, atau zona habitat..." type="text"/>
    </div>

    <!-- Filter Selects & View Toggles -->
    <div class="flex flex-wrap items-center gap-2.5">
      <!-- Filter Tingkat Risiko -->
      <div class="relative min-w-[170px]">
        <select class="w-full appearance-none bg-white border border-border-subtle rounded-lg px-3 py-2 text-xs font-semibold text-slate-navy focus:border-injourney-teal outline-none pr-8 cursor-pointer" id="riskFilter" onchange="filterCatalog()">
          <option value="all">Semua Tingkat Risiko</option>
          <option value="kritis">Risiko Kritis (Kategori 4)</option>
          <option value="sedang">Risiko Sedang (Kategori 3)</option>
          <option value="rendah">Risiko Rendah (Kategori 2)</option>
        </select>
        <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-secondary pointer-events-none text-sm">expand_more</span>
      </div>

      <!-- Filter Zona Habitat -->
      <div class="relative min-w-[180px]">
        <select class="w-full appearance-none bg-white border border-border-subtle rounded-lg px-3 py-2 text-xs font-semibold text-slate-navy focus:border-injourney-teal outline-none pr-8 cursor-pointer" id="habitatFilter" onchange="filterCatalog()">
          <option value="all">Semua Zona Habitat</option>
          <option value="runway">Runway / Strip Buffer</option>
          <option value="perimeter">Perimeter &amp; Kanal Saluran</option>
          <option value="apron">Area Apron / Taxiway</option>
        </select>
        <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-secondary pointer-events-none text-sm">expand_more</span>
      </div>

      <!-- Layout View Switcher -->
      <div class="flex items-center bg-surface-container rounded-lg p-0.5 border border-border-subtle ml-auto lg:ml-0">
        <button type="button" class="p-1.5 rounded-md bg-white text-injourney-teal shadow-xs transition-all cursor-pointer" id="viewGridBtn" onclick="setViewMode('grid')" title="Tampilan Kartu Grid">
          <span class="material-symbols-outlined text-base block">grid_view</span>
        </button>
        <button type="button" class="p-1.5 rounded-md text-secondary hover:text-slate-navy transition-all cursor-pointer" id="viewTableBtn" onclick="setViewMode('table')" title="Tampilan Tabel Detail">
          <span class="material-symbols-outlined text-base block">table_rows</span>
        </button>
      </div>

    </div>
  </div>

  <!-- Category Filter Chips -->
  <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs font-semibold">
    <span class="text-secondary mr-1 hidden sm:inline">Kategori:</span>
    <button type="button" class="category-chip px-3 py-1.5 rounded-lg bg-injourney-teal text-white transition-all shadow-xs flex items-center gap-1.5 cursor-pointer" onclick="setCategoryFilter('all', this)">
      <span class="material-symbols-outlined text-sm">apps</span>
      Semua Satwa ({{ $totalSpesies }})
    </button>
    <button type="button" class="category-chip px-3 py-1.5 rounded-lg bg-surface-container text-slate-navy hover:bg-surface-container-high transition-all flex items-center gap-1.5 cursor-pointer" onclick="setCategoryFilter('burung', this)">
      <span class="material-symbols-outlined text-sm">flight</span>
      Burung / Avian ({{ $avianCount }})
    </button>
    <button type="button" class="category-chip px-3 py-1.5 rounded-lg bg-surface-container text-slate-navy hover:bg-surface-container-high transition-all flex items-center gap-1.5 cursor-pointer" onclick="setCategoryFilter('reptil', this)">
      <span class="material-symbols-outlined text-sm">pest_control</span>
      Reptil ({{ $reptilCount }})
    </button>
    <button type="button" class="category-chip px-3 py-1.5 rounded-lg bg-surface-container text-slate-navy hover:bg-surface-container-high transition-all flex items-center gap-1.5 cursor-pointer" onclick="setCategoryFilter('mamalia', this)">
      <span class="material-symbols-outlined text-sm">pets</span>
      Mamalia ({{ $mamaliaCount }})
    </button>
  </div>
</section>
