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

    <form action="{{ route('admin.manajemen.users.store') }}" method="POST" class="space-y-4 text-xs">
      @csrf
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
          <input name="jabatan" placeholder="Contoh: AMC Airside Supervisor" class="w-full px-3 py-2 border border-border-subtle rounded-lg focus:border-injourney-teal outline-none bg-white"/>
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

    <form id="editUserForm" method="POST" class="space-y-4 text-xs">
      @csrf
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
      <div class="pt-4 border-t border-border-subtle flex items-center justify-end gap-2">
        <button type="button" class="px-4 py-2 border border-border-subtle rounded-lg bg-white text-slate-navy hover:bg-slate-50 font-semibold cursor-pointer" onclick="closeModal('modalEditUser')">Batal</button>
        <button type="submit" class="px-5 py-2 bg-injourney-teal hover:bg-injourney-dark-teal text-white font-bold rounded-lg shadow-xs cursor-pointer">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>
