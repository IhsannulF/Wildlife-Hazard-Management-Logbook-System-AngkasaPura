@php
  $currentTab = $tab ?? request()->query('tab', 'users');
  if (!in_array($currentTab, ['users', 'fields'])) {
    $currentTab = 'users';
  }
  $userCount = $totalUsers ?? \App\Models\User::count();
  $fieldCount = $totalFields ?? \App\Models\FormField::count();
@endphp

<!-- ================= UNIFIED PAGE HEADER: MANAJEMEN USER & KONFIGURASI ================= -->
@component('partials.page-header', [
  'title' => 'Manajemen User & Konfigurasi Sistem',
  'subtitle' => 'Pusat pengelolaan akun petugas sisi udara, hak akses, dan pengaturan formulir dinamis.',
  'icon' => 'manage_accounts',
])
  <!-- Action Cluster Bar (Sub-tabs & Action Button) -->
  <div class="flex items-center flex-nowrap gap-2.5 shrink-0 self-start md:self-center overflow-x-auto max-w-full">
    <!-- Sub-tabs: Akun Petugas & Field Formulir -->
    <div class="inline-flex items-center shrink-0 bg-slate-100/80 rounded-lg p-1 border border-slate-200/80 text-xs font-semibold whitespace-nowrap h-10 box-border">
      <a href="{{ route('admin.users', ['tab' => 'users']) }}" class="h-8 px-3 inline-flex items-center justify-center gap-1.5 rounded-md transition-all box-border {{ $currentTab === 'users' ? 'bg-white text-[#00A9C1] font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 font-semibold hover:bg-white/50' }}">
        <span class="material-symbols-outlined text-base leading-none select-none">group</span>
        <span class="leading-none">Akun Petugas</span>
        <span class="inline-flex items-center justify-center px-1.5 h-4 min-w-[18px] rounded-full text-[10px] font-bold leading-none select-none {{ $currentTab === 'users' ? 'bg-cyan-50 text-[#00A9C1]' : 'bg-slate-200/70 text-slate-600' }}">{{ $userCount }}</span>
      </a>
      <a href="{{ route('admin.users', ['tab' => 'fields']) }}" class="h-8 px-3 inline-flex items-center justify-center gap-1.5 rounded-md transition-all box-border {{ $currentTab === 'fields' ? 'bg-white text-[#00A9C1] font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 font-semibold hover:bg-white/50' }}">
        <span class="material-symbols-outlined text-base leading-none select-none">tune</span>
        <span class="leading-none">Field Formulir</span>
        <span class="inline-flex items-center justify-center px-1.5 h-4 min-w-[18px] rounded-full text-[10px] font-bold leading-none select-none {{ $currentTab === 'fields' ? 'bg-cyan-50 text-[#00A9C1]' : 'bg-slate-200/70 text-slate-600' }}">{{ $fieldCount }}</span>
      </a>
    </div>

    <!-- Action Button -->
    @if($currentTab === 'users')
      <button type="button" onclick="openModal('modalAddUser')" class="h-10 px-3.5 inline-flex items-center justify-center gap-1.5 text-xs font-bold rounded-lg bg-[#00A9C1] hover:bg-[#007fa3] text-white shadow-xs transition-all active:scale-[0.98] shrink-0 whitespace-nowrap cursor-pointer box-border">
        <span class="material-symbols-outlined text-base leading-none select-none">person_add</span>
        <span class="leading-none">Tambah User Baru</span>
      </button>
    @else
      <button type="button" onclick="openModal('modalAddField')" class="h-10 px-3.5 inline-flex items-center justify-center gap-1.5 text-xs font-bold rounded-lg bg-[#00A9C1] hover:bg-[#007fa3] text-white shadow-xs transition-all active:scale-[0.98] shrink-0 whitespace-nowrap cursor-pointer box-border">
        <span class="material-symbols-outlined text-base leading-none select-none">add</span>
        <span class="leading-none">Tambah Field Baru</span>
      </button>
    @endif
  </div>
@endcomponent
