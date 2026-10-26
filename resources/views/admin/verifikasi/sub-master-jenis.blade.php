<!-- SUB-VIEW: MASTER JENIS SATWA LIAR JUANDA -->
<section class="bg-card-bg rounded-xl border border-border-subtle shadow-sm overflow-hidden space-y-4 p-5">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-border-subtle">
    <div>
      <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface flex items-center gap-2">
        <span class="material-symbols-outlined text-[#007fa3]">flutter_dash</span>
        Master Jenis Satwa Liar (Airside Wildlife Species)
      </h2>
      <p class="font-body-sm text-body-sm text-secondary">
        Katalog taksonomi spesies satwa liar lokal yang terpantau pada logbook patroli keselamatan bandara.
      </p>
    </div>
    <div class="flex items-center gap-2">
      <span class="text-xs text-secondary font-medium">Total: <strong>{{ count($masterJenisList) }}</strong> Spesies Terdata</span>
      <a href="{{ route('admin.manajemen') }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-injourney-teal text-white text-xs font-semibold hover:bg-injourney-dark-teal transition-colors shadow-2xs">
        <span class="material-symbols-outlined text-sm">add</span>
        Kelola Katalog
      </a>
    </div>
  </div>

  <!-- Master Jenis Table -->
  <div class="overflow-x-auto rounded-lg border border-border-subtle">
    <table class="w-full text-left border-collapse text-xs">
      <thead>
        <tr class="bg-surface-container-low text-secondary font-label-md uppercase tracking-wider border-b border-border-subtle">
          <th class="py-3 px-3.5">Spesimen Foto</th>
          <th class="py-3 px-3.5">Nama Jenis Satwa</th>
          <th class="py-3 px-3.5">Kategori Ordo / Takson</th>
          <th class="py-3 px-3.5 text-center">Tingkat Risiko ICAO</th>
          <th class="py-3 px-3.5">Grid Hotspot Utama</th>
          <th class="py-3 px-3.5">Karakteristik &amp; Perilaku</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-border-subtle">
        @foreach($masterJenisList as $j)
          @php
            $isObj = is_object($j);
            $nama = $isObj ? $j->nama : $j['nama'];
            $kategori = $isObj ? ($j->kategori ?? 'Avian / Burung') : ($j['kategori'] ?? 'Avian');
            $risiko = $isObj ? ($j->tingkat_risiko ?? 'Sedang') : ($j['tingkat_risiko'] ?? 'Sedang');
            $hotspot = $isObj ? ($j->grid_hotspot ?? 'K-10') : ($j['grid_hotspot'] ?? 'K-10');
            $deskripsi = $isObj ? ($j->deskripsi ?? 'Aktivitas pengamatan berkala di sisi udara.') : ($j['deskripsi'] ?? '-');
            $fotoPath = $isObj ? ($j->foto_path ?? null) : ($j['foto_path'] ?? null);
            $imgSrc = $fotoPath ? asset('images/' . $fotoPath) : asset('images/biawak.jpeg');
          @endphp
          <tr class="hover:bg-surface-container-low/50 transition-colors">
            <!-- Foto -->
            <td class="py-3 px-3.5 whitespace-nowrap">
              <div class="w-12 h-12 rounded-lg border border-border-subtle bg-slate-100 overflow-hidden">
                <img src="{{ $imgSrc }}" alt="{{ $nama }}" class="w-full h-full object-cover">
              </div>
            </td>

            <!-- Nama Satwa -->
            <td class="py-3 px-3.5 font-bold text-on-surface">
              {{ $nama }}
            </td>

            <!-- Kategori -->
            <td class="py-3 px-3.5 whitespace-nowrap text-secondary font-medium">
              {{ $kategori }}
            </td>

            <!-- Risiko ICAO -->
            <td class="py-3 px-3.5 text-center whitespace-nowrap">
              @if(strtolower($risiko) === 'kritis')
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200">
                  <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                  Kritis (Hazard A)
                </span>
              @elseif(strtolower($risiko) === 'tinggi')
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                  <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                  Tinggi (Hazard B)
                </span>
              @else
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-800 border border-slate-200">
                  <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                  Sedang
                </span>
              @endif
            </td>

            <!-- Grid Hotspot -->
            <td class="py-3 px-3.5 whitespace-nowrap">
              <span class="font-code-coordinate text-code-coordinate font-bold text-injourney-teal bg-cyan-50 px-2 py-0.5 rounded border border-cyan-200">
                GRID {{ $hotspot }}
              </span>
            </td>

            <!-- Deskripsi -->
            <td class="py-3 px-3.5 text-secondary">
              {{ $deskripsi }}
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</section>
