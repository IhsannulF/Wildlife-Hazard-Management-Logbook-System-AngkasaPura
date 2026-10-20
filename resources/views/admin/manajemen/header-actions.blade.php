<!-- BREADCRUMB, PAGE TITLE & ACTION CLUSTER SECTION -->
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 pb-3 border-b border-border-subtle">
  <!-- Page Title & Short Purpose -->
  <div class="min-w-0">
    <h1 class="text-base sm:text-lg font-bold text-on-surface tracking-tight">
      Katalog Master Satwa Liar Sisi Udara
    </h1>
    <h2 class="text-[11px] text-secondary font-normal mt-0.5">
      Pusat pengelolaan master data spesies, tingkat risiko satwa, dan konfigurasi sistem.
    </h2>
  </div>

  <!-- Action Cluster Bar (Single Row) -->
  <div class="flex items-center flex-nowrap gap-2 shrink-0 self-start lg:self-center overflow-x-auto max-w-full">
    <a href="{{ route('admin.export.excel') }}" class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 sm:py-2 text-xs font-semibold rounded-lg border border-border-subtle bg-white text-slate-navy hover:bg-surface-container-low transition-colors shadow-2xs shrink-0 whitespace-nowrap">
      <span class="material-symbols-outlined text-sm">download</span>
      <span>Export Excel</span>
    </a>
    <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 sm:py-2 text-xs font-semibold rounded-lg border border-border-subtle bg-white text-slate-navy hover:bg-surface-container-low transition-colors shadow-2xs shrink-0 whitespace-nowrap">
      <span class="material-symbols-outlined text-sm">print</span>
      <span>Cetak PDF</span>
    </button>
    <button type="button" onclick="openModal('addSpeciesModal')" class="inline-flex items-center gap-1 px-3 py-1.5 sm:py-2 text-xs font-bold rounded-lg bg-injourney-teal hover:bg-injourney-dark-teal text-white shadow-xs transition-all active:scale-[0.98] shrink-0 whitespace-nowrap">
      <span class="material-symbols-outlined text-sm">add</span>
      <span>+ Tambah Spesies</span>
    </button>
  </div>
</div>

<!-- Alert Notifications -->
@if(session('sukses'))
  <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-xs font-semibold flex items-center justify-between">
    <span class="flex items-center gap-2">
      <span class="material-symbols-outlined text-sm text-resolved-emerald">check_circle</span>
      {{ session('sukses') }}
    </span>
    <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold">&times;</button>
  </div>
@endif
@if(session('error'))
  <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-xs font-semibold flex items-center justify-between">
    <span class="flex items-center gap-2">
      <span class="material-symbols-outlined text-sm text-warning-rose">error</span>
      {{ session('error') }}
    </span>
    <button onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 font-bold">&times;</button>
  </div>
@endif
