<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mode Offline — InJourney Airports</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
  <link rel="manifest" href="{{ asset('manifest.json') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,100..1000;1,100..1000&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
  <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            "slate-navy": "#0F172A",
            "injourney-teal": "#00A9C1",
            "injourney-dark": "#007fa3",
          }
        }
      }
    };
  </script>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex items-center justify-center p-4" style="font-family: 'DM Sans', sans-serif;">
  <div class="max-w-md w-full bg-white rounded-2xl shadow-xl border border-slate-100 p-8 text-center relative overflow-hidden">
    <!-- Header Accent Bar -->
    <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600"></div>

    <!-- Status Icon -->
    <div class="w-20 h-20 bg-amber-50 rounded-2xl border border-amber-200 flex items-center justify-center mx-auto mb-6 shadow-sm">
      <span class="material-symbols-outlined text-amber-500 text-5xl">wifi_off</span>
    </div>

    <!-- Badge -->
    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100/80 border border-amber-200 text-amber-800 text-xs font-semibold uppercase tracking-wider mb-3">
      <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
      Airside Offline Mode
    </div>

    <!-- Title & Explanation -->
    <h1 class="text-2xl font-bold text-slate-900 mb-2">Koneksi Internet Terputus</h1>
    <p class="text-sm text-slate-500 leading-relaxed mb-6">
      Perangkat Anda sedang berada di area tanpa sinyal (<em class="text-slate-600 font-medium">dead-zone</em> sisi udara). 
      Anda tetap dapat membuat laporan satwa liar; draf dan foto akan diamankan di penyimpanan lokal perangkat.
    </p>

    <!-- Actions -->
    <div class="space-y-3">
      <a href="{{ url('/laporan/create') }}" class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-injourney-teal hover:bg-injourney-dark text-white font-semibold text-sm shadow-md shadow-injourney-teal/20 transition-all">
        <span class="material-symbols-outlined text-lg">edit_document</span>
        Buka Form Pelaporan Offline
      </a>

      <button type="button" onclick="window.location.reload()" class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-sm transition-all">
        <span class="material-symbols-outlined text-lg">refresh</span>
        Coba Muat Ulang Halaman
      </button>
    </div>

    <!-- Status auto-reload listener -->
    <div id="onlineNotice" class="hidden mt-6 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center justify-center gap-2">
      <span class="material-symbols-outlined text-emerald-600 text-sm">wifi</span>
      Sinyal terdeteksi! Memuat ulang sistem...
    </div>

    <!-- Footer meta -->
    <div class="mt-8 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
      <span>WHMS InJourney Airports</span>
      <span>PWA Offline Engine v2.0</span>
    </div>
  </div>

  <script>
    window.addEventListener('online', () => {
      const notice = document.getElementById('onlineNotice');
      if (notice) notice.classList.remove('hidden');
      setTimeout(() => {
        window.location.reload();
      }, 1200);
    });
  </script>
</body>
</html>
