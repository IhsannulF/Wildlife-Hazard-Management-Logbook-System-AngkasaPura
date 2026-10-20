<!-- MODAL TAMBAH SPESIES BARU & MODAL EDIT SPESIES -->

<!-- 1. MODAL TAMBAH SPESIES BARU -->
<div class="fixed inset-0 z-50 hidden bg-slate-navy/60 backdrop-blur-xs flex items-center justify-center p-4" id="addSpeciesModal">
  <div class="bg-card-bg border border-border-subtle rounded-2xl max-w-xl w-full max-h-[90vh] overflow-y-auto shadow-2xl transition-all">
    <div class="p-5 border-b border-border-subtle flex items-center justify-between sticky top-0 bg-white z-10">
      <div>
        <span class="text-[11px] uppercase tracking-wider text-secondary font-semibold">Manajemen Master Data</span>
        <h2 class="text-base font-bold text-slate-navy">Tambah Master Spesies Baru</h2>
      </div>
      <button class="p-2 text-secondary hover:text-slate-navy rounded-lg hover:bg-surface-container cursor-pointer" onclick="closeModal('addSpeciesModal')" type="button">
        <span class="material-symbols-outlined text-lg">close</span>
      </button>
    </div>

    <form action="{{ route('admin.manajemen.satwa.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
      @csrf
      <div>
        <label class="block text-xs font-semibold text-slate-navy mb-1">
          Nama Satwa (Nama Umum Operasional) <span class="text-rose-500">*</span>
        </label>
        <input name="nama" class="w-full px-3.5 py-2.5 bg-white border border-border-subtle rounded-lg text-xs font-medium text-slate-navy focus:border-injourney-teal focus:ring-2 focus:ring-injourney-teal/20 outline-none" placeholder="Contoh: Burung Gagak Hutan, Kucing Liar, dsb." required type="text"/>
        <p class="text-[11px] text-secondary mt-1">Gunakan nama umum lapangan yang mudah dipahami petugas dinas (tanpa nama latin).</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-navy mb-1">
            Kategori Taksonomi <span class="text-rose-500">*</span>
          </label>
          <select name="kategori" class="w-full bg-white border border-border-subtle rounded-lg px-3 py-2 text-xs font-medium text-slate-navy focus:border-injourney-teal outline-none" required>
            <option value="burung">Burung / Avian</option>
            <option value="reptil">Reptil</option>
            <option value="mamalia">Mamalia</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-navy mb-1">
            Tingkat Risiko Tabrakan <span class="text-rose-500">*</span>
          </label>
          <select name="tingkat_risiko" class="w-full bg-white border border-border-subtle rounded-lg px-3 py-2 text-xs font-medium text-slate-navy focus:border-injourney-teal outline-none" required>
            <option value="kritis">Risiko Kritis (Kategori 4)</option>
            <option value="sedang" selected>Risiko Sedang (Kategori 3)</option>
            <option value="rendah">Risiko Rendah (Kategori 2)</option>
          </select>
        </div>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-navy mb-1">
          Zona Grid Hotspot Acuan
        </label>
        <input name="grid_hotspot" class="w-full px-3.5 py-2 bg-white border border-border-subtle rounded-lg text-xs font-medium text-slate-navy focus:border-injourney-teal focus:ring-2 focus:ring-injourney-teal/20 outline-none" placeholder="Contoh: K-10, Kanal Perimeter Selatan" type="text"/>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-navy mb-1">
          Deskripsi Karakter &amp; Bahaya di Sisi Udara
        </label>
        <textarea name="deskripsi" class="w-full px-3.5 py-2 bg-white border border-border-subtle rounded-lg text-xs text-slate-navy focus:border-injourney-teal focus:ring-2 focus:ring-injourney-teal/20 outline-none" placeholder="Jelaskan pola terbang, kebiasaan mencari makan, atau potensi benturan terhadap pesawat..." rows="3"></textarea>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-navy mb-1">
          Metode Pengusiran Resmi (SOP)
        </label>
        <textarea name="sop_pengusiran" class="w-full px-3.5 py-2 bg-white border border-border-subtle rounded-lg text-xs text-slate-navy focus:border-injourney-teal focus:ring-2 focus:ring-injourney-teal/20 outline-none" placeholder="Tuliskan metode pengusiran resmi, satu per baris..." rows="2"></textarea>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-navy mb-1">
          Unggah Foto Dokumentasi Referensi (Maks 5MB)
        </label>
        <input name="foto" type="file" accept="image/*" class="w-full text-xs text-secondary file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-surface-container file:text-injourney-teal hover:file:bg-surface-container-high cursor-pointer"/>
      </div>

      <div class="pt-4 border-t border-border-subtle flex items-center justify-end gap-2">
        <button class="px-4 py-2 text-xs font-semibold rounded-lg border border-border-subtle bg-white text-slate-navy hover:bg-slate-50 cursor-pointer" onclick="closeModal('addSpeciesModal')" type="button">
          Batal
        </button>
        <button class="px-5 py-2 text-xs font-bold rounded-lg bg-injourney-teal hover:bg-injourney-dark-teal text-white shadow-xs cursor-pointer" type="submit">
          Simpan ke Master Database
        </button>
      </div>
    </form>
  </div>
</div>

<!-- 2. MODAL EDIT MASTER SPESIES -->
<div class="fixed inset-0 z-50 hidden bg-slate-navy/60 backdrop-blur-xs flex items-center justify-center p-4" id="editSpeciesModal">
  <div class="bg-card-bg border border-border-subtle rounded-2xl max-w-xl w-full max-h-[90vh] overflow-y-auto shadow-2xl transition-all">
    <div class="p-5 border-b border-border-subtle flex items-center justify-between sticky top-0 bg-white z-10">
      <div>
        <span class="text-[11px] uppercase tracking-wider text-secondary font-semibold">Ubah Data Satwa</span>
        <h2 class="text-base font-bold text-slate-navy">Edit Master Spesies</h2>
      </div>
      <button class="p-2 text-secondary hover:text-slate-navy rounded-lg hover:bg-surface-container cursor-pointer" onclick="closeModal('editSpeciesModal')" type="button">
        <span class="material-symbols-outlined text-lg">close</span>
      </button>
    </div>

    <form id="editSpeciesForm" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
      @csrf
      <div>
        <label class="block text-xs font-semibold text-slate-navy mb-1">
          Nama Satwa (Nama Umum) <span class="text-rose-500">*</span>
        </label>
        <input id="editNama" name="nama" class="w-full px-3.5 py-2 bg-white border border-border-subtle rounded-lg text-xs font-medium text-slate-navy focus:border-injourney-teal outline-none" required type="text"/>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-navy mb-1">
            Kategori Taksonomi <span class="text-rose-500">*</span>
          </label>
          <select id="editKategori" name="kategori" class="w-full bg-white border border-border-subtle rounded-lg px-3 py-2 text-xs font-medium text-slate-navy focus:border-injourney-teal outline-none">
            <option value="burung">Burung / Avian</option>
            <option value="reptil">Reptil</option>
            <option value="mamalia">Mamalia</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-navy mb-1">
            Tingkat Risiko Tabrakan <span class="text-rose-500">*</span>
          </label>
          <select id="editRisiko" name="tingkat_risiko" class="w-full bg-white border border-border-subtle rounded-lg px-3 py-2 text-xs font-medium text-slate-navy focus:border-injourney-teal outline-none">
            <option value="kritis">Risiko Kritis (Kategori 4)</option>
            <option value="sedang">Risiko Sedang (Kategori 3)</option>
            <option value="rendah">Risiko Rendah (Kategori 2)</option>
          </select>
        </div>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-navy mb-1">
          Zona Grid Hotspot Acuan
        </label>
        <input id="editGrid" name="grid_hotspot" class="w-full px-3.5 py-2 bg-white border border-border-subtle rounded-lg text-xs font-medium text-slate-navy focus:border-injourney-teal outline-none" type="text"/>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-navy mb-1">
          Deskripsi Karakter &amp; Bahaya
        </label>
        <textarea id="editDeskripsi" name="deskripsi" class="w-full px-3.5 py-2 bg-white border border-border-subtle rounded-lg text-xs text-slate-navy focus:border-injourney-teal outline-none" rows="3"></textarea>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-navy mb-1">
          Metode Pengusiran Resmi (SOP)
        </label>
        <textarea id="editSop" name="sop_pengusiran" class="w-full px-3.5 py-2 bg-white border border-border-subtle rounded-lg text-xs text-slate-navy focus:border-injourney-teal outline-none" rows="2"></textarea>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-navy mb-1">
          Perbarui Foto Dokumentasi (Opsional)
        </label>
        <input name="foto" type="file" accept="image/*" class="w-full text-xs text-secondary file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-surface-container file:text-injourney-teal hover:file:bg-surface-container-high cursor-pointer"/>
      </div>

      <div class="pt-4 border-t border-border-subtle flex items-center justify-end gap-2">
        <button class="px-4 py-2 text-xs font-semibold rounded-lg border border-border-subtle bg-white text-slate-navy hover:bg-slate-50 cursor-pointer" onclick="closeModal('editSpeciesModal')" type="button">
          Batal
        </button>
        <button class="px-5 py-2 text-xs font-bold rounded-lg bg-injourney-teal hover:bg-injourney-dark-teal text-white shadow-xs cursor-pointer" type="submit">
          Simpan Perubahan
        </button>
      </div>
    </form>
  </div>
</div>
