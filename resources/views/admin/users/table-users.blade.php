<!-- TAB 1: MANAJEMEN AKUN PETUGAS TABLE PANEL -->
<div class="bg-card-bg rounded-xl border border-border-subtle shadow-sm p-4 sm:p-5">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-border-subtle mb-4">
    <div>
      <h3 class="text-sm sm:text-base font-bold text-slate-navy flex items-center gap-2">
        <span class="material-symbols-outlined text-injourney-teal">manage_accounts</span>
        Daftar Akun Petugas &amp; Hak Akses
      </h3>
      <p class="text-[11px] text-secondary mt-0.5">
        Kelola 5 akun dinas departemen operasional sisi udara: Admin, AMC, PK / ARFF, Safety (SMS), dan Security (Avsec).
      </p>
    </div>

    <div class="flex items-center gap-2">
      <!-- Search User Input -->
      <div class="relative">
        <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-secondary text-sm">search</span>
        <input id="userSearchInput" onkeyup="filterUsersTable()" class="pl-8 pr-2.5 py-1.5 text-xs border border-border-subtle rounded-lg focus:outline-none focus:border-injourney-teal bg-canvas-light w-48 sm:w-56" placeholder="Cari nama / username..." type="text"/>
      </div>

      <!-- Role Filter Select -->
      <select id="userRoleFilter" onchange="filterUsersTable()" class="text-xs border border-border-subtle rounded-lg px-2.5 py-1.5 bg-canvas-light text-slate-navy focus:outline-none focus:border-injourney-teal cursor-pointer">
        <option value="all">Semua Role</option>
        <option value="admin">Administrator</option>
        <option value="pegawai">Pegawai</option>
      </select>
    </div>
  </div>

  <!-- High-Density Responsive Table -->
  <div class="overflow-x-auto">
    <table id="tblUsers" class="w-full text-left border-collapse text-xs">
      <thead>
        <tr class="bg-surface-container-low text-secondary text-[10px] uppercase font-bold tracking-wider border-b border-border-subtle">
          <th class="py-2.5 px-3 min-w-[200px]">Nama Lengkap</th>
          <th class="py-2.5 px-3 min-w-[130px]">Username</th>
          <th class="py-2.5 px-3 min-w-[160px]">Jabatan / Posko</th>
          <th class="py-2.5 px-3 min-w-[120px]">Hak Akses Role</th>
          <th class="py-2.5 px-3 min-w-[110px]">Status</th>
          <th class="py-2.5 px-3 text-right min-w-[130px]">Aksi</th>
        </tr>
      </thead>
      <tbody id="tblUsersBody" class="divide-y divide-border-subtle text-[11px]">
        @foreach($users as $u)
          @php
            $userInit = strtoupper(substr($u->namalengkap ?: $u->username, 0, 2));
          @endphp
          <tr class="user-row hover:bg-surface-container-low/60 transition-colors" data-role="{{ $u->role }}">
            <td class="py-2.5 px-3">
              <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-tr {{ $u->role === 'admin' ? 'from-[#00A9C1] to-[#007fa3]' : 'from-slate-600 to-slate-800' }} text-white flex items-center justify-center font-bold text-[11px] shrink-0">
                  {{ $userInit }}
                </div>
                <div class="min-w-0">
                  <div class="font-bold text-slate-navy truncate user-name">{{ $u->namalengkap }}</div>
                  @if($u->id === auth()->id())
                    <span class="inline-flex items-center text-[9px] px-1.5 py-0.2 rounded bg-cyan-100 text-cyan-800 font-bold">Akun Anda</span>
                  @endif
                </div>
              </div>
            </td>
            <td class="py-2.5 px-3 font-code-coordinate text-injourney-teal font-semibold user-username">{{ $u->username }}</td>
            <td class="py-2.5 px-3 text-secondary user-jabatan">
              <div>{{ $u->jabatan ?: '-' }}</div>
              @if(!empty($u->tanda_tangan))
                <span class="inline-flex items-center gap-0.5 text-[9px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.2 rounded mt-0.5">
                  <span class="material-symbols-outlined text-[10px]">draw</span>
                  Spesimen TTD Tersimpan
                </span>
              @elseif($u->role === 'admin')
                <span class="inline-flex items-center gap-0.5 text-[9px] font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.2 rounded mt-0.5">
                  <span class="material-symbols-outlined text-[10px]">pending</span>
                  Belum Ada Spesimen TTD
                </span>
              @endif
            </td>
            <td class="py-2.5 px-3 whitespace-nowrap">
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $u->role === 'admin' ? 'bg-cyan-100 text-cyan-800' : 'bg-slate-100 text-slate-700' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $u->role === 'admin' ? 'bg-injourney-teal' : 'bg-slate-400' }}"></span>
                {{ $u->role }}
              </span>
            </td>
            <td class="py-2.5 px-3 whitespace-nowrap">
              @if($u->aktif)
                <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-full">
                  <span class="w-1.5 h-1.5 rounded-full bg-resolved-emerald"></span>
                  Aktif
                </span>
              @else
                <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-full">
                  <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                  Nonaktif
                </span>
              @endif
            </td>
            <td class="py-2.5 px-3 text-right whitespace-nowrap">
              <div class="flex items-center justify-end gap-1.5">
                <!-- Edit Button -->
                <button type="button" onclick="openEditUserModal({{ $u->id }}, '{{ addslashes($u->namalengkap) }}', '{{ addslashes($u->username) }}', '{{ $u->role }}', '{{ addslashes($u->jabatan ?? '') }}')" class="p-1 text-secondary hover:text-injourney-teal rounded hover:bg-surface-container transition-colors cursor-pointer" title="Ubah Data User">
                  <span class="material-symbols-outlined text-base">edit</span>
                </button>

                <!-- Toggle Status Form -->
                <form action="{{ route('admin.manajemen.users.toggle', $u->id) }}" method="POST" class="inline m-0">
                  @csrf
                  <button type="submit" class="p-1 {{ $u->aktif ? 'text-amber-600 hover:text-amber-800' : 'text-emerald-600 hover:text-emerald-800' }} rounded hover:bg-surface-container transition-colors cursor-pointer" title="{{ $u->aktif ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}">
                    <span class="material-symbols-outlined text-base">{{ $u->aktif ? 'block' : 'check_circle' }}</span>
                  </button>
                </form>

                <!-- Delete Form -->
                @if($u->id !== auth()->id())
                  <form action="{{ route('admin.manajemen.users.delete', $u->id) }}" method="POST" class="inline m-0" onsubmit="return confirm('Hapus permanen akun user {{ $u->username }}?');">
                    @csrf
                    <button type="submit" class="p-1 text-rose-500 hover:text-rose-700 rounded hover:bg-rose-50 transition-colors cursor-pointer" title="Hapus User">
                      <span class="material-symbols-outlined text-base">delete</span>
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
