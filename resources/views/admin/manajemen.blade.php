<!DOCTYPE html>
<html class="h-full bg-canvas-light" lang="id">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Katalog Master Satwa Liar Sisi Udara - InJourney Airports WHMS</title>

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
            "primary": "#006874",
            "primary-fixed": "#97f0ff",
            "alert-amber": "#F59E0B",
            "tertiary": "#006780",
            "secondary-fixed-dim": "#bec6e0",
            "secondary": "#565e74",
            "tertiary-fixed": "#b7eaff",
            "outline": "#6d797c",
            "primary-fixed-dim": "#5dd7e9",
            "on-secondary-fixed": "#131b2e",
            "injourney-teal": "#00A3B4",
            "background": "#f8f9ff",
            "tertiary-container": "#2ca0c1",
            "surface-container-high": "#dce9ff",
            "slate-navy": "#0F172A",
            "secondary-fixed": "#dae2fd",
            "runway-grid": "#334155",
            "error-container": "#ffdad6",
            "injourney-dark-teal": "#0891B2",
            "on-secondary-fixed-variant": "#3f465c",
            "on-error": "#ffffff",
            "surface-container-lowest": "#ffffff",
            "surface-bright": "#f8f9ff",
            "inverse-on-surface": "#eaf1ff",
            "border-subtle": "#E2E8F0",
            "canvas-light": "#F8FAFC",
            "surface-container": "#e5eeff",
            "error": "#ba1a1a",
            "surface": "#f8f9ff",
            "on-secondary-container": "#5c647a",
            "on-background": "#0b1c30",
            "on-tertiary-container": "#00313f",
            "resolved-emerald": "#10B981",
            "surface-container-low": "#eff4ff",
            "inverse-primary": "#5dd7e9",
            "on-primary-fixed-variant": "#004f58",
            "tertiary-fixed-dim": "#6cd3f7",
            "slate-surface": "#1E293B",
            "secondary-container": "#dae2fd",
            "inverse-surface": "#213145",
            "on-primary-fixed": "#001f24",
            "on-tertiary": "#ffffff",
            "on-primary-container": "#003238",
            "on-surface": "#0b1c30",
            "primary-container": "#00a3b4",
            "on-surface-variant": "#3d494b",
            "on-primary": "#ffffff",
            "warning-rose": "#EF4444",
            "surface-variant": "#d3e4fe",
            "card-bg": "#FFFFFF",
            "on-tertiary-fixed-variant": "#004e61",
            "on-secondary": "#ffffff",
            "outline-variant": "#bcc9cb",
            "surface-dim": "#cbdbf5",
            "surface-container-highest": "#d3e4fe",
            "on-tertiary-fixed": "#001f28",
            "surface-tint": "#006874",
            "on-error-container": "#93000a"
          },
          "borderRadius": {
            "DEFAULT": "0.25rem",
            "lg": "0.5rem",
            "xl": "0.75rem",
            "full": "9999px"
          },
          "fontFamily": {
            "label-sm": ["DM Sans"],
            "body-md": ["DM Sans"],
            "headline-sm": ["DM Sans"],
            "headline-xl-mobile": ["DM Sans"],
            "body-sm": ["DM Sans"],
            "label-md": ["DM Sans"],
            "body-lg": ["DM Sans"],
            "headline-md": ["DM Sans"],
            "code-coordinate": ["DM Sans"],
            "label-lg": ["DM Sans"],
            "headline-xl": ["DM Sans"],
            "headline-lg": ["DM Sans"]
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
    .badge-pulse {
      animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: .45; }
    }
    /* Custom scrollbar */
    ::-webkit-scrollbar {
      width: 6px;
      height: 6px;
    }
    ::-webkit-scrollbar-track {
      background: #F1F5F9;
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
  @include('partials.navbar', ['activePage' => 'manajemen'])

  <!-- TOP UNIFIED PAGE HEADER -->
  @include('partials.page-header', [
    'title' => 'Katalog Master Satwa Liar Sisi Udara',
    'subtitle' => 'Pusat pengelolaan master data spesies, tingkat risiko satwa, dan konfigurasi sistem.',
    'icon' => 'pets',
    'actions' => '
      <a href="' . route('admin.export.excel') . '" class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 sm:py-2 text-xs font-semibold rounded-lg border border-slate-200/90 bg-white text-slate-800 hover:bg-slate-50 transition-colors shadow-2xs shrink-0 whitespace-nowrap">
        <span class="material-symbols-outlined text-sm">download</span>
        <span>Export Excel</span>
      </a>
      <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 sm:py-2 text-xs font-semibold rounded-lg border border-slate-200/90 bg-white text-slate-800 hover:bg-slate-50 transition-colors shadow-2xs shrink-0 whitespace-nowrap">
        <span class="material-symbols-outlined text-sm">print</span>
        <span>Cetak PDF</span>
      </button>
      <button type="button" onclick="openModal(\'addSpeciesModal\')" class="inline-flex items-center gap-1 px-3 py-1.5 sm:py-2 text-xs font-bold rounded-lg bg-[#00A9C1] hover:bg-[#007fa3] text-white shadow-xs transition-all active:scale-[0.98] shrink-0 whitespace-nowrap cursor-pointer">
        <span class="material-symbols-outlined text-sm">add</span>
        <span>Tambah Spesies</span>
      </button>
    '
  ])

  <!-- ================= MAIN OPERATIONAL CONTENT CONTAINER ================= -->
  <main class="flex-1 max-w-[1680px] w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">
    
    <!-- 1. BREADCRUMB, PAGE TITLE & ACTION CLUSTER SECTION -->
    @include('admin.manajemen.header-actions')

    <!-- 2. SUMMARY METRIC CARDS (ICAO AVIATION STAT CARDS) -->
    @include('admin.manajemen.kpi-cards')

    <!-- 3. FILTER & CONTROLS TOOLBAR -->
    @include('admin.manajemen.toolbar-filters')

    <!-- 4. GRID KATALOG KARTU SATWA INTERAKTIF -->
    @include('admin.manajemen.catalog-grid')

    <!-- 5. TABLE VIEW (ALTERNATIVE OPERATIONAL MODE) -->
    @include('admin.manajemen.catalog-table')

    <!-- 6. EMPTY STATE & DYNAMIC 2-ROW PAGINATION BAR -->
    @include('admin.manajemen.empty-and-pagination')

    <!-- 7. AIRSIDE RUNWAY GRID MAP TELEMETRY PREVIEW -->
    @include('admin.manajemen.runway-heatmap')

  </main>

  <!-- 8. MODAL DETAIL PROFIL SATWA & SOP ICAO DOC 9137 -->
  @include('admin.manajemen.modal-detail')

  <!-- 9. MODAL TAMBAH SPESIES & MODAL EDIT SPESIES -->
  @include('admin.manajemen.modal-form')

  <!-- 10. FOOTER STANDAR INJOURNEY AIRPORTS -->
  @include('admin.manajemen.footer')

  <!-- 11. CLIENT-SIDE INTERACTIVITY & DYNAMIC PAGINATION SCRIPTS -->
  @include('admin.manajemen.scripts')

</body>
</html>
