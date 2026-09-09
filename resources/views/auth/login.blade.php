<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — Portal Satwa Liar</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans min-h-screen flex flex-col items-center justify-center p-4 relative bg-cover bg-center bg-no-out bg-fixed" style="background-image: linear-gradient(180deg, rgba(220,235,245,0.7) 0%, rgba(180,215,235,0.8) 100%), url('{{ asset('images/bg_login.jpeg') }}');">

  <!-- Logo Area -->
  <div class="mb-6 flex flex-col items-center">
    <img src="{{ asset('images/logo_login.png') }}" alt="InJourney Airports" class="w-44 sm:w-52 h-auto object-contain drop-shadow-sm">
  </div>

  <!-- Glass Card -->
  <div class="w-full max-w-md bg-white/90 backdrop-blur-xl border border-white/70 rounded-3xl p-8 sm:p-10 shadow-2xl shadow-slate-900/10 transition-all">
    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 text-center tracking-tight mb-1.5">Portal Satwa Liar</h1>
    <p class="text-xs sm:text-sm text-slate-500 text-center mb-6">Silakan login menggunakan akun dinas Anda</p>

    @if($errors->any())
      <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl text-xs font-semibold mb-5 flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <span>{{ $errors->first() }}</span>
      </div>
    @endif

    <form action="{{ route('login') }}" method="POST" class="space-y-4">
      @csrf
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2" for="username">Username</label>
        <input type="text" id="username" name="username" class="w-full px-4 py-3 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-white/90 text-slate-800 transition-all placeholder:text-slate-400" value="{{ old('username') }}" placeholder="cth: amc.divisi" required autofocus>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2" for="password">Password</label>
        <input type="password" id="password" name="password" class="w-full px-4 py-3 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-white/90 text-slate-800 transition-all placeholder:text-slate-400" placeholder="Masukkan password" required>
      </div>

      <button type="submit" class="w-full py-3.5 px-4 bg-gradient-to-r from-[#00A9C1] to-[#007fa3] hover:from-[#007fa3] hover:to-[#005f7a] text-white font-bold text-sm sm:text-base rounded-xl shadow-lg shadow-cyan-500/25 active:scale-[0.99] transition-all duration-200 mt-2 cursor-pointer">
        Masuk ke Sistem
      </button>
    </form>

    <div class="mt-8 pt-6 border-t border-slate-200/60 text-center text-xs text-slate-400 leading-relaxed">
      Logbook Bahaya Satwa Liar (Wildlife Hazard Management)<br>
      <span class="font-semibold text-slate-500">PT Aviasi Pariwisata Indonesia</span>
    </div>
  </div>

</body>
</html>
