<!-- MASTER DATA LOGBOOK TABLE PANEL -->
<section class="bg-card-bg rounded-xl border border-border-subtle shadow-sm overflow-hidden flex flex-col">
  
  <!-- Table Header Status & Info -->
  <div class="px-space-md py-space-sm bg-surface-container-lowest border-b border-border-subtle flex flex-wrap items-center justify-between gap-space-sm">
    <div class="flex items-center gap-2">
      <span class="material-symbols-outlined text-injourney-teal text-lg">fact_check</span>
      <span class="font-headline-sm text-headline-sm text-on-surface">
        Antrean Verifikasi Berita Acara (BA) Sisi Udara
      </span>
    </div>
    <div class="flex items-center gap-3 flex-wrap">
      <!-- Pengaturan Jumlah Data Tampil -->
      <div class="flex items-center gap-1.5 text-xs text-secondary font-body-sm">
        <label for="perPageTop" class="font-medium whitespace-nowrap text-secondary flex items-center gap-1">
          <span class="material-symbols-outlined text-sm text-injourney-teal">tune</span>
          Tampilkan:
        </label>
        <select id="perPageTop" onchange="changePerPage(this.value)" class="h-8 pl-2.5 pr-7 py-1 text-xs font-semibold rounded-lg border border-border-subtle bg-white text-on-surface focus:border-injourney-teal focus:ring-1 focus:ring-injourney-teal cursor-pointer shadow-2xs">
          <option value="10" {{ ($perPage ?? 10) == 10 ? 'selected' : '' }}>10 data</option>
          <option value="20" {{ ($perPage ?? 10) == 20 ? 'selected' : '' }}>20 data</option>
          <option value="50" {{ ($perPage ?? 10) == 50 ? 'selected' : '' }}>50 data</option>
          <option value="100" {{ ($perPage ?? 10) == 100 ? 'selected' : '' }}>100 data</option>
        </select>
      </div>
      <div class="h-4 w-px bg-border-subtle hidden sm:block"></div>
      <span class="text-xs text-secondary font-body-sm">Menampilkan <strong class="text-on-surface font-semibold">{{ $laporans->count() }} dari {{ $totalLaporan }}</strong> log</span>
    </div>
  </div>

  <!-- Dynamic Table Scroll Container: Locked when navbar is hidden, scrollable when navbar is pinned -->
  <div class="logbook-table-wrapper w-full">
    <table class="w-full text-left border-collapse table-auto" id="logbookTable">
      <thead>
        <tr class="border-b border-border-subtle bg-canvas-light text-secondary font-label-md text-label-md uppercase tracking-wider">
          <th class="py-2.5 px-2.5 xl:px-3 font-semibold whitespace-nowrap" scope="col">No. Register / ID</th>
          <th class="py-2.5 px-2.5 xl:px-3 font-semibold whitespace-nowrap" scope="col">Waktu &amp; Tanggal</th>
          <th class="py-2.5 px-2.5 xl:px-3 font-semibold" scope="col">Petugas &amp; Unit</th>
          <th class="py-2.5 px-2.5 xl:px-3 font-semibold" scope="col">Spesies &amp; Qty</th>
          <th class="py-2.5 px-2.5 xl:px-3 font-semibold" scope="col">Lokasi &amp; Grid</th>
          <th class="py-2.5 px-2.5 xl:px-3 font-semibold" scope="col">Aktivitas &amp; Risiko</th>
          <th class="py-2.5 px-2.5 xl:px-3 font-semibold text-center whitespace-nowrap" scope="col">Status Tindakan</th>
          <th class="py-2.5 px-2.5 xl:px-3 font-semibold text-center whitespace-nowrap" scope="col">Aksi &amp; Dispersal</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-border-subtle font-body-sm text-body-sm">
        @forelse($laporans as $lap)
          @php
            $regId = $lap->no_laporan ?: ('WHM-' . ($lap->tanggal ? $lap->tanggal->format('Y') : date('Y')) . '-' . str_pad($lap->id, 4, '0', STR_PAD_LEFT));
            $tglFormatted = $lap->tanggal ? \Carbon\Carbon::parse($lap->tanggal)->translatedFormat('d M Y') : '-';
            $jamFormatted = $lap->created_at ? $lap->created_at->format('H:i') . ' WIB' : '08:00 WIB';
            $officerName = $lap->nama_petugas ?: ($lap->user ? ($lap->user->namalengkap ?: $lap->user->username) : 'Petugas Sisi Udara');
            $officerInitials = strtoupper(substr($officerName, 0, 2));
            $unitName = $lap->unit_kerja ?: 'AMC Airside';

            $firstSatwa = $lap->detailSatwa->first();
            $speciesName = $firstSatwa ? $firstSatwa->nama_satwa : 'Satwa Liar';
            $totalQty = $lap->detailSatwa->sum('jumlah') ?: 1;
            $gridName = $lap->grid_lokasi ?: ($firstSatwa->grid ?? 'K-10');

            // Species icon detection
            $sNameLower = strtolower($speciesName);
            if (str_contains($sNameLower, 'burung') || str_contains($sNameLower, 'blekok') || str_contains($sNameLower, 'kuntul') || str_contains($sNameLower, 'layang') || str_contains($sNameLower, 'cangak')) {
              $speciesIcon = 'flutter_dash';
              $iconColor = 'text-injourney-teal';
            } elseif (str_contains($sNameLower, 'biawak') || str_contains($sNameLower, 'ular') || str_contains($sNameLower, 'kadal')) {
              $speciesIcon = 'pest_control';
              $iconColor = 'text-alert-amber';
            } else {
              $speciesIcon = 'pets';
              $iconColor = 'text-secondary';
            }

            $dateYmd = $lap->tanggal ? ($lap->tanggal instanceof \Carbon\Carbon ? $lap->tanggal->format('Y-m-d') : date('Y-m-d', strtotime($lap->tanggal))) : '';
            $isResolved = ($lap->status === 'sudah');
            $isPending = !$isResolved;
          @endphp
          <tr class="hover:bg-cyan-50/40 transition-colors cursor-pointer group" id="row-{{ $lap->id }}" data-status="{{ $lap->status ?: 'belum' }}" data-grid="{{ $gridName }}" data-date="{{ $dateYmd }}" onclick="openReportDetailAjax({{ $lap->id }})" title="Klik untuk membuka rincian lengkap">
            <!-- No. Register -->
            <td class="py-2.5 px-2.5 xl:px-3 font-code-coordinate text-code-coordinate whitespace-nowrap">
              <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full shrink-0 {{ $isPending ? 'bg-alert-amber' : 'bg-resolved-emerald' }}"></span>
                <span class="font-bold text-on-surface group-hover:text-injourney-teal transition-colors flex items-center gap-1 text-[11px] xl:text-xs">
                  {{ $regId }}
                  <span class="material-symbols-outlined text-[13px] opacity-0 group-hover:opacity-100 text-injourney-teal transition-opacity">open_in_new</span>
                </span>
              </div>
            </td>

            <!-- Waktu & Tanggal -->
            <td class="py-2.5 px-2.5 xl:px-3 whitespace-nowrap">
              <div class="font-semibold text-on-surface text-[11px] xl:text-xs">{{ $tglFormatted }}</div>
              <div class="text-[10px] xl:text-[11px] text-secondary font-mono flex items-center gap-1 mt-0.5">
                <span class="material-symbols-outlined text-[12px] text-slate-400">schedule</span>
                {{ $jamFormatted }}
              </div>
            </td>

            <!-- Petugas & Unit -->
            <td class="py-2.5 px-2.5 xl:px-3">
              <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-full bg-surface-container-high text-on-surface font-bold text-[10px] flex items-center justify-center shrink-0">
                  {{ $officerInitials }}
                </div>
                <div class="min-w-0">
                  <span class="font-medium text-on-surface text-[11px] xl:text-xs block truncate max-w-[90px] xl:max-w-[125px]" title="{{ $officerName }}">{{ $officerName }}</span>
                  <span class="font-label-sm text-[9px] xl:text-[10px] text-secondary block truncate max-w-[90px] xl:max-w-[125px]" title="{{ $unitName }}">{{ $unitName }}</span>
                </div>
              </div>
            </td>

            <!-- Spesies & Kuantitas -->
            <td class="py-2.5 px-2.5 xl:px-3">
              <div class="flex items-center gap-1.5 xl:gap-2">
                <div class="w-6 h-6 rounded-lg bg-surface-container-low flex items-center justify-center shrink-0">
                  <span class="material-symbols-outlined {{ $iconColor }} text-sm">{{ $speciesIcon }}</span>
                </div>
                <div class="min-w-0">
                  <div class="flex items-center gap-1">
                    <span class="font-semibold text-on-surface text-[11px] xl:text-xs truncate max-w-[85px] xl:max-w-[120px]" title="{{ $speciesName }}">{{ $speciesName }}</span>
                    <span class="inline-flex items-center px-1 py-0.2 rounded-full text-[9px] font-bold bg-surface-container text-on-surface shrink-0">
                      {{ $totalQty }}
                    </span>
                  </div>
                  @if($lap->ciri_ukuran)
                    <div class="text-[10px] text-secondary group-hover:text-injourney-teal flex items-center gap-0.5 mt-0.5 truncate max-w-[95px] xl:max-w-[130px]" title="{{ $lap->ciri_ukuran }} (Klik untuk lihat lengkap)">
                      <span class="truncate">{{ Str::limit($lap->ciri_ukuran, 18) }}</span>
                    </div>
                  @endif
                </div>
              </div>
            </td>

            <!-- Lokasi & Grid -->
            <td class="py-2.5 px-2.5 xl:px-3">
              <div class="flex items-center gap-1.5">
                <span class="text-on-surface font-medium text-[11px] xl:text-xs truncate max-w-[80px] xl:max-w-[115px]" title="{{ $lap->area_inspeksi ?: 'Sisi Udara' }}">
                  {{ $lap->area_inspeksi ?: 'Sisi Udara' }}
                </span>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded font-code-coordinate text-[9px] xl:text-[10px] font-bold shrink-0 {{ $isPending ? 'bg-rose-50 text-warning-rose border border-rose-200' : 'bg-surface-container text-on-surface border border-border-subtle' }}">
                  {{ $gridName }}
                </span>
              </div>
            </td>

            <!-- Aktivitas & Risiko ICAO -->
            <td class="py-2.5 px-2.5 xl:px-3">
              <div class="max-w-[125px] xl:max-w-[165px] space-y-1">
                @php
                  $riskLvl = strtolower($lap->risk_level ?? 'rendah');
                  $riskSc = $lap->risk_score ?? 1;
                @endphp
                @if($riskLvl === 'kritis')
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-600 text-white shadow-xs ring-2 ring-rose-300 animate-pulse whitespace-nowrap">
                    <span class="material-symbols-outlined text-[12px]">warning</span>
                    KRITIS ({{ $riskSc }})
                  </span>
                @elseif($riskLvl === 'tinggi')
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-600 text-white shadow-xs ring-1 ring-amber-300 whitespace-nowrap">
                    <span class="material-symbols-outlined text-[12px]">priority_high</span>
                    TINGGI ({{ $riskSc }})
                  </span>
                @elseif($riskLvl === 'sedang')
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300 whitespace-nowrap">
                    SEDANG ({{ $riskSc }})
                  </span>
                @else
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 whitespace-nowrap">
                    RENDAH ({{ $riskSc }})
                  </span>
                @endif
                <div class="text-[10px] text-secondary truncate" title="{{ $lap->aktivitas_satwa ?: ($lap->detail_pengusiran ?: 'Aktivitas terpantau') }}">
                  {{ Str::limit($lap->aktivitas_satwa ?: ($lap->detail_pengusiran ?: 'Aktivitas terpantau'), 18) }}
                </div>
              </div>
            </td>

            <!-- Status Tindakan -->
            <td class="py-2.5 px-2.5 xl:px-3 text-center whitespace-nowrap" id="status-cell-{{ $lap->id }}">
              @if($isResolved)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] xl:text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                  <span class="w-1.5 h-1.5 rounded-full bg-resolved-emerald"></span>
                  Ditangani
                </span>
              @else
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] xl:text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                  <span class="w-1.5 h-1.5 rounded-full bg-alert-amber"></span>
                  Belum Ditangani
                </span>
              @endif
            </td>

            <!-- Aksi & Dispersal -->
            <td class="py-2.5 px-2.5 xl:px-3 text-center whitespace-nowrap">
              <div class="inline-flex items-center justify-center gap-1.5">
                <span id="dispersal-cell-{{ $lap->id }}">
                  @if($isPending)
                    <button id="btn-resolve-{{ $lap->id }}" class="px-2 py-1 rounded-lg bg-injourney-teal hover:bg-injourney-dark-teal text-white font-label-md text-[10px] xl:text-[11px] font-semibold transition-colors cursor-pointer shadow-xs inline-flex items-center gap-1" onclick="event.stopPropagation(); quickResolve({{ $lap->id }}, '{{ $regId }}')" title="Tangani Dispersal Sekarang" type="button">
                      <span class="material-symbols-outlined text-xs">bolt</span>
                      Dispersal
                    </button>
                  @else
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] xl:text-[11px] font-medium text-emerald-700 bg-emerald-50 border border-emerald-200">
                      <span class="material-symbols-outlined text-xs text-resolved-emerald">check_circle</span>
                      Terkendali
                    </span>
                  @endif
                </span>

                @if(Route::has('admin.laporan.cetak'))
                  <a href="{{ route('admin.laporan.cetak', $lap->id) }}" target="_blank" onclick="event.stopPropagation();" class="p-1 rounded-lg text-secondary hover:text-on-surface hover:bg-surface-container-high transition-colors" title="Cetak Berita Acara (BA)">
                    <span class="material-symbols-outlined text-base">description</span>
                  </a>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center py-12 text-secondary">
              <div class="flex flex-col items-center justify-center gap-2">
                <span class="material-symbols-outlined text-4xl text-outline">inbox</span>
                <span class="font-semibold text-sm">Tidak ada data laporan satwa yang sesuai filter antrean verifikasi.</span>
                <button onclick="resetFilters()" class="text-xs text-injourney-teal hover:underline font-bold mt-1 cursor-pointer">Reset Filter Pencarian</button>
              </div>
            </td>
          </tr>
        @endforelse
        <tr id="noResultsRow" class="hidden">
          <td colspan="8" class="text-center py-12 text-secondary">
            <div class="flex flex-col items-center justify-center gap-2">
              <span class="material-symbols-outlined text-4xl text-outline">search_off</span>
              <span class="font-semibold text-sm">Tidak ada data laporan yang cocok dengan rentang tanggal atau kata kunci ini.</span>
              <button onclick="resetFilters()" class="text-xs text-injourney-teal hover:underline font-bold mt-1 cursor-pointer">Reset Semua Filter</button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Pagination Footer -->
  <div class="px-space-md py-3 bg-surface-container-lowest border-t border-border-subtle flex flex-wrap items-center justify-between gap-space-sm text-xs text-secondary">
    <div class="flex items-center gap-3 sm:gap-4 flex-wrap">
      <span>Total: <strong class="text-on-surface font-semibold">{{ $laporans->total() }}</strong> catatan antrean berita acara</span>
      <div class="h-4 w-px bg-border-subtle hidden sm:block"></div>
      <div class="flex items-center gap-1.5">
        <label for="perPageBottom" class="text-secondary font-medium whitespace-nowrap">Tampilkan per halaman:</label>
        <select id="perPageBottom" onchange="changePerPage(this.value)" class="h-8 pl-2.5 pr-7 py-1 text-xs font-semibold rounded-lg border border-border-subtle bg-white text-on-surface focus:border-injourney-teal focus:ring-1 focus:ring-injourney-teal cursor-pointer shadow-2xs">
          <option value="10" {{ ($perPage ?? 10) == 10 ? 'selected' : '' }}>10 data</option>
          <option value="20" {{ ($perPage ?? 10) == 20 ? 'selected' : '' }}>20 data</option>
          <option value="50" {{ ($perPage ?? 10) == 50 ? 'selected' : '' }}>50 data</option>
          <option value="100" {{ ($perPage ?? 10) == 100 ? 'selected' : '' }}>100 data</option>
        </select>
      </div>
    </div>
    <div>
      {{ $laporans->links() }}
    </div>
  </div>
</section>
