<!-- 4. BOTTOM SECTION: ACTIVE INCIDENTS AUDIT TABLE & TEMPORAL DISPERSAL LOG -->
<div class="bg-card-bg rounded-xl border border-border-subtle shadow-sm p-3.5 sm:p-4 mb-6">
  
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-2.5 pb-2.5 border-b border-border-subtle mb-3.5">
    <div>
      <h2 class="text-sm sm:text-base font-bold text-slate-navy">
        Daftar Log Koordinat Grid Aktif &amp; Pemantauan Intervensi
      </h2>
      <p class="text-[11px] text-secondary mt-0.5">
        Integrasi langsung dengan Master Logbook Sisi Udara dan Berita Acara Ditjen Perhubungan Udara.
      </p>
    </div>

    <div class="flex items-center gap-2">
      <div class="relative">
        <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-secondary text-sm">search</span>
        <input id="gridTableSearch" onkeyup="searchGridTable()" class="pl-8 pr-2.5 py-1 text-[11px] border border-border-subtle rounded-lg focus:outline-none focus:border-injourney-teal bg-canvas-light w-52" placeholder="Cari Grid / Satwa..." type="text"/>
      </div>
    </div>
  </div>

  <!-- High-Density Responsive Table Container with Pagination -->
  <div id="tblGridAuditContainer">
    <div class="overflow-x-auto">
      <table id="tblGridAudit" class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-surface-container-low text-secondary text-[10px] uppercase font-bold tracking-wider border-b border-border-subtle">
            <th class="py-2.5 px-2.5 sm:px-3 w-[85px] whitespace-nowrap">Kode Grid</th>
            <th class="py-2.5 px-2.5 sm:px-3 min-w-[130px]">Zona Lokasi</th>
            <th class="py-2.5 px-2.5 sm:px-3 min-w-[110px]">Spesies Dominan</th>
            <th class="py-2.5 px-2.5 sm:px-3 min-w-[115px] whitespace-nowrap">Tingkat Risiko</th>
            <th class="py-2.5 px-2.5 sm:px-3 min-w-[95px] whitespace-nowrap">Jumlah Laporan</th>
            <th class="py-2.5 px-2.5 sm:px-3 min-w-[140px]">Aksi Mitigasi Terakhir</th>
            <th class="py-2.5 px-2.5 sm:px-3 min-w-[105px] whitespace-nowrap">Status Terkini</th>
            <th class="py-2.5 px-2.5 sm:px-3 text-right min-w-[80px] whitespace-nowrap">Tindakan</th>
          </tr>
        </thead>
        <tbody id="tblGridAuditBody" class="divide-y divide-border-subtle text-[11px]">
          @forelse($tableGrids as $gRow)
            <tr onclick="selectGrid('{{ $gRow['grid'] }}')" class="audit-row hover:bg-surface-container-low/60 transition-colors cursor-pointer group">
              <td class="py-2.5 px-2.5 sm:px-3 whitespace-nowrap">
                <span class="inline-flex items-center justify-center font-code-coordinate font-bold text-rose-600 bg-rose-50 border border-rose-200/90 px-2 py-0.5 rounded text-[11px] tracking-tight whitespace-nowrap shadow-2xs">
                  Grid&nbsp;{{ $gRow['grid'] }}
                </span>
              </td>
              <td class="py-2.5 px-2.5 sm:px-3 font-medium text-slate-navy leading-snug">{{ $gRow['zone_name'] }}</td>
              <td class="py-2.5 px-2.5 sm:px-3 leading-snug">
                <span class="font-semibold text-slate-navy">{{ $gRow['dominant_species'] }}</span>
              </td>
              <td class="py-2.5 px-2.5 sm:px-3 whitespace-nowrap">
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold {{ $gRow['risk_badge_class'] }}">
                  <span class="w-1.5 h-1.5 rounded-full {{ $gRow['risk_level'] === 'Kritis' ? 'bg-warning-rose animate-pulse' : ($gRow['risk_level'] === 'Sedang' ? 'bg-alert-amber' : 'bg-injourney-teal') }}"></span>
                  {{ $gRow['risk_category'] }}
                </span>
              </td>
              <td class="py-2.5 px-2.5 sm:px-3 font-code-coordinate font-bold text-slate-navy whitespace-nowrap">{{ $gRow['count'] }} Kejadian</td>
              <td class="py-2.5 px-2.5 sm:px-3 text-secondary leading-snug">{{ $gRow['last_mitigation'] }}</td>
              <td class="py-2.5 px-2.5 sm:px-3 whitespace-nowrap">
                @if($gRow['status'] === 'Belum Ditangani')
                  <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-800 bg-amber-100 px-2 py-0.5 rounded-full">
                    <span class="w-1.5 h-1.5 rounded-full bg-alert-amber"></span>
                    Belum Ditangani
                  </span>
                @else
                  <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-full">
                    <span class="w-1.5 h-1.5 rounded-full bg-resolved-emerald"></span>
                    Telah Ditangani
                  </span>
                @endif
              </td>
              <td class="py-2.5 px-2.5 sm:px-3 text-right whitespace-nowrap">
                <a href="{{ route('admin.dashboard', ['search' => $gRow['grid']]) }}" class="inline-flex items-center gap-1 text-injourney-teal hover:text-injourney-dark-teal font-bold hover:underline text-xs">
                  <span>Buka Log</span>
                  <span class="material-symbols-outlined text-xs">arrow_forward</span>
                </a>
              </td>
            </tr>
          @empty
            <tr id="auditEmptyRow">
              <td colspan="8" class="text-center py-6 text-secondary">Tidak ada data grid yang cocok dengan filter.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Pagination Controls for Table (10 per page) -->
    <div id="tablePaginationBar" class="mt-3.5 pt-3 border-t border-border-subtle flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
      <div class="text-secondary text-[11px]">
        Menampilkan <span id="auditPageStart" class="font-bold text-slate-navy">1</span> - <span id="auditPageEnd" class="font-bold text-slate-navy">10</span> dari <span id="auditTotalRows" class="font-bold text-slate-navy">{{ count($tableGrids) }}</span> data grid aktif
      </div>
      
      <div class="flex items-center gap-1.5">
        <!-- Previous Button -->
        <button type="button" id="btnAuditPrev" onclick="changeAuditPage(-1)" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-border-subtle bg-card-bg hover:bg-surface-container-low text-slate-navy disabled:opacity-40 disabled:cursor-not-allowed font-medium text-xs transition-colors shadow-2xs cursor-pointer">
          <span class="material-symbols-outlined text-sm">chevron_left</span>
          <span>Sebelumnya</span>
        </button>

        <!-- Dynamic Page Number Buttons -->
        <div id="auditPageNumbers" class="flex items-center gap-1">
        </div>

        <!-- Next Button -->
        <button type="button" id="btnAuditNext" onclick="changeAuditPage(1)" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-border-subtle bg-card-bg hover:bg-surface-container-low text-slate-navy disabled:opacity-40 disabled:cursor-not-allowed font-medium text-xs transition-colors shadow-2xs cursor-pointer">
          <span>Selanjutnya</span>
          <span class="material-symbols-outlined text-sm">chevron_right</span>
        </button>
      </div>
    </div>
  </div>

</div>
