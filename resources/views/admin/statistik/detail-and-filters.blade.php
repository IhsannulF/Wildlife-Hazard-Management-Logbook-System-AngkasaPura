<!-- 3. DETAIL & AUDIT ROW BELOW WIDE AIRSIDE MAP: DETAIL GRID TERPILIH & FILTER CEPAT (SPAN 2 COLUMNS) -->
<div id="detailCard" class="lg:col-span-2 bg-card-bg rounded-xl border-2 border-warning-rose/30 p-3.5 sm:p-4 shadow-sm relative overflow-hidden transition-all duration-200 flex flex-col justify-between">
  <div id="detailTopBar" class="absolute top-0 left-0 right-0 h-1 bg-warning-rose"></div>
  
  <div class="grid grid-cols-1 md:grid-cols-12 gap-5 h-full">
    
    <!-- LEFT SUB-PANEL: DETAIL GRID TERPILIH (MD:COL-SPAN-7) -->
    <div class="md:col-span-7 flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between mb-2">
          <div class="flex items-center gap-2">
            <span id="detailGridBadge" class="font-code-coordinate text-xs font-bold bg-error-container text-error px-2 py-0.5 rounded">
              GRID {{ $selectedGrid['grid'] ?? 'K-10' }}
            </span>
            <span id="detailRiskBadge" class="text-[10px] px-2 py-0.5 rounded-full bg-red-100 text-red-800 font-bold">
              {{ $selectedGrid['risk_category'] ?? 'Kategori 4 - Kritis' }}
            </span>
          </div>
          <span class="text-[11px] text-secondary font-medium">Terpilih Aktif</span>
        </div>

        <h2 id="detailZoneName" class="text-sm sm:text-base font-bold text-slate-navy">
          {{ $selectedGrid['zone_name'] ?? 'Bahu Runway 25R & Kanal Selatan' }}
        </h2>
        <p id="detailDesc" class="text-[11px] text-secondary mt-0.5 mb-2.5 leading-relaxed">
          {{ $selectedGrid['desc'] ?? 'Zona perimeter strip aktif runway 25R berdekatan dengan jalur drainase induk sisi barat.' }}
        </p>

        <!-- Detail Metrics Grid -->
        <div class="grid grid-cols-2 gap-2.5 mb-2.5">
          <div class="bg-surface-container-low p-2.5 rounded-lg border border-border-subtle">
            <span class="text-[10px] uppercase text-secondary font-bold block">Total Temuan</span>
            <span id="detailCount" class="text-sm sm:text-base font-bold text-slate-navy mt-0.5 block">{{ $selectedGrid['count'] ?? 38 }} Kejadian</span>
            <span id="detailFreq" class="text-[10px] text-warning-rose font-semibold mt-0.5 block">Hotspot Utama</span>
          </div>
          <div class="bg-surface-container-low p-2.5 rounded-lg border border-border-subtle">
            <span class="text-[10px] uppercase text-secondary font-bold block">Spesies Dominan</span>
            <span id="detailSpecies" class="text-xs sm:text-sm font-bold text-slate-navy truncate mt-0.5 block">{{ $selectedGrid['dominant_species'] ?? 'Biawak Air' }}</span>
            <span id="detailStatus" class="text-[10px] text-secondary mt-0.5 block">{{ $selectedGrid['status'] ?? 'Telah Ditangani' }}</span>
          </div>
        </div>

        <!-- Operational Time Pattern -->
        <div class="mb-2.5 bg-surface-container-low/70 p-2.5 rounded-lg border border-border-subtle text-[11px]">
          <div class="flex items-center gap-1.5 font-bold text-slate-navy mb-1">
            <span class="material-symbols-outlined text-xs text-injourney-teal">schedule</span>
            <span>Puncak Frekuensi Waktu Muncul:</span>
          </div>
          <p id="detailHours" class="text-secondary leading-relaxed">
            {{ $selectedGrid['peak_hours'] ?? 'Paling sering teridentifikasi pukul 06:30 - 08:30 WIB & 16:00 - 17:30 WIB saat pasang air kanal drainase dan kelembaban rumput shoulder meningkat.' }}
          </p>
        </div>

        <!-- Mitigation SOP Recommendation -->
        <div class="mb-3 p-2.5 rounded-lg bg-amber-50/80 border border-amber-200 text-[11px]">
          <div class="flex items-center gap-1.5 font-bold text-amber-900 mb-1">
            <span class="material-symbols-outlined text-xs text-alert-amber">warning</span>
            <span>Rekomendasi Tindakan Unit AMC:</span>
          </div>
          <ul id="detailSopList" class="text-amber-950 space-y-1 list-disc list-inside leading-relaxed text-[11px]">
            @if(!empty($selectedGrid['mitigation_sop']))
              @foreach($selectedGrid['mitigation_sop'] as $sop)
                <li>{{ $sop }}</li>
              @endforeach
            @else
              <li>Peningkatan patroli kendaraan bersirene unit AMC interval 30 menit.</li>
              <li>Pembersihan vegetasi gulma pada saluran gorong-gorong sekitar sel.</li>
              <li>Aktivasi repellent trap &amp; pagar penghalau satwa melata.</li>
            @endif
          </ul>
        </div>
      </div>

      <!-- Quick Action Buttons -->
      <div class="flex items-center gap-2 pt-1">
        <a id="btnViewLog" href="{{ route('admin.dashboard', ['search' => $selectedGrid['grid'] ?? 'K-10']) }}" class="flex-1 py-1.5 px-2.5 border border-border-subtle rounded-lg bg-card-bg hover:bg-surface-container-low text-on-surface text-xs font-semibold transition-colors text-center shadow-2xs">
          Lihat Log Terkait
        </a>
        <button type="button" onclick="triggerDispersalAction()" class="flex-1 py-1.5 px-2.5 rounded-lg bg-injourney-teal hover:bg-injourney-dark-teal text-white text-xs font-semibold transition-colors text-center shadow-xs cursor-pointer">
          Perintah Dispersal
        </button>
      </div>
    </div>

    <!-- RIGHT SUB-PANEL: FILTER CEPAT KATEGORI & ZONA (MD:COL-SPAN-5) -->
    <div class="md:col-span-5 md:border-l md:border-border-subtle md:pl-5 flex flex-col justify-between">
      <div id="quickFilterCard" class="flex flex-col justify-between h-full">
        <div>
          <div class="flex items-center justify-between mb-3 pb-2 border-b border-border-subtle">
            <div class="flex items-center gap-1.5">
              <span class="material-symbols-outlined text-injourney-teal text-base">tune</span>
              <h3 class="text-xs sm:text-sm font-bold text-slate-navy">
                Filter Cepat Kategori Satwa &amp; Zona
              </h3>
            </div>
            <span id="filterLoadingIndicator" class="hidden items-center gap-1 text-[10px] text-injourney-teal font-semibold">
              <span class="w-2 h-2 rounded-full bg-injourney-teal animate-ping"></span>
              Memperbarui...
            </span>
          </div>

          <div class="space-y-3">
            <!-- Kategori Fauna -->
            <div>
              <span class="text-[10px] uppercase text-secondary font-bold block mb-1.5">Kategori Fauna:</span>
              <div class="flex flex-wrap gap-1.5 text-[11px] font-semibold">
                <a href="{{ route('admin.statistik', ['time_filter' => $timeFilter, 'kategori' => 'all', 'zona' => $zonaFilter]) }}" class="quick-filter-link px-2.5 py-1 rounded-lg transition-colors {{ $kategori === 'all' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                  Semua Satwa
                </a>
                <a href="{{ route('admin.statistik', ['time_filter' => $timeFilter, 'kategori' => 'burung', 'zona' => $zonaFilter]) }}" class="quick-filter-link px-2.5 py-1 rounded-lg transition-colors {{ $kategori === 'burung' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                  Avian / Burung
                </a>
                <a href="{{ route('admin.statistik', ['time_filter' => $timeFilter, 'kategori' => 'reptil', 'zona' => $zonaFilter]) }}" class="quick-filter-link px-2.5 py-1 rounded-lg transition-colors {{ $kategori === 'reptil' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                  Reptil
                </a>
                <a href="{{ route('admin.statistik', ['time_filter' => $timeFilter, 'kategori' => 'mamalia', 'zona' => $zonaFilter]) }}" class="quick-filter-link px-2.5 py-1 rounded-lg transition-colors {{ $kategori === 'mamalia' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                  Mamalia
                </a>
              </div>
            </div>

            <!-- Zona Operasional -->
            <div class="pt-2.5 border-t border-border-subtle">
              <span class="text-[10px] uppercase text-secondary font-bold block mb-1.5">Zona Operasional:</span>
              <div class="flex flex-wrap gap-1.5 text-[11px] font-semibold">
                <a href="{{ route('admin.statistik', ['time_filter' => $timeFilter, 'kategori' => $kategori, 'zona' => 'all']) }}" class="quick-filter-link px-2.5 py-1 rounded-lg transition-colors {{ $zonaFilter === 'all' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                  Semua Zona
                </a>
                <a href="{{ route('admin.statistik', ['time_filter' => $timeFilter, 'kategori' => $kategori, 'zona' => 'runway']) }}" class="quick-filter-link px-2.5 py-1 rounded-lg transition-colors {{ $zonaFilter === 'runway' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                  Runway Strip
                </a>
                <a href="{{ route('admin.statistik', ['time_filter' => $timeFilter, 'kategori' => $kategori, 'zona' => 'taxiway']) }}" class="quick-filter-link px-2.5 py-1 rounded-lg transition-colors {{ $zonaFilter === 'taxiway' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                  Taxiway
                </a>
                <a href="{{ route('admin.statistik', ['time_filter' => $timeFilter, 'kategori' => $kategori, 'zona' => 'apron']) }}" class="quick-filter-link px-2.5 py-1 rounded-lg transition-colors {{ $zonaFilter === 'apron' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                  Apron &amp; Hanggar
                </a>
                <a href="{{ route('admin.statistik', ['time_filter' => $timeFilter, 'kategori' => $kategori, 'zona' => 'perimeter']) }}" class="quick-filter-link px-2.5 py-1 rounded-lg transition-colors {{ $zonaFilter === 'perimeter' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                  Perimeter Luar
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Guidance & Context Tip -->
        <div class="mt-4 p-2.5 bg-surface-container-low rounded-lg border border-border-subtle text-[11px] text-secondary leading-relaxed">
          <div class="flex items-center gap-1.5 font-bold text-slate-navy mb-0.5">
            <span class="material-symbols-outlined text-xs text-injourney-teal">touch_app</span>
            <span>Interaktivitas Denah Sisi Udara:</span>
          </div>
          <p>Klik sel koordinat mana saja pada denah runway 10/28 atau daftar hotspot untuk memperbarui detail sektor secara instan.</p>
        </div>
      </div>
    </div>

  </div>
</div>
