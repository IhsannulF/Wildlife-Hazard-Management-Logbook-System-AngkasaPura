<!-- MASTER DATA LOGBOOK TABLE PANEL -->
<section class="bg-card-bg rounded-xl border border-border-subtle shadow-sm overflow-hidden flex flex-col">
  
  <!-- Table Header Status & Info -->
  <div class="px-space-md py-space-sm bg-surface-container-lowest border-b border-border-subtle flex flex-wrap items-center justify-between gap-space-sm">
    <div class="flex items-center gap-2">
      <span class="material-symbols-outlined text-injourney-teal text-lg">flight_takeoff</span>
      <span class="font-headline-sm text-headline-sm text-on-surface">
        Daftar Kejadian &amp; Intervensi Satwa Sisi Udara
      </span>
      @if($isVerifikasi)
        <span class="font-label-sm text-label-sm bg-cyan-50 text-[#007fa3] border border-cyan-200 px-2.5 py-0.5 rounded-full font-bold flex items-center gap-1">
          <span class="material-symbols-outlined text-xs">fact_check</span> Verifikasi Berita Acara (BA)
        </span>
      @else
        <span class="font-label-sm text-label-sm bg-surface-container text-tertiary px-2 py-0.5 rounded-full">Live Synchronized</span>
      @endif
    </div>
    <div class="flex items-center gap-2">
      <span class="text-xs text-secondary font-body-sm">Menampilkan <strong class="text-on-surface font-semibold">{{ $laporans->count() }} dari {{ $totalLaporan }}</strong> log</span>
    </div>
  </div>

  <!-- Responsive Data Table -->
  <div class="overflow-x-auto">
    <table class="w-full text-left border-collapse" id="logbookTable">
      <thead>
        <tr class="border-b border-border-subtle bg-canvas-light text-secondary font-label-md text-label-md uppercase tracking-wider">
          <th class="py-3 px-4 font-semibold" scope="col">No. Register / ID</th>
          <th class="py-3 px-4 font-semibold" scope="col">Waktu &amp; Tanggal</th>
          <th class="py-3 px-4 font-semibold" scope="col">Petugas &amp; Unit</th>
          <th class="py-3 px-4 font-semibold" scope="col">Spesies &amp; Kuantitas</th>
          <th class="py-3 px-4 font-semibold" scope="col">Lokasi &amp; Grid Sisi Udara</th>
          <th class="py-3 px-4 font-semibold" scope="col">Aktivitas &amp; Risiko</th>
          <th class="py-3 px-4 font-semibold" scope="col">Status Tindakan</th>
          <th class="py-3 px-4 font-semibold text-center" scope="col">Dispersal</th>
          <th class="py-3 px-4 font-semibold text-center" scope="col">Aksi Terpadu</th>
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
            } elseif (str_contains($sNameLower, 'biawak') || str_contains($sNameLower, 'ular') || str_contains($sNameLower, 'reptil')) {
              $speciesIcon = 'pest_control';
              $iconColor = 'text-alert-amber';
            } elseif (str_contains($sNameLower, 'kera') || str_contains($sNameLower, 'monyet')) {
              $speciesIcon = 'cruelty_free';
              $iconColor = 'text-secondary';
            } else {
              $speciesIcon = 'pets';
              $iconColor = 'text-secondary';
            }

            $dateYmd = $lap->tanggal ? ($lap->tanggal instanceof \Carbon\Carbon ? $lap->tanggal->format('Y-m-d') : date('Y-m-d', strtotime($lap->tanggal))) : '';
            $isResolved = ($lap->status === 'sudah');
            $isPending = !$isResolved;
          @endphp
          <tr class="hover:bg-cyan-50/40 transition-colors cursor-pointer group" id="row-{{ $lap->id }}" data-id="{{ $lap->id }}" data-status="{{ $lap->status }}" data-date="{{ $dateYmd }}" onclick="openReportDetailAjax({{ $lap->id }})" title="Klik untuk membuka rincian lengkap">
            <!-- Register / ID -->
            <td class="py-3 px-4 font-code-coordinate text-code-coordinate whitespace-nowrap">
              <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full shrink-0 {{ $isPending ? 'bg-alert-amber' : 'bg-resolved-emerald' }}"></span>
                <span class="font-bold text-on-surface group-hover:text-injourney-teal transition-colors flex items-center gap-1">
                  {{ $regId }}
                  <span class="material-symbols-outlined text-[13px] opacity-0 group-hover:opacity-100 text-injourney-teal transition-opacity">open_in_new</span>
                </span>
              </div>
            </td>

            <!-- Waktu & Tanggal -->
            <td class="py-3 px-4 whitespace-nowrap">
              <div class="font-semibold text-on-surface text-xs">{{ $tglFormatted }}</div>
              <div class="text-[11px] text-secondary font-mono flex items-center gap-1 mt-0.5">
                <span class="material-symbols-outlined text-[12px] text-slate-400">schedule</span>
                {{ $jamFormatted }}
              </div>
            </td>

            <!-- Petugas & Unit -->
            <td class="py-3 px-4">
              <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-full bg-surface-container-high text-on-surface font-bold text-[11px] flex items-center justify-center shrink-0">
                  {{ $officerInitials }}
                </div>
                <div class="min-w-0 max-w-[130px]">
                  <span class="font-medium text-on-surface text-xs block truncate" title="{{ $officerName }}">{{ $officerName }}</span>
                  <span class="font-label-sm text-[10px] text-secondary block truncate" title="{{ $unitName }}">{{ $unitName }}</span>
                </div>
              </div>
            </td>

            <!-- Spesies & Kuantitas -->
            <td class="py-3 px-4">
              <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-surface-container-low flex items-center justify-center shrink-0">
                  <span class="material-symbols-outlined {{ $iconColor }} text-base">{{ $speciesIcon }}</span>
                </div>
                <div class="min-w-0 max-w-[190px]">
                  <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="font-semibold text-on-surface text-xs truncate" title="{{ $speciesName }}">{{ $speciesName }}</span>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-surface-container text-on-surface shrink-0">
                      {{ $totalQty }} Ekor
                    </span>
                  </div>
                  @if($lap->ciri_ukuran)
                    <div class="text-[11px] text-secondary group-hover:text-injourney-teal flex items-center gap-1 mt-0.5 truncate" title="{{ $lap->ciri_ukuran }} (Klik untuk lihat lengkap)">
                      <span class="truncate">{{ Str::limit($lap->ciri_ukuran, 24) }}</span>
                      <span class="text-[10px] text-injourney-teal font-semibold group-hover:underline shrink-0">&rsaquo;</span>
                    </div>
                  @endif
                </div>
              </div>
            </td>

            <!-- Lokasi & Grid -->
            <td class="py-3 px-4 whitespace-nowrap">
              <div class="flex items-center gap-2">
                <span class="text-on-surface font-medium text-xs truncate max-w-[140px]" title="{{ $lap->area_inspeksi ?: 'Sisi Udara' }}">
                  {{ $lap->area_inspeksi ?: 'Sisi Udara' }}
                </span>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded font-code-coordinate text-[10px] font-bold shrink-0 {{ $isPending ? 'bg-rose-50 text-warning-rose border border-rose-200' : 'bg-surface-container text-on-surface border border-border-subtle' }}">
                  GRID {{ $gridName }}
                </span>
              </div>
            </td>

            <!-- Aktivitas & Risiko -->
            <td class="py-3 px-4">
              <div class="max-w-[190px]">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium {{ $isPending ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-surface-container text-on-surface' }} truncate max-w-full">
                  <span class="material-symbols-outlined text-[13px] {{ $isPending ? 'text-alert-amber' : 'text-secondary' }}">{{ $isPending ? 'warning' : 'info' }}</span>
                  <span class="truncate">{{ Str::limit($lap->aktivitas_satwa ?: ($lap->detail_pengusiran ?: 'Aktivitas terpantau'), 24) }}</span>
                </span>
                @if($lap->kondisi_apron)
                  <div class="text-[10px] text-secondary group-hover:text-injourney-teal flex items-center gap-1 mt-0.5 truncate" title="Kondisi: {{ $lap->kondisi_apron }} (Klik untuk lihat lengkap)">
                    <span class="truncate">Kondisi: {{ Str::limit($lap->kondisi_apron, 20) }}</span>
                    <span class="text-injourney-teal font-semibold shrink-0">&rsaquo;</span>
                  </div>
                @endif
              </div>
            </td>

            <!-- Status Tindakan -->
            <td class="py-3 px-4 whitespace-nowrap" id="status-cell-{{ $lap->id }}">
              @if($isResolved)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                  <span class="w-1.5 h-1.5 rounded-full bg-resolved-emerald"></span>
                  Sudah Ditangani
                </span>
              @else
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                  <span class="w-1.5 h-1.5 rounded-full bg-alert-amber"></span>
                  Belum Ditangani
                </span>
              @endif
            </td>

            <!-- Dispersal Satwa Terpisah -->
            <td class="py-3 px-4 text-center whitespace-nowrap" id="dispersal-cell-{{ $lap->id }}">
              @if($isPending)
                <button id="btn-resolve-{{ $lap->id }}" class="px-2.5 py-1 rounded-lg bg-injourney-teal hover:bg-injourney-dark-teal text-white font-label-md text-xs font-semibold transition-colors cursor-pointer shadow-xs inline-flex items-center gap-1" onclick="event.stopPropagation(); quickResolve({{ $lap->id }}, '{{ $regId }}')" title="Tangani Dispersal Sekarang" type="button">
                  <span class="material-symbols-outlined text-xs">bolt</span>
                  Dispersal
                </button>
              @else
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200">
                  <span class="material-symbols-outlined text-xs text-resolved-emerald">check_circle</span>
                  Terkendali
                </span>
              @endif
            </td>

            <!-- Aksi Terpadu (Tetap & Tidak Pindah-Pindah) -->
            <td class="py-3 px-4 text-center whitespace-nowrap">
              <div class="inline-flex items-center justify-center gap-1.5">
                <button class="p-1.5 rounded-lg text-secondary hover:text-injourney-teal hover:bg-surface-container-high transition-colors cursor-pointer" onclick="event.stopPropagation(); openReportDetailAjax({{ $lap->id }})" title="Lihat Detail Pratinjau" type="button">
                  <span class="material-symbols-outlined text-lg">visibility</span>
                </button>

                @if(Route::has('admin.laporan.cetak'))
                  <a href="{{ route('admin.laporan.cetak', $lap->id) }}" target="_blank" onclick="event.stopPropagation();" class="p-1.5 rounded-lg text-secondary hover:text-on-surface hover:bg-surface-container-high transition-colors" title="Cetak Berita Acara (BA)">
                    <span class="material-symbols-outlined text-lg">description</span>
                  </a>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" class="text-center py-12 text-secondary">
              <div class="flex flex-col items-center justify-center gap-2">
                <span class="material-symbols-outlined text-4xl text-outline">inbox</span>
                <span class="font-semibold text-sm">Tidak ada data laporan satwa yang sesuai filter.</span>
                <button onclick="resetFilters()" class="text-xs text-injourney-teal hover:underline font-bold mt-1 cursor-pointer">Reset Filter Pencarian</button>
              </div>
            </td>
          </tr>
        @endforelse
        <tr id="noResultsRow" class="hidden">
          <td colspan="9" class="text-center py-12 text-secondary">
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
    <div class="flex items-center gap-2">
      <span>Total: <strong class="text-on-surface font-semibold">{{ $laporans->total() }}</strong> catatan insiden satwa</span>
    </div>
    <div>
      {{ $laporans->links() }}
    </div>
  </div>
</section>
