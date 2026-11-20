<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Bundel Logbook Bulanan WHMS — {{ $bundle['periode_label'] }}</title>
  @include('reports.partials.berita-acara-styles')
  @include('reports.partials.monthly-bundle-styles')
</head>
<body>
  <!-- BAGIAN I: HALAMAN COVER RESMI -->
  @include('reports.partials.monthly-cover', ['bundle' => $bundle])

  <!-- BAGIAN II: KATA PENGANTAR & DAFTAR ISI KRONOLOGIS -->
  <div class="ba-page-break"></div>
  @include('reports.partials.monthly-foreword-index', ['bundle' => $bundle])

  <!-- BAGIAN III: REKAPITULASI STATISTIK & GRAFIK TEMPORAL BULANAN -->
  <div class="ba-page-break"></div>
  @include('reports.partials.monthly-statistics', ['bundle' => $bundle])

  <!-- BAGIAN IV: KOMPILASI SELURUH LEMBAR BERITA ACARA KRONOLOGIS -->
  @php
    $totalBaItems = count($bundle['berita_acara_items']);
  @endphp
  @forelse($bundle['berita_acara_items'] as $idx => $ba)
    <div class="ba-page-break"></div>
    <div class="bundle-ba-wrapper">
      <div class="bundle-ba-marker">
        Bagian IV &bull; Lampiran Berita Acara {{ $idx + 1 }} dari {{ $totalBaItems }} &bull; {{ $ba['no_laporan'] }} ({{ $ba['tanggal_formatted'] }})
      </div>
      @include('reports.partials.berita-acara-sheet', ['ba' => $ba])
    </div>
  @empty
    <div class="ba-page-break"></div>
    <table class="bundle-part-banner">
      <tr>
        <td>
          <div class="bundle-part-eyebrow">Bagian IV &bull; Lampiran Berita Acara</div>
          <div class="bundle-part-title">SURAT KETERANGAN NIHIL KEJADIAN SATWA LIAR</div>
        </td>
        <td class="bundle-part-code">
          {{ $bundle['bundle_code'] }}<br>{{ $bundle['periode_label'] }}
        </td>
      </tr>
    </table>
    <div class="bundle-empty-appendix">
      <div style="font-size: 11pt; font-weight: bold; color: #003e56; margin-bottom: 6pt;">
        NIHIL PENERBITAN BERITA ACARA — {{ strtoupper($bundle['periode_label']) }}
      </div>
      <div style="font-size: 8.5pt; color: #475569; line-height: 1.5;">
        Tidak terdapat laporan temuan atau insiden satwa liar pada periode <strong>{{ $bundle['periode_label'] }}</strong>
        untuk cakupan <strong>{{ $bundle['filter_summary']['unit_label'] }}</strong> dan status <strong>{{ $bundle['filter_summary']['status_label'] }}</strong>.<br>
        Dokumen ini tetap sah sebagai arsip kepatuhan pemantauan kelaikudaraan bulanan WHMS InJourney Airports.
      </div>
    </div>
  @endforelse
</body>
</html>

