<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Admin — Portal Satwa Liar')</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --sidebar-bg: #ffffff;
      --sidebar-w:  264px;
      --topbar-h:   64px;
      --bg:         #eaf6f8;
      --surface:    #ffffff;
      --border:     #d0e8ef;
      --text:       #1a202c;
      --muted:      #718096;
      --accent:     #00A9C1;
      --accent-dark:#007fa3;
      --font:       'DM Sans', sans-serif;
      --radius:     16px;
    }

    body {
      font-family: var(--font);
      background: linear-gradient(160deg, #eaf6f8 0%, #f0f9fb 40%, #e8f4f0 100%);
      color: var(--text);
      min-height: 100vh;
    }

    /* ── OVERLAY ── */
    .overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:99; }
    .overlay.show { display:block; }

    /* ── TOPBAR mobile ── */
    .topbar { display:none; position:fixed; top:0; left:0; right:0; height:var(--topbar-h); background:linear-gradient(135deg,#00A9C1,#007fa3); align-items:center; justify-content:space-between; padding:0 20px; z-index:98; }
    .topbar-brand { display:flex; align-items:center; gap:10px; color:#fff; font-weight:600; font-size:14px; }
    .hamburger { width:42px; height:42px; background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.3); border-radius:10px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:5px; cursor:pointer; padding:0; }
    .hamburger span { display:block; width:18px; height:2px; background:#fff; border-radius:2px; transition:transform 0.25s,opacity 0.25s; }
    .hamburger.open span:nth-child(1) { transform:translateY(7px) rotate(45deg); }
    .hamburger.open span:nth-child(2) { opacity:0; }
    .hamburger.open span:nth-child(3) { transform:translateY(-7px) rotate(-45deg); }

    /* ── SIDEBAR ── */
    .sidebar { position:fixed; left:0; top:0; bottom:0; width:var(--sidebar-w); background:rgba(255,255,255,0.85); backdrop-filter:blur(24px); display:flex; flex-direction:column; z-index:100; border-right:1px solid rgba(255,255,255,0.6); box-shadow:6px 0 32px rgba(0,0,0,.04); }

    /* ── MAIN ── */
    .main { margin-left:var(--sidebar-w); padding:36px 44px; min-height:100vh; }

    /* ── ALERT ── */
    .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; display: flex; align-items: center; justify-content: space-between; }
    .alert-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .alert-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

    @media (max-width: 768px) {
      .topbar { display: flex; }
      .sidebar { transform: translateX(-100%); transition: transform 0.25s ease; }
      .sidebar.open { transform: translateX(0); }
      .main { margin-left: 0; padding: calc(var(--topbar-h) + 20px) 16px 30px; }
    }
  </style>
  @yield('styles')
</head>
<body>
  <!-- Overlay mobile -->
  <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

  <!-- Topbar mobile -->
  <div class="topbar">
    <div class="topbar-brand">
      <img src="{{ asset('images/logo_login.png') }}" alt="Logo" height="28" style="object-fit: contain;">
      <span>Portal Satwa Liar</span>
    </div>
    <button class="hamburger" id="hamburgerBtn" onclick="toggleSidebar()" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
  </div>

  <!-- Sidebar -->
  @include('partials.sidebar', ['activePage' => $activePage ?? 'dashboard'])

  <!-- Main Content -->
  <main class="main">
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

  <script>
    function toggleSidebar() {
      const sb = document.querySelector('.sidebar');
      const ov = document.getElementById('overlay');
      const hb = document.getElementById('hamburgerBtn');
      sb.classList.toggle('open');
      ov.classList.toggle('show');
      hb.classList.toggle('open');
    }
  </script>
  @yield('scripts')
</body>
</html>
