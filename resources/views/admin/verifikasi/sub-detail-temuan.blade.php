<!-- SUB-VIEW: DETAIL TEMUAN INDIVIDU SATWA -->
<section class="bg-card-bg rounded-xl border border-border-subtle shadow-sm overflow-hidden space-y-4 p-5">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-border-subtle">
    <div>
      <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface flex items-center gap-2">
        <span class="material-symbols-outlined text-[#007fa3]">checklist</span>
        Detail Temuan Individu Satwa Liar
      </h2>
      <p class="font-body-sm text-body-sm text-secondary">
        Daftar rincian individu per jenis satwa, titik lokasi pengamatan, dan kuantitas ekor di sisi udara.
      </p>
    </div>
    <div class="flex items-center gap-3 flex-wrap">
      <div class="flex items-center gap-1.5 text-xs text-secondary font-body-sm">
        <label for="perPageDetailTop" class="font-medium whitespace-nowrap text-secondary flex items-center gap-1">
          <span class="material-symbols-outlined text-sm text-[#007fa3]">tune</span>
          Tampilkan:
        </label>
        <select id="perPageDetailTop" onchange="changePerPage(this.value)" class="h-8 pl-2.5 pr-7 py-1 text-xs font-semibold rounded-lg border border-border-subtle bg-white text-on-surface focus:border-injourney-teal focus:ring-1 focus:ring-injourney-teal cursor-pointer shadow-2xs">
          <option value="10" {{ ($perPage ?? 10) == 10 ? 'selected' : '' }}>10 data</option>
          <option value="20" {{ ($perPage ?? 10) == 20 ? 'selected' : '' }}>20 data</option>
          <option value="50" {{ ($perPage ?? 10) == 50 ? 'selected' : '' }}>50 data</option>
          <option value="100" {{ ($perPage ?? 10) == 100 ? 'selected' : '' }}>100 data</option>
        </select>
      </div>
      <div class="h-4 w-px bg-border-subtle hidden sm:block"></div>
      <span class="text-xs text-secondary font-medium">Total: <strong>{{ $detailTemuanList->total() }}</strong> catatan temuan</span>
    </div>
  </div>

  <!-- Detail Temuan Table -->
  <div class="overflow-x-auto rounded-lg border border-border-subtle">
    <table class="w-full text-left border-collapse text-xs">
      <thead>
        <tr class="bg-surface-container-low text-secondary font-label-md uppercase tracking-wider border-b border-border-subtle">
          <th class="py-3 px-3.5">No</th>
          <th class="py-3 px-3.5">ID Patroli</th>
          <th class="py-3 px-3.5">Tanggal</th>
          <th class="py-3 px-3.5">Titik / Zona Koordinat</th>
          <th class="py-3 px-3.5">Jenis Satwa Liar</th>
          <th class="py-3 px-3.5 text-center">Jumlah</th>
          <th class="py-3 px-3.5">Petugas Patroli</th>
          <th class="py-3 px-3.5 text-center">Foto Bukti</th>
          <th class="py-3 px-3.5 text-center">Status Laporan</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-border-subtle">
        @forelse($detailTemuanList as $idx => $item)
          @php
            $lap = $item->laporan;
            $patrolHex = $lap ? substr(md5($lap->id . ($lap->tanggal ? $lap->tanggal->format('Ymd') : '20260101')), 0, 8) : 'AUTO-' . $item->id;
            $foto = $item->fotoLaporan->first();
            $hasFoto = ($foto && $foto->nama_file) || $item->foto_path;
            $fotoUrl = $foto ? asset('storage/uploads/satwa/' . $foto->nama_file) : ($item->foto_path ? asset('images/' . $item->foto_path) : null);
          @endphp
          <tr class="hover:bg-surface-container-low/50 transition-colors">
            <td class="py-3 px-3.5 text-secondary font-medium">
              {{ $detailTemuanList->firstItem() + $idx }}
            </td>
            <td class="py-3 px-3.5 whitespace-nowrap">
              <span class="font-code-coordinate text-code-coordinate font-bold text-injourney-teal bg-cyan-50 px-2 py-0.5 rounded border border-cyan-200">
                {{ $patrolHex }}
              </span>
            </td>
            <td class="py-3 px-3.5 whitespace-nowrap text-on-surface">
              {{ $lap && $lap->tanggal ? $lap->tanggal->format('d/m/Y') : '-' }}
            </td>
            <td class="py-3 px-3.5">
              <span class="font-code-coordinate text-code-coordinate font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded">
                GRID {{ $item->grid ?: ($lap?->grid_lokasi ?: 'K-10') }}
              </span>
            </td>
            <td class="py-3 px-3.5 font-semibold text-on-surface">
              {{ $item->nama_satwa }}
            </td>
            <td class="py-3 px-3.5 text-center font-bold text-slate-900 whitespace-nowrap">
              <span class="px-2 py-0.5 bg-cyan-50 text-cyan-800 rounded font-code-coordinate">
                {{ $item->jumlah }} ekor
              </span>
            </td>
            <td class="py-3 px-3.5 text-secondary">
              {{ $lap ? $lap->nama_petugas : '-' }}
            </td>
            <td class="py-3 px-3.5 text-center whitespace-nowrap">
              @if($hasFoto && $fotoUrl)
                <button type="button" onclick="openPhotoPreview('{{ $fotoUrl }}', '{{ $item->nama_satwa }}')" class="inline-flex items-center gap-1 text-injourney-teal hover:underline font-semibold" title="Lihat Foto Dokumentasi">
                  <span class="material-symbols-outlined text-base">image</span>
                  Lihat
                </button>
              @else
                <span class="text-slate-400 italic text-[11px]">Tidak ada</span>
              @endif
            </td>
            <td class="py-3 px-3.5 text-center whitespace-nowrap">
              @if($lap && $lap->status === 'sudah')
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                  Terverifikasi
                </span>
              @else
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                  <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                  Belum Ditangani
                </span>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" class="text-center py-10 text-secondary">
              Tidak ada data temuan satwa liar yang sesuai.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- Pagination Footer -->
  <div class="pt-3 border-t border-border-subtle flex flex-wrap items-center justify-between gap-3 text-xs text-secondary">
    <div class="flex items-center gap-3 sm:gap-4 flex-wrap">
      <span>Total: <strong class="text-on-surface font-semibold">{{ $detailTemuanList->total() }}</strong> rincian temuan satwa</span>
      <div class="h-4 w-px bg-border-subtle hidden sm:block"></div>
      <div class="flex items-center gap-1.5">
        <label for="perPageDetailBottom" class="text-secondary font-medium whitespace-nowrap">Tampilkan per halaman:</label>
        <select id="perPageDetailBottom" onchange="changePerPage(this.value)" class="h-8 pl-2.5 pr-7 py-1 text-xs font-semibold rounded-lg border border-border-subtle bg-white text-on-surface focus:border-injourney-teal focus:ring-1 focus:ring-injourney-teal cursor-pointer shadow-2xs">
          <option value="10" {{ ($perPage ?? 10) == 10 ? 'selected' : '' }}>10 data</option>
          <option value="20" {{ ($perPage ?? 10) == 20 ? 'selected' : '' }}>20 data</option>
          <option value="50" {{ ($perPage ?? 10) == 50 ? 'selected' : '' }}>50 data</option>
          <option value="100" {{ ($perPage ?? 10) == 100 ? 'selected' : '' }}>100 data</option>
        </select>
      </div>
    </div>
    <div>
      {{ $detailTemuanList->links() }}
    </div>
  </div>
</section>
