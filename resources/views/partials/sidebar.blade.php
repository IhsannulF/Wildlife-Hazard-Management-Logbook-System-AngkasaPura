@php
  $activePage = $activePage ?? 'dashboard';
  $user = auth()->user();
  $namaUser = $user ? ($user->namalengkap ?: $user->username) : 'Admin';
  $jabatan  = $user ? ($user->jabatan ?: ucfirst($user->role)) : 'Administrator';
  $inisial  = strtoupper(substr($namaUser, 0, 2));
@endphp
<aside class="sidebar bg-white/90 backdrop-blur-2xl border-r border-slate-200/80 shadow-xs flex flex-col z-50">
  <!-- Brand Logo & Header -->
  <div class="p-6 border-b border-slate-100 flex flex-col gap-4 bg-gradient-to-b from-teal-50/40 to-transparent">
    <!-- Logo InJourney (Bigger & Clearer) -->
    <div class="flex items-center justify-start py-1">
      <img src="{{ asset('images/logo_login.png') }}" alt="InJourney Airports" class="h-11 sm:h-12 w-auto object-contain">
    </div>

    <!-- User Profile Card -->
    <div class="flex items-center gap-3.5 p-2.5 rounded-2xl bg-white/90 border border-slate-200/70 shadow-2xs">
      <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-[#00A9C1] to-[#00C4DF] text-white flex items-center justify-center font-extrabold text-sm shadow-sm shadow-cyan-500/20 shrink-0">
        {{ $inisial }}
      </div>
      <div class="overflow-hidden">
        <div class="font-bold text-sm text-slate-800 truncate leading-tight">{{ $namaUser }}</div>
        <div class="text-[11px] text-slate-400 font-semibold truncate mt-0.5">{{ $jabatan }}</div>
      </div>
    </div>
  </div>

  <!-- Navigation Menu (Text Only, Tanpa Emote/Ikon) -->
  <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-3 rounded-2xl text-sm font-bold tracking-wide transition-all duration-200 {{ $activePage === 'dashboard' ? 'bg-gradient-to-r from-teal-500/15 to-teal-500/5 text-teal-700 border-l-4 border-teal-500 shadow-2xs' : 'text-slate-600 hover:bg-slate-50/80 hover:text-slate-900' }}">
      Dashboard
    </a>

    <a href="{{ route('admin.statistik') }}" class="block px-4 py-3 rounded-2xl text-sm font-bold tracking-wide transition-all duration-200 {{ $activePage === 'statistic' ? 'bg-gradient-to-r from-teal-500/15 to-teal-500/5 text-teal-700 border-l-4 border-teal-500 shadow-2xs' : 'text-slate-600 hover:bg-slate-50/80 hover:text-slate-900' }}">
      Statistic
    </a>

    <a href="{{ route('admin.manajemen') }}" class="block px-4 py-3 rounded-2xl text-sm font-bold tracking-wide transition-all duration-200 {{ $activePage === 'laporan' || $activePage === 'manajemen' ? 'bg-gradient-to-r from-teal-500/15 to-teal-500/5 text-teal-700 border-l-4 border-teal-500 shadow-2xs' : 'text-slate-600 hover:bg-slate-50/80 hover:text-slate-900' }}">
      Manage Form
    </a>
  </nav>

  <div class="flex-1"></div>

  <!-- Logout Button -->
  <div class="p-4 border-t border-slate-100">
    <a href="{{ route('logout') }}" class="block text-center px-4 py-3 rounded-2xl text-sm font-bold text-rose-600 hover:bg-rose-50 transition-all duration-150 cursor-pointer" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
      Logout
    </a>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
      @csrf
    </form>
  </div>
</aside>
