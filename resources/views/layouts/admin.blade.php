<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Admin — InJourney Airports')</title>
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
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @include('partials.tailwind-gradient-fix')
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --font: 'DM Sans', sans-serif;
    }

    body {
      font-family: var(--font);
      background: #F8FAFC;
      color: #0b1c30;
      min-height: 100vh;
    }

    .main-container {
      max-width: 1680px;
      margin: 0 auto;
      padding: 24px 32px 48px;
      min-height: 100vh;
    }

    .alert { padding: 12px 16px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; display: flex; align-items: center; justify-content: space-between; }
    .alert-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .alert-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

    @media (max-width: 768px) {
      .main-container { padding: 16px 16px 32px; }
    }
  </style>
  @yield('styles')
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
  <!-- Shared Unified Side Navigation Bar -->
  @include('partials.navbar', ['activePage' => $activePage ?? ''])

  <!-- Main Content -->
  <main class="main-container">
    @if(session('sukses'))
      <div class="alert alert-success">
        <span>{{ session('sukses') }}</span>
        <button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;color:inherit;font-weight:bold;">&times;</button>
      </div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger">
        <span>{{ session('error') }}</span>
        <button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;color:inherit;font-weight:bold;">&times;</button>
      </div>
    @endif

    @yield('content')
  </main>

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
