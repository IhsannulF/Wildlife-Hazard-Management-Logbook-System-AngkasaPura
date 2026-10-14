<!DOCTYPE html>
<html lang="id" class="h-full bg-canvas-light text-on-surface">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Manajemen User &amp; Konfigurasi Sistem - InJourney Airports</title>

  <!-- Favicon InJourney Airports -->
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
  <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

  <!-- Google Fonts: DM Sans & Material Symbols -->
  <link href="https://fonts.googleapis.com" rel="preconnect"/>
  <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          "colors": {
            "inverse-on-surface": "#eaf1ff",
            "injourney-dark-teal": "#0891B2",
            "runway-grid": "#334155",
            "on-tertiary": "#ffffff",
            "resolved-emerald": "#10B981",
            "injourney-teal": "#00A3B4",
            "surface-container-highest": "#d3e4fe",
            "error-container": "#ffdad6",
            "on-primary-container": "#003238",
            "surface-container-high": "#dce9ff",
            "on-tertiary-fixed": "#001f28",
            "on-secondary-fixed": "#131b2e",
            "surface-dim": "#cbdbf5",
            "tertiary-container": "#2ca0c1",
            "outline": "#6d797c",
            "secondary-fixed": "#dae2fd",
            "inverse-surface": "#213145",
            "warning-rose": "#EF4444",
            "surface-tint": "#006874",
            "surface": "#f8f9ff",
            "surface-container": "#e5eeff",
            "error": "#ba1a1a",
            "on-secondary-fixed-variant": "#3f465c",
            "primary-fixed-dim": "#5dd7e9",
            "on-error": "#ffffff",
            "primary-container": "#00a3b4",
            "on-secondary-container": "#5c647a",
            "slate-navy": "#0F172A",
            "on-surface": "#0b1c30",
            "on-error-container": "#93000a",
            "inverse-primary": "#5dd7e9",
            "tertiary-fixed": "#b7eaff",
            "on-tertiary-container": "#00313f",
            "border-subtle": "#E2E8F0",
            "on-primary-fixed-variant": "#004f58",
            "background": "#f8f9ff",
            "surface-variant": "#d3e4fe",
            "tertiary-fixed-dim": "#6cd3f7",
            "canvas-light": "#F8FAFC",
            "on-background": "#0b1c30",
            "tertiary": "#006780",
            "primary-fixed": "#97f0ff",
            "on-surface-variant": "#3d494b",
            "surface-container-lowest": "#ffffff",
            "on-tertiary-fixed-variant": "#004e61",
            "outline-variant": "#bcc9cb",
            "on-secondary": "#ffffff",
            "alert-amber": "#F59E0B",
            "secondary-fixed-dim": "#bec6e0",
            "primary": "#006874",
            "on-primary-fixed": "#001f24",
            "surface-container-low": "#eff4ff",
            "slate-surface": "#1E293B",
            "secondary-container": "#dae2fd",
            "on-primary": "#ffffff",
            "surface-bright": "#f8f9ff",
            "card-bg": "#FFFFFF",
            "secondary": "#565e74"
          },
          "borderRadius": {
            "DEFAULT": "0.25rem",
            "lg": "0.5rem",
            "xl": "0.75rem",
            "full": "9999px"
          },
          "fontFamily": {
            "headline-sm": ["DM Sans"],
            "label-lg": ["DM Sans"],
            "headline-lg": ["DM Sans"],
            "label-sm": ["DM Sans"],
            "body-sm": ["DM Sans"],
            "body-lg": ["DM Sans"],
            "label-md": ["DM Sans"],
            "headline-md": ["DM Sans"],
            "headline-xl": ["DM Sans"],
            "code-coordinate": ["DM Sans"],
            "body-md": ["DM Sans"]
          }
        }
      }
    }
  </script>
  <style>
    .material-symbols-outlined {
      font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
      display: inline-block;
      vertical-align: middle;
      line-height: 1;
    }
    /* Custom scrollbar */
    ::-webkit-scrollbar {
      width: 6px;
      height: 6px;
    }
    ::-webkit-scrollbar-track {
      background: #F8FAFC;
    }
    ::-webkit-scrollbar-thumb {
      background: #CBD5E1;
      border-radius: 9999px;
    }
    ::-webkit-scrollbar-thumb:hover {
      background: #94A3B8;
    }
  </style>
</head>
<body class="min-h-full bg-canvas-light text-on-surface font-body-md antialiased selection:bg-injourney-teal selection:text-white flex flex-col">

  <!-- SIDEBAR NAVIGATION -->
  @include('partials.navbar', ['activePage' => 'users'])

  <!-- SUB-HEADER & COMMAND STRIP (Included from partial) -->
  @include('partials.navbar-user')

  <!-- ================= MAIN OPERATIONAL CONTENT CONTAINER ================= -->
  <main class="flex-1 max-w-[1680px] w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- Flash Alert Notifications -->
    @if(session('sukses'))
      <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-xs font-semibold flex items-center justify-between shadow-2xs">
        <span class="flex items-center gap-2">
          <span class="material-symbols-outlined text-sm text-resolved-emerald">check_circle</span>
          {{ session('sukses') }}
        </span>
        <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold">&times;</button>
      </div>
    @endif
    @if(session('error'))
      <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-xs font-semibold flex items-center justify-between shadow-2xs">
        <span class="flex items-center gap-2">
          <span class="material-symbols-outlined text-sm text-warning-rose">error</span>
          {{ session('error') }}
        </span>
        <button onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 font-bold">&times;</button>
      </div>
    @endif

    <!-- 1. SUMMARY KPI BENTO CARDS -->
    <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3.5">
      
      <!-- Card 1: Total Pengguna -->
      <div class="bg-card-bg border border-border-subtle rounded-xl p-3.5 shadow-sm relative overflow-hidden flex flex-col justify-between">
        <div class="absolute top-0 left-0 right-0 h-1 bg-injourney-teal"></div>
        <div class="flex items-center justify-between mb-1.5">
          <span class="text-[10px] sm:text-[11px] uppercase text-secondary font-bold tracking-wider">Total Akun Petugas</span>
          <div class="w-8 h-8 rounded-lg bg-surface-container flex items-center justify-center text-injourney-teal">
            <span class="material-symbols-outlined text-base">group</span>
          </div>
        </div>
        <div class="flex items-baseline gap-2">
          <span class="text-lg sm:text-xl font-bold text-slate-navy">{{ $totalUsers }}</span>
          <span class="text-[11px] text-secondary font-medium">Akun Terdaftar</span>
        </div>
        <div class="mt-2.5 pt-2 border-t border-border-subtle flex items-center justify-between text-[11px] text-secondary">
          <span>Status Akses:</span>
          <span class="text-resolved-emerald font-semibold flex items-center gap-1">
            <span class="w-1.5 h-1.5 rounded-full bg-resolved-emerald"></span>
            {{ $activeUsersCount }} Aktif
          </span>
        </div>
      </div>

      <!-- Card 2: Petugas Lapangan (AMC / ARFF) -->
      <div class="bg-card-bg border border-border-subtle rounded-xl p-3.5 shadow-sm relative overflow-hidden flex flex-col justify-between">
        <div class="absolute top-0 left-0 right-0 h-1 bg-cyan-600"></div>
        <div class="flex items-center justify-between mb-1.5">
          <span class="text-[10px] sm:text-[11px] uppercase text-secondary font-bold tracking-wider">Petugas Lapangan (Pegawai)</span>
          <div class="w-8 h-8 rounded-lg bg-cyan-50 flex items-center justify-center text-cyan-600">
            <span class="material-symbols-outlined text-base">badge</span>
          </div>
        </div>
        <div class="flex items-baseline gap-2">
          <span class="text-lg sm:text-xl font-bold text-slate-navy">{{ $pegawaiCount }}</span>
          <span class="text-[11px] text-secondary font-medium">Personil Lapangan</span>
        </div>
        <div class="mt-2.5 pt-2 border-t border-border-subtle flex items-center justify-between text-[11px] text-secondary">
          <span>Otoritas:</span>
          <span class="font-medium text-slate-navy">Input Laporan &amp; Intervensi</span>
        </div>
      </div>

      <!-- Card 3: Administrator & Safety Manager -->
      <div class="bg-card-bg border border-border-subtle rounded-xl p-3.5 shadow-sm relative overflow-hidden flex flex-col justify-between">
        <div class="absolute top-0 left-0 right-0 h-1 bg-[#007fa3]"></div>
        <div class="flex items-center justify-between mb-1.5">
          <span class="text-[10px] sm:text-[11px] uppercase text-secondary font-bold tracking-wider">Superadmin &amp; Manager</span>
          <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-[#007fa3]">
            <span class="material-symbols-outlined text-base">admin_panel_settings</span>
          </div>
        </div>
        <div class="flex items-baseline gap-2">
          <span class="text-lg sm:text-xl font-bold text-slate-navy">{{ $adminCount }}</span>
          <span class="text-[11px] text-secondary font-medium">Administrator</span>
        </div>
        <div class="mt-2.5 pt-2 border-t border-border-subtle flex items-center justify-between text-[11px] text-secondary">
          <span>Otoritas:</span>
          <span class="font-medium text-slate-navy">Verifikasi BA &amp; Konfigurasi</span>
        </div>
      </div>

      <!-- Card 4: Field Formulir Aktif -->
      <div class="bg-card-bg border border-border-subtle rounded-xl p-3.5 shadow-sm relative overflow-hidden flex flex-col justify-between">
        <div class="absolute top-0 left-0 right-0 h-1 bg-alert-amber"></div>
        <div class="flex items-center justify-between mb-1.5">
          <span class="text-[10px] sm:text-[11px] uppercase text-secondary font-bold tracking-wider">Field Form Pelaporan</span>
          <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-alert-amber">
            <span class="material-symbols-outlined text-base">dynamic_form</span>
          </div>
        </div>
        <div class="flex items-baseline gap-2">
          <span class="text-lg sm:text-xl font-bold text-slate-navy">{{ $activeFieldsCount }}</span>
          <span class="text-[11px] text-secondary font-medium">Field Aktif</span>
        </div>
        <div class="mt-2.5 pt-2 border-t border-border-subtle flex items-center justify-between text-[11px] text-secondary">
          <span>Total Parameter:</span>
          <span class="font-code-coordinate text-slate-navy font-bold">{{ $totalFields }} Kolom Form</span>
        </div>
      </div>

    </section>

    @if($tab === 'users')
      <!-- ================= 2. TAB: MANAJEMEN AKUN PETUGAS ================= -->
      <div class="bg-card-bg rounded-xl border border-border-subtle shadow-sm p-4 sm:p-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-border-subtle mb-4">
          <div>
            <h3 class="text-sm sm:text-base font-bold text-slate-navy flex items-center gap-2">
              <span class="material-symbols-outlined text-injourney-teal">manage_accounts</span>
              Daftar Akun Petugas &amp; Hak Akses
            </h3>
            <p class="text-[11px] text-secondary mt-0.5">
              Kelola kredensial akun dinas petugas lapangan AMC, ARFF, Avsec, dan tim keselamatan operasional.
            </p>
          </div>

          <div class="flex items-center gap-2">
            <!-- Search User Input -->
            <div class="relative">
              <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-secondary text-sm">search</span>
              <input id="userSearchInput" onkeyup="filterUsersTable()" class="pl-8 pr-2.5 py-1.5 text-xs border border-border-subtle rounded-lg focus:outline-none focus:border-injourney-teal bg-canvas-light w-48 sm:w-56" placeholder="Cari nama / username..." type="text"/>
            </div>

            <!-- Role Filter Select -->
            <select id="userRoleFilter" onchange="filterUsersTable()" class="text-xs border border-border-subtle rounded-lg px-2.5 py-1.5 bg-canvas-light text-slate-navy focus:outline-none focus:border-injourney-teal">
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
                  <td class="py-2.5 px-3 text-secondary user-jabatan">{{ $u->jabatan ?: '-' }}</td>
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
                      <button type="button" onclick="openEditUserModal({{ $u->id }}, '{{ addslashes($u->namalengkap) }}', '{{ addslashes($u->username) }}', '{{ $u->role }}', '{{ addslashes($u->jabatan ?? '') }}')" class="p-1 text-secondary hover:text-injourney-teal rounded hover:bg-surface-container transition-colors" title="Ubah Data User">
                        <span class="material-symbols-outlined text-base">edit</span>
                      </button>

                      <!-- Toggle Status Form -->
                      <form action="{{ route('admin.manajemen.users.toggle', $u->id) }}" method="POST" class="inline m-0">
                        @csrf
                        <button type="submit" class="p-1 {{ $u->aktif ? 'text-amber-600 hover:text-amber-800' : 'text-emerald-600 hover:text-emerald-800' }} rounded hover:bg-surface-container transition-colors" title="{{ $u->aktif ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}">
                          <span class="material-symbols-outlined text-base">{{ $u->aktif ? 'block' : 'check_circle' }}</span>
                        </button>
                      </form>

                      <!-- Delete Form -->
                      @if($u->id !== auth()->id())
                        <form action="{{ route('admin.manajemen.users.delete', $u->id) }}" method="POST" class="inline m-0" onsubmit="return confirm('Hapus permanen akun user {{ $u->username }}?');">
                          @csrf
                          <button type="submit" class="p-1 text-rose-500 hover:text-rose-700 rounded hover:bg-rose-50 transition-colors" title="Hapus User">
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

    @else
      <!-- ================= 3. TAB: PENGATURAN FIELD FORMULIR ================= -->
      <div class="bg-card-bg rounded-xl border border-border-subtle shadow-sm p-4 sm:p-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-border-subtle mb-4">
          <div>
            <h3 class="text-sm sm:text-base font-bold text-slate-navy flex items-center gap-2">
              <span class="material-symbols-outlined text-injourney-teal">tune</span>
              Pengaturan Field Form Pelaporan Dinamis
            </h3>
            <p class="text-[11px] text-secondary mt-0.5">
              Atur parameter input formulir pelaporan petugas lapangan, tipe data, dan validasi isian wajib.
            </p>
          </div>

          <button type="button" onclick="openModal('modalAddField')" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold rounded-lg bg-injourney-teal hover:bg-injourney-dark-teal text-white shadow-xs transition-all active:scale-[0.98] self-start sm:self-center">
            <span class="material-symbols-outlined text-sm">add</span>
            <span>+ Tambah Field Baru</span>
          </button>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-surface-container-low text-secondary text-[10px] uppercase font-bold tracking-wider border-b border-border-subtle">
                <th class="py-2.5 px-3 w-16">Urutan</th>
                <th class="py-2.5 px-3 min-w-[180px]">Label Field</th>
                <th class="py-2.5 px-3 min-w-[120px]">Tipe Input</th>
                <th class="py-2.5 px-3 min-w-[130px]">Status Wajib</th>
                <th class="py-2.5 px-3 min-w-[110px]">Status Aktif</th>
                <th class="py-2.5 px-3 text-right min-w-[120px]">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-border-subtle text-[11px]">
              @foreach($fields as $f)
                <tr class="hover:bg-surface-container-low/60 transition-colors">
                  <td class="py-2.5 px-3 font-code-coordinate font-bold text-slate-navy">{{ $f->urutan }}</td>
                  <td class="py-2.5 px-3 font-semibold text-slate-navy">{{ $f->label }}</td>
                  <td class="py-2.5 px-3">
                    <span class="px-2 py-0.5 rounded bg-surface-container font-code-coordinate uppercase text-[10px] font-bold text-slate-700">
                      {{ $f->tipe }}
                    </span>
                  </td>
                  <td class="py-2.5 px-3">
                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold {{ $f->wajib ? 'text-amber-800 bg-amber-50 px-2 py-0.5 rounded-full' : 'text-slate-500' }}">
                      {{ $f->wajib ? 'Wajib (Required)' : 'Opsional' }}
                    </span>
                  </td>
                  <td class="py-2.5 px-3">
                    <span class="px-2 py-0.5 rounded-full font-bold text-[10px] {{ $f->aktif ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                      {{ $f->aktif ? 'Aktif' : 'Nonaktif' }}
                    </span>
                  </td>
                  <td class="py-2.5 px-3 text-right whitespace-nowrap">
                    <div class="flex items-center justify-end gap-2">
                      <form action="{{ route('admin.manajemen.fields.toggle', $f->id) }}" method="POST" class="inline m-0">
                        @csrf
                        <button type="submit" class="text-xs font-semibold {{ $f->aktif ? 'text-amber-600 hover:underline' : 'text-emerald-600 hover:underline' }}">
                          {{ $f->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                      </form>
                      <form action="{{ route('admin.manajemen.fields.delete', $f->id) }}" method="POST" class="inline m-0" onsubmit="return confirm('Hapus field {{ $f->label }}?');">
                        @csrf
                        <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline">Hapus</button>
                      </form>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    @endif

  </main>

  <!-- ================= MODAL TAMBAH USER ================= -->
  <div class="fixed inset-0 z-50 hidden bg-slate-navy/60 backdrop-blur-xs flex items-center justify-center p-4" id="modalAddUser">
    <div class="bg-card-bg border border-border-subtle rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl p-6">
      <div class="flex items-center justify-between pb-3.5 border-b border-border-subtle mb-4">
        <div>
          <span class="text-[10px] uppercase tracking-wider text-secondary font-bold">Kredensial Petugas</span>
          <h3 class="text-base font-bold text-slate-navy">Tambah Akun Petugas Baru</h3>
        </div>
        <button class="p-1.5 text-secondary hover:text-slate-navy rounded-lg hover:bg-surface-container" onclick="closeModal('modalAddUser')">
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
          <button type="button" class="px-4 py-2 border border-border-subtle rounded-lg bg-white text-slate-navy hover:bg-slate-50 font-semibold" onclick="closeModal('modalAddUser')">Batal</button>
          <button type="submit" class="px-5 py-2 bg-injourney-teal hover:bg-injourney-dark-teal text-white font-bold rounded-lg shadow-xs">Simpan User</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ================= MODAL EDIT USER ================= -->
  <div class="fixed inset-0 z-50 hidden bg-slate-navy/60 backdrop-blur-xs flex items-center justify-center p-4" id="modalEditUser">
    <div class="bg-card-bg border border-border-subtle rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl p-6">
      <div class="flex items-center justify-between pb-3.5 border-b border-border-subtle mb-4">
        <div>
          <span class="text-[10px] uppercase tracking-wider text-secondary font-bold">Kredensial Petugas</span>
          <h3 class="text-base font-bold text-slate-navy">Edit Data Akun Petugas</h3>
        </div>
        <button class="p-1.5 text-secondary hover:text-slate-navy rounded-lg hover:bg-surface-container" onclick="closeModal('modalEditUser')">
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
          <button type="button" class="px-4 py-2 border border-border-subtle rounded-lg bg-white text-slate-navy hover:bg-slate-50 font-semibold" onclick="closeModal('modalEditUser')">Batal</button>
          <button type="submit" class="px-5 py-2 bg-injourney-teal hover:bg-injourney-dark-teal text-white font-bold rounded-lg shadow-xs">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ================= MODAL TAMBAH FIELD ================= -->
  <div class="fixed inset-0 z-50 hidden bg-slate-navy/60 backdrop-blur-xs flex items-center justify-center p-4" id="modalAddField">
    <div class="bg-card-bg border border-border-subtle rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl p-6">
      <div class="flex items-center justify-between pb-3.5 border-b border-border-subtle mb-4">
        <div>
          <span class="text-[10px] uppercase tracking-wider text-secondary font-bold">Parameter Formulir</span>
          <h3 class="text-base font-bold text-slate-navy">Tambah Field Baru</h3>
        </div>
        <button class="p-1.5 text-secondary hover:text-slate-navy rounded-lg hover:bg-surface-container" onclick="closeModal('modalAddField')">
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
          <button type="button" class="px-4 py-2 border border-border-subtle rounded-lg bg-white text-slate-navy hover:bg-slate-50 font-semibold" onclick="closeModal('modalAddField')">Batal</button>
          <button type="submit" class="px-5 py-2 bg-injourney-teal hover:bg-injourney-dark-teal text-white font-bold rounded-lg shadow-xs">Simpan Field</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ================= FOOTER STANDAR INJOURNEY ================= -->
  <footer class="bg-card-bg border-t border-border-subtle mt-auto py-4 px-4 sm:px-6 lg:px-8">
    <div class="max-w-[1680px] mx-auto flex justify-end items-center text-xs text-secondary">
      <div class="flex items-center gap-2.5 text-right">
        <span class="font-bold text-slate-navy">PT Angkasa Pura Indonesia</span>
        <span>•</span>
        <span>InJourney Airports WHMS Portal</span>
      </div>
    </div>
  </footer>

  <!-- ================= JAVASCRIPT ================= -->
  <script>
    function openModal(modalId) {
      const modal = document.getElementById(modalId);
      if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
      }
    }

    function closeModal(modalId) {
      const modal = document.getElementById(modalId);
      if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
      }
    }

    function openEditUserModal(id, nama, username, role, jabatan) {
      document.getElementById('editUserNama').value = nama;
      document.getElementById('editUserUsername').value = username;
      document.getElementById('editUserRole').value = role;
      document.getElementById('editUserJabatan').value = jabatan;

      const form = document.getElementById('editUserForm');
      form.action = "{{ url('admin/manajemen/users') }}/" + id;

      openModal('modalEditUser');
    }

    function toggleAddOpsi(type) {
      const container = document.getElementById('addOpsiContainer');
      if (container) {
        if (type === 'dropdown') {
          container.classList.remove('hidden');
        } else {
          container.classList.add('hidden');
        }
      }
    }

    function filterUsersTable() {
      const search = (document.getElementById('userSearchInput')?.value || '').toLowerCase();
      const roleFilter = document.getElementById('userRoleFilter')?.value || 'all';
      const rows = document.querySelectorAll('.user-row');

      rows.forEach(row => {
        const name = (row.querySelector('.user-name')?.innerText || '').toLowerCase();
        const username = (row.querySelector('.user-username')?.innerText || '').toLowerCase();
        const jabatan = (row.querySelector('.user-jabatan')?.innerText || '').toLowerCase();
        const role = row.getAttribute('data-role');

        const matchesSearch = !search || name.includes(search) || username.includes(search) || jabatan.includes(search);
        const matchesRole = (roleFilter === 'all') || (role === roleFilter);

        row.style.display = (matchesSearch && matchesRole) ? '' : 'none';
      });
    }

    // Close modal on escape key
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        closeModal('modalAddUser');
        closeModal('modalEditUser');
        closeModal('modalAddField');
      }
    });
  </script>

</body>
</html>
