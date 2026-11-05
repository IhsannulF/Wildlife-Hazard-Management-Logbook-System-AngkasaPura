<!-- QUICK FILTER, SEARCH & AIRSIDE GRID TAG CONTROLS -->
<section class="bg-card-bg rounded-xl border border-border-subtle p-space-md shadow-sm space-y-space-md">
  <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-space-md">
    
    <!-- Status Tabs -->
    <div class="flex items-center gap-1.5 bg-surface-container-low p-1 rounded-lg border border-border-subtle self-start">
      <a href="{{ route('admin.verifikasi', array_filter(['sub' => request('sub'), 'per_page' => $perPage ?? request('per_page'), 'start_date' => $startDate ?? null, 'end_date' => $endDate ?? null, 'search' => $search ?? null])) }}" class="px-3.5 py-1.5 rounded-md font-label-lg text-label-lg transition-all {{ empty($statusFilter) ? 'bg-white text-injourney-teal font-bold shadow-sm' : 'text-secondary hover:text-on-surface' }}" id="tab-all">
        Semua Status <span class="ml-1 text-xs px-1.5 py-0.2 rounded-full bg-surface-container-high text-on-surface">{{ $totalLaporan }}</span>
      </a>
      <a href="{{ route('admin.verifikasi', array_filter(['status' => 'belum', 'sub' => request('sub'), 'per_page' => $perPage ?? request('per_page'), 'start_date' => $startDate ?? null, 'end_date' => $endDate ?? null, 'search' => $search ?? null])) }}" class="px-3.5 py-1.5 rounded-md font-label-lg text-label-lg transition-all {{ $statusFilter === 'belum' ? 'bg-white text-warning-rose font-bold shadow-sm' : 'text-secondary hover:text-on-surface' }}" id="tab-pending">
        Belum Ditangani <span class="ml-1 text-xs px-1.5 py-0.2 rounded-full bg-rose-100 text-warning-rose font-bold">{{ $belum }}</span>
      </a>
      <a href="{{ route('admin.verifikasi', array_filter(['status' => 'sudah', 'sub' => request('sub'), 'per_page' => $perPage ?? request('per_page'), 'start_date' => $startDate ?? null, 'end_date' => $endDate ?? null, 'search' => $search ?? null])) }}" class="px-3.5 py-1.5 rounded-md font-label-lg text-label-lg transition-all {{ $statusFilter === 'sudah' ? 'bg-white text-emerald-800 font-bold shadow-sm' : 'text-secondary hover:text-on-surface' }}" id="tab-resolved">
        Sudah Ditangani <span class="ml-1 text-xs px-1.5 py-0.2 rounded-full bg-emerald-100 text-emerald-800 font-bold">{{ $ditangani }}</span>
      </a>
    </div>

    <!-- Search Input Bar -->
    <div class="relative flex-1 max-w-xl">
      <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-lg pointer-events-none">search</span>
      <input class="w-full h-[42px] pl-10 pr-4 rounded-lg border border-border-subtle bg-white text-on-surface placeholder:text-outline font-body-md focus:border-injourney-teal focus:ring-2 focus:ring-injourney-teal/20 transition-all outline-none" id="searchInput" onkeyup="searchTable()" onkeydown="if(event.key === 'Enter') applyDateFilterServer()" value="{{ $search ?? '' }}" placeholder="Cari no. laporan, petugas, spesies satwa, atau koordinat grid..." type="text"/>
    </div>
  </div>

  <!-- Secondary Filters: Unit Kerja, Area Sisi Udara, Tanggal (Satu Baris) -->
  <div class="pt-space-sm border-t border-border-subtle flex flex-wrap items-center justify-between gap-space-sm">
    <div class="flex flex-wrap items-center gap-space-sm sm:gap-space-md text-sm">
      <!-- Unit Kerja -->
      <div class="flex items-center gap-1.5">
        <label class="font-label-md text-label-md text-secondary whitespace-nowrap">Unit Kerja:</label>
        <select id="filterUnit" onchange="searchTable()" class="h-9 px-2.5 py-1 text-xs font-label-md rounded-lg border border-border-subtle bg-white text-on-surface focus:border-injourney-teal focus:ring-0">
          <option value="">Semua Unit</option>
          <option value="AMC">AMC (Apron Movement Control)</option>
          <option value="PK">PK / ARFF (Airport Rescue)</option>
          <option value="SMS">Safety (SMS)</option>
          <option value="Security">Security (Avsec)</option>
        </select>
      </div>

      <!-- Area Inspeksi -->
      <div class="flex items-center gap-1.5">
        <label class="font-label-md text-label-md text-secondary whitespace-nowrap">Area Sisi Udara:</label>
        <select id="filterArea" onchange="searchTable()" class="h-9 px-2.5 py-1 text-xs font-label-md rounded-lg border border-border-subtle bg-white text-on-surface focus:border-injourney-teal focus:ring-0">
          <option value="">Semua Area Sisi Udara</option>
          <option value="Runway">Runway 07L / 25R</option>
          <option value="Taxiway">Taxiway Alpha &amp; Bravo</option>
          <option value="Apron">Apron Komersial</option>
          <option value="Perimeter">Perimeter Utara / Selatan</option>
          <option value="Kanal">Kanal Drainase Terbuka</option>
        </select>
      </div>

      <!-- Unified Single Date & Range Calendar -->
      @include('partials.date-range-picker')
    </div>

    <!-- Reset Filter Button -->
    <button class="text-xs text-injourney-teal hover:underline font-label-md flex items-center gap-1 cursor-pointer" onclick="resetFilters()" type="button" title="Reset semua filter">
      <span class="material-symbols-outlined text-[15px]">refresh</span>
      <span>Reset Filter</span>
    </button>
  </div>
</section>
