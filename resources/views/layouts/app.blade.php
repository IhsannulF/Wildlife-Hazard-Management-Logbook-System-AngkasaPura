<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Portal Satwa Liar — InJourney Airports')</title>
  <!-- Favicon InJourney Airports -->
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
  <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
  <!-- PWA Manifest & Meta Tags -->
  <link rel="manifest" href="{{ asset('manifest.json') }}">
  <meta name="theme-color" content="#00A9C1">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="apple-mobile-web-app-title" content="WHMS InJourney">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
  <!-- Tailwind CSS CDN with configuration -->
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            "surface-container": "#e5eeff",
            "on-secondary": "#ffffff",
            "on-secondary-container": "#5c647a",
            "error-container": "#ffdad6",
            "tertiary": "#006780",
            "slate-navy": "#0F172A",
            "primary-container": "#00a3b4",
            "on-surface": "#0b1c30",
            "surface-tint": "#006874",
            "tertiary-fixed": "#b7eaff",
            "injourney-teal": "#00A9C1",
            "injourney-dark": "#007fa3",
          }
        }
      }
    };
  </script>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @yield('styles')
</head>
<body>
  @yield('content')
  <!-- Floating PWA Offline Sync Widget -->
  <div id="whms-offline-sync-widget" class="hidden fixed bottom-6 left-6 z-50">
    <div class="bg-white/95 backdrop-blur-md border border-amber-200 shadow-xl rounded-2xl p-4 flex items-center gap-3.5 max-w-sm">
      <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
        <span class="material-symbols-outlined text-2xl">cloud_off</span>
      </div>
      <div class="min-w-0 flex-1">
        <div class="text-xs font-bold text-slate-800">Draf Offline Tersimpan</div>
        <div class="text-[11px] text-slate-500 truncate"><span class="whms-draft-count font-bold text-amber-600">0</span> laporan menunggu sinkronisasi</div>
      </div>
      <button type="button" id="whms-sync-btn" onclick="WHMS_OFFLINE.syncAllDrafts()" class="px-3 py-2 rounded-xl bg-injourney-teal hover:bg-injourney-dark text-white text-xs font-bold flex items-center gap-1.5 shadow-sm transition-all shrink-0 cursor-pointer">
        <span class="material-symbols-outlined text-sm">cloud_sync</span>
        Sinkronkan
      </button>
    </div>
  </div>

  <script src="{{ asset('js/whms-offline.js') }}"></script>
  @yield('scripts')
</body>
</html>
