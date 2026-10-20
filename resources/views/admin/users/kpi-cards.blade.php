<!-- SUMMARY KPI BENTO CARDS -->
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
