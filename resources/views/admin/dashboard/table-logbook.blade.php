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
      <button class="text-xs font-label-md text-secondary hover:text-on-surface flex items-center gap-1 px-2.5 py-1 rounded border border-border-subtle bg-white cursor-pointer" onclick="window.print()">
        <span class="material-symbols-outlined text-xs">print</span> Cetak Register
      </button>
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
          <th class="py-3 px-4 font-semibold text-center" scope="col">Aksi Terpadu</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-border-subtle font-body-sm text-body-sm">
        @forelse($laporans as $lap)
          @php
            $regId = $lap->no_laporan ?: ('WHM-' . ($lap->tanggal ? $lap->tanggal->format('Y') : date('Y')) . '-' . str_pad($lap->id, 4, '0', STR_PAD_LEFT));
            $unitSlug = strtoupper(Str::slug($lap->unit_kerja ?: 'AMC-AIRSIDE'));
            $tglFormatted = $lap->tanggal ? \Carbon\Carbon::parse($lap->tanggal)->translatedFormat('d M Y') : '-';
            $jamFormatted = $lap->created_at ? $lap->created_at->format('H:i') . ' WIB' : '08:00 WIB';
            $officerName = $lap->nama_petugas ?: ($lap->user ? ($lap->user->namalengkap ?: $lap->user->username) : 'Petugas Sisi Udara');
            $officerInitials = strtoupper(substr($officerName, 0, 2));

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

            $isResolved = ($lap->status === 'sudah');
            $isPending = !$isResolved;
          @endphp
          <tr class="hover:bg-surface-container-low transition-colors group" id="row-{{ $lap->id }}" data-id="{{ $lap->id }}" data-status="{{ $lap->status }}">
            <!-- Register / ID -->
            <td class="py-3.5 px-4">
              <span class="font-code-coordinate text-code-coordinate font-bold text-injourney-teal block">{{ $regId }}</span>
              <span class="font-label-sm text-label-sm text-secondary">{{ $unitSlug }}</span>
            </td>

            <!-- Waktu & Tanggal -->
            <td class="py-3.5 px-4 whitespace-nowrap">
              <span class="font-semibold text-on-surface block">{{ $tglFormatted }}</span>
              <span class="text-xs text-secondary flex items-center gap-1">
                <span class="material-symbols-outlined text-[13px]">schedule</span> {{ $jamFormatted }}
              </span>
            </td>

            <!-- Petugas & Unit -->
            <td class="py-3.5 px-4">
              <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full {{ $isPending ? 'bg-rose-100 text-warning-rose' : 'bg-surface-container-high text-tertiary' }} font-bold text-xs flex items-center justify-center">
                  {{ $officerInitials }}
                </div>
                <div>
                  <span class="font-semibold text-on-surface block">{{ $officerName }}</span>
                  <span class="font-label-sm text-label-sm text-secondary">{{ $lap->unit_kerja ?: 'Unit Sisi Udara' }}</span>
                </div>
              </div>
            </td>

            <!-- Spesies & Kuantitas -->
            <td class="py-3.5 px-4">
              <div class="flex items-center gap-2">
                <span class="material-symbols-outlined {{ $iconColor }} text-lg">{{ $speciesIcon }}</span>
                <div>
                  <span class="font-semibold text-on-surface block">{{ $speciesName }}</span>
                  <span class="font-label-sm text-label-sm text-secondary">{{ $totalQty }} Ekor {{ $lap->ciri_ukuran ? "({$lap->ciri_ukuran})" : '' }}</span>
                </div>
              </div>
            </td>

            <!-- Lokasi & Grid -->
            <td class="py-3.5 px-4">
              <div>
                <span class="text-on-surface font-medium block">{{ $lap->area_inspeksi ?: 'Sisi Udara' }}</span>
                <span class="inline-flex items-center gap-1 font-code-coordinate text-code-coordinate {{ $isPending ? 'bg-rose-100 text-rose-800' : 'bg-surface-container text-on-surface' }} px-2 py-0.5 rounded font-bold mt-0.5">
                  @if($isPending)
                    <span class="w-1.5 h-1.5 rounded-full bg-warning-rose animate-ping"></span>
                  @endif
                  GRID {{ $gridName }}
                </span>
              </div>
            </td>

            <!-- Aktivitas & Risiko -->
            <td class="py-3.5 px-4">
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold {{ $isPending ? 'bg-rose-50 text-warning-rose border border-rose-200' : 'bg-surface-container text-on-surface' }}">
                <span class="material-symbols-outlined text-xs">{{ $isPending ? 'warning' : 'info' }}</span>
                {{ Str::limit($lap->aktivitas_satwa ?: ($lap->detail_pengusiran ?: 'Aktivitas di runway/perimeter'), 32) }}
              </span>
              <span class="text-[11px] text-secondary block mt-0.5">
                @if($lap->kondisi_apron)
                  Kondisi: {{ $lap->kondisi_apron }}
                @else
                  Level: {{ $isPending ? 'Tinggi (Kritis)' : 'Terkendali' }}
                @endif
              </span>
            </td>

            <!-- Status Tindakan -->
            <td class="py-3.5 px-4 whitespace-nowrap" id="status-cell-{{ $lap->id }}">
              @if($isResolved)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                  <span class="w-2 h-2 rounded-full bg-resolved-emerald"></span>
                  Sudah Ditangani
                </span>
              @else
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                  <span class="w-2 h-2 rounded-full bg-alert-amber"></span>
                  Belum Ditangani
                </span>
              @endif
            </td>

            <!-- Aksi Terpadu -->
            <td class="py-3.5 px-4 text-center whitespace-nowrap">
              <div class="inline-flex items-center gap-1">
                <button class="p-1.5 rounded text-secondary hover:text-injourney-teal hover:bg-surface-container-high transition-colors cursor-pointer" onclick="openReportDetailAjax({{ $lap->id }})" title="Lihat Detail Pratinjau" type="button">
                  <span class="material-symbols-outlined text-lg">visibility</span>
                </button>

                @if($isPending)
                  <button id="btn-resolve-{{ $lap->id }}" class="px-2 py-1 rounded bg-injourney-teal hover:bg-injourney-dark-teal text-white font-label-md text-xs transition-colors cursor-pointer" onclick="quickResolve({{ $lap->id }}, '{{ $regId }}')" title="Tangani Sekarang" type="button">
                    Dispersal
                  </button>
                @endif

                @if(Route::has('admin.laporan.cetak'))
                  <a href="{{ route('admin.laporan.cetak', $lap->id) }}" target="_blank" class="p-1.5 rounded text-secondary hover:text-on-surface hover:bg-surface-container-high transition-colors" title="Cetak Berita Acara (BA)">
                    <span class="material-symbols-outlined text-lg">description</span>
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
                <span class="font-semibold text-sm">Tidak ada data laporan satwa yang sesuai filter.</span>
                <button onclick="resetFilters()" class="text-xs text-injourney-teal hover:underline font-bold mt-1 cursor-pointer">Reset Filter Pencarian</button>
              </div>
            </td>
          </tr>
        @endforelse
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
