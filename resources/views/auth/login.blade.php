<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — Portal Satwa Liar</title>
  <!-- Favicon InJourney Airports -->
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
  <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
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
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2" for="username">Username Departemen</label>
        <input type="text" id="username" name="username" class="w-full px-4 py-3 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-white/90 text-slate-800 transition-all placeholder:text-slate-400" value="{{ old('username') }}" placeholder="cth: amc, pk, safety, security, admin" required autofocus>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2" for="password">Password</label>
        <div class="relative">
          <input type="password" id="password" name="password" class="w-full pl-4 pr-11 py-3 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-white/90 text-slate-800 transition-all placeholder:text-slate-400" placeholder="Masukkan password" required>
          <button type="button" id="togglePassword" aria-label="Lihat password" title="Lihat password" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer transition-colors">
            <svg id="eyeOpenIcon" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
            <svg id="eyeSlashIcon" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
            </svg>
          </button>
        </div>
      </div>

      <div class="flex items-center justify-between pt-0.5">
        <label for="remember" class="inline-flex items-center gap-2.5 cursor-pointer select-none group">
          <input type="checkbox" id="remember" name="remember" value="1" {{ old('remember') ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-[#00A9C1] focus:ring-2 focus:ring-teal-500 focus:ring-offset-0 cursor-pointer accent-[#00A9C1]">
          <span class="text-xs sm:text-sm font-medium text-slate-600 group-hover:text-slate-800 transition-colors">Ingat saya</span>
        </label>
      </div>

      <button type="submit" class="w-full py-3.5 px-4 bg-gradient-to-r from-[#00A9C1] to-[#007fa3] hover:from-[#007fa3] hover:to-[#005f7a] text-white font-bold text-sm sm:text-base rounded-xl shadow-lg shadow-cyan-500/25 active:scale-[0.99] transition-all duration-200 mt-2 cursor-pointer">
        Masuk ke Sistem
      </button>
    </form>

    <div class="mt-6 pt-5 border-t border-slate-200/60 text-center text-xs text-slate-400 leading-relaxed">
      Logbook Bahaya Satwa Liar (Wildlife Hazard Management)<br>
      <span class="font-semibold text-slate-500">PT Aviasi Pariwisata Indonesia</span>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const togglePasswordBtn = document.getElementById('togglePassword');
      const passwordInput = document.getElementById('password');
      const eyeOpenIcon = document.getElementById('eyeOpenIcon');
      const eyeSlashIcon = document.getElementById('eyeSlashIcon');

      if (togglePasswordBtn && passwordInput && eyeOpenIcon && eyeSlashIcon) {
        togglePasswordBtn.addEventListener('click', function () {
          const isPassword = passwordInput.getAttribute('type') === 'password';
          passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
          eyeOpenIcon.classList.toggle('hidden', isPassword);
          eyeSlashIcon.classList.toggle('hidden', !isPassword);
          const label = isPassword ? 'Sembunyikan password' : 'Lihat password';
          togglePasswordBtn.setAttribute('aria-label', label);
          togglePasswordBtn.setAttribute('title', label);
        });
      }
    });
  </script>
</body>
</html>
