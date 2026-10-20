<!DOCTYPE html>
<html lang="id" class="h-full bg-canvas-light text-on-surface">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Manajemen User &amp; Konfigurasi Sistem - InJourney Airports</title>

  <!-- Favicon InJourney Airports -->
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
  <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

  <!-- Google Fonts: DM Sans & Material Symbols -->
  <link href="https://fonts.googleapis.com" rel="preconnect"/>
  <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          "colors": {
            "inverse-on-surface": "#eaf1ff",
            "injourney-dark-teal": "#0891B2",
            "runway-grid": "#334155",
            "on-tertiary": "#ffffff",
            "resolved-emerald": "#10B981",
            "injourney-teal": "#00A3B4",
            "surface-container-highest": "#d3e4fe",
            "error-container": "#ffdad6",
            "on-primary-container": "#003238",
            "surface-container-high": "#dce9ff",
            "on-tertiary-fixed": "#001f28",
            "on-secondary-fixed": "#131b2e",
            "surface-dim": "#cbdbf5",
            "tertiary-container": "#2ca0c1",
            "outline": "#6d797c",
            "secondary-fixed": "#dae2fd",
            "inverse-surface": "#213145",
            "warning-rose": "#EF4444",
            "surface-tint": "#006874",
            "surface": "#f8f9ff",
            "surface-container": "#e5eeff",
            "error": "#ba1a1a",
            "on-secondary-fixed-variant": "#3f465c",
            "primary-fixed-dim": "#5dd7e9",
            "on-error": "#ffffff",
            "primary-container": "#00a3b4",
            "on-secondary-container": "#5c647a",
            "slate-navy": "#0F172A",
            "on-surface": "#0b1c30",
            "on-error-container": "#93000a",
            "inverse-primary": "#5dd7e9",
            "tertiary-fixed": "#b7eaff",
            "on-tertiary-container": "#00313f",
            "border-subtle": "#E2E8F0",
            "on-primary-fixed-variant": "#004f58",
            "background": "#f8f9ff",
            "surface-variant": "#d3e4fe",
            "tertiary-fixed-dim": "#6cd3f7",
            "canvas-light": "#F8FAFC",
            "on-background": "#0b1c30",
            "tertiary": "#006780",
            "primary-fixed": "#97f0ff",
            "on-surface-variant": "#3d494b",
            "surface-container-lowest": "#ffffff",
            "on-tertiary-fixed-variant": "#004e61",
            "outline-variant": "#bcc9cb",
            "on-secondary": "#ffffff",
            "alert-amber": "#F59E0B",
            "secondary-fixed-dim": "#bec6e0",
            "primary": "#006874",
            "on-primary-fixed": "#001f24",
            "surface-container-low": "#eff4ff",
            "slate-surface": "#1E293B",
            "secondary-container": "#dae2fd",
            "on-primary": "#ffffff",
            "surface-bright": "#f8f9ff",
            "card-bg": "#FFFFFF",
            "secondary": "#565e74"
          },
          "borderRadius": {
            "DEFAULT": "0.25rem",
            "lg": "0.5rem",
            "xl": "0.75rem",
            "full": "9999px"
          },
          "fontFamily": {
            "headline-sm": ["DM Sans"],
            "label-lg": ["DM Sans"],
            "headline-lg": ["DM Sans"],
            "label-sm": ["DM Sans"],
            "body-sm": ["DM Sans"],
            "body-lg": ["DM Sans"],
            "label-md": ["DM Sans"],
            "headline-md": ["DM Sans"],
            "headline-xl": ["DM Sans"],
            "code-coordinate": ["DM Sans"],
            "body-md": ["DM Sans"]
          }
        }
      }
    }
  </script>
  <style>
    .material-symbols-outlined {
      font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
      display: inline-block;
      vertical-align: middle;
      line-height: 1;
    }
    /* Custom scrollbar */
    ::-webkit-scrollbar {
      width: 6px;
      height: 6px;
    }
    ::-webkit-scrollbar-track {
      background: #F8FAFC;
    }
    ::-webkit-scrollbar-thumb {
      background: #CBD5E1;
      border-radius: 9999px;
    }
    ::-webkit-scrollbar-thumb:hover {
      background: #94A3B8;
    }
  </style>
</head>
<body class="min-h-full bg-canvas-light text-on-surface font-body-md antialiased selection:bg-injourney-teal selection:text-white flex flex-col">

  <!-- SIDEBAR NAVIGATION -->
  @include('partials.navbar', ['activePage' => 'users'])

  <!-- SUB-HEADER & COMMAND STRIP -->
  @include('partials.navbar-user')

  <!-- ================= MAIN OPERATIONAL CONTENT CONTAINER ================= -->
  <main class="flex-1 max-w-[1680px] w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- Flash Alert Notifications -->
    @include('admin.users.alerts')

    <!-- 1. SUMMARY KPI BENTO CARDS -->
    @include('admin.users.kpi-cards')

    <!-- 2. TAB CONTENT: USERS VS FIELDS -->
    @if($tab === 'users')
      @include('admin.users.table-users')
    @else
      @include('admin.users.table-fields')
    @endif

  </main>

  <!-- 3. MODAL TAMBAH & EDIT USER -->
  @include('admin.users.modal-user')

  <!-- 4. MODAL TAMBAH FIELD -->
  @include('admin.users.modal-field')

  <!-- 5. FOOTER STANDAR INJOURNEY -->
  @include('admin.users.footer')

  <!-- 6. JAVASCRIPT & MODAL HANDLERS -->
  @include('admin.users.scripts')

</body>
</html>
