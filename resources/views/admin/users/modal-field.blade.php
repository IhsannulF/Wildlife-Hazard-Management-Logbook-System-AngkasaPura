<!-- MODAL TAMBAH FIELD FORMULIR -->
<div class="fixed inset-0 z-50 hidden bg-slate-navy/60 backdrop-blur-xs flex items-center justify-center p-4" id="modalAddField">
  <div class="bg-card-bg border border-border-subtle rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl p-6">
    <div class="flex items-center justify-between pb-3.5 border-b border-border-subtle mb-4">
      <div>
        <span class="text-[10px] uppercase tracking-wider text-secondary font-bold">Parameter Formulir</span>
        <h3 class="text-base font-bold text-slate-navy">Tambah Field Baru</h3>
      </div>
      <button class="p-1.5 text-secondary hover:text-slate-navy rounded-lg hover:bg-surface-container cursor-pointer" onclick="closeModal('modalAddField')" type="button">
        <span class="material-symbols-outlined text-lg">close</span>
      </button>
    </div>

    <form action="{{ route('admin.manajemen.fields.store') }}" method="POST" class="space-y-4 text-xs">
      @csrf
      <div>
        <label class="block font-semibold text-slate-navy mb-1">Label Input <span class="text-rose-500">*</span></label>
        <input name="label" required placeholder="Contoh: Titik Ketinggian Satwa" class="w-full px-3 py-2 border border-border-subtle rounded-lg focus:border-injourney-teal outline-none bg-white"/>
      </div>
      <div>
        <label class="block font-semibold text-slate-navy mb-1">Tipe Input <span class="text-rose-500">*</span></label>
        <select name="tipe" id="addSelectTipe" onchange="toggleAddOpsi(this.value)" class="w-full px-3 py-2 border border-border-subtle rounded-lg focus:border-injourney-teal outline-none bg-white">
          <option value="text">Teks Pendek</option>
          <option value="textarea">Teks Panjang (Textarea)</option>
          <option value="dropdown">Dropdown Pilihan</option>
          <option value="date">Tanggal</option>
          <option value="number">Angka</option>
        </select>
      </div>
      <div id="addOpsiContainer" class="hidden">
        <label class="block font-semibold text-slate-navy mb-1">Opsi Dropdown (Satu per baris)</label>
        <textarea name="opsi" rows="3" placeholder="Pilihan 1&#10;Pilihan 2" class="w-full px-3 py-2 border border-border-subtle rounded-lg focus:border-injourney-teal outline-none bg-white"></textarea>
      </div>
      <div class="flex items-center gap-2">
        <input type="checkbox" name="wajib" id="addWajib" value="1" class="rounded text-injourney-teal focus:ring-injourney-teal"/>
        <label for="addWajib" class="font-semibold text-slate-navy">Wajib Diisi (Required)</label>
      </div>
      <div class="pt-4 border-t border-border-subtle flex items-center justify-end gap-2">
        <button type="button" class="px-4 py-2 border border-border-subtle rounded-lg bg-white text-slate-navy hover:bg-slate-50 font-semibold cursor-pointer" onclick="closeModal('modalAddField')">Batal</button>
        <button type="submit" class="px-5 py-2 bg-injourney-teal hover:bg-injourney-dark-teal text-white font-bold rounded-lg shadow-xs cursor-pointer">Simpan Field</button>
      </div>
    </form>
  </div>
</div>
