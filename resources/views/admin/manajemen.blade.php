@extends('layouts.admin', ['activePage' => 'manajemen'])

@section('title', 'Manage Form & Master Data — Portal Satwa Liar')

@section('content')
  <!-- Breadcrumbs -->
  <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-6">
    <a href="{{ route('admin.dashboard') }}" class="hover:text-teal-600 transition-colors">Dashboard</a>
    <span>&rsaquo;</span>
    <span class="text-slate-700">Manajemen Form & Pengguna</span>
  </div>

  <!-- Tab Navigation -->
  <div class="flex items-center gap-3 border-b border-slate-200 mb-8 overflow-x-auto pb-1">
    <a href="{{ route('admin.manajemen', ['tab' => 'fields']) }}" class="px-6 py-3.5 text-xs sm:text-sm font-extrabold transition-all border-b-2 whitespace-nowrap {{ $tab === 'fields' ? 'border-[#00A9C1] text-[#00A9C1]' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
      Field Formulir
    </a>
    <a href="{{ route('admin.manajemen', ['tab' => 'satwa']) }}" class="px-6 py-3.5 text-xs sm:text-sm font-extrabold transition-all border-b-2 whitespace-nowrap {{ $tab === 'satwa' ? 'border-[#00A9C1] text-[#00A9C1]' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
      Master Jenis Satwa
    </a>
    <a href="{{ route('admin.manajemen', ['tab' => 'users']) }}" class="px-6 py-3.5 text-xs sm:text-sm font-extrabold transition-all border-b-2 whitespace-nowrap {{ $tab === 'users' ? 'border-[#00A9C1] text-[#00A9C1]' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
      Manajemen User
    </a>
  </div>

  @if($tab === 'fields')
    <!-- TAB FIELDS -->
    <div class="bg-white rounded-3xl border border-slate-200/90 p-7 shadow-sm">
      <div class="flex items-center justify-between gap-4 mb-7 flex-wrap">
        <div>
          <h3 class="text-base sm:text-lg font-extrabold text-slate-800 tracking-tight">Pengaturan Field Form Pelaporan</h3>
          <p class="text-xs text-slate-400 mt-1">Atur input dinamis, tipe data, dan opsi dropdown yang muncul di formulir petugas</p>
        </div>
        <button type="button" onclick="openModal('modalAddField')" class="px-5 py-2.5 bg-gradient-to-r from-[#00A9C1] to-[#007fa3] text-white text-xs font-bold rounded-xl shadow-xs hover:opacity-95 cursor-pointer">
          + Tambah Field Baru
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 text-[11px] font-extrabold uppercase tracking-wider text-slate-500 border-b border-slate-200/80">
              <th class="py-4 px-5">Urutan</th>
              <th class="py-4 px-5">Nama Field</th>
              <th class="py-4 px-5">Label</th>
              <th class="py-4 px-5">Tipe</th>
              <th class="py-4 px-5">Wajib</th>
              <th class="py-4 px-5">Status</th>
              <th class="py-4 px-5 text-center min-w-[200px]">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs">
            @foreach($fields as $f)
              <tr class="hover:bg-slate-50/70 transition-colors">
                <td class="py-4 px-5 font-bold text-teal-600">#{{ $f->urutan }}</td>
                <td class="py-4 px-5 font-mono text-[11px] text-slate-600 font-medium">{{ $f->nama_field }}</td>
                <td class="py-4 px-5 font-bold text-slate-800">{{ $f->label }}</td>
                <td class="py-4 px-5">
                  <span class="px-2.5 py-1 bg-teal-50 text-teal-700 font-extrabold text-[10px] rounded-lg border border-teal-200">
                    {{ strtoupper($f->tipe) }}
                  </span>
                </td>
                <td class="py-4 px-5 font-semibold text-slate-600">{{ $f->wajib ? 'Ya' : 'Tidak' }}</td>
                <td class="py-4 px-5">
                  <form action="{{ route('admin.manajemen.fields.toggle', $f->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="cursor-pointer">
                      @if($f->aktif)
                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 font-bold text-[10px] rounded-lg border border-emerald-200">Aktif</span>
                      @else
                        <span class="px-2.5 py-1 bg-slate-100 text-slate-600 font-bold text-[10px] rounded-lg border border-slate-200">Nonaktif</span>
                      @endif
                    </button>
                  </form>
                </td>
                <td class="py-4 px-5 text-center whitespace-nowrap">
                  <div class="inline-flex items-center justify-center gap-2">
                    <button type="button" onclick="openEditFieldModal({{ $f->id }}, '{{ addslashes($f->label) }}', '{{ $f->tipe }}', '{{ addslashes($f->placeholder) }}', {{ $f->wajib }}, '{{ addslashes($f->keterangan) }}', '{{ addslashes(implode("\n", $f->options->pluck('nilai')->toArray())) }}')" class="inline-flex items-center gap-1 px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold text-xs rounded-xl shadow-2xs hover:shadow-xs transition-all cursor-pointer">
                      <svg class="w-3.5 h-3.5 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                      <span>Edit</span>
                    </button>
                    @if(!in_array($f->nama_field, ['nama_petugas','tanggal','kondisi_cuaca','unit_kerja','area_inspeksi','tanda_tangan']))
                      <form action="{{ route('admin.manajemen.fields.delete', $f->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus field ini?')">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 font-bold text-xs rounded-xl shadow-2xs hover:shadow-xs transition-all cursor-pointer">
                          <svg class="w-3.5 h-3.5 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                          <span>Hapus</span>
                        </button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

  @elseif($tab === 'satwa')
    <!-- TAB SATWA -->
    <div class="bg-white rounded-3xl border border-slate-200/90 p-7 shadow-sm">
      <div class="flex items-center justify-between gap-4 mb-7 flex-wrap">
        <div>
          <h3 class="text-base sm:text-lg font-extrabold text-slate-800 tracking-tight">Katalog Master Jenis Satwa Bandara</h3>
          <p class="text-xs text-slate-400 mt-1">Kelola daftar satwa liar beserta foto referensinya</p>
        </div>
        <button type="button" onclick="openModal('modalAddSatwa')" class="px-5 py-2.5 bg-gradient-to-r from-[#00A9C1] to-[#007fa3] text-white text-xs font-bold rounded-xl shadow-xs hover:opacity-95 cursor-pointer">
          + Tambah Satwa Baru
        </button>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
        @foreach($satwaList as $st)
          <div class="bg-white border border-slate-200/80 rounded-3xl overflow-hidden shadow-xs hover:shadow-md transition-shadow flex flex-col justify-between">
            <div>
              <div class="h-36 bg-slate-100 overflow-hidden">
                <img src="{{ $st->foto_url }}" alt="{{ $st->nama }}" class="w-full h-full object-cover">
              </div>
              <div class="p-4 pb-2">
                <strong class="block text-sm font-bold text-slate-800 truncate mb-1">{{ $st->nama }}</strong>
                <span class="text-[11px] text-slate-400">Master Satwa Bandara</span>
              </div>
            </div>
            <div class="p-4 pt-0">
              <div class="flex items-center justify-between gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" onclick="openEditSatwaModal({{ $st->id }}, '{{ addslashes($st->nama) }}')" class="flex-1 inline-flex items-center justify-center gap-1 py-2 px-3 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold text-xs rounded-xl transition-all cursor-pointer">
                  <svg class="w-3 h-3 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  <span>Edit</span>
                </button>
                <form action="{{ route('admin.manajemen.satwa.delete', $st->id) }}" method="POST" class="flex-1" onsubmit="return confirm('Hapus satwa ini?')">
                  @csrf
                  <button type="submit" class="w-full inline-flex items-center justify-center gap-1 py-2 px-3 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 font-bold text-xs rounded-xl transition-all cursor-pointer">
                    <svg class="w-3 h-3 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    <span>Hapus</span>
                  </button>
                </form>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>

  @elseif($tab === 'users')
    <!-- TAB USERS -->
    <div class="bg-white rounded-3xl border border-slate-200/90 p-7 shadow-sm">
      <div class="flex items-center justify-between gap-4 mb-7 flex-wrap">
        <div>
          <h3 class="text-base sm:text-lg font-extrabold text-slate-800 tracking-tight">Manajemen Pengguna Sistem</h3>
          <p class="text-xs text-slate-400 mt-1">Kelola akun petugas divisi operasional dan administrator</p>
        </div>
        <button type="button" onclick="openModal('modalAddUser')" class="px-5 py-2.5 bg-gradient-to-r from-[#00A9C1] to-[#007fa3] text-white text-xs font-bold rounded-xl shadow-xs hover:opacity-95 cursor-pointer">
          + Tambah Pengguna
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 text-[11px] font-extrabold uppercase tracking-wider text-slate-500 border-b border-slate-200/80">
              <th class="py-4 px-5">ID</th>
              <th class="py-4 px-5">Nama Lengkap</th>
              <th class="py-4 px-5">Username</th>
              <th class="py-4 px-5">Jabatan / Role</th>
              <th class="py-4 px-5">Status</th>
              <th class="py-4 px-5 text-center min-w-[200px]">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs">
            @foreach($users as $u)
              <tr class="hover:bg-slate-50/70 transition-colors">
                <td class="py-4 px-5 font-bold text-teal-600">#{{ $u->id }}</td>
                <td class="py-4 px-5 font-bold text-slate-800">{{ $u->namalengkap ?: '-' }}</td>
                <td class="py-4 px-5 font-mono text-[11px] text-slate-600 font-medium">{{ $u->username }}</td>
                <td class="py-4 px-5">
                  <span class="px-3 py-1 rounded-full font-bold text-[10px] {{ $u->role === 'admin' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-teal-50 text-teal-700 border border-teal-200' }}">
                    {{ strtoupper($u->role) }}
                  </span>
                </td>
                <td class="py-4 px-5">
                  <form action="{{ route('admin.manajemen.users.toggle', $u->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="cursor-pointer">
                      @if($u->aktif)
                        <span class="px-3 py-1 bg-emerald-50 text-emerald-700 font-bold text-[10px] rounded-full border border-emerald-200">Aktif</span>
                      @else
                        <span class="px-3 py-1 bg-rose-50 text-rose-700 font-bold text-[10px] rounded-full border border-rose-200">Nonaktif</span>
                      @endif
                    </button>
                  </form>
                </td>
                <td class="py-4 px-5 text-center whitespace-nowrap">
                  <div class="inline-flex items-center justify-center gap-2">
                    <button type="button" onclick="openEditUserModal({{ $u->id }}, '{{ addslashes($u->namalengkap) }}', '{{ addslashes($u->username) }}', '{{ $u->role }}')" class="inline-flex items-center gap-1 px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold text-xs rounded-xl shadow-2xs hover:shadow-xs transition-all cursor-pointer">
                      <svg class="w-3.5 h-3.5 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                      <span>Edit</span>
                    </button>
                    @if($u->id !== auth()->id())
                      <form action="{{ route('admin.manajemen.users.delete', $u->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus user ini?')">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 font-bold text-xs rounded-xl shadow-2xs hover:shadow-xs transition-all cursor-pointer">
                          <svg class="w-3.5 h-3.5 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                          <span>Hapus</span>
                        </button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endif

  <!-- MODAL ADD FIELD -->
  <div id="modalAddField" style="display:none;" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl border border-slate-100 overflow-hidden">
      <div class="bg-gradient-to-r from-[#00A9C1] to-[#007fa3] p-6 flex items-center justify-between text-white">
        <h4 class="text-sm sm:text-base font-bold">Tambah Field Form Baru</h4>
        <button type="button" onclick="closeModal('modalAddField')" class="text-white text-xl font-bold cursor-pointer">&times;</button>
      </div>
      <form action="{{ route('admin.manajemen.fields.store') }}" method="POST" class="p-6 space-y-4">
        @csrf
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1.5">Label Field</label>
          <input type="text" name="label" required placeholder="cth: Lokasi Kejadian" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-teal-500 outline-none">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1.5">Tipe Input</label>
          <select name="tipe" id="addTipe" onchange="toggleOpsiField(this, 'addOpsiGroup')" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-teal-500 outline-none font-medium">
            <option value="text">Teks Pendek</option>
            <option value="textarea">Teks Panjang</option>
            <option value="dropdown">Dropdown</option>
            <option value="date">Tanggal</option>
            <option value="number">Angka</option>
          </select>
        </div>
        <div id="addOpsiGroup" style="display:none;">
          <label class="block text-xs font-bold text-slate-700 mb-1.5">Opsi Dropdown (1 baris 1 opsi)</label>
          <textarea name="opsi" rows="3" placeholder="Pilihan 1&#10;Pilihan 2" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-teal-500 outline-none"></textarea>
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1.5">Placeholder / Bantuan</label>
          <input type="text" name="placeholder" placeholder="Teks bantuan..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-teal-500 outline-none">
        </div>
        <div class="flex items-center gap-2 pt-1">
          <input type="checkbox" name="wajib" id="addWajib" value="1" checked class="w-4 h-4 text-teal-600 rounded cursor-pointer">
          <label for="addWajib" class="text-xs font-bold text-slate-700 cursor-pointer">Wajib Diisi</label>
        </div>
        <div class="pt-3 flex justify-end gap-3">
          <button type="button" onclick="closeModal('modalAddField')" class="px-5 py-2.5 border border-slate-200 text-slate-600 rounded-xl text-xs font-bold hover:bg-slate-50 cursor-pointer">Batal</button>
          <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-[#00A9C1] to-[#007fa3] text-white rounded-xl text-xs font-bold hover:opacity-95 cursor-pointer shadow-sm">Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL EDIT FIELD -->
  <div id="modalEditField" style="display:none;" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl border border-slate-100 overflow-hidden">
      <div class="bg-gradient-to-r from-[#00A9C1] to-[#007fa3] p-6 flex items-center justify-between text-white">
        <h4 class="text-sm sm:text-base font-bold">Edit Field Form</h4>
        <button type="button" onclick="closeModal('modalEditField')" class="text-white text-xl font-bold cursor-pointer">&times;</button>
      </div>
      <form id="formEditField" action="" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
        @csrf
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1.5">Label Field</label>
          <input type="text" name="label" id="editLabel" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-teal-500 outline-none">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1.5">Tipe Input</label>
          <select name="tipe" id="editTipe" onchange="toggleOpsiField(this, 'editOpsiGroup')" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-teal-500 outline-none font-medium">
            <option value="text">Teks Pendek</option>
            <option value="textarea">Teks Panjang</option>
            <option value="dropdown">Dropdown</option>
            <option value="date">Tanggal</option>
            <option value="number">Angka</option>
            <option value="grid">Gridmap</option>
          </select>
        </div>
        <div id="editOpsiGroup" style="display:none;">
          <label class="block text-xs font-bold text-slate-700 mb-1.5">Opsi Dropdown (1 baris 1 opsi)</label>
          <textarea name="opsi" id="editOpsi" rows="3" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-teal-500 outline-none"></textarea>
        </div>
        <div id="editGridGroup" style="display:none;">
          <label class="block text-xs font-bold text-slate-700 mb-1.5">Upload Gambar Peta Gridmap Baru (Opsional)</label>
          <input type="file" name="gridmap_file" accept="image/*" class="text-xs">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1.5">Placeholder / Bantuan</label>
          <input type="text" name="placeholder" id="editPlaceholder" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-teal-500 outline-none">
        </div>
        <div class="flex items-center gap-2 pt-1">
          <input type="checkbox" name="wajib" id="editWajib" value="1" class="w-4 h-4 text-teal-600 rounded cursor-pointer">
          <label for="editWajib" class="text-xs font-bold text-slate-700 cursor-pointer">Wajib Diisi</label>
        </div>
        <div class="pt-3 flex justify-end gap-3">
          <button type="button" onclick="closeModal('modalEditField')" class="px-5 py-2.5 border border-slate-200 text-slate-600 rounded-xl text-xs font-bold hover:bg-slate-50 cursor-pointer">Batal</button>
          <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-[#00A9C1] to-[#007fa3] text-white rounded-xl text-xs font-bold hover:opacity-95 cursor-pointer shadow-sm">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL ADD SATWA -->
  <div id="modalAddSatwa" style="display:none;" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl border border-slate-100 overflow-hidden">
      <div class="bg-gradient-to-r from-[#00A9C1] to-[#007fa3] p-6 flex items-center justify-between text-white">
        <h4 class="text-sm sm:text-base font-bold">Tambah Master Satwa Baru</h4>
        <button type="button" onclick="closeModal('modalAddSatwa')" class="text-white text-xl font-bold cursor-pointer">&times;</button>
      </div>
      <form action="{{ route('admin.manajemen.satwa.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
        @csrf
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Satwa</label>
          <input type="text" name="nama" required placeholder="cth: Burung Elang Bondol" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-teal-500 outline-none">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1.5">Foto Referensi</label>
          <input type="file" name="foto" accept="image/*" class="text-xs">
        </div>
        <div class="pt-3 flex justify-end gap-3">
          <button type="button" onclick="closeModal('modalAddSatwa')" class="px-5 py-2.5 border border-slate-200 text-slate-600 rounded-xl text-xs font-bold hover:bg-slate-50 cursor-pointer">Batal</button>
          <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-[#00A9C1] to-[#007fa3] text-white rounded-xl text-xs font-bold hover:opacity-95 cursor-pointer shadow-sm">Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL EDIT SATWA -->
  <div id="modalEditSatwa" style="display:none;" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl border border-slate-100 overflow-hidden">
      <div class="bg-gradient-to-r from-[#00A9C1] to-[#007fa3] p-6 flex items-center justify-between text-white">
        <h4 class="text-sm sm:text-base font-bold">Edit Master Satwa</h4>
        <button type="button" onclick="closeModal('modalEditSatwa')" class="text-white text-xl font-bold cursor-pointer">&times;</button>
      </div>
      <form id="formEditSatwa" action="" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
        @csrf
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Satwa</label>
          <input type="text" name="nama" id="editNamaSatwa" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-teal-500 outline-none">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1.5">Ganti Foto (Opsional)</label>
          <input type="file" name="foto" accept="image/*" class="text-xs">
        </div>
        <div class="pt-3 flex justify-end gap-3">
          <button type="button" onclick="closeModal('modalEditSatwa')" class="px-5 py-2.5 border border-slate-200 text-slate-600 rounded-xl text-xs font-bold hover:bg-slate-50 cursor-pointer">Batal</button>
          <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-[#00A9C1] to-[#007fa3] text-white rounded-xl text-xs font-bold hover:opacity-95 cursor-pointer shadow-sm">Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL ADD USER -->
  <div id="modalAddUser" style="display:none;" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl border border-slate-100 overflow-hidden">
      <div class="bg-gradient-to-r from-[#00A9C1] to-[#007fa3] p-6 flex items-center justify-between text-white">
        <h4 class="text-sm sm:text-base font-bold">Tambah Pengguna Baru</h4>
        <button type="button" onclick="closeModal('modalAddUser')" class="text-white text-xl font-bold cursor-pointer">&times;</button>
      </div>
      <form action="{{ route('admin.manajemen.users.store') }}" method="POST" class="p-6 space-y-4">
        @csrf
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Lengkap / Unit</label>
          <input type="text" name="nama" required placeholder="cth: AMC Divisi" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-teal-500 outline-none">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1.5">Username</label>
          <input type="text" name="username" required placeholder="cth: amc.divisi" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-teal-500 outline-none">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1.5">Password</label>
          <input type="password" name="password" required placeholder="Minimal 4 karakter" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-teal-500 outline-none">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1.5">Role</label>
          <select name="role" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-teal-500 outline-none font-medium">
            <option value="pegawai">Pegawai</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <div class="pt-3 flex justify-end gap-3">
          <button type="button" onclick="closeModal('modalAddUser')" class="px-5 py-2.5 border border-slate-200 text-slate-600 rounded-xl text-xs font-bold hover:bg-slate-50 cursor-pointer">Batal</button>
          <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-[#00A9C1] to-[#007fa3] text-white rounded-xl text-xs font-bold hover:opacity-95 cursor-pointer shadow-sm">Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL EDIT USER -->
  <div id="modalEditUser" style="display:none;" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl border border-slate-100 overflow-hidden">
      <div class="bg-gradient-to-r from-[#00A9C1] to-[#007fa3] p-6 flex items-center justify-between text-white">
        <h4 class="text-sm sm:text-base font-bold">Edit Pengguna</h4>
        <button type="button" onclick="closeModal('modalEditUser')" class="text-white text-xl font-bold cursor-pointer">&times;</button>
      </div>
      <form id="formEditUser" action="" method="POST" class="p-6 space-y-4">
        @csrf
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Lengkap / Unit</label>
          <input type="text" name="nama" id="editNamaUser" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-teal-500 outline-none">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1.5">Username</label>
          <input type="text" name="username" id="editUsernameUser" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-teal-500 outline-none">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1.5">Password Baru (Kosongkan jika tidak diubah)</label>
          <input type="password" name="password" placeholder="Kosongkan jika tetap" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-teal-500 outline-none">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1.5">Role</label>
          <select name="role" id="editRoleUser" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-teal-500 outline-none font-medium">
            <option value="pegawai">Pegawai</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <div class="pt-3 flex justify-end gap-3">
          <button type="button" onclick="closeModal('modalEditUser')" class="px-5 py-2.5 border border-slate-200 text-slate-600 rounded-xl text-xs font-bold hover:bg-slate-50 cursor-pointer">Batal</button>
          <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-[#00A9C1] to-[#007fa3] text-white rounded-xl text-xs font-bold hover:opacity-95 cursor-pointer shadow-sm">Simpan</button>
        </div>
      </form>
    </div>
  </div>
@endsection

@section('scripts')
<script>
  function openModal(id) {
    document.getElementById(id).style.display = 'flex';
  }
  function closeModal(id) {
    document.getElementById(id).style.display = 'none';
  }

  function toggleOpsiField(select, targetId) {
    document.getElementById(targetId).style.display = select.value === 'dropdown' ? 'block' : 'none';
  }

  function openEditFieldModal(id, label, tipe, placeholder, wajib, keterangan, opsi) {
    document.getElementById('formEditField').action = `/admin/manajemen/fields/${id}`;
    document.getElementById('editLabel').value = label;
    document.getElementById('editTipe').value = tipe;
    document.getElementById('editPlaceholder').value = placeholder;
    document.getElementById('editWajib').checked = wajib == 1;
    document.getElementById('editOpsi').value = opsi;
    
    document.getElementById('editOpsiGroup').style.display = tipe === 'dropdown' ? 'block' : 'none';
    document.getElementById('editGridGroup').style.display = tipe === 'grid' ? 'block' : 'none';
    openModal('modalEditField');
  }

  function openEditSatwaModal(id, nama) {
    document.getElementById('formEditSatwa').action = `/admin/manajemen/satwa/${id}`;
    document.getElementById('editNamaSatwa').value = nama;
    openModal('modalEditSatwa');
  }

  function openEditUserModal(id, nama, username, role) {
    document.getElementById('formEditUser').action = `/admin/manajemen/users/${id}`;
    document.getElementById('editNamaUser').value = nama;
    document.getElementById('editUsernameUser').value = username;
    document.getElementById('editRoleUser').value = role;
    openModal('modalEditUser');
  }
</script>
@endsection
