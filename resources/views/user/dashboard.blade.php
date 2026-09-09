@extends('layouts.app')

@section('title', 'Dashboard Pegawai — Portal Satwa Liar')

@section('content')
<div class="min-h-screen flex flex-col bg-cover bg-center bg-no-repeat bg-fixed" style="background-image: linear-gradient(180deg, rgba(220,235,245,0.7) 0%, rgba(180,215,235,0.85) 100%), url('{{ asset('images/bg_login.jpeg') }}');">

  <!-- Top Color Gradient Bar -->
  <div class="h-1.5 w-full bg-gradient-to-r from-[#00A9C1] via-[#4FADC9] via-[#88B146] via-[#F0B14B] to-[#D94F4F]"></div>

  <!-- Topbar -->
  <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-slate-200/80 px-6 sm:px-10 h-16 flex items-center justify-between shadow-xs">
    <div class="flex items-center gap-3">
      <img src="{{ asset('images/logo_login.png') }}" alt="InJourney Airports" class="h-8 sm:h-9 object-contain">
    </div>
    <div class="flex items-center gap-3">
      <div class="flex items-center gap-2 bg-teal-50/80 border border-teal-200/60 rounded-full py-1 px-3 sm:px-4">
        <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-[#00A9C1] to-[#00C4DF] text-white text-xs font-bold flex items-center justify-center shadow-xs">
          {{ strtoupper(substr($user->namalengkap ?: $user->username, 0, 2)) }}
        </div>
        <span class="text-xs sm:text-sm font-semibold text-slate-700">{{ $user->namalengkap ?: $user->username }}</span>
      </div>
      <form action="{{ route('logout') }}" method="POST" class="m-0">
        @csrf
        <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 text-xs sm:text-sm font-semibold transition-all cursor-pointer">
          Keluar
        </button>
      </form>
    </div>
  </header>

  <!-- Main Hero Content -->
  <main class="flex-1 flex items-center justify-center p-6 sm:p-10">
    <div class="w-full max-w-xl bg-white/90 backdrop-blur-xl border border-white/70 rounded-3xl p-8 sm:p-12 text-center shadow-2xl shadow-slate-900/10">
      @if(session('sukses'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-2xl mb-6 text-sm font-semibold flex items-center justify-center gap-2">
          <svg class="w-5 h-5 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
          </svg>
          <span>{{ session('sukses') }}</span>
        </div>
      @endif

      <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-extrabold uppercase tracking-wider bg-teal-50 text-teal-700 border border-teal-200/60 mb-5">
        {{ $user->namalengkap ?: 'Petugas Lapangan' }}
      </span>

      <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mb-4">
        Selamat Datang di Portal Satwa Liar
      </h1>

      <p class="text-slate-600 text-sm sm:text-base leading-relaxed mb-8">
        Sistem pencatatan logbook digital pemantauan dan pengendalian satwa liar (<em>Wildlife Hazard Management</em>) area sisi udara bandara. Laporkan segera setiap temuan satwa liar untuk menjaga keselamatan penerbangan.
      </p>

      <a href="{{ route('laporan.create') }}" class="inline-flex items-center justify-center gap-2.5 px-8 py-4 bg-gradient-to-r from-[#00A9C1] to-[#007fa3] hover:from-[#007fa3] hover:to-[#005f7a] text-white font-bold text-sm sm:text-base rounded-2xl shadow-xl shadow-cyan-500/30 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 cursor-pointer">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 5v14M5 12h14"/>
        </svg>
        <span>Buat Laporan Baru</span>
      </a>
    </div>
  </main>

  <!-- Footer -->
  <footer class="p-4 text-center text-xs text-slate-500 font-medium">
    &copy; {{ date('Y') }} PT Angkasa Pura (Persero) — Wildlife Hazard Management System
  </footer>

</div>
@endsection
