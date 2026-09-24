@php
  $currUser = auth()->user() ?? ($user ?? null);
  $isAdmin = $currUser ? ($currUser->role === 'admin') : false;
  $userName = $currUser ? ($currUser->namalengkap ?: $currUser->username) : 'Petugas Sisi Udara';
  $userRole = $currUser ? ($currUser->jabatan ?: ($isAdmin ? 'Safety Manager / Admin' : 'AMC Airside Staff')) : 'Airside Staff';
  $initials = strtoupper(substr($userName, 0, 2));
  $homeRoute = $isAdmin ? route('admin.dashboard') : route('user.dashboard');
  $currentPage = $activePage ?? '';
@endphp

<!-- TOP APP BAR (Shared Unified Component) -->
<header class="bg-white border-b border-slate-200 shadow-xs docked full-width top-0 sticky z-40">
  <div class="w-full px-4 sm:px-6 lg:px-8 flex items-center justify-between h-14 sm:h-15">
    
    <!-- Brand & Title (Smaller, Single-Line) -->
    <div class="flex items-center gap-3">
      <a class="flex items-center gap-2 sm:gap-2.5 group whitespace-nowrap" href="{{ $homeRoute }}">
        <img alt="InJourney Airports" class="h-7 sm:h-8 w-auto object-contain" src="{{ asset('images/logo_login.png') }}"/>
        <div class="h-4 w-px bg-slate-200 mx-0.5 hidden sm:block"></div>
        <div class="flex items-center gap-2 whitespace-nowrap">
          <span class="text-xs sm:text-sm font-bold text-slate-800 tracking-tight whitespace-nowrap">
            InJourney Airports <span class="font-normal text-slate-300 mx-1">|</span> <span class="text-slate-600 font-semibold text-xs sm:text-xs">Wildlife Hazard Management</span>
          </span>
          <span class="hidden xl:inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-200">
            ICAO Annex 14
          </span>
        </div>
      </a>
    </div>

    <!-- Center Navigation Links (Compact text, Single-Line) -->
    <nav class="hidden lg:flex items-center gap-4 xl:gap-6 h-full text-xs font-semibold whitespace-nowrap">
      <a class="py-4 sm:py-4.5 transition-colors {{ ($currentPage === 'dashboard' || request()->routeIs('admin.dashboard') || request()->routeIs('user.dashboard')) && request()->get('status') !== 'belum' ? 'text-[#00A9C1] border-b-2 border-[#00A9C1] font-bold' : 'text-slate-500 hover:text-slate-900' }}" href="{{ $homeRoute }}">
        Dashboard
      </a>
      @if($isAdmin)
        <a class="py-4 sm:py-4.5 transition-colors {{ ($currentPage === 'statistic' || request()->routeIs('admin.statistik')) ? 'text-[#00A9C1] border-b-2 border-[#00A9C1] font-bold' : 'text-slate-500 hover:text-slate-900' }}" href="{{ route('admin.statistik') }}">
          Peta Sebaran Grid
        </a>
        <a class="py-4 sm:py-4.5 transition-colors {{ ($currentPage === 'verifikasi' || request()->get('status') === 'belum') ? 'text-[#00A9C1] border-b-2 border-[#00A9C1] font-bold' : 'text-slate-500 hover:text-slate-900' }}" href="{{ route('admin.dashboard', ['status' => 'belum']) }}">
          Verifikasi BA
        </a>
        <a class="py-4 sm:py-4.5 transition-colors {{ ($currentPage === 'manajemen' || request()->routeIs('admin.manajemen')) ? 'text-[#00A9C1] border-b-2 border-[#00A9C1] font-bold' : 'text-slate-500 hover:text-slate-900' }}" href="{{ route('admin.manajemen') }}">
          Katalog Satwa
        </a>
      @endif
    </nav>

    <!-- Trailing Action Cluster (Compact, Single-Line) -->
    <div class="flex items-center gap-2 sm:gap-2.5">
      @if($isAdmin)
        <a href="{{ route('admin.export.excel') }}" class="hidden sm:inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 text-xs font-semibold transition-colors shadow-2xs whitespace-nowrap" title="Unduh Spreadsheet Format DKPPU">
          <span class="material-symbols-outlined text-slate-500 text-sm">file_download</span>
          <span>Export Excel</span>
        </a>
      @endif

      <a href="{{ route('laporan.create') }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-[#00A9C1] hover:bg-[#007fa3] text-white text-xs font-semibold shadow-xs transition-all active:scale-[0.98] whitespace-nowrap">
        <span class="material-symbols-outlined text-sm">add</span>
        <span>Buat Laporan</span>
      </a>

      <div class="h-5 w-px bg-slate-200 mx-0.5 hidden sm:block"></div>

      <!-- Profile Avatar & Info (Single-line / Compact) -->
      <div class="flex items-center gap-2 pl-0.5">
        <div class="w-7 h-7 rounded-full bg-cyan-100 text-[#007fa3] border border-cyan-200 flex items-center justify-center font-bold text-xs shrink-0">
          {{ $initials }}
        </div>
        <div class="hidden xl:flex flex-col text-left leading-none">
          <span class="text-xs font-semibold text-slate-800 truncate max-w-[120px]">{{ $userName }}</span>
          <span class="text-[10px] text-slate-400 mt-0.5 truncate max-w-[120px]">{{ $userRole }}</span>
        </div>

        <!-- Logout Form -->
        <form action="{{ route('logout') }}" method="POST" class="inline m-0">
          @csrf
          <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors ml-0.5 cursor-pointer" title="Keluar / Logout">
            <span class="material-symbols-outlined text-lg leading-none">logout</span>
          </button>
        </form>
      </div>

    </div>
  </div>
</header>
