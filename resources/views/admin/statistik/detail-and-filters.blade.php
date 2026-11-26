<!-- 3. DETAIL & AUDIT ROW BELOW WIDE AIRSIDE MAP: DETAIL GRID TERPILIH & FILTER CEPAT (SPAN 2 COLUMNS) -->
<div id="detailCard" data-selected-grid="{{ $selectedGrid['grid'] ?? '-' }}" class="lg:col-span-2 bg-card-bg rounded-xl border-2 border-warning-rose/30 p-3.5 sm:p-4 shadow-sm relative overflow-hidden transition-all duration-200 flex flex-col justify-between">
  <div id="detailTopBar" class="absolute top-0 left-0 right-0 h-1 bg-warning-rose"></div>
  
  <div class="grid grid-cols-1 md:grid-cols-12 gap-5 h-full">
    
    <!-- LEFT SUB-PANEL: DETAIL GRID TERPILIH & STATISTIK BURUNG (MD:COL-SPAN-6) -->
    <div class="md:col-span-6 flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between mb-2">
          <div class="flex items-center gap-2">
            <span id="detailGridBadge" class="font-code-coordinate text-xs font-bold bg-error-container text-error px-2 py-0.5 rounded">
              GRID {{ $selectedGrid['grid'] ?? '-' }}
            </span>
            <span id="detailRiskBadge" class="text-[10px] px-2 py-0.5 rounded-full bg-red-100 text-red-800 font-bold">
              {{ $selectedGrid['risk_category'] ?? 'Tanpa Data' }}
            </span>
          </div>
          <span class="text-[11px] text-secondary font-medium">Terpilih Aktif</span>
        </div>

        <h2 id="detailZoneName" class="text-sm sm:text-base font-bold text-slate-navy">
          {{ $selectedGrid['zone_name'] ?? '-' }}
        </h2>
        <p id="detailDesc" class="text-[11px] text-secondary mt-0.5 mb-2.5 leading-relaxed">
          {{ $selectedGrid['desc'] ?? '' }}
        </p>

        <!-- Detail Metrics Grid -->
        <div class="grid grid-cols-2 gap-2.5 mb-2.5">
          <div class="bg-surface-container-low p-2.5 rounded-lg border border-border-subtle">
            <span class="text-[10px] uppercase text-secondary font-bold block">Total Temuan</span>
            <span id="detailCount" class="text-sm sm:text-base font-bold text-slate-navy mt-0.5 block">{{ $selectedGrid['count'] ?? 0 }} Temuan</span>
            <span id="detailFreq" class="text-[10px] text-warning-rose font-semibold mt-0.5 block" title="Dua jam dengan jumlah satwa terbanyak menurut jam laporan">Jam puncak: {{ $selectedGrid['peak_hours'] ?? 'Belum ada data jam' }}</span>
          </div>
          <div class="bg-surface-container-low p-2.5 rounded-lg border border-border-subtle">
            <span class="text-[10px] uppercase text-secondary font-bold block">Spesies Dominan</span>
            <span id="detailSpecies" class="text-xs sm:text-sm font-bold text-slate-navy truncate mt-0.5 block">{{ $selectedGrid['dominant_species'] ?? 'Tidak Ada' }}</span>
            <span id="detailStatus" class="text-[10px] text-secondary mt-0.5 block">{{ $selectedGrid['status'] ?? 'Telah Ditangani' }}</span>
          </div>
        </div>

        <!-- DETAIL STATISTIK PENGUMPULAN BURUNG TERBANYAK DI GRID TERPILIH -->
        <div id="detailBirdContainer" class="mb-2 p-3 rounded-xl bg-gradient-to-br from-cyan-50/90 via-sky-50/70 to-blue-50/50 border border-cyan-200/80 shadow-2xs">
          <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-1.5">
              <span class="material-symbols-outlined text-sm text-[#00A9C1]">flutter</span>
              <span class="text-xs font-bold text-slate-navy">Statistik Pengumpulan Burung di Grid Ini</span>
            </div>
            <span id="detailBirdTotalBadge" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#00A9C1] text-white shadow-2xs">
              {{ $selectedGrid['bird_stats']['total_birds'] ?? 0 }} Ekor Terkumpul
            </span>
          </div>

          <!-- Empty State (Jika Steril dari Burung) -->
          <div id="detailBirdEmptyState" class="{{ (!empty($selectedGrid['bird_stats']['species_breakdown'])) ? 'hidden' : '' }} py-3 px-3 bg-white/70 rounded-lg border border-cyan-100 text-center">
            <span class="material-symbols-outlined text-emerald-600 text-xl mb-0.5">verified_user</span>
            <p class="text-[11px] font-semibold text-slate-700">Area Steril dari Aktivitas Burung</p>
            <p class="text-[10px] text-secondary mt-0.5">Tidak tercatat populasi atau insiden burung pada koordinat grid ini dalam periode terpilih.</p>
          </div>

          <!-- Ranking Spesies Burung Terbanyak di Grid Ini -->
          <div id="detailBirdList" class="{{ (empty($selectedGrid['bird_stats']['species_breakdown'])) ? 'hidden' : '' }} space-y-2">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Peringkat Spesies Burung Terbanyak:</span>
            <div id="detailBirdSpeciesBars" class="space-y-1.5">
              @if(!empty($selectedGrid['bird_stats']['species_breakdown']))
                @foreach($selectedGrid['bird_stats']['species_breakdown'] as $bIdx => $bSp)
                  <div class="bg-white/80 p-2 rounded-lg border border-cyan-100">
                    <div class="flex items-center justify-between text-[11px] mb-1">
                      <span class="font-bold text-slate-800 flex items-center gap-1">
                        <span class="w-3.5 h-3.5 rounded-full bg-cyan-100 text-[#007fa3] text-[9px] font-black inline-flex items-center justify-center">
                          {{ $bIdx + 1 }}
                        </span>
                        {{ $bSp['species'] }}
                      </span>
                      <span class="font-code-coordinate font-bold text-slate-700">{{ $bSp['count'] }} Ekor ({{ $bSp['percentage'] }}%)</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                      <div class="bg-gradient-to-r from-[#00A9C1] to-blue-500 h-1.5 rounded-full" style="width: {{ $bSp['percentage'] }}%"></div>
                    </div>
                  </div>
                @endforeach
              @endif
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Action Buttons -->
      <div class="flex items-center gap-2 pt-2 border-t border-border-subtle mt-2">
        <a id="btnViewLog" href="{{ route('admin.dashboard', ['search' => $selectedGrid['grid'] ?? '']) }}" class="flex-1 py-1.5 px-2.5 border border-border-subtle rounded-lg bg-card-bg hover:bg-surface-container-low text-on-surface text-xs font-semibold transition-colors text-center shadow-2xs">
          Lihat Log Terkait
        </a>
        <button type="button" onclick="triggerDispersalAction()" class="flex-1 py-1.5 px-2.5 rounded-lg bg-injourney-teal hover:bg-injourney-dark-teal text-white text-xs font-semibold transition-colors text-center shadow-xs cursor-pointer">
          Perintah Dispersal
        </button>
      </div>
    </div>

    <!-- RIGHT SUB-PANEL: FILTER CEPAT & RIWAYAT LAPORAN BA TERKINI (MD:COL-SPAN-6) -->
    <div class="md:col-span-6 md:border-l md:border-border-subtle md:pl-5 flex flex-col justify-between">
      <div id="quickFilterCard" class="flex flex-col justify-between h-full space-y-3">
        <div>
          <div class="flex items-center justify-between mb-2.5 pb-2 border-b border-border-subtle">
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

          <div class="space-y-2.5">
            <!-- Kategori Fauna -->
            <div>
              <span class="text-[10px] uppercase text-secondary font-bold block mb-1">Kategori Fauna:</span>
              <div class="flex flex-wrap gap-1.5 text-[11px] font-semibold">
                <a href="{{ route('admin.statistik', [...$periodeQuery, 'kategori' => 'all', 'zona' => $zonaFilter]) }}" class="quick-filter-link px-2.5 py-1 rounded-lg transition-colors {{ $kategori === 'all' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                  Semua Satwa
                </a>
                <a href="{{ route('admin.statistik', [...$periodeQuery, 'kategori' => 'burung', 'zona' => $zonaFilter]) }}" class="quick-filter-link px-2.5 py-1 rounded-lg transition-colors {{ $kategori === 'burung' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                  Avian / Burung
                </a>
                <a href="{{ route('admin.statistik', [...$periodeQuery, 'kategori' => 'reptil', 'zona' => $zonaFilter]) }}" class="quick-filter-link px-2.5 py-1 rounded-lg transition-colors {{ $kategori === 'reptil' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                  Reptil
                </a>
                <a href="{{ route('admin.statistik', [...$periodeQuery, 'kategori' => 'mamalia', 'zona' => $zonaFilter]) }}" class="quick-filter-link px-2.5 py-1 rounded-lg transition-colors {{ $kategori === 'mamalia' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                  Mamalia
                </a>
              </div>
            </div>

            <!-- Zona Operasional -->
            <div class="pt-2 border-t border-border-subtle">
              <span class="text-[10px] uppercase text-secondary font-bold block mb-1">Zona Operasional:</span>
              <div class="flex flex-wrap gap-1.5 text-[11px] font-semibold">
                <a href="{{ route('admin.statistik', [...$periodeQuery, 'kategori' => $kategori, 'zona' => 'all']) }}" class="quick-filter-link px-2.5 py-1 rounded-lg transition-colors {{ $zonaFilter === 'all' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                  Semua Zona
                </a>
                <a href="{{ route('admin.statistik', [...$periodeQuery, 'kategori' => $kategori, 'zona' => 'runway']) }}" class="quick-filter-link px-2.5 py-1 rounded-lg transition-colors {{ $zonaFilter === 'runway' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                  Runway Strip
                </a>
                <a href="{{ route('admin.statistik', [...$periodeQuery, 'kategori' => $kategori, 'zona' => 'taxiway']) }}" class="quick-filter-link px-2.5 py-1 rounded-lg transition-colors {{ $zonaFilter === 'taxiway' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                  Taxiway
                </a>
                <a href="{{ route('admin.statistik', [...$periodeQuery, 'kategori' => $kategori, 'zona' => 'apron']) }}" class="quick-filter-link px-2.5 py-1 rounded-lg transition-colors {{ $zonaFilter === 'apron' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                  Apron &amp; Hanggar
                </a>
                <a href="{{ route('admin.statistik', [...$periodeQuery, 'kategori' => $kategori, 'zona' => 'perimeter']) }}" class="quick-filter-link px-2.5 py-1 rounded-lg transition-colors {{ $zonaFilter === 'perimeter' ? 'bg-injourney-teal text-white shadow-xs' : 'bg-surface-container-low text-secondary hover:bg-surface-container-high' }}">
                  Perimeter Luar
                </a>
              </div>
            </div>
          </div>

          <!-- RIWAYAT LAPORAN BA TERKINI DI GRID INI (MENGISI AREA KOSONG SEBELAH KANAN) -->
          <div id="detailBirdReportsSection" class="mt-3.5 p-3 rounded-xl bg-gradient-to-br from-slate-50 to-cyan-50/40 border border-cyan-200/70 shadow-2xs">
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm text-[#00A9C1]">history_edu</span>
                <span class="text-xs font-bold text-slate-navy">Riwayat Laporan BA Terkini di Grid Ini</span>
              </div>
              <span class="text-[10px] text-secondary font-medium">Data Berita Acara</span>
            </div>

            <div id="detailBirdReportsList" class="space-y-1.5 max-h-52 overflow-y-auto pr-0.5">
              @if(!empty($selectedGrid['bird_stats']['recent_reports']))
                @foreach($selectedGrid['bird_stats']['recent_reports'] as $rRep)
                  <div class="text-[11px] bg-white p-2 rounded-lg border border-cyan-100 flex items-center justify-between shadow-2xs hover:border-cyan-300 transition-colors">
                    <div class="truncate mr-2">
                      <div class="flex items-center gap-1.5">
                        <span class="font-code-coordinate font-bold text-slate-800">{{ $rRep['nomor_ba'] }}</span>
                        <span class="text-secondary text-[10px]">• {{ $rRep['tanggal'] }} ({{ $rRep['jam'] }})</span>
                      </div>
                      <div class="text-slate-600 truncate text-[10px] mt-0.5">
                        {{ $rRep['spesies'] }} — {{ $rRep['jumlah'] }} Ekor • {{ $rRep['tindak_lanjut'] }}
                      </div>
                    </div>
                    <span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 whitespace-nowrap">{{ $rRep['status'] }}</span>
                  </div>
                @endforeach
              @else
                <div class="py-3 px-2 text-center text-secondary text-[11px] bg-white/60 rounded-lg border border-dashed border-slate-200">
                  Tidak ada catatan riwayat BA pada grid ini.
                </div>
              @endif
            </div>
          </div>
        </div>

        <!-- Guidance & Context Tip -->
        <div class="mt-2.5 p-2 bg-surface-container-low rounded-lg border border-border-subtle text-[10px] text-secondary leading-relaxed">
          <div class="flex items-center gap-1 font-bold text-slate-navy mb-0.5">
            <span class="material-symbols-outlined text-xs text-injourney-teal">touch_app</span>
            <span>Interaktivitas Denah:</span>
          </div>
          <p>Klik titik koordinat atau daftar hotspot untuk memperbarui detail sektor secara instan.</p>
        </div>
      </div>
    </div>

  </div>
</div>
