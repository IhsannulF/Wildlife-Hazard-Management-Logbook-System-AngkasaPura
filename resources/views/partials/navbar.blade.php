@php
  $currUser = auth()->user() ?? ($user ?? null);
  $isAdmin = $currUser ? ($currUser->role === 'admin') : false;
  $userName = $currUser ? ($currUser->namalengkap ?: $currUser->username) : 'Petugas Sisi Udara';
  $userRole = $currUser ? ($currUser->jabatan ?: ($isAdmin ? 'Safety Manager / Admin' : 'AMC Airside Staff')) : 'Airside Staff';
  $initials = strtoupper(substr($userName, 0, 2));
  $homeRoute = $isAdmin ? route('admin.dashboard') : route('user.dashboard');
  $currentPage = $activePage ?? '';
@endphp

<!-- Instant Desktop Sidebar Pinned & Active Navigation State Initialization (Prevents flash / layout shift) -->
<script>
  (function() {
    try {
      if (localStorage.getItem('whms_sidebar_pinned') === 'true' && window.innerWidth >= 1024) {
        document.documentElement.classList.add('sidebar-pinned');
      } else if (sessionStorage.getItem('whms_sidebar_navigating') === 'true' && window.innerWidth >= 1024) {
        document.documentElement.classList.add('sidebar-nav-open');
      }
    } catch (e) {}
  })();
</script>

<!-- Responsive Desktop Offset & Auto-Hide Styling -->
<style>
  /* Smooth transition for body padding when pinning/unpinning */
  body {
    transition: padding-left 0.28s cubic-bezier(0.16, 1, 0.3, 1);
  }

  /* When pinned on desktop: reserve 16rem space for permanent sidebar docking */
  @media (min-width: 1024px) {
    html.sidebar-pinned body {
      padding-left: 16rem !important;
    }

    html.sidebar-pinned #mainSidebar,
    html.sidebar-nav-open #mainSidebar {
      transform: translateX(0) !important;
      translate: 0 0 !important;
      --tw-translate-x: 0 !important;
      box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05) !important;
    }

    html.sidebar-pinned #sidebarTriggerTab,
    html.sidebar-pinned #sidebarTriggerTabFallback,
    html.sidebar-pinned #sidebarTriggerZone {
      display: none !important;
      pointer-events: none !important;
    }

    /* Hide fallback floating trigger if the page has the unified whms-page-header */
    body:has(.whms-page-header) #sidebarTriggerTabFallback,
    .whms-page-header ~ #sidebarTriggerTabFallback {
      display: none !important;
    }
  }

  /* Unpinned (Auto-Hide default on desktop): Content occupies full width */
  @media (min-width: 1024px) {
    html:not(.sidebar-pinned) body {
      padding-left: 0 !important;
    }
  }

  /* Ultra-smooth hardware-accelerated sliding transition for the sidebar */
  #mainSidebar {
    will-change: transform, translate;
    transition: transform 0.38s cubic-bezier(0.16, 1, 0.3, 1), translate 0.38s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.38s cubic-bezier(0.16, 1, 0.3, 1) !important;
  }

  /* Visual Affordance Floating Handle (Pojok Kiri Atas) */
  #sidebarTriggerTab {
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
    transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s ease, border-color 0.2s ease, color 0.2s ease;
  }
  #sidebarTriggerTab:hover {
    box-shadow: 0 4px 14px rgba(0, 169, 193, 0.22);
    transform: scale(1.05);
  }
</style>

<!-- ================= MOBILE TOP APP BAR (Visible on < lg) ================= -->
<header class="lg:hidden sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200 px-4 py-2.5 flex items-center justify-between shadow-2xs">
  <div class="flex items-center gap-2.5">
    <button type="button" onclick="toggleSidebar(true)" class="p-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00A9C1]" aria-label="Buka Menu Navigasi">
      <span class="material-symbols-outlined text-2xl leading-none">menu</span>
    </button>
    <a href="{{ $homeRoute }}" class="flex items-center gap-2">
      <img src="{{ asset('images/logo_login.png') }}" alt="InJourney Airports" class="h-6 sm:h-7 w-auto object-contain">
      <div class="h-3.5 w-px bg-slate-200 mx-0.5"></div>
      <span class="text-xs font-bold text-slate-800 tracking-tight">WHMS</span>
    </a>
  </div>

  <div class="flex items-center gap-2">
    @if(!$isAdmin)
      <a href="{{ route('laporan.create') }}" class="p-1.5 sm:px-2.5 sm:py-1 rounded-lg bg-[#00A9C1] hover:bg-[#007fa3] text-white text-xs font-semibold flex items-center gap-1 shadow-2xs transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00A9C1]">
        <span class="material-symbols-outlined text-sm">add</span>
        <span class="hidden sm:inline">Buat Laporan</span>
      </a>
    @endif
    <div class="w-7 h-7 rounded-full bg-cyan-100 text-[#007fa3] border border-cyan-200 flex items-center justify-center font-bold text-xs shrink-0 select-none">
      {{ $initials }}
    </div>
  </div>
</header>

<!-- ================= MOBILE BACKDROP OVERLAY ================= -->
<div id="sidebarBackdrop" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-40 hidden transition-opacity lg:hidden" onclick="toggleSidebar(false)"></div>

<!-- ================= DESKTOP AUTO-HIDE TRIGGER ZONE & FLOATING TAB ================= -->
<!-- 1. Invisible Edge Trigger Strip (detects cursor entering left edge gutter) -->
<div id="sidebarTriggerZone" 
     class="hidden lg:block fixed inset-y-0 left-0 w-6 z-40" 
     aria-hidden="true"></div>

<!-- 2. Visual Affordance Floating Handle Tab (Fallback only for pages without .whms-page-header) -->
<button type="button" 
        id="sidebarTriggerTabFallback" 
        onclick="toggleSidebarClick()" 
        class="hidden lg:flex fixed top-4 left-4 z-40 w-10 h-10 rounded-xl bg-white/95 backdrop-blur-md hover:bg-white text-slate-700 hover:text-[#00A9C1] border border-slate-200/90 shadow-sm hover:shadow-md items-center justify-center cursor-pointer select-none group transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00A9C1]" 
        title="Buka Menu Navigasi"
        aria-label="Buka Menu Navigasi">
  <span class="material-symbols-outlined text-2xl text-slate-600 group-hover:text-[#00A9C1] transition-transform leading-none">menu</span>
</button>

<!-- ================= SIDEBAR NAVIGATION (Auto-Hide on Desktop, Off-Canvas on Mobile) ================= -->
<aside id="mainSidebar" 
       class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200/90 shadow-2xl flex flex-col transition-transform duration-300 ease-in-out -translate-x-full select-none"
       aria-label="Menu Navigasi Utama">
  
  <!-- 1. Brand Logo & System Info Header -->
  <div class="px-3.5 py-3 border-b border-slate-100 flex items-center justify-between bg-gradient-to-b from-cyan-50/30 to-transparent relative">
    <a href="{{ $homeRoute }}" class="flex items-center gap-2 group min-w-0 pr-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00A9C1] rounded-lg">
      <img src="{{ asset('images/logo_login.png') }}" alt="InJourney Airports" class="h-10 sm:h-11 w-auto object-contain transition-transform group-hover:scale-[1.02] shrink-0">
      <div class="h-7 w-px bg-slate-200 shrink-0"></div>
      <div class="flex items-center gap-1.5 min-w-0">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
        <span class="text-[11px] font-bold text-slate-700 tracking-tight leading-tight">WHMS Airside Logbook</span>
      </div>
    </a>

    <!-- Mobile Close Button (< lg) -->
    <button type="button" 
            class="lg:hidden p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer shrink-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00A9C1]" 
            onclick="toggleSidebar(false)" 
            aria-label="Tutup Menu">
      <span class="material-symbols-outlined text-xl leading-none">close</span>
    </button>

    <!-- Desktop Pin / Unpin Toggle Button (>= lg) -->
    <button type="button" 
            id="sidebarPinBtn" 
            onclick="toggleSidebarPin()" 
            class="hidden lg:flex p-1.5 rounded-lg text-slate-400 hover:text-[#007fa3] hover:bg-cyan-50 transition-colors cursor-pointer shrink-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00A9C1]" 
            title="Sematkan Sidebar (Tetap Terbuka)" 
            aria-label="Sematkan Sidebar">
      <span class="material-symbols-outlined text-lg leading-none transition-transform" id="sidebarPinIcon">push_pin</span>
    </button>
  </div>

  <!-- 2. User Profile Card & Primary Action -->
  <div class="p-3.5 border-b border-slate-100">
    <div class="flex items-center gap-3 p-2.5 rounded-2xl bg-white border border-slate-200/80 shadow-2xs">
      <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#00A9C1] to-[#007fa3] text-white flex items-center justify-center font-extrabold text-sm shadow-xs shadow-cyan-500/20 shrink-0 select-none">
        {{ $initials }}
      </div>
      <div class="min-w-0 flex-1">
        <div class="font-bold text-xs text-slate-800 truncate" title="{{ $userName }}">{{ $userName }}</div>
        <div class="text-[10px] text-slate-500 font-medium truncate mt-0.5" title="{{ $userRole }}">{{ $userRole }}</div>
      </div>
    </div>
    
    @if(!$isAdmin)
      <!-- Primary CTA: Buat Laporan -->
      <div class="mt-3">
        <a href="{{ route('laporan.create') }}" class="w-full flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-gradient-to-r from-[#00A9C1] to-[#007fa3] hover:from-[#007fa3] hover:to-[#00607d] text-white text-xs font-bold shadow-xs hover:shadow-cyan-500/20 transition-all active:scale-[0.98] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00A9C1]">
          <span class="material-symbols-outlined text-base">add_circle</span>
          <span>Buat Laporan Baru</span>
        </a>
      </div>
    @endif
  </div>

  <!-- 3. Navigation Links List -->
  <nav class="flex-1 overflow-y-auto px-3 py-3 space-y-4" aria-label="Daftar Navigasi">
    <!-- Group: Menu Operasional -->
    <div>
      <div class="px-2 pb-1.5 text-[10px] font-bold tracking-wider text-slate-400 uppercase select-none">Menu Operasional</div>
      <div class="space-y-1">
        @php
          $isVerifNav = ($currentPage === 'verifikasi' || request()->routeIs('admin.verifikasi*') || request()->get('tab') === 'verifikasi');
        @endphp
        <!-- Dashboard -->
        <a href="{{ $homeRoute }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00A9C1] {{ ($currentPage === 'dashboard' || request()->routeIs('admin.dashboard') || request()->routeIs('user.dashboard')) && !$isVerifNav ? 'font-bold bg-cyan-50/90 text-[#007fa3] border-l-4 border-[#00A9C1] shadow-2xs' : 'font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
          <span class="material-symbols-outlined text-lg {{ ($currentPage === 'dashboard' || request()->routeIs('admin.dashboard') || request()->routeIs('user.dashboard')) && !$isVerifNav ? 'text-[#00A9C1]' : 'text-slate-400' }}">space_dashboard</span>
          <span class="flex-1">Dashboard</span>
        </a>

        @if(!$isAdmin)
          <!-- Form Laporan -->
          <a href="{{ route('laporan.create') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00A9C1] {{ ($currentPage === 'create' || request()->routeIs('laporan.create')) ? 'font-bold bg-cyan-50/90 text-[#007fa3] border-l-4 border-[#00A9C1] shadow-2xs' : 'font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
            <span class="material-symbols-outlined text-lg {{ ($currentPage === 'create' || request()->routeIs('laporan.create')) ? 'text-[#00A9C1]' : 'text-slate-400' }}">edit_note</span>
            <span class="flex-1">Form Input Satwa</span>
          </a>
        @endif
      </div>
    </div>

    @if($isAdmin)
      <!-- Group: Analisis & Spasial -->
      <div>
        <div class="px-2 pb-1.5 text-[10px] font-bold tracking-wider text-slate-400 uppercase select-none">Analisis Spasial &amp; BA</div>
        <div class="space-y-1">
          <!-- Peta Sebaran Grid -->
          <a href="{{ route('admin.statistik') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00A9C1] {{ ($currentPage === 'statistic' || request()->routeIs('admin.statistik')) ? 'font-bold bg-cyan-50/90 text-[#007fa3] border-l-4 border-[#00A9C1] shadow-2xs' : 'font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
            <span class="material-symbols-outlined text-lg {{ ($currentPage === 'statistic' || request()->routeIs('admin.statistik')) ? 'text-[#00A9C1]' : 'text-slate-400' }}">grid_view</span>
            <span class="flex-1">Peta Sebaran Grid</span>
          </a>

          @php
            $activeSub = $currentSub ?? (
              request()->routeIs('admin.verifikasi.patroli') ? 'patroli' : (
              request()->routeIs('admin.verifikasi.detail-temuan') ? 'detail-temuan' : (
              request()->routeIs('admin.verifikasi.backup-temuan') ? 'backup-temuan' : (
              request()->routeIs('admin.verifikasi.master-titik') ? 'master-titik' : (
              request()->routeIs('admin.verifikasi.master-jenis') ? 'master-jenis' : (
              request()->routeIs('admin.verifikasi.master-preview') ? 'master-preview' : 'autocrat')))))
            );
          @endphp
          <!-- Verifikasi Berita Acara (Collapsible Dropdown) -->
          <div class="space-y-1">
            <div class="flex items-center rounded-xl text-xs transition-all {{ $isVerifNav ? 'bg-cyan-50/90 text-[#007fa3] border-l-4 border-[#00A9C1] shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
              <a href="{{ route('admin.verifikasi') }}" class="flex-1 flex items-center gap-3 px-3 py-2.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00A9C1] rounded-l-xl {{ $isVerifNav ? 'font-bold' : 'font-medium' }}" title="Verifikasi Berita Acara (BA)">
                <span class="material-symbols-outlined text-lg {{ $isVerifNav ? 'text-[#00A9C1]' : 'text-slate-400' }}">fact_check</span>
                <span class="flex-1">Verifikasi BA</span>
              </a>
              <button type="button" onclick="toggleVerifikasiDropdown(event)" class="px-2 py-2.5 text-slate-400 hover:text-[#007fa3] transition-transform cursor-pointer rounded-r-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00A9C1]" title="Buka/Tutup Submenu" aria-label="Buka/Tutup Submenu Verifikasi">
                <span class="material-symbols-outlined text-base transition-transform duration-200 {{ $isVerifNav ? 'rotate-180 text-[#00A9C1]' : '' }}" id="verifChevron">expand_more</span>
              </button>
            </div>

            <!-- Submenu: 7 Data Spesifik Safety Autocrat -->
            <div id="verifikasiSubmenu" class="pl-3 py-1 space-y-0.5 border-l-2 border-cyan-200/80 ml-3.5 {{ $isVerifNav ? '' : 'hidden' }}">
              <!-- 1. Laporan Autocrat -->
              <a href="{{ route('admin.verifikasi.autocrat') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-[11px] transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00A9C1] {{ ($isVerifNav && $activeSub === 'autocrat') ? 'font-bold text-[#007fa3] bg-cyan-100/70 border border-cyan-200/50' : 'font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                <span class="material-symbols-outlined text-[15px] {{ ($isVerifNav && $activeSub === 'autocrat') ? 'text-[#00A9C1]' : 'text-slate-400' }}">description</span>
                <span class="truncate">Laporan Autocrat</span>
              </a>

              <!-- 2. Patroli -->
              <a href="{{ route('admin.verifikasi.patroli') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-[11px] transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00A9C1] {{ ($isVerifNav && $activeSub === 'patroli') ? 'font-bold text-[#007fa3] bg-cyan-100/70 border border-cyan-200/50' : 'font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                <span class="material-symbols-outlined text-[15px] {{ ($isVerifNav && $activeSub === 'patroli') ? 'text-[#00A9C1]' : 'text-slate-400' }}">commute</span>
                <span class="truncate">Patroli</span>
              </a>

              <!-- 3. Detail Temuan -->
              <a href="{{ route('admin.verifikasi.detail-temuan') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-[11px] transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00A9C1] {{ ($isVerifNav && $activeSub === 'detail-temuan') ? 'font-bold text-[#007fa3] bg-cyan-100/70 border border-cyan-200/50' : 'font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                <span class="material-symbols-outlined text-[15px] {{ ($isVerifNav && $activeSub === 'detail-temuan') ? 'text-[#00A9C1]' : 'text-slate-400' }}">checklist</span>
                <span class="truncate">Detail Temuan</span>
              </a>

              <!-- 4. Back Up Detail Temuan -->
              <a href="{{ route('admin.verifikasi.backup-temuan') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-[11px] transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00A9C1] {{ ($isVerifNav && $activeSub === 'backup-temuan') ? 'font-bold text-[#007fa3] bg-cyan-100/70 border border-cyan-200/50' : 'font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                <span class="material-symbols-outlined text-[15px] {{ ($isVerifNav && $activeSub === 'backup-temuan') ? 'text-[#00A9C1]' : 'text-slate-400' }}">backup</span>
                <span class="truncate">Back Up Detail Temuan</span>
              </a>

              <!-- 5. Master Titik -->
              <a href="{{ route('admin.verifikasi.master-titik') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-[11px] transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00A9C1] {{ ($isVerifNav && $activeSub === 'master-titik') ? 'font-bold text-[#007fa3] bg-cyan-100/70 border border-cyan-200/50' : 'font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                <span class="material-symbols-outlined text-[15px] {{ ($isVerifNav && $activeSub === 'master-titik') ? 'text-[#00A9C1]' : 'text-slate-400' }}">location_on</span>
                <span class="truncate">Master Titik</span>
              </a>

              <!-- 6. Master Jenis -->
              <a href="{{ route('admin.verifikasi.master-jenis') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-[11px] transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00A9C1] {{ ($isVerifNav && $activeSub === 'master-jenis') ? 'font-bold text-[#007fa3] bg-cyan-100/70 border border-cyan-200/50' : 'font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                <span class="material-symbols-outlined text-[15px] {{ ($isVerifNav && $activeSub === 'master-jenis') ? 'text-[#00A9C1]' : 'text-slate-400' }}">flutter_dash</span>
                <span class="truncate">Master Jenis</span>
              </a>

              <!-- 7. Master Preview -->
              <a href="{{ route('admin.verifikasi.master-preview') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-[11px] transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00A9C1] {{ ($isVerifNav && $activeSub === 'master-preview') ? 'font-bold text-[#007fa3] bg-cyan-100/70 border border-cyan-200/50' : 'font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                <span class="material-symbols-outlined text-[15px] {{ ($isVerifNav && $activeSub === 'master-preview') ? 'text-[#00A9C1]' : 'text-slate-400' }}">photo_library</span>
                <span class="truncate">Master Preview</span>
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Group: Master Data & Pelaporan -->
      <div>
        <div class="px-2 pb-1.5 text-[10px] font-bold tracking-wider text-slate-400 uppercase select-none">Master Data &amp; Regulasi</div>
        <div class="space-y-1">
          <!-- Katalog Satwa -->
          <a href="{{ route('admin.manajemen') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00A9C1] {{ ($currentPage === 'manajemen' || request()->routeIs('admin.manajemen')) ? 'font-bold bg-cyan-50/90 text-[#007fa3] border-l-4 border-[#00A9C1] shadow-2xs' : 'font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
            <span class="material-symbols-outlined text-lg {{ ($currentPage === 'manajemen' || request()->routeIs('admin.manajemen')) ? 'text-[#00A9C1]' : 'text-slate-400' }}">pets</span>
            <span class="flex-1">Katalog Satwa</span>
          </a>

          <!-- Manajemen User -->
          <a href="{{ route('admin.users') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00A9C1] {{ ($currentPage === 'users' || request()->routeIs('admin.users')) ? 'font-bold bg-cyan-50/90 text-[#007fa3] border-l-4 border-[#00A9C1] shadow-2xs' : 'font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
            <span class="material-symbols-outlined text-lg {{ ($currentPage === 'users' || request()->routeIs('admin.users')) ? 'text-[#00A9C1]' : 'text-slate-400' }}">manage_accounts</span>
            <span class="flex-1">Manajemen User</span>
          </a>

          <!-- Export Excel DKPPU -->
          <a href="{{ route('admin.export.excel') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00A9C1] {{ ($currentPage === 'export_excel' || request()->routeIs('admin.export.excel*') || request()->routeIs('export.excel*')) ? 'font-bold bg-cyan-50/90 text-[#007fa3] border-l-4 border-[#00A9C1] shadow-2xs' : 'font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}" title="Pusat Konfigurasi & Unduh Spreadsheet DKPPU">
            <span class="material-symbols-outlined text-lg {{ ($currentPage === 'export_excel' || request()->routeIs('admin.export.excel*') || request()->routeIs('export.excel*')) ? 'text-[#00A9C1]' : 'text-slate-400' }}">file_download</span>
            <span class="flex-1">Export Excel</span>
          </a>
        </div>
      </div>
    @endif
  </nav>

  <!-- 4. Sidebar Bottom / Footer Actions -->
  <div class="p-3 border-t border-slate-100 bg-slate-50/50">
    <!-- Logout Form -->
    <form action="{{ route('logout') }}" method="POST" class="m-0">
      @csrf
      <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-100 transition-colors cursor-pointer text-left focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-500" title="Keluar dari sesi akun saat ini">
        <span class="material-symbols-outlined text-base">logout</span>
        <span>Keluar / Logout</span>
      </button>
    </form>
  </div>
</aside>

<!-- ================= SIDEBAR AUTO-HIDE CONTROLLER SCRIPT ================= -->
<script>
  let closeTimeout = null;
  let openTimeout = null;
  let isMouseInSidebar = false;
  let isMouseInTrigger = false;
  let isFocusInSidebar = false;

  function isDesktop() {
    return window.innerWidth >= 1024;
  }

  function isPinned() {
    return document.documentElement.classList.contains('sidebar-pinned');
  }

  // --- DESKTOP OPEN / CLOSE ACTIONS ---
  function openDesktopSidebar() {
    if (closeTimeout) {
      clearTimeout(closeTimeout);
      closeTimeout = null;
    }
    const sidebar = document.getElementById('mainSidebar');
    if (!sidebar) return;

    sidebar.classList.remove('-translate-x-full');
    sidebar.classList.add('translate-x-0');
    sidebar.style.removeProperty('translate');
    sidebar.style.removeProperty('--tw-translate-x');
  }

  function closeDesktopSidebar(delay = 260) {
    if (isPinned()) return;
    if (closeTimeout) clearTimeout(closeTimeout);

    closeTimeout = setTimeout(() => {
      // Don't close if cursor or focus is still inside sidebar or trigger zone
      if (isMouseInSidebar || isMouseInTrigger || isFocusInSidebar) return;

      const sidebar = document.getElementById('mainSidebar');
      if (!sidebar) return;

      sidebar.classList.add('-translate-x-full');
      sidebar.classList.remove('translate-x-0');
    }, delay);
  }

  // Click on trigger tab (for touch laptops, tablets, or explicit mouse clicks)
  function toggleSidebarClick() {
    if (openTimeout) {
      clearTimeout(openTimeout);
      openTimeout = null;
    }
    if (!isDesktop()) {
      toggleSidebar(true);
      return;
    }
    const sidebar = document.getElementById('mainSidebar');
    if (!sidebar) return;
    const isHidden = sidebar.classList.contains('-translate-x-full');
    if (isHidden) {
      openDesktopSidebar();
    } else {
      closeDesktopSidebar(0);
    }
  }

  // Pin toggle logic (dock permanently vs auto-hide)
  function toggleSidebarPin() {
    const html = document.documentElement;
    const pinIcon = document.getElementById('sidebarPinIcon');
    const pinBtn = document.getElementById('sidebarPinBtn');
    const nowPinned = !html.classList.contains('sidebar-pinned');

    if (nowPinned) {
      html.classList.add('sidebar-pinned');
      localStorage.setItem('whms_sidebar_pinned', 'true');
      if (pinIcon) {
        pinIcon.classList.add('rotate-45', 'text-[#00A9C1]');
      }
      if (pinBtn) {
        pinBtn.title = 'Lepas Sematan (Auto-Hide Otomatis)';
      }
      openDesktopSidebar();
    } else {
      html.classList.remove('sidebar-pinned');
      localStorage.setItem('whms_sidebar_pinned', 'false');
      if (pinIcon) {
        pinIcon.classList.remove('rotate-45', 'text-[#00A9C1]');
      }
      if (pinBtn) {
        pinBtn.title = 'Sematkan Sidebar (Tetap Terbuka)';
      }
      // If mouse is already outside, close after grace period
      if (!isMouseInSidebar && !isMouseInTrigger) {
        closeDesktopSidebar(220);
      }
    }
  }

  // Sync Pin Icon visual state
  function syncPinIconState() {
    const pinIcon = document.getElementById('sidebarPinIcon');
    const pinBtn = document.getElementById('sidebarPinBtn');
    if (isPinned()) {
      if (pinIcon) {
        pinIcon.classList.add('rotate-45', 'text-[#00A9C1]');
      }
      if (pinBtn) {
        pinBtn.title = 'Lepas Sematan (Auto-Hide Otomatis)';
      }
    } else {
      if (pinIcon) {
        pinIcon.classList.remove('rotate-45', 'text-[#00A9C1]');
      }
      if (pinBtn) {
        pinBtn.title = 'Sematkan Sidebar (Tetap Terbuka)';
      }
    }
  }

  // --- MOBILE SIDEBAR TOGGLE ---
  function toggleSidebar(open) {
    const sidebar = document.getElementById('mainSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    if (!sidebar || !backdrop) return;
    
    const shouldOpen = open !== undefined ? open : sidebar.classList.contains('-translate-x-full');
    if (shouldOpen) {
      sidebar.classList.remove('-translate-x-full');
      sidebar.classList.add('translate-x-0');
      backdrop.classList.remove('hidden');
      document.body.classList.add('overflow-hidden');
    } else {
      sidebar.classList.add('-translate-x-full');
      sidebar.classList.remove('translate-x-0');
      backdrop.classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
    }
  }

  // --- VERIFIKASI SUBMENU TOGGLE ---
  function toggleVerifikasiDropdown(event) {
    if (event) {
      event.preventDefault();
      event.stopPropagation();
    }
    const submenu = document.getElementById('verifikasiSubmenu');
    const chevron = document.getElementById('verifChevron');
    if (!submenu) return;
    
    const isHidden = submenu.classList.contains('hidden');
    if (isHidden) {
      submenu.classList.remove('hidden');
      if (chevron) chevron.classList.add('rotate-180', 'text-[#00A9C1]');
    } else {
      submenu.classList.add('hidden');
      if (chevron) chevron.classList.remove('rotate-180', 'text-[#00A9C1]');
    }
  }

  // --- INITIALIZE EVENT LISTENERS ---
  document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('mainSidebar');
    if (isPinned() && sidebar) {
      sidebar.classList.remove('-translate-x-full');
      sidebar.classList.add('translate-x-0');
      sidebar.style.removeProperty('translate');
      sidebar.style.removeProperty('--tw-translate-x');
    }
    syncPinIconState();
    const triggerZone = document.getElementById('sidebarTriggerZone');
    const triggerTab = document.getElementById('sidebarTriggerTab');

    // Trigger Zone Hover with graceful transition delay (120ms)
    if (triggerZone) {
      triggerZone.addEventListener('mouseenter', function() {
        if (!isDesktop() || isPinned()) return;
        isMouseInTrigger = true;
        if (openTimeout) clearTimeout(openTimeout);
        openTimeout = setTimeout(() => {
          if (isMouseInTrigger) openDesktopSidebar();
        }, 120);
      });
      triggerZone.addEventListener('mouseleave', function() {
        isMouseInTrigger = false;
        if (openTimeout) {
          clearTimeout(openTimeout);
          openTimeout = null;
        }
        if (isDesktop() && !isPinned()) closeDesktopSidebar(260);
      });
    }

    // Trigger Tabs (Unified Page Header tab & Fallback tab) Hover & Focus
    const triggerTabs = document.querySelectorAll('#sidebarTriggerTab, #sidebarTriggerTabFallback');
    triggerTabs.forEach(tab => {
      tab.addEventListener('mouseenter', function() {
        if (!isDesktop() || isPinned()) return;
        isMouseInTrigger = true;
        if (openTimeout) clearTimeout(openTimeout);
        openTimeout = setTimeout(() => {
          if (isMouseInTrigger) openDesktopSidebar();
        }, 120);
      });
      tab.addEventListener('mouseleave', function() {
        isMouseInTrigger = false;
        if (openTimeout) {
          clearTimeout(openTimeout);
          openTimeout = null;
        }
        if (isDesktop() && !isPinned()) closeDesktopSidebar(260);
      });
      tab.addEventListener('focus', function() {
        if (!isDesktop() || isPinned()) return;
        openDesktopSidebar();
      });
    });

    // Sidebar Hover
    if (sidebar) {
      sidebar.addEventListener('mouseenter', function() {
        if (!isDesktop() || isPinned()) return;
        isMouseInSidebar = true;
        if (openTimeout) {
          clearTimeout(openTimeout);
          openTimeout = null;
        }
        if (closeTimeout) {
          clearTimeout(closeTimeout);
          closeTimeout = null;
        }
        openDesktopSidebar();
      });
      sidebar.addEventListener('mouseleave', function() {
        isMouseInSidebar = false;
        if (isDesktop() && !isPinned()) closeDesktopSidebar(260);
      });

      // Keyboard Focus Trap & Accessibility
      sidebar.addEventListener('focusin', function() {
        if (!isDesktop() || isPinned()) return;
        isFocusInSidebar = true;
        openDesktopSidebar();
      });
      sidebar.addEventListener('focusout', function(e) {
        if (!sidebar.contains(e.relatedTarget)) {
          isFocusInSidebar = false;
          if (isDesktop() && !isPinned()) closeDesktopSidebar(260);
        }
      });

      // Intercept internal navigation clicks inside the sidebar to preserve open state on the next page
      const navLinks = sidebar.querySelectorAll('a[href]');
      navLinks.forEach(link => {
        link.addEventListener('click', function() {
          const href = link.getAttribute('href');
          if (!href || href === '#' || href.startsWith('javascript:')) return;
          if (link.target && link.target !== '_self') return;
          if (href.includes('logout')) return;

          if (isDesktop() && !isPinned()) {
            sessionStorage.setItem('whms_sidebar_navigating', 'true');
          }
        });
      });
    }

    // Handle persistent sidebar open state across page navigation on desktop
    const wasNavigating = sessionStorage.getItem('whms_sidebar_navigating') === 'true';
    if (wasNavigating) {
      sessionStorage.removeItem('whms_sidebar_navigating');
      if (isDesktop() && !isPinned() && sidebar) {
        isMouseInSidebar = true;
        sidebar.classList.remove('-translate-x-full');
        sidebar.classList.add('translate-x-0');
        document.documentElement.classList.remove('sidebar-nav-open');

        // Check if cursor actually loaded outside the sidebar (e.g. mouse moved away)
        const checkInitialCursor = function(e) {
          document.removeEventListener('mousemove', checkInitialCursor);
          if (!sidebar.contains(e.target) && !isPinned()) {
            isMouseInSidebar = false;
            closeDesktopSidebar(260);
          }
        };
        document.addEventListener('mousemove', checkInitialCursor, { once: true });
      } else {
        document.documentElement.classList.remove('sidebar-nav-open');
      }
    }

    // Close on click outside when unpinned on desktop
    document.addEventListener('click', function(e) {
      if (!isDesktop() || isPinned()) return;
      if (!sidebar) return;
      if (!sidebar.contains(e.target) && 
          (!triggerTab || !triggerTab.contains(e.target)) && 
          (!triggerZone || !triggerZone.contains(e.target))) {
        closeDesktopSidebar(0);
      }
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        if (window.innerWidth < 1024) {
          toggleSidebar(false);
        } else if (!isPinned()) {
          closeDesktopSidebar(0);
        }
      }
    });

    // Window resize handler
    window.addEventListener('resize', function() {
      if (isDesktop()) {
        const backdrop = document.getElementById('sidebarBackdrop');
        if (backdrop) backdrop.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        if (localStorage.getItem('whms_sidebar_pinned') === 'true') {
          document.documentElement.classList.add('sidebar-pinned');
        }
      } else {
        document.documentElement.classList.remove('sidebar-pinned');
      }
      syncPinIconState();
    });
  });
</script>
