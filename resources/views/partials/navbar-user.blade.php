@php
  $currentTab = $tab ?? request()->query('tab', 'users');
  if (!in_array($currentTab, ['users', 'fields'])) {
    $currentTab = 'users';
  }
  $userCount = $totalUsers ?? \App\Models\User::count();
  $fieldCount = $totalFields ?? \App\Models\FormField::count();
@endphp

<!-- ================= SUB-HEADER & COMMAND STRIP (Single Row - Fixed Height & Dimensions) ================= -->
<section class="bg-card-bg border-b border-border-subtle px-4 sm:px-6 lg:px-8 py-3.5">
  <div class="max-w-[1680px] mx-auto flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 min-h-[44px]">
    
    <!-- Page Title & Short Purpose -->
    <div class="min-w-0">
      <h1 class="text-base sm:text-lg font-bold text-on-surface tracking-tight">
        Manajemen User &amp; Konfigurasi Sistem
      </h1>
      <h2 class="text-[11px] text-secondary font-normal mt-0.5">
        Pusat pengelolaan akun petugas sisi udara, hak akses, dan pengaturan formulir dinamis.
      </h2>
    </div>

    <!-- Action Cluster Bar (Single Row) -->
    <div class="flex items-center flex-nowrap gap-2.5 shrink-0 self-start lg:self-center overflow-x-auto max-w-full">
      <!-- Sub-tabs: Akun Petugas & Field Formulir with Fixed Height -->
      <div class="inline-flex items-center shrink-0 bg-surface-container rounded-lg p-1 border border-border-subtle text-xs font-semibold whitespace-nowrap h-10 box-border">
        <a href="{{ route('admin.users', ['tab' => 'users']) }}" class="h-8 px-3 inline-flex items-center justify-center gap-1.5 rounded-md transition-all box-border {{ $currentTab === 'users' ? 'bg-white text-injourney-teal font-bold shadow-2xs' : 'text-secondary hover:text-slate-navy font-semibold hover:bg-white/40' }}">
          <span class="material-symbols-outlined text-base leading-none select-none">group</span>
          <span class="leading-none">Akun Petugas</span>
          <span class="inline-flex items-center justify-center px-1.5 h-4 min-w-[18px] rounded-full text-[10px] font-bold leading-none select-none {{ $currentTab === 'users' ? 'bg-cyan-50 text-injourney-teal' : 'bg-slate-200/70 text-slate-600' }}">{{ $userCount }}</span>
        </a>
        <a href="{{ route('admin.users', ['tab' => 'fields']) }}" class="h-8 px-3 inline-flex items-center justify-center gap-1.5 rounded-md transition-all box-border {{ $currentTab === 'fields' ? 'bg-white text-injourney-teal font-bold shadow-2xs' : 'text-secondary hover:text-slate-navy font-semibold hover:bg-white/40' }}">
          <span class="material-symbols-outlined text-base leading-none select-none">tune</span>
          <span class="leading-none">Field Formulir</span>
          <span class="inline-flex items-center justify-center px-1.5 h-4 min-w-[18px] rounded-full text-[10px] font-bold leading-none select-none {{ $currentTab === 'fields' ? 'bg-cyan-50 text-injourney-teal' : 'bg-slate-200/70 text-slate-600' }}">{{ $fieldCount }}</span>
        </a>
      </div>

      <!-- Action Button (Locked Exact Height & Alignment matching the tab) -->
      @if($currentTab === 'users')
        <button type="button" onclick="openModal('modalAddUser')" class="h-10 px-3.5 inline-flex items-center justify-center gap-1.5 text-xs font-bold rounded-lg bg-injourney-teal hover:bg-injourney-dark-teal text-white shadow-xs transition-all active:scale-[0.98] shrink-0 whitespace-nowrap cursor-pointer box-border">
          <span class="material-symbols-outlined text-base leading-none select-none">person_add</span>
          <span class="leading-none">Tambah User Baru</span>
        </button>
      @else
        <button type="button" onclick="openModal('modalAddField')" class="h-10 px-3.5 inline-flex items-center justify-center gap-1.5 text-xs font-bold rounded-lg bg-injourney-teal hover:bg-injourney-dark-teal text-white shadow-xs transition-all active:scale-[0.98] shrink-0 whitespace-nowrap cursor-pointer box-border">
          <span class="material-symbols-outlined text-base leading-none select-none">add</span>
          <span class="leading-none">Tambah Field Baru</span>
        </button>
      @endif
    </div>

  </div>
</section>
