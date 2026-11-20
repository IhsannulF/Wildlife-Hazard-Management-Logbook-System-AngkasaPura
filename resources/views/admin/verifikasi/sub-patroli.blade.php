<!-- SUB-VIEW: SESI PATROLI SISI UDARA (OPERASIONAL SAFETY) -->
<section class="bg-card-bg rounded-xl border border-border-subtle shadow-sm overflow-hidden space-y-4 p-5">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-border-subtle">
    <div>
      <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface flex items-center gap-2">
        <span class="material-symbols-outlined text-[#007fa3]">commute</span>
        Log Sesi Patroli Sisi Udara
      </h2>
      <p class="font-body-sm text-body-sm text-secondary">
        Rekaman per sesi patroli runway, perimeter, dan koordinasi dengan ATC Airside Juanda.
      </p>
    </div>
    <div class="flex items-center gap-3 flex-wrap">
      <div class="flex items-center gap-1.5 text-xs text-secondary font-body-sm">
        <label for="perPagePatroliTop" class="font-medium whitespace-nowrap text-secondary flex items-center gap-1">
          <span class="material-symbols-outlined text-sm text-[#007fa3]">tune</span>
          Tampilkan:
        </label>
        <select id="perPagePatroliTop" onchange="changePerPage(this.value)" class="h-8 pl-2.5 pr-7 py-1 text-xs font-semibold rounded-lg border border-border-subtle bg-white text-on-surface focus:border-injourney-teal focus:ring-1 focus:ring-injourney-teal cursor-pointer shadow-2xs">
          <option value="10" {{ ($perPage ?? 10) == 10 ? 'selected' : '' }}>10 data</option>
          <option value="20" {{ ($perPage ?? 10) == 20 ? 'selected' : '' }}>20 data</option>
          <option value="50" {{ ($perPage ?? 10) == 50 ? 'selected' : '' }}>50 data</option>
          <option value="100" {{ ($perPage ?? 10) == 100 ? 'selected' : '' }}>100 data</option>
        </select>
      </div>
      <div class="h-4 w-px bg-border-subtle hidden sm:block"></div>
      <span class="text-xs text-secondary font-medium">Total: <strong>{{ $laporans->total() }}</strong> sesi tercatat</span>
      <a href="{{ route('admin.export.excel') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-border-subtle bg-white text-xs font-semibold text-secondary hover:text-on-surface hover:bg-slate-50 transition-colors">
        <span class="material-symbols-outlined text-sm">download</span>
        Export Excel
      </a>
    </div>
  </div>

  <!-- Patrol Table -->
  <div class="logbook-table-wrapper overflow-x-auto rounded-lg border border-border-subtle">
    <table class="w-full text-left border-collapse text-xs">
      <thead>
        <tr class="bg-surface-container-low text-secondary font-label-md uppercase tracking-wider border-b border-border-subtle">
          <th class="py-3 px-3.5">ID Patroli</th>
          <th class="py-3 px-3.5">Tanggal &amp; Waktu</th>
          <th class="py-3 px-3.5">Petugas Pelapor</th>
          <th class="py-3 px-3.5 text-center">Call ATC</th>
          <th class="py-3 px-3.5">Cuaca</th>
          <th class="py-3 px-3.5">Area &amp; Grid</th>
          <th class="py-3 px-3.5">Temuan Satwa</th>
          <th class="py-3 px-3.5 text-center">Status BA</th>
          <th class="py-3 px-3.5 text-center">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-border-subtle">
        @forelse($laporans as $lap)
          @php
            $isPending = ($lap->status === 'belum' || empty($lap->status));
            $regId = $lap->no_laporan ?: ('WHM-' . ($lap->tanggal ? $lap->tanggal->format('Y') : '2026') . '-' . str_pad($lap->id, 4, '0', STR_PAD_LEFT));
            $patrolHex = substr(md5($lap->id . ($lap->tanggal ? $lap->tanggal->format('Ymd') : '20260101')), 0, 8);
          @endphp
          <tr class="hover:bg-surface-container-low/50 transition-colors">
            <!-- ID Patroli -->
            <td class="py-3 px-3.5 whitespace-nowrap">
              <span class="font-code-coordinate text-code-coordinate font-bold text-injourney-teal bg-cyan-50 px-2 py-0.5 rounded border border-cyan-200">
                {{ $patrolHex }}
              </span>
              <span class="text-[10px] text-slate-400 block mt-0.5">{{ $regId }}</span>
            </td>

            <!-- Tanggal & Waktu -->
            <td class="py-3 px-3.5 whitespace-nowrap">
              <div class="font-semibold text-on-surface">{{ $lap->tanggal ? $lap->tanggal->format('d/m/Y') : '-' }}</div>
              <div class="text-secondary text-[11px]">{{ $lap->created_at ? $lap->created_at->format('H:i:s') : '08:00:00' }} WIB</div>
            </td>

            <!-- Petugas -->
            <td class="py-3 px-3.5">
              <div class="font-semibold text-on-surface">{{ $lap->nama_petugas }}</div>
              <div class="text-secondary text-[11px]">{{ $lap->unit_kerja ?: 'AMC Airside' }}</div>
            </td>

            <!-- Call ATC -->
            <td class="py-3 px-3.5 text-center whitespace-nowrap">
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                TIDAK
              </span>
            </td>

            <!-- Cuaca -->
            <td class="py-3 px-3.5 whitespace-nowrap">
              <span class="inline-flex items-center gap-1 font-medium text-slate-700">
                <span class="material-symbols-outlined text-xs text-amber-500">wb_sunny</span>
                {{ strtoupper($lap->kondisi_cuaca ?: 'CERAH') }}
              </span>
            </td>

            <!-- Area & Grid -->
            <td class="py-3 px-3.5">
              <div class="font-medium text-on-surface">{{ $lap->area_inspeksi ?: 'Sisi Udara' }}</div>
              <div class="font-code-coordinate text-code-coordinate text-xs font-bold text-injourney-teal">
                GRID {{ $lap->grid_lokasi ?: 'K-10' }}
              </div>
            </td>

            <!-- Ringkasan Temuan -->
            <td class="py-3 px-3.5">
              @if($lap->detailSatwa && $lap->detailSatwa->count() > 0)
                <div class="space-y-0.5">
                  @foreach($lap->detailSatwa->take(2) as $ds)
                    <div class="text-[11px] text-slate-700 font-medium">
                      &bull; {{ $ds->nama_satwa }}: <strong>{{ $ds->jumlah }} ekor</strong>
                    </div>
                  @endforeach
                  @if($lap->detailSatwa->count() > 2)
                    <span class="text-[10px] text-slate-400 italic">+{{ $lap->detailSatwa->count() - 2 }} spesies lainnya</span>
                  @endif
                </div>
              @else
                <span class="text-secondary italic">Tidak ada temuan</span>
              @endif
            </td>

            <!-- Status BA -->
            <td class="py-3 px-3.5 text-center whitespace-nowrap">
              @if(!$isPending)
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                  Terverifikasi
                </span>
              @else
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                  <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                  Antrean BA
                </span>
              @endif
            </td>

            <!-- Aksi -->
            <td class="py-3 px-3.5 text-center whitespace-nowrap">
              <div class="inline-flex items-center gap-1">
                <button type="button" onclick="openReportDetailAjax({{ $lap->id }})" class="p-1 rounded text-secondary hover:text-[#007fa3] hover:bg-slate-100 transition-colors" title="Lihat Pratinjau Patroli">
                  <span class="material-symbols-outlined text-base">visibility</span>
                </button>
                <a href="{{ route('admin.laporan.cetak', $lap->id) }}" target="_blank" class="p-1 rounded text-secondary hover:text-slate-800 hover:bg-slate-100 transition-colors" title="Cetak Berita Acara">
                  <span class="material-symbols-outlined text-base">print</span>
                </a>
                @if(Route::has('admin.laporan.pdf'))
                  <a href="{{ route('admin.laporan.pdf', $lap->id) }}" class="p-1 rounded text-emerald-700 hover:text-emerald-900 hover:bg-emerald-50 transition-colors" title="Unduh PDF Resmi">
                    <span class="material-symbols-outlined text-base">picture_as_pdf</span>
                  </a>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" class="text-center py-10 text-secondary">
              Tidak ada data sesi patroli yang ditemukan.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- Pagination Footer -->
  <div class="pt-3 border-t border-border-subtle flex flex-wrap items-center justify-between gap-3 text-xs text-secondary">
    <div class="flex items-center gap-3 sm:gap-4 flex-wrap">
      <span>Total: <strong class="text-on-surface font-semibold">{{ $laporans->total() }}</strong> sesi patroli</span>
      <div class="h-4 w-px bg-border-subtle hidden sm:block"></div>
      <div class="flex items-center gap-1.5">
        <label for="perPagePatroliBottom" class="text-secondary font-medium whitespace-nowrap">Tampilkan per halaman:</label>
        <select id="perPagePatroliBottom" onchange="changePerPage(this.value)" class="h-8 pl-2.5 pr-7 py-1 text-xs font-semibold rounded-lg border border-border-subtle bg-white text-on-surface focus:border-injourney-teal focus:ring-1 focus:ring-injourney-teal cursor-pointer shadow-2xs">
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
