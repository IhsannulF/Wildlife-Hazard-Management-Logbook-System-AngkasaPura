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
    <div class="flex items-center gap-3 flex-wrap">
      <div class="flex items-center gap-1.5 text-xs text-secondary font-body-sm">
        <label for="perPageJenisTop" class="font-medium whitespace-nowrap text-secondary flex items-center gap-1">
          <span class="material-symbols-outlined text-sm text-[#007fa3]">tune</span>
          Tampilkan:
        </label>
        <select id="perPageJenisTop" onchange="changePerPage(this.value)" class="h-8 pl-2.5 pr-7 py-1 text-xs font-semibold rounded-lg border border-border-subtle bg-white text-on-surface focus:border-injourney-teal focus:ring-1 focus:ring-injourney-teal cursor-pointer shadow-2xs">
          <option value="10" {{ ($perPage ?? 10) == 10 ? 'selected' : '' }}>10 data</option>
          <option value="20" {{ ($perPage ?? 10) == 20 ? 'selected' : '' }}>20 data</option>
          <option value="50" {{ ($perPage ?? 10) == 50 ? 'selected' : '' }}>50 data</option>
          <option value="100" {{ ($perPage ?? 10) == 100 ? 'selected' : '' }}>100 data</option>
        </select>
      </div>
      <div class="h-4 w-px bg-border-subtle hidden sm:block"></div>
      <span class="text-xs text-secondary font-medium">Total: <strong>{{ method_exists($masterJenisList, 'total') ? $masterJenisList->total() : count($masterJenisList) }}</strong> Spesies Terdata</span>
      <a href="{{ route('admin.manajemen') }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-injourney-teal text-white text-xs font-semibold hover:bg-injourney-dark-teal transition-colors shadow-2xs">
        <span class="material-symbols-outlined text-sm">add</span>
        Kelola Katalog
      </a>
    </div>
  </div>

  <!-- Master Jenis Table -->
  <div class="logbook-table-wrapper overflow-x-auto rounded-lg border border-border-subtle">
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

  <!-- Pagination Footer -->
  <div class="pt-3 border-t border-border-subtle flex flex-wrap items-center justify-between gap-3 text-xs text-secondary">
    <div class="flex items-center gap-3 sm:gap-4 flex-wrap">
      <span>Total: <strong class="text-on-surface font-semibold">{{ method_exists($masterJenisList, 'total') ? $masterJenisList->total() : count($masterJenisList) }}</strong> spesies satwa</span>
      <div class="h-4 w-px bg-border-subtle hidden sm:block"></div>
      <div class="flex items-center gap-1.5">
        <label for="perPageJenisBottom" class="text-secondary font-medium whitespace-nowrap">Tampilkan per halaman:</label>
        <select id="perPageJenisBottom" onchange="changePerPage(this.value)" class="h-8 pl-2.5 pr-7 py-1 text-xs font-semibold rounded-lg border border-border-subtle bg-white text-on-surface focus:border-injourney-teal focus:ring-1 focus:ring-injourney-teal cursor-pointer shadow-2xs">
          <option value="10" {{ ($perPage ?? 10) == 10 ? 'selected' : '' }}>10 data</option>
          <option value="20" {{ ($perPage ?? 10) == 20 ? 'selected' : '' }}>20 data</option>
          <option value="50" {{ ($perPage ?? 10) == 50 ? 'selected' : '' }}>50 data</option>
          <option value="100" {{ ($perPage ?? 10) == 100 ? 'selected' : '' }}>100 data</option>
        </select>
      </div>
    </div>
    @if(method_exists($masterJenisList, 'links'))
      <div>
        {{ $masterJenisList->links() }}
      </div>
    @endif
  </div>
</section>
