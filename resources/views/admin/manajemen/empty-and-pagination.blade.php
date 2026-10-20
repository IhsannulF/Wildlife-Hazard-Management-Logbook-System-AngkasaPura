<!-- EMPTY SEARCH STATE & DYNAMIC 2-ROW PAGINATION -->
<div id="catalogEmptyState" class="hidden bg-card-bg border border-border-subtle rounded-xl p-8 text-center space-y-3">
  <div class="w-12 h-12 rounded-full bg-surface-container flex items-center justify-center text-secondary mx-auto">
    <span class="material-symbols-outlined text-2xl">search_off</span>
  </div>
  <h4 class="text-sm font-bold text-slate-navy">Tidak Ada Satwa Liar Ditemukan</h4>
  <p class="text-xs text-secondary max-w-sm mx-auto">Tidak ada spesies yang sesuai dengan kata kunci pencarian atau kombinasi filter saat ini.</p>
  <button type="button" onclick="resetCatalogFilters()" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-injourney-teal text-white text-xs font-semibold hover:bg-injourney-dark-teal transition-all shadow-2xs cursor-pointer">
    <span class="material-symbols-outlined text-sm">restart_alt</span>
    Reset Semua Filter
  </button>
</div>

<!-- DYNAMIC 2-ROW PAGINATION BAR -->
<div id="catalogPaginationContainer" class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 pb-1 border-t border-border-subtle">
  <div class="text-xs text-secondary font-medium">
    Menampilkan <span id="paginationRangeText" class="font-bold text-slate-navy">1 - 6</span> dari <span id="paginationTotalText" class="font-bold text-slate-navy">{{ $totalSpesies }}</span> spesies satwa <span class="text-slate-400 font-normal">(maksimal 2 baris)</span>
  </div>

  <div class="flex items-center gap-1.5">
    <button type="button" id="prevPageBtn" onclick="changeCatalogPage(-1)" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-border-subtle bg-white text-xs font-semibold text-slate-navy hover:bg-surface-container hover:text-injourney-teal transition-all disabled:opacity-35 disabled:cursor-not-allowed disabled:pointer-events-none shadow-2xs cursor-pointer" title="Halaman Sebelumnya">
      <span class="material-symbols-outlined text-base">chevron_left</span>
      <span class="hidden sm:inline">Sebelumnya</span>
    </button>

    <div id="pageNumberButtons" class="flex items-center gap-1">
      <!-- Dynamic Page Numbers -->
    </div>

    <button type="button" id="nextPageBtn" onclick="changeCatalogPage(1)" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-border-subtle bg-white text-xs font-semibold text-slate-navy hover:bg-surface-container hover:text-injourney-teal transition-all disabled:opacity-35 disabled:cursor-not-allowed disabled:pointer-events-none shadow-2xs cursor-pointer" title="Halaman Selanjutnya">
      <span class="hidden sm:inline">Selanjutnya</span>
      <span class="material-symbols-outlined text-base">chevron_right</span>
    </button>
  </div>
</div>
