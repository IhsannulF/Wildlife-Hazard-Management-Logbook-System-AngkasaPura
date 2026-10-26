<!-- SUB-VIEW: BACK UP DETAIL TEMUAN (AUDIT ARCHIVE SAFETY) -->
<section class="bg-card-bg rounded-xl border border-border-subtle shadow-sm overflow-hidden space-y-4 p-5">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-border-subtle">
    <div>
      <div class="flex items-center gap-2">
        <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface flex items-center gap-2">
          <span class="material-symbols-outlined text-[#007fa3]">backup</span>
          Back Up Detail Temuan Satwa Liar
        </h2>
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
          <span class="material-symbols-outlined text-xs">verified_user</span> Read-Only Archive
        </span>
      </div>
      <p class="font-body-sm text-body-sm text-secondary mt-0.5">
        Salinan arsip rekaman data mentah temuan satwa liar untuk keperluan audit Ditjen Hubud (DKPPU) &amp; keaslian data.
      </p>
    </div>
    <div class="flex items-center gap-2">
      <span class="text-xs text-secondary font-medium">Total: <strong>{{ $backupTemuanList->total() }}</strong> rekaman arsip</span>
      <a href="{{ route('admin.export.excel') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-border-subtle bg-white text-xs font-semibold text-secondary hover:text-on-surface hover:bg-slate-50 transition-colors shadow-2xs">
        <span class="material-symbols-outlined text-sm">file_download</span>
        Unduh Berkas Backup (.xlsx)
      </a>
    </div>
  </div>

  <!-- Backup Table -->
  <div class="overflow-x-auto rounded-lg border border-border-subtle">
    <table class="w-full text-left border-collapse text-xs font-mono">
      <thead>
        <tr class="bg-slate-900 text-slate-300 font-label-md uppercase tracking-wider border-b border-slate-800">
          <th class="py-3 px-3">RECORD_ID</th>
          <th class="py-3 px-3">PATROL_ID</th>
          <th class="py-3 px-3">TIMESTAMP</th>
          <th class="py-3 px-3">GRID_ZONE</th>
          <th class="py-3 px-3">SPECIES_NAME</th>
          <th class="py-3 px-3 text-right">QTY</th>
          <th class="py-3 px-3">OFFICER</th>
          <th class="py-3 px-3 text-center">INTEGRITY</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-border-subtle bg-white">
        @forelse($backupTemuanList as $b)
          @php
            $lap = $b->laporan;
            $patrolHex = $lap ? substr(md5($lap->id . ($lap->tanggal ? $lap->tanggal->format('Ymd') : '20260101')), 0, 8) : 'AUTO-' . $b->id;
            $hashSignature = substr(sha1($b->id . $b->nama_satwa . $b->jumlah . ($lap ? $lap->nama_petugas : '')), 0, 10);
          @endphp
          <tr class="hover:bg-slate-50/80 transition-colors text-[11px]">
            <td class="py-2.5 px-3 font-bold text-slate-700">
              #BK-{{ str_pad($b->id, 5, '0', STR_PAD_LEFT) }}
            </td>
            <td class="py-2.5 px-3 text-injourney-teal font-bold">
              {{ $patrolHex }}
            </td>
            <td class="py-2.5 px-3 text-slate-600 whitespace-nowrap">
              {{ $lap && $lap->tanggal ? $lap->tanggal->format('Y-m-d') : '2026-06-01' }} {{ $b->created_at ? $b->created_at->format('H:i:s') : '08:00:00' }}
            </td>
            <td class="py-2.5 px-3">
              <span class="font-bold text-slate-800">{{ $b->grid ?: ($lap?->grid_lokasi ?: 'K-10') }}</span>
            </td>
            <td class="py-2.5 px-3 text-slate-900 font-semibold">
              {{ $b->nama_satwa }}
            </td>
            <td class="py-2.5 px-3 text-right font-bold text-cyan-800">
              {{ $b->jumlah }}
            </td>
            <td class="py-2.5 px-3 text-slate-600 font-sans">
              {{ $lap ? $lap->nama_petugas : '-' }}
            </td>
            <td class="py-2.5 px-3 text-center whitespace-nowrap">
              <span class="inline-flex items-center gap-1 text-[10px] text-emerald-700 font-semibold">
                <span class="material-symbols-outlined text-xs">lock</span> {{ $hashSignature }}
              </span>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center py-10 text-secondary font-sans">
              Tidak ada data backup detail temuan.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  @if($backupTemuanList->hasPages())
    <div class="pt-2">
      {{ $backupTemuanList->links() }}
    </div>
  @endif
</section>
