<!-- MODAL TAMBAH USER & MODAL EDIT USER -->

<!-- 1. MODAL TAMBAH USER -->
<div class="fixed inset-0 z-50 hidden bg-slate-navy/60 backdrop-blur-xs flex items-center justify-center p-4" id="modalAddUser">
  <div class="bg-card-bg border border-border-subtle rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl p-6">
    <div class="flex items-center justify-between pb-3.5 border-b border-border-subtle mb-4">
      <div>
        <span class="text-[10px] uppercase tracking-wider text-secondary font-bold">Kredensial Petugas</span>
        <h3 class="text-base font-bold text-slate-navy">Tambah Akun Petugas Baru</h3>
      </div>
      <button class="p-1.5 text-secondary hover:text-slate-navy rounded-lg hover:bg-surface-container cursor-pointer" onclick="closeModal('modalAddUser')" type="button">
        <span class="material-symbols-outlined text-lg">close</span>
      </button>
    </div>

    <form id="addUserForm" action="{{ route('admin.manajemen.users.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
      @csrf
      <input type="hidden" name="tanda_tangan" id="addUserSignatureInput">
      <div>
        <label class="block font-semibold text-slate-navy mb-1">Nama Lengkap Petugas <span class="text-rose-500">*</span></label>
        <input name="namalengkap" required placeholder="Contoh: Budi Santoso, S.T." class="w-full px-3 py-2 border border-border-subtle rounded-lg focus:border-injourney-teal outline-none bg-white"/>
      </div>
      <div>
        <label class="block font-semibold text-slate-navy mb-1">Username Login <span class="text-rose-500">*</span></label>
        <input name="username" required placeholder="Contoh: amc.budisantoso" class="w-full px-3 py-2 border border-border-subtle rounded-lg focus:border-injourney-teal outline-none bg-white"/>
      </div>
      <div>
        <label class="block font-semibold text-slate-navy mb-1">Password Baru <span class="text-rose-500">*</span></label>
        <input name="password" type="password" required minlength="6" placeholder="Minimal 6 karakter" class="w-full px-3 py-2 border border-border-subtle rounded-lg focus:border-injourney-teal outline-none bg-white"/>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block font-semibold text-slate-navy mb-1">Role Akses <span class="text-rose-500">*</span></label>
          <select name="role" class="w-full px-3 py-2 border border-border-subtle rounded-lg focus:border-injourney-teal outline-none bg-white">
            <option value="pegawai">Pegawai / Petugas Lapangan</option>
            <option value="admin">Administrator / Safety Manager</option>
          </select>
        </div>
        <div>
          <label class="block font-semibold text-slate-navy mb-1">Jabatan / Posko</label>
          <input name="jabatan" placeholder="Contoh: Safety Manager" class="w-full px-3 py-2 border border-border-subtle rounded-lg focus:border-injourney-teal outline-none bg-white"/>
        </div>
      </div>

      <!-- Spesimen TTD Digital (Opsional / Untuk Pengesahan PDF Safety Manager) -->
      <div class="p-3 rounded-xl bg-slate-50 border border-border-subtle space-y-2.5">
        <div class="flex items-center justify-between gap-2">
          <div>
            <label class="block font-semibold text-slate-navy">Spesimen Tanda Tangan Resmi (PDF)</label>
            <span class="text-[10px] text-secondary">Direkomendasikan untuk Administrator / Safety Manager pengesah Berita Acara</span>
          </div>
          <button type="button" onclick="clearUserSignatureCanvas('add')" class="text-[11px] font-semibold text-rose-600 hover:underline cursor-pointer shrink-0">Hapus Kanvas</button>
        </div>
        <div class="border border-dashed border-slate-300 rounded-lg bg-white overflow-hidden">
          <canvas id="addUserSignatureCanvas" class="w-full h-28 block cursor-crosshair"></canvas>
        </div>
        <div>
          <label class="block text-[10px] font-semibold text-secondary mb-1">Atau unggah berkas TTD (PNG/JPG maks. 1MB)</label>
          <input type="file" name="tanda_tangan_file" accept="image/png,image/jpeg,image/webp" class="block w-full text-[11px] text-secondary file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border file:border-border-subtle file:text-[11px] file:font-semibold file:bg-white file:text-slate-navy hover:file:bg-slate-100 cursor-pointer"/>
        </div>
      </div>

      <div class="pt-4 border-t border-border-subtle flex items-center justify-end gap-2">
        <button type="button" class="px-4 py-2 border border-border-subtle rounded-lg bg-white text-slate-navy hover:bg-slate-50 font-semibold cursor-pointer" onclick="closeModal('modalAddUser')">Batal</button>
        <button type="submit" class="px-5 py-2 bg-injourney-teal hover:bg-injourney-dark-teal text-white font-bold rounded-lg shadow-xs cursor-pointer">Simpan User</button>
      </div>
    </form>
  </div>
</div>

<!-- 2. MODAL EDIT USER -->
<div class="fixed inset-0 z-50 hidden bg-slate-navy/60 backdrop-blur-xs flex items-center justify-center p-4" id="modalEditUser">
  <div class="bg-card-bg border border-border-subtle rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl p-6">
    <div class="flex items-center justify-between pb-3.5 border-b border-border-subtle mb-4">
      <div>
        <span class="text-[10px] uppercase tracking-wider text-secondary font-bold">Kredensial Petugas</span>
        <h3 class="text-base font-bold text-slate-navy">Edit Data Akun Petugas</h3>
      </div>
      <button class="p-1.5 text-secondary hover:text-slate-navy rounded-lg hover:bg-surface-container cursor-pointer" onclick="closeModal('modalEditUser')" type="button">
        <span class="material-symbols-outlined text-lg">close</span>
      </button>
    </div>

    <form id="editUserForm" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
      @csrf
      <input type="hidden" name="tanda_tangan" id="editUserSignatureInput">
      <div>
        <label class="block font-semibold text-slate-navy mb-1">Nama Lengkap Petugas <span class="text-rose-500">*</span></label>
        <input id="editUserNama" name="namalengkap" required class="w-full px-3 py-2 border border-border-subtle rounded-lg focus:border-injourney-teal outline-none bg-white"/>
      </div>
      <div>
        <label class="block font-semibold text-slate-navy mb-1">Username Login <span class="text-rose-500">*</span></label>
        <input id="editUserUsername" name="username" required class="w-full px-3 py-2 border border-border-subtle rounded-lg focus:border-injourney-teal outline-none bg-white"/>
      </div>
      <div>
        <label class="block font-semibold text-slate-navy mb-1">Ganti Password (Kosongkan jika tidak diubah)</label>
        <input name="password" type="password" minlength="6" placeholder="Masukkan password baru jika ingin mengubah" class="w-full px-3 py-2 border border-border-subtle rounded-lg focus:border-injourney-teal outline-none bg-white"/>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block font-semibold text-slate-navy mb-1">Role Akses <span class="text-rose-500">*</span></label>
          <select id="editUserRole" name="role" class="w-full px-3 py-2 border border-border-subtle rounded-lg focus:border-injourney-teal outline-none bg-white">
            <option value="pegawai">Pegawai / Petugas Lapangan</option>
            <option value="admin">Administrator / Safety Manager</option>
          </select>
        </div>
        <div>
          <label class="block font-semibold text-slate-navy mb-1">Jabatan / Posko</label>
          <input id="editUserJabatan" name="jabatan" class="w-full px-3 py-2 border border-border-subtle rounded-lg focus:border-injourney-teal outline-none bg-white"/>
        </div>
      </div>

      <!-- Spesimen TTD Digital -->
      <div class="p-3 rounded-xl bg-slate-50 border border-border-subtle space-y-2.5">
        <div class="flex items-center justify-between gap-2">
          <div>
            <label class="block font-semibold text-slate-navy">Spesimen Tanda Tangan Resmi (PDF)</label>
            <span class="text-[10px] text-secondary">Otomatis disematkan pada Berita Acara PDF saat akun ini mengesahkan BA</span>
          </div>
          <button type="button" onclick="clearUserSignatureCanvas('edit')" class="text-[11px] font-semibold text-rose-600 hover:underline cursor-pointer shrink-0">Hapus Kanvas</button>
        </div>

        <div id="editUserCurrentSigWrapper" class="hidden p-2.5 bg-white border border-emerald-200 rounded-lg space-y-2">
          <div class="flex items-center justify-between text-[10px]">
            <span class="font-bold text-emerald-700">TTD Tersimpan Saat Ini:</span>
            <label class="inline-flex items-center gap-1 text-rose-600 font-semibold cursor-pointer">
              <input type="checkbox" name="remove_tanda_tangan" value="1" id="editUserRemoveSignature" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
              <span>Hapus TTD</span>
            </label>
          </div>
          <div class="bg-slate-50 rounded border border-slate-200 p-1.5 flex items-center justify-center">
            <img id="editUserCurrentSigImg" src="" alt="Spesimen TTD Tersimpan" class="max-h-16 object-contain">
          </div>
        </div>

        <div class="border border-dashed border-slate-300 rounded-lg bg-white overflow-hidden">
          <canvas id="editUserSignatureCanvas" class="w-full h-28 block cursor-crosshair"></canvas>
        </div>
        <div>
          <label class="block text-[10px] font-semibold text-secondary mb-1">Atau unggah gambar TTD baru (PNG/JPG maks. 1MB)</label>
          <input type="file" name="tanda_tangan_file" accept="image/png,image/jpeg,image/webp" class="block w-full text-[11px] text-secondary file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border file:border-border-subtle file:text-[11px] file:font-semibold file:bg-white file:text-slate-navy hover:file:bg-slate-100 cursor-pointer"/>
        </div>
      </div>

      <div class="pt-4 border-t border-border-subtle flex items-center justify-end gap-2">
        <button type="button" class="px-4 py-2 border border-border-subtle rounded-lg bg-white text-slate-navy hover:bg-slate-50 font-semibold cursor-pointer" onclick="closeModal('modalEditUser')">Batal</button>
        <button type="submit" class="px-5 py-2 bg-injourney-teal hover:bg-injourney-dark-teal text-white font-bold rounded-lg shadow-xs cursor-pointer">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>
