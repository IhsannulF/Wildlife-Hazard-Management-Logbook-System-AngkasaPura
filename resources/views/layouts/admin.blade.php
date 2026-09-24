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

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
  @vite(['resources/css/app.css', 'resources/js/app.js'])
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
      min-height: calc(100vh - 56px);
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
  <!-- Shared Unified Top Navigation Bar -->
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

  @yield('scripts')
</body>
</html>
