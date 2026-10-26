<!-- SUB-VIEW: MASTER TITIK & ZONA PENGAMATAN SISI UDARA JUANDA -->
<section class="bg-card-bg rounded-xl border border-border-subtle shadow-sm overflow-hidden space-y-4 p-5">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-border-subtle">
    <div>
      <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface flex items-center gap-2">
        <span class="material-symbols-outlined text-[#007fa3]">location_on</span>
        Master Titik &amp; Zona Pengamatan Sisi Udara (Juanda)
      </h2>
      <p class="font-body-sm text-body-sm text-secondary">
        Katalog titik pemantauan rutin (Titik 1 - Titik 8) dan pembagian zona operasi sisi udara sesuai formulir safety.
      </p>
    </div>
    <div class="flex items-center gap-2">
      <span class="text-xs text-secondary font-medium">Total: <strong>{{ count($masterTitikList) }}</strong> Titik &amp; Sektor Terdaftar</span>
    </div>
  </div>

  <!-- Master Titik Table -->
  <div class="overflow-x-auto rounded-lg border border-border-subtle">
    <table class="w-full text-left border-collapse text-xs">
      <thead>
        <tr class="bg-surface-container-low text-secondary font-label-md uppercase tracking-wider border-b border-border-subtle">
          <th class="py-3 px-3.5">Kode Titik / Zona</th>
          <th class="py-3 px-3.5">Nama Area Pengamatan</th>
          <th class="py-3 px-3.5">Grid Koordinat Utama</th>
          <th class="py-3 px-3.5">Kategori Wilayah</th>
          <th class="py-3 px-3.5">Deskripsi Lokasi &amp; Karakteristik Lapangan</th>
          <th class="py-3 px-3.5 text-center">Status Operasional</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-border-subtle">
        @foreach($masterTitikList as $titik)
          @php
            $isCritical = str_contains($titik['tipe'], 'Critical');
          @endphp
          <tr class="hover:bg-surface-container-low/50 transition-colors">
            <!-- Kode Titik -->
            <td class="py-3 px-3.5 whitespace-nowrap">
              <span class="font-bold text-slate-800 bg-slate-100 px-2.5 py-1 rounded border border-slate-200">
                {{ $titik['kode'] }}
              </span>
            </td>

            <!-- Nama Area -->
            <td class="py-3 px-3.5 font-semibold text-on-surface">
              {{ $titik['zona'] }}
            </td>

            <!-- Grid Koordinat -->
            <td class="py-3 px-3.5 whitespace-nowrap">
              <span class="font-code-coordinate text-code-coordinate font-bold {{ $isCritical ? 'text-warning-rose bg-rose-50 border border-rose-200' : 'text-injourney-teal bg-cyan-50 border border-cyan-200' }} px-2 py-0.5 rounded">
                GRID {{ $titik['grid'] }}
              </span>
            </td>

            <!-- Kategori -->
            <td class="py-3 px-3.5 whitespace-nowrap">
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold {{ $isCritical ? 'bg-amber-50 text-amber-800' : 'bg-slate-100 text-slate-700' }}">
                <span class="material-symbols-outlined text-xs">{{ $isCritical ? 'warning' : 'place' }}</span>
                {{ $titik['tipe'] }}
              </span>
            </td>

            <!-- Deskripsi -->
            <td class="py-3 px-3.5 text-secondary">
              {{ $titik['deskripsi'] }}
            </td>

            <!-- Status -->
            <td class="py-3 px-3.5 text-center whitespace-nowrap">
              <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                {{ $titik['status'] }}
              </span>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</section>
