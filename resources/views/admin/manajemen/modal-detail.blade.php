<!-- MODAL DETAIL PROFIL SATWA & SOP ICAO DOC 9137 -->
<div class="fixed inset-0 z-50 hidden bg-slate-navy/60 backdrop-blur-xs flex items-center justify-center p-4" id="speciesDetailModal">
  <div class="bg-card-bg border border-border-subtle rounded-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto shadow-2xl transition-all">
    <div class="p-5 border-b border-border-subtle flex items-center justify-between sticky top-0 bg-white z-10">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-injourney-teal/10 text-injourney-teal flex items-center justify-center font-bold">
          <span class="material-symbols-outlined text-2xl">verified</span>
        </div>
        <div>
          <span class="text-[11px] uppercase tracking-wider text-secondary font-semibold">Profil Standar Operasional Prosedur (SOP)</span>
          <h2 class="text-lg font-bold text-slate-navy" id="modalSpeciesName">Biawak Air</h2>
        </div>
      </div>
      <button class="p-2 text-secondary hover:text-slate-navy rounded-lg hover:bg-surface-container cursor-pointer" onclick="closeModal('speciesDetailModal')" type="button">
        <span class="material-symbols-outlined text-lg">close</span>
      </button>
    </div>

    <div class="p-6 space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-surface-container-low p-4 rounded-xl border border-border-subtle">
          <span class="text-xs uppercase text-secondary font-semibold block">Klasifikasi Operasional</span>
          <span class="text-base font-bold text-slate-navy mt-1 block" id="modalSpeciesCategory">Reptil</span>
        </div>
        <div class="bg-surface-container-low p-4 rounded-xl border border-border-subtle">
          <span class="text-xs uppercase text-secondary font-semibold block">Tingkat Risiko Tabrakan</span>
          <span class="text-base font-bold text-rose-600 mt-1 block" id="modalSpeciesRisk">Kategori 4 - Kritis</span>
        </div>
        <div class="bg-surface-container-low p-4 rounded-xl border border-border-subtle">
          <span class="text-xs uppercase text-secondary font-semibold block">Titik Hotspot Utama</span>
          <span class="text-base font-bold text-injourney-teal font-code-coordinate mt-1 block" id="modalSpeciesGrid">Grid K-10</span>
        </div>
      </div>

      <div class="space-y-3">
        <h3 class="text-sm font-bold text-slate-navy flex items-center gap-2">
          <span class="material-symbols-outlined text-injourney-teal text-base">info</span>
          Karakteristik Lapangan &amp; Jam Aktivitas Puncak
        </h3>
        <p class="text-xs text-slate-navy bg-slate-50 p-4 rounded-xl border border-border-subtle leading-relaxed" id="modalSpeciesDesc">
          Deskripsi karakteristik lapangan.
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
          <div class="flex items-center gap-2 text-slate-navy">
            <span class="material-symbols-outlined text-injourney-teal text-base">schedule</span>
            <span><strong>Jam Puncak:</strong> <span id="modalSpeciesHours">09:00 - 14:00 WIB</span></span>
          </div>
          <div class="flex items-center gap-2 text-slate-navy">
            <span class="material-symbols-outlined text-injourney-teal text-base">scale</span>
            <span><strong>Bobot Rata-rata:</strong> <span id="modalSpeciesWeight">8 - 15 Kilogram</span></span>
          </div>
        </div>
      </div>

      <div class="space-y-3">
        <h3 class="text-sm font-bold text-slate-navy flex items-center gap-2">
          <span class="material-symbols-outlined text-injourney-teal text-base">security</span>
          SOP Khusus Intervensi Sisi Udara (ICAO Doc 9137 Compliant)
        </h3>
        <ol class="space-y-2 text-xs text-slate-navy list-decimal list-inside bg-surface-container-low p-4 rounded-xl border border-border-subtle leading-relaxed">
          <li class="pl-1"><strong>Langkah 1:</strong> Petugas Patroli AMC memberi tahu ATC Tower (TWR) mengenai temuan satwa di grid aktif untuk koordinasi pergerakan.</li>
          <li class="pl-1"><strong>Langkah 2:</strong> Dekati lokasi satwa menggunakan kendaraan dinas pemantau satwa dengan menyalakan lampu hazard rotari kuning.</li>
          <li class="pl-1"><strong>Langkah 3:</strong> Terapkan jaring tangkap atau perangkap kandang. DILARANG menggunakan proyektil tajam di runway buffer area.</li>
          <li class="pl-1"><strong>Langkah 4:</strong> Ambil foto dokumentasi, tentukan koordinat akurat pada logbook WHMS, dan lakukan validasi pelaporan.</li>
        </ol>
      </div>
    </div>

    <div class="p-4 border-t border-border-subtle bg-surface-container-low rounded-b-2xl flex items-center justify-between">
      <button type="button" class="px-4 py-2 text-xs font-semibold rounded-lg border border-border-subtle bg-white text-slate-navy hover:bg-slate-50 transition-colors cursor-pointer" onclick="closeModal('speciesDetailModal')">
        Tutup
      </button>
      <div class="flex gap-2">
        <a id="modalBtnViewLog" href="{{ route('admin.dashboard') }}" class="px-4 py-2 text-xs font-semibold rounded-lg border border-border-subtle bg-white text-slate-navy hover:bg-slate-50 transition-colors flex items-center gap-1.5">
          <span class="material-symbols-outlined text-sm">history</span>
          Lihat Log Temuan Satwa Ini
        </a>
        <button type="button" class="px-4 py-2 text-xs font-bold rounded-lg bg-injourney-teal hover:bg-injourney-dark-teal text-white transition-colors cursor-pointer" onclick="window.print()">
          Cetak Kartu SOP (A4)
        </button>
      </div>
    </div>
  </div>
</div>
