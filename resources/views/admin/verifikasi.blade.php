@php
  $currUser = auth()->user() ?? ($user ?? null);
  $userName = $currUser ? ($currUser->namalengkap ?: $currUser->username) : 'Petugas Sisi Udara';
  $userRole = $currUser ? ($currUser->jabatan ?: 'AMC Airside Staff') : 'Airside Staff';
@endphp
<!DOCTYPE html>
<html class="h-full bg-canvas-light text-on-surface" lang="id">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Verifikasi Berita Acara (BA) - InJourney Airports</title>

  <!-- Favicon InJourney Airports -->
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
  <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

  <!-- Google Fonts: DM Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
  <!-- Google Material Symbols Outlined -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

  <!-- Tailwind CSS CDN with configuration -->
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          "colors": {
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
            "injourney-teal": "#00A3B4",
            "resolved-emerald": "#10B981",
            "canvas-light": "#F8FAFC",
            "surface": "#f8f9ff",
            "on-primary-fixed-variant": "#004f58",
            "secondary-container": "#dae2fd",
            "warning-rose": "#EF4444",
            "inverse-on-surface": "#eaf1ff",
            "surface-variant": "#d3e4fe",
            "on-primary-container": "#003238",
            "on-error": "#ffffff",
            "on-tertiary": "#ffffff",
            "on-primary": "#ffffff",
            "on-background": "#0b1c30",
            "surface-container-highest": "#d3e4fe",
            "on-tertiary-container": "#00313f",
            "inverse-surface": "#213145",
            "card-bg": "#FFFFFF",
            "surface-bright": "#f8f9ff",
            "border-subtle": "#E2E8F0",
            "outline": "#6d797c",
            "slate-surface": "#1E293B",
            "background": "#f8f9ff",
            "surface-container-high": "#dce9ff",
            "outline-variant": "#bcc9cb",
            "error": "#ba1a1a",
            "injourney-dark-teal": "#0891B2",
            "tertiary-fixed-dim": "#6cd3f7",
            "primary-fixed-dim": "#5dd7e9",
            "on-tertiary-fixed-variant": "#004e61",
            "on-error-container": "#93000a",
            "surface-container-low": "#eff4ff",
            "on-primary-fixed": "#001f24",
            "on-secondary-fixed-variant": "#3f465c",
            "secondary-fixed-dim": "#bec6e0",
            "surface-dim": "#cbdbf5",
            "runway-grid": "#334155",
            "on-surface-variant": "#3d494b",
            "on-tertiary-fixed": "#001f28",
            "primary": "#006874",
            "primary-fixed": "#97f0ff",
            "surface-container-lowest": "#ffffff",
            "secondary": "#565e74",
            "tertiary-container": "#2ca0c1",
            "on-secondary-fixed": "#131b2e",
            "secondary-fixed": "#dae2fd",
            "inverse-primary": "#5dd7e9",
            "alert-amber": "#F59E0B"
          },
          "borderRadius": {
            "DEFAULT": "0.25rem",
            "lg": "0.5rem",
            "xl": "0.75rem",
            "full": "9999px"
          },
          "spacing": {
            "space-xs": "0.25rem",
            "margin-mobile": "1rem",
            "space-md": "1rem",
            "gutter-mobile": "0.75rem",
            "margin": "2rem",
            "gutter": "1.25rem",
            "space-sm": "0.5rem",
            "space-lg": "1.5rem",
            "space-xl": "2rem"
          },
          "fontFamily": {
            "headline-md": ["DM Sans", "sans-serif"],
            "headline-xl": ["DM Sans", "sans-serif"],
            "headline-lg": ["DM Sans", "sans-serif"],
            "headline-sm": ["DM Sans", "sans-serif"],
            "label-md": ["DM Sans", "sans-serif"],
            "code-coordinate": ["DM Sans", "monospace"],
            "headline-xl-mobile": ["DM Sans", "sans-serif"],
            "body-md": ["DM Sans", "sans-serif"],
            "body-sm": ["DM Sans", "sans-serif"],
            "label-lg": ["DM Sans", "sans-serif"],
            "body-lg": ["DM Sans", "sans-serif"],
            "label-sm": ["DM Sans", "sans-serif"]
          },
          "fontSize": {
            "headline-md": ["18px", { "lineHeight": "24px", "fontWeight": "600" }],
            "headline-xl": ["24px", { "lineHeight": "30px", "letterSpacing": "-0.01em", "fontWeight": "700" }],
            "headline-lg": ["20px", { "lineHeight": "26px", "letterSpacing": "-0.01em", "fontWeight": "700" }],
            "headline-sm": ["15px", { "lineHeight": "22px", "fontWeight": "600" }],
            "label-md": ["11px", { "lineHeight": "15px", "letterSpacing": "0.02em", "fontWeight": "600" }],
            "code-coordinate": ["12px", { "lineHeight": "16px", "letterSpacing": "0.04em", "fontWeight": "700" }],
            "headline-xl-mobile": ["20px", { "lineHeight": "26px", "letterSpacing": "-0.01em", "fontWeight": "700" }],
            "body-md": ["13px", { "lineHeight": "19px", "fontWeight": "400" }],
            "body-sm": ["11px", { "lineHeight": "15px", "fontWeight": "400" }],
            "label-lg": ["13px", { "lineHeight": "18px", "letterSpacing": "0.01em", "fontWeight": "600" }],
            "body-lg": ["15px", { "lineHeight": "22px", "fontWeight": "400" }],
            "label-sm": ["10px", { "lineHeight": "14px", "letterSpacing": "0.04em", "fontWeight": "700" }]
          }
        },
      },
    }
  </script>
  <style>
    .material-symbols-outlined {
      font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
      display: inline-block;
      vertical-align: middle;
      line-height: 1;
    }
    .material-symbols-fill {
      font-variation-settings: 'FILL' 1, 'wght' 500, 'GRAD' 0, 'opsz' 24;
      display: inline-block;
      vertical-align: middle;
      line-height: 1;
    }
    /* Custom subtle scrollbar */
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
<body class="h-full font-body-md text-on-surface antialiased bg-canvas-light selection:bg-injourney-teal selection:text-white">

  <!-- SIDEBAR NAVIGATION -->
  @include('partials.navbar', ['activePage' => 'verifikasi'])

  <!-- MAIN VIEWPORT CONTAINER -->
  <main class="max-w-[1680px] mx-auto px-4 sm:px-6 lg:px-space-xl py-space-lg space-y-space-lg">
    
    <!-- 1. Page Hero Title & Bento Stat Cards -->
    @include('admin.verifikasi.hero-header')

    <!-- 2. Quick Filters, Search & Airside Grid Tag Controls -->
    @include('admin.verifikasi.table-filters')

    <!-- 3. Master Data Logbook Table Panel -->
    @include('admin.verifikasi.table-logbook')

  </main>

  <!-- 4. Detail Modal (2-Halaman dengan Signature Pad & Grid Schematic) -->
  @include('admin.verifikasi.modal-detail')

  <!-- 5. Toast Notification -->
  @include('admin.verifikasi.toast')

  <!-- 6. Core Scripts (Toast & Refresh) -->
  @include('admin.verifikasi.scripts-core')

  <!-- 7. Verifikasi BA Specific Scripts -->
  @include('admin.verifikasi.scripts-verifikasi')

</body>
</html>
