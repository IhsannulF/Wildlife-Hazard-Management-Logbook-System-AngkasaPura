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
  @yield('scripts')
</body>
</html>
