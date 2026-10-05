@php
  $currUser = auth()->user() ?? ($user ?? null);
  $isAdmin = $currUser ? ($currUser->role === 'admin') : false;
  $userName = $currUser ? ($currUser->namalengkap ?: $currUser->username) : 'Petugas Sisi Udara';
  $userRole = $currUser ? ($currUser->jabatan ?: ($isAdmin ? 'Safety Manager / Admin' : 'AMC Airside Staff')) : 'Airside Staff';
  $initials = strtoupper(substr($userName, 0, 2));
  $homeRoute = $isAdmin ? route('admin.dashboard') : route('user.dashboard');
  $currentPage = $activePage ?? '';
@endphp

<!-- Responsive Desktop Offset Styling -->
<style>
  @media (min-width: 1024px) {
    body {
      padding-left: 16rem !important;
    }
  }
</style>

<!-- ================= MOBILE TOP APP BAR (Visible on < lg) ================= -->
<header class="lg:hidden sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200 px-4 py-2.5 flex items-center justify-between shadow-2xs">
  <div class="flex items-center gap-2.5">
    <button type="button" onclick="toggleSidebar(true)" class="p-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors cursor-pointer" aria-label="Buka Menu Navigasi">
      <span class="material-symbols-outlined text-2xl leading-none">menu</span>
    </button>
    <a href="{{ $homeRoute }}" class="flex items-center gap-2">
      <img src="{{ asset('images/logo_login.png') }}" alt="InJourney Airports" class="h-6 sm:h-7 w-auto object-contain">
      <div class="h-3.5 w-px bg-slate-200 mx-0.5"></div>
      <span class="text-xs font-bold text-slate-800 tracking-tight">WHMS</span>
    </a>
  </div>

  <div class="flex items-center gap-2">
    <a href="{{ route('laporan.create') }}" class="p-1.5 sm:px-2.5 sm:py-1 rounded-lg bg-[#00A9C1] hover:bg-[#007fa3] text-white text-xs font-semibold flex items-center gap-1 shadow-2xs transition-colors">
      <span class="material-symbols-outlined text-sm">add</span>
      <span class="hidden sm:inline">Buat Laporan</span>
    </a>
    <div class="w-7 h-7 rounded-full bg-cyan-100 text-[#007fa3] border border-cyan-200 flex items-center justify-center font-bold text-xs shrink-0">
      {{ $initials }}
    </div>
  </div>
</header>

<!-- ================= MOBILE BACKDROP OVERLAY ================= -->
<div id="sidebarBackdrop" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-40 hidden transition-opacity lg:hidden" onclick="toggleSidebar(false)"></div>

<!-- ================= SIDEBAR NAVIGATION (Fixed on Desktop, Off-Canvas on Mobile) ================= -->
<aside id="mainSidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200/90 shadow-xl lg:shadow-xs flex flex-col transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 select-none">
  
  <!-- 1. Brand Logo & System Info Header -->
  <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-gradient-to-b from-cyan-50/30 to-transparent">
    <a href="{{ $homeRoute }}" class="flex items-center gap-2.5 group">
      <img src="{{ asset('images/logo_login.png') }}" alt="InJourney Airports" class="h-8 w-auto object-contain transition-transform group-hover:scale-[1.02]">
    </a>
    <button type="button" class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer" onclick="toggleSidebar(false)" aria-label="Tutup Menu">
      <span class="material-symbols-outlined text-xl leading-none">close</span>
    </button>
  </div>

  <!-- Sub-Header Strip -->
  <div class="px-4 py-2 bg-slate-50/70 border-b border-slate-100 flex items-center justify-between text-[11px]">
    <div class="flex items-center gap-1.5 text-slate-600 font-semibold truncate">
      <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
      <span class="truncate">WHMS Airside Logbook</span>
    </div>
    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-200 shrink-0">
      ICAO Annex 14
    </span>
  </div>

  <!-- 2. User Profile Card & Primary Action -->
  <div class="p-3.5 border-b border-slate-100">
    <div class="flex items-center gap-3 p-2.5 rounded-2xl bg-white border border-slate-200/80 shadow-2xs">
      <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#00A9C1] to-[#007fa3] text-white flex items-center justify-center font-extrabold text-sm shadow-xs shadow-cyan-500/20 shrink-0">
        {{ $initials }}
      </div>
      <div class="min-w-0 flex-1">
        <div class="font-bold text-xs text-slate-800 truncate" title="{{ $userName }}">{{ $userName }}</div>
        <div class="text-[10px] text-slate-500 font-medium truncate mt-0.5" title="{{ $userRole }}">{{ $userRole }}</div>
      </div>
    </div>
    
    <!-- Primary CTA: Buat Laporan -->
    <div class="mt-3">
      <a href="{{ route('laporan.create') }}" class="w-full flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-gradient-to-r from-[#00A9C1] to-[#007fa3] hover:from-[#007fa3] hover:to-[#00607d] text-white text-xs font-bold shadow-xs hover:shadow-cyan-500/20 transition-all active:scale-[0.98]">
        <span class="material-symbols-outlined text-base">add_circle</span>
        <span>Buat Laporan Baru</span>
      </a>
    </div>
  </div>

  <!-- 3. Navigation Links List -->
  <nav class="flex-1 overflow-y-auto px-3 py-3 space-y-4">
    <!-- Group: Menu Operasional -->
    <div>
      <div class="px-2 pb-1.5 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Menu Operasional</div>
      <div class="space-y-1">
        <!-- Dashboard -->
        <a href="{{ $homeRoute }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition-all {{ ($currentPage === 'dashboard' || request()->routeIs('admin.dashboard') || request()->routeIs('user.dashboard')) && request()->get('status') !== 'belum' ? 'font-bold bg-cyan-50/90 text-[#007fa3] border-l-4 border-[#00A9C1] shadow-2xs' : 'font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
          <span class="material-symbols-outlined text-lg {{ ($currentPage === 'dashboard' || request()->routeIs('admin.dashboard') || request()->routeIs('user.dashboard')) && request()->get('status') !== 'belum' ? 'text-[#00A9C1]' : 'text-slate-400' }}">space_dashboard</span>
          <span class="flex-1">Dashboard</span>
        </a>

        @if(!$isAdmin)
          <!-- User Form Laporan -->
          <a href="{{ route('laporan.create') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition-all {{ ($currentPage === 'create' || request()->routeIs('laporan.create')) ? 'font-bold bg-cyan-50/90 text-[#007fa3] border-l-4 border-[#00A9C1] shadow-2xs' : 'font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
            <span class="material-symbols-outlined text-lg {{ ($currentPage === 'create' || request()->routeIs('laporan.create')) ? 'text-[#00A9C1]' : 'text-slate-400' }}">edit_note</span>
            <span class="flex-1">Form Input Satwa</span>
          </a>
        @endif
      </div>
    </div>

    @if($isAdmin)
      <!-- Group: Analisis & Spasial -->
      <div>
        <div class="px-2 pb-1.5 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Analisis Spasial &amp; BA</div>
        <div class="space-y-1">
          <!-- Peta Sebaran Grid -->
          <a href="{{ route('admin.statistik') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition-all {{ ($currentPage === 'statistic' || request()->routeIs('admin.statistik')) ? 'font-bold bg-cyan-50/90 text-[#007fa3] border-l-4 border-[#00A9C1] shadow-2xs' : 'font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
            <span class="material-symbols-outlined text-lg {{ ($currentPage === 'statistic' || request()->routeIs('admin.statistik')) ? 'text-[#00A9C1]' : 'text-slate-400' }}">grid_view</span>
            <span class="flex-1 truncate">Peta Sebaran Grid</span>
            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-200/80">Heatmap</span>
          </a>

          <!-- Verifikasi Berita Acara -->
          <a href="{{ route('admin.dashboard', ['status' => 'belum']) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition-all {{ ($currentPage === 'verifikasi' || request()->get('status') === 'belum') ? 'font-bold bg-cyan-50/90 text-[#007fa3] border-l-4 border-[#00A9C1] shadow-2xs' : 'font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
            <span class="material-symbols-outlined text-lg {{ ($currentPage === 'verifikasi' || request()->get('status') === 'belum') ? 'text-[#00A9C1]' : 'text-slate-400' }}">fact_check</span>
            <span class="flex-1 truncate">Verifikasi BA</span>
            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Pending</span>
          </a>
        </div>
      </div>

      <!-- Group: Master Data & Pelaporan -->
      <div>
        <div class="px-2 pb-1.5 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Master Data &amp; Regulasi</div>
        <div class="space-y-1">
          <!-- Katalog Satwa -->
          <a href="{{ route('admin.manajemen') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition-all {{ ($currentPage === 'manajemen' || request()->routeIs('admin.manajemen')) ? 'font-bold bg-cyan-50/90 text-[#007fa3] border-l-4 border-[#00A9C1] shadow-2xs' : 'font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
            <span class="material-symbols-outlined text-lg {{ ($currentPage === 'manajemen' || request()->routeIs('admin.manajemen')) ? 'text-[#00A9C1]' : 'text-slate-400' }}">pets</span>
            <span class="flex-1 truncate">Katalog Satwa</span>
            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-100 text-slate-600">SOP</span>
          </a>

          <!-- Export Excel DKPPU -->
          <a href="{{ route('admin.export.excel') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-colors group" title="Unduh Spreadsheet Format DKPPU">
            <span class="material-symbols-outlined text-lg text-slate-400 group-hover:text-slate-600">file_download</span>
            <span class="flex-1 truncate">Export Excel</span>
            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">DKPPU</span>
          </a>
        </div>
      </div>
    @endif
  </nav>

  <!-- 4. Sidebar Bottom / Footer Actions -->
  <div class="p-3 border-t border-slate-100 bg-slate-50/50 space-y-2">
    <div class="px-2 py-1 flex items-center justify-between text-[10px] text-slate-400">
      <span>Status Sistem</span>
      <span class="font-semibold text-emerald-600 flex items-center gap-1">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Online
      </span>
    </div>

    <!-- Logout Form -->
    <form action="{{ route('logout') }}" method="POST" class="m-0">
      @csrf
      <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-100 transition-colors cursor-pointer text-left" title="Keluar dari sesi akun saat ini">
        <span class="material-symbols-outlined text-base">logout</span>
        <span>Keluar / Logout</span>
      </button>
    </form>
  </div>
</aside>

<!-- ================= SIDEBAR CONTROLLER SCRIPT ================= -->
<script>
  function toggleSidebar(open) {
    const sidebar = document.getElementById('mainSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    if (!sidebar || !backdrop) return;
    
    const shouldOpen = open !== undefined ? open : sidebar.classList.contains('-translate-x-full');
    if (shouldOpen) {
      sidebar.classList.remove('-translate-x-full');
      backdrop.classList.remove('hidden');
      document.body.classList.add('overflow-hidden', 'lg:overflow-auto');
    } else {
      sidebar.classList.add('-translate-x-full');
      backdrop.classList.add('hidden');
      document.body.classList.remove('overflow-hidden', 'lg:overflow-auto');
    }
  }

  // Close mobile sidebar on window resize to desktop
  window.addEventListener('resize', function() {
    if (window.innerWidth >= 1024) {
      const backdrop = document.getElementById('sidebarBackdrop');
      if (backdrop) backdrop.classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
    }
  });
</script>
