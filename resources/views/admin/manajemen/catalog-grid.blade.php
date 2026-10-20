<!-- GRID KATALOG KARTU SATWA INTERAKTIF -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4 sm:gap-5" id="catalogGridView">
  @foreach($satwaList as $satwa)
    @php
      $cat = strtolower($satwa->kategori ?: (str_contains($satwa->nama, 'Burung') ? 'burung' : (str_contains($satwa->nama, 'Biawak') || str_contains($satwa->nama, 'Ular') ? 'reptil' : 'mamalia')));
      $risk = strtolower($satwa->tingkat_risiko ?: 'sedang');
      $habitat = strtolower($satwa->grid_hotspot ?: 'runway');
      $idCode = 'WHMS-' . strtoupper(substr($cat, 0, 3)) . '-' . str_pad($satwa->id, 3, '0', STR_PAD_LEFT);
      $gridDisplay = $satwa->grid_hotspot ?: 'K-10';
      $descText = $satwa->deskripsi ?: 'Pemantauan intensif di area perimeter keselamatan penerbangan bandara.';
      $sopLines = array_filter(array_map('trim', explode("\n", $satwa->sop_pengusiran ?: "Sirene Patroli AMC\nBio-Akustik / Repellent")));
    @endphp

    <div class="species-card bg-card-bg border border-border-subtle rounded-xl overflow-hidden shadow-sm hover:shadow-md hover:border-injourney-teal/60 transition-all duration-200 flex flex-col justify-between"
         data-category="{{ $cat }}"
         data-habitat="{{ $habitat }}"
         data-name="{{ strtolower($satwa->nama) }}"
         data-risk="{{ $risk }}">
      <div class="flex-1 flex flex-col">
        <!-- Visual Photo Container with Badges -->
        <div class="relative h-44 sm:h-48 w-full bg-slate-900 overflow-hidden group">
          <img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" src="{{ $satwa->foto_url }}" alt="{{ $satwa->nama }}" onerror="this.src='{{ asset('images/biawak.jpeg') }}'"/>
          <div class="absolute inset-0 bg-gradient-to-t from-slate-navy/85 via-black/20 to-black/40"></div>
          
          <!-- Top Badges Flex Row: Prevents collision/overlapping at any viewport width -->
          <div class="absolute top-2.5 inset-x-2.5 flex items-center justify-between gap-1.5 z-10">
            <!-- Category Tag -->
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-slate-900/85 backdrop-blur-md text-white text-[11px] font-bold uppercase tracking-wider border border-white/15 shadow-xs shrink-0">
              <span class="material-symbols-outlined text-xs text-cyan-300">
                {{ $cat === 'burung' ? 'flight' : ($cat === 'reptil' ? 'pest_control' : 'pets') }}
              </span>
              <span>{{ $cat === 'burung' ? 'Avian' : ($cat === 'reptil' ? 'Reptil' : 'Mamalia') }}</span>
            </span>

            <!-- Risk Level Badge -->
            @if($risk === 'kritis')
              <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-rose-500/95 backdrop-blur-xs text-white text-[11px] font-bold tracking-wide shadow-xs shrink-0">
                <span class="w-1.5 h-1.5 rounded-full bg-white badge-pulse"></span>
                <span>Kat. 4 (Kritis)</span>
              </span>
            @elseif($risk === 'rendah')
              <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/95 backdrop-blur-xs text-white text-[11px] font-bold tracking-wide shadow-xs shrink-0">
                <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                <span>Kat. 2 (Rendah)</span>
              </span>
            @else
              <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-500/95 backdrop-blur-xs text-white text-[11px] font-bold tracking-wide shadow-xs shrink-0">
                <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                <span>Kat. 3 (Sedang)</span>
              </span>
            @endif
          </div>

          <!-- Bottom Photo Identifier Bar -->
          <div class="absolute bottom-2.5 inset-x-2.5 flex items-center justify-between gap-2 text-white z-10">
            <span class="text-xs font-code-coordinate text-cyan-300 bg-slate-900/80 px-2 py-0.5 rounded backdrop-blur-xs font-bold border border-white/10 shrink-0">{{ $idCode }}</span>
            <span class="text-xs flex items-center gap-1 text-slate-200 bg-slate-900/60 px-2 py-0.5 rounded backdrop-blur-xs border border-white/10 font-medium shrink-0">
              <span class="material-symbols-outlined text-xs text-injourney-teal">pin_drop</span>
              <span>Grid {{ $gridDisplay }}</span>
            </span>
          </div>
        </div>

        <!-- Card Content Body -->
        <div class="p-4 space-y-3 flex-1 flex flex-col justify-between">
          <div>
            <h3 class="text-sm sm:text-base font-bold text-slate-navy hover:text-injourney-teal transition-colors line-clamp-1" title="{{ $satwa->nama }}">{{ $satwa->nama }}</h3>
            <p class="text-xs text-secondary mt-1 line-clamp-2 leading-relaxed min-h-[2rem]">
              {{ $descText }}
            </p>
          </div>

          <!-- Habitat & Hotspot Tag -->
          <div class="bg-surface-container-low rounded-lg p-2.5 border border-border-subtle">
            <div class="flex items-center justify-between text-xs mb-1">
              <span class="text-secondary text-[10px] uppercase font-bold tracking-wider">Hotspot &amp; Habitat</span>
              <span class="px-1.5 py-0.2 rounded bg-injourney-teal/10 text-injourney-teal font-code-coordinate text-[11px] font-bold shrink-0">Grid {{ $gridDisplay }}</span>
            </div>
            <p class="text-xs font-medium text-slate-navy flex items-center gap-1.5 truncate" title="{{ $satwa->grid_hotspot ?: 'Runway 07L/25R & Perimeter' }}">
              <span class="material-symbols-outlined text-sm text-injourney-teal shrink-0">location_on</span>
              <span class="truncate">{{ $satwa->grid_hotspot ?: 'Runway 07L/25R & Perimeter' }}</span>
            </p>
          </div>

          <!-- SOP Dispersal Badges -->
          <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-secondary block mb-1">Metode Pengusiran (ICAO):</span>
            <div class="flex flex-wrap gap-1.5">
              @foreach(array_slice($sopLines, 0, 2) as $sopLine)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-slate-100 text-slate-navy text-[11px] font-medium border border-slate-200/80 truncate max-w-full" title="{{ $sopLine }}">
                  <span class="material-symbols-outlined text-xs text-injourney-teal shrink-0">shield</span>
                  <span class="truncate">{{ $sopLine }}</span>
                </span>
              @endforeach
            </div>
          </div>
        </div>
      </div>

      <!-- Card Footer Actions -->
      <div class="p-4 pt-0">
        <div class="pt-3 border-t border-border-subtle flex items-center justify-between gap-2">
          <button type="button" class="flex-1 py-1.5 px-3 rounded-lg bg-injourney-teal/10 hover:bg-injourney-teal hover:text-white text-injourney-teal text-xs font-semibold text-center transition-colors truncate cursor-pointer"
                  onclick="openSpeciesDetail('{{ addslashes($satwa->nama) }}', '{{ addslashes($satwa->kategori_label) }}', '{{ addslashes($satwa->risiko_label) }}', '{{ addslashes($gridDisplay) }}', '{{ addslashes($descText) }}', '{{ addslashes($satwa->jam_puncak ?: '08:00 - 15:00 WIB') }}', '{{ addslashes($satwa->bobot_rata_rata ?: 'FOD Ringan - Sedang') }}')">
            Detail Profil &amp; SOP
          </button>
          <div class="flex items-center gap-1 shrink-0">
            <button type="button" onclick="openEditSpeciesModal({{ $satwa->id }}, '{{ addslashes($satwa->nama) }}', '{{ $cat }}', '{{ $risk }}', '{{ addslashes($gridDisplay) }}', '{{ addslashes($descText) }}', '{{ addslashes($satwa->sop_pengusiran ?: '') }}')" class="p-1.5 text-secondary hover:text-injourney-teal hover:bg-surface-container rounded-lg transition-colors border border-border-subtle bg-white cursor-pointer" title="Edit Spesies">
              <span class="material-symbols-outlined text-base">edit</span>
            </button>
            <form action="{{ route('admin.manajemen.satwa.delete', $satwa->id) }}" method="POST" class="inline m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data spesies {{ addslashes($satwa->nama) }}?');">
              @csrf
              <button type="submit" class="p-1.5 text-secondary hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors border border-border-subtle bg-white cursor-pointer" title="Hapus Spesies">
                <span class="material-symbols-outlined text-base">delete</span>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  @endforeach
</div>
