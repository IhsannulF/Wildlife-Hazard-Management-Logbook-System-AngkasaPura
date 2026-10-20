<!-- TABLE VIEW (ALTERNATIVE OPERATIONAL MODE) -->
<div class="hidden bg-card-bg border border-border-subtle rounded-xl overflow-hidden shadow-sm" id="catalogTableView">
  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs">
      <thead class="bg-surface-container text-slate-navy uppercase tracking-wider font-semibold border-b border-border-subtle">
        <tr>
          <th class="py-3 px-4">Spesies (Nama Umum)</th>
          <th class="py-3 px-4">Klasifikasi</th>
          <th class="py-3 px-4">Level Risiko (ICAO Strike)</th>
          <th class="py-3 px-4">Hotspot Grid</th>
          <th class="py-3 px-4">Aktivitas &amp; Karakter Bahaya</th>
          <th class="py-3 px-4">SOP Pengusiran Utama</th>
          <th class="py-3 px-4 text-right">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-border-subtle">
        @foreach($satwaList as $satwa)
          @php
            $cat = strtolower($satwa->kategori ?: (str_contains($satwa->nama, 'Burung') ? 'burung' : (str_contains($satwa->nama, 'Biawak') || str_contains($satwa->nama, 'Ular') ? 'reptil' : 'mamalia')));
            $risk = strtolower($satwa->tingkat_risiko ?: 'sedang');
            $habitat = strtolower($satwa->grid_hotspot ?: 'runway');
            $gridDisplay = $satwa->grid_hotspot ?: 'K-10';
          @endphp
          <tr class="species-table-row hover:bg-surface-container-low transition-colors"
              data-category="{{ $cat }}"
              data-habitat="{{ $habitat }}"
              data-name="{{ strtolower($satwa->nama) }}"
              data-risk="{{ $risk }}">
            <td class="py-3.5 px-4 font-bold text-slate-navy flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full {{ $risk === 'kritis' ? 'bg-rose-500' : ($risk === 'rendah' ? 'bg-emerald-500' : 'bg-amber-500') }}"></span>
              {{ $satwa->nama }}
            </td>
            <td class="py-3.5 px-4"><span class="px-2 py-0.5 rounded bg-surface-container text-[11px] font-semibold">{{ $satwa->kategori_label }}</span></td>
            <td class="py-3.5 px-4">
              <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $risk === 'kritis' ? 'bg-red-100 text-red-800' : ($risk === 'rendah' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800') }}">
                {{ $satwa->risiko_label }}
              </span>
            </td>
            <td class="py-3.5 px-4 font-code-coordinate text-injourney-teal font-bold">Grid {{ $gridDisplay }}</td>
            <td class="py-3.5 px-4 text-secondary max-w-xs truncate">{{ $satwa->deskripsi ?: 'Pemantauan rutin area sisi udara.' }}</td>
            <td class="py-3.5 px-4 text-slate-navy max-w-xs truncate">{{ $satwa->sop_pengusiran ?: 'Sirene patroli & repellent trap' }}</td>
            <td class="py-3.5 px-4 text-right whitespace-nowrap">
              <button type="button" class="text-injourney-teal hover:underline font-bold mr-2 cursor-pointer"
                      onclick="openSpeciesDetail('{{ addslashes($satwa->nama) }}', '{{ addslashes($satwa->kategori_label) }}', '{{ addslashes($satwa->risiko_label) }}', '{{ addslashes($gridDisplay) }}', '{{ addslashes($satwa->deskripsi ?: '') }}', '{{ addslashes($satwa->jam_puncak ?: '') }}', '{{ addslashes($satwa->bobot_rata_rata ?: '') }}')">
                Detail SOP
              </button>
              <button type="button" onclick="openEditSpeciesModal({{ $satwa->id }}, '{{ addslashes($satwa->nama) }}', '{{ $cat }}', '{{ $risk }}', '{{ addslashes($gridDisplay) }}', '{{ addslashes($satwa->deskripsi ?: '') }}', '{{ addslashes($satwa->sop_pengusiran ?: '') }}')" class="text-secondary hover:text-slate-navy font-bold cursor-pointer">
                Edit
              </button>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
