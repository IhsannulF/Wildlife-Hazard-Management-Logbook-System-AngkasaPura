<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Berita Acara {{ $ba['no_laporan'] }} — InJourney Airports</title>
  @include('reports.partials.berita-acara-styles')
</head>
<body>
  @include('reports.partials.berita-acara-sheet', ['ba' => $ba])
</body>
</html>

