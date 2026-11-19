@php
  $pageTitle = $title ?? 'Kecerdasan Buatan (AI) & Mitigasi Prediktif';
  $pageSubtitle = $subtitle ?? 'Pilar 4: Deteksi probabilitas birdstrike, analisis histori insiden, dan integrasi visi satwa.';
  $pageIcon = $icon ?? 'psychology';
  $headerActions = $actions ?? null;
@endphp

<!-- ================= UNIFIED PAGE HEADER: PILAR 4 AI WHMS ================= -->
@include('partials.page-header', [
  'title' => $pageTitle,
  'subtitle' => $pageSubtitle,
  'icon' => $pageIcon,
  'actions' => $headerActions,
])
