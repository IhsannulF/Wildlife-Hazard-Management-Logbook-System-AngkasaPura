<!DOCTYPE html>
<html lang="id" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
  <meta charset="UTF-8">
  <meta name="ProgId" content="Excel.Sheet">
  <title>Rekapitulasi Satwa Liar - InJourney Airports</title>
  <style>
    body {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 11px;
      color: #1e293b;
    }
    .title-block {
      margin-bottom: 16px;
    }
    .instansi-title {
      font-size: 14px;
      font-weight: bold;
      color: #0f172a;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .doc-title {
      font-size: 16px;
      font-weight: bold;
      color: #006874;
      text-transform: uppercase;
      margin-top: 4px;
    }
    .meta-table {
      margin-bottom: 12px;
      font-size: 11px;
      border-collapse: collapse;
    }
    .meta-table td {
      padding: 2px 10px 2px 0;
      border: none;
      white-space: nowrap;
    }
    table.data-table {
      border-collapse: collapse;
      table-layout: fixed;
      margin-top: 8px;
    }
    table.data-table th, table.data-table td {
      border: 1px solid #94a3b8;
      padding: 6px 8px;
      font-size: 10.5px;
      vertical-align: middle;
    }
    table.data-table th {
      background-color: #00A9C1;
      color: #ffffff;
      font-weight: bold;
      text-align: center;
      vertical-align: middle;
      white-space: nowrap;
      height: 30px;
    }
    .row-even {
      background-color: #f8fafc;
    }
    .row-odd {
      background-color: #ffffff;
    }
    .summary-row {
      background-color: #e2f8fa;
      font-weight: bold;
      border-top: 2px solid #006874;
      border-bottom: 2px solid #006874;
    }
    .summary-row td {
      font-weight: bold;
      color: #00363d;
    }
    .text-center { text-align: center; }
    .text-left { text-align: left; }
    .text-right { text-align: right; }
    .badge-status-selesai {
      color: #047857;
      font-weight: bold;
    }
    .badge-status-belum {
      color: #b91c1c;
      font-weight: bold;
    }
    .sign-table {
      margin-top: 24px;
      width: 100%;
      border-collapse: collapse;
    }
    .sign-table td {
      border: none;
      font-size: 11px;
    }
  </style>
</head>
<body>

  <!-- KOP DOKUMEN RESMI STANDAR DKPPU / INJOURNEY AIRPORTS -->
  <div class="title-block">
    <div class="instansi-title">PT ANGKASA PURA INDONESIA — INJOURNEY AIRPORTS</div>
    <div class="doc-title">REKAPITULASI LOGBOOK PENGAMATAN & PENGENDALIAN BAHAYA SATWA LIAR (WHMS)</div>
    <div style="font-size: 10px; color: #64748b; margin-top: 2px;">
      Standar Kepatuhan: ICAO Annex 14 Bab 9 • ICAO Doc 9137 Part 3 • Formulir Baku Ditjen Perhubungan Udara (DKPPU)
    </div>
  </div>

  @php
    $colCount = max(1, count($activeColumns));
    $resolvedWidths = $columnWidths ?? [];
    $resolvedWidthsAlt = $columnWidthsAlt ?? [];
    $resolvedWrapCols = $wrapColumns ?? [];
    $resolvedRowHeightsPt = $rowHeightsPt ?? [];

    if (empty($resolvedWidths)) {
      foreach ($activeColumns as $colKey => $col) {
        $headerPx = (int) ceil(mb_strlen(trim((string) ($col['label'] ?? $colKey))) * 8.2) + 28;
        $maxPx = max(48, $headerPx);
        foreach ($rows as $r) {
          $cellLen = mb_strlen(trim((string) ($r[$colKey] ?? '-')));
          $maxPx = max($maxPx, (int) ceil($cellLen * 7.8) + 28);
        }
        $wrap = $maxPx > 420;
        $finalPx = $wrap ? 420 : $maxPx;
        $resolvedWidths[$colKey] = $finalPx;
        $resolvedWidthsAlt[$colKey] = (int) round(($finalPx / 7.0) * 256);
        $resolvedWrapCols[$colKey] = $wrap;
      }
    }

    $resolvedTotalWidth = $totalTableWidthPx ?? array_sum($resolvedWidths);
    $metaLabelSpan = $colCount >= 6 ? 2 : 1;
    $metaValueSpan = $colCount >= 6 ? max(1, (int) floor(($colCount - 4) / 2)) : 1;
    $signLeftSpan = max(1, (int) floor($colCount / 2));
    $signRightSpan = max(1, $colCount - $signLeftSpan);
  @endphp

  <!-- METADATA REKAPITULASI -->
  <table class="meta-table">
    <tr>
      <td colspan="{{ $metaLabelSpan }}" style="font-weight: bold;">Waktu Ekspor:</td>
      <td colspan="{{ $metaValueSpan }}">{{ $exportTime }}</td>
      <td colspan="{{ $metaLabelSpan }}" style="font-weight: bold;">Total Laporan Terkait:</td>
      <td colspan="{{ $metaValueSpan }}">{{ $summaryData['total_laporan'] }} Laporan</td>
    </tr>
    <tr>
      <td colspan="{{ $metaLabelSpan }}" style="font-weight: bold;">Mode Tampilan Baris:</td>
      <td colspan="{{ $metaValueSpan }}">{{ ($requestParams['mode_baris'] ?? '') === 'per_satwa' ? 'Rincian Per Temuan Satwa (Standar DKPPU)' : 'Ringkasan Per Laporan' }}</td>
      <td colspan="{{ $metaLabelSpan }}" style="font-weight: bold;">Tingkat Penyelesaian:</td>
      <td colspan="{{ $metaValueSpan }}">{{ $summaryData['persen_selesai'] }}% Telah Ditangani</td>
    </tr>
    @if(!empty($requestParams['unit_kerja']) && $requestParams['unit_kerja'] !== 'all')
      <tr>
        <td colspan="{{ $metaLabelSpan }}" style="font-weight: bold;">Filter Unit Kerja:</td>
        <td colspan="{{ max(1, $colCount - $metaLabelSpan) }}">{{ $requestParams['unit_kerja'] }}</td>
      </tr>
    @endif
  </table>

  <!-- TABEL UTAMA SPREADSHEET -->
  <table class="data-table" width="{{ $resolvedTotalWidth }}" style="width: {{ $resolvedTotalWidth }}px; table-layout: fixed;">
    <colgroup>
      @foreach($activeColumns as $colKey => $col)
        @php
          $w = $resolvedWidths[$colKey] ?? 120;
          $alt = $resolvedWidthsAlt[$colKey] ?? (int) round(($w / 7.0) * 256);
        @endphp
        <col width="{{ $w }}" style="mso-width-source:userset; mso-width-alt:{{ $alt }}; width:{{ $w }}px;">
      @endforeach
    </colgroup>
    <thead>
      <tr style="height: 26pt; mso-height-source: userset;">
        @foreach($activeColumns as $colKey => $col)
          @php $w = $resolvedWidths[$colKey] ?? 120; @endphp
          <th class="text-center" width="{{ $w }}" style="width: {{ $w }}px; white-space: nowrap;">{{ $col['label'] }}</th>
        @endforeach
      </tr>
    </thead>
    <tbody>
      @forelse($rows as $idx => $r)
        @php $rowHeightPt = $resolvedRowHeightsPt[$idx] ?? 22; @endphp
        <tr class="{{ $idx % 2 === 0 ? 'row-even' : 'row-odd' }}" style="height: {{ $rowHeightPt }}pt; mso-height-source: userset;">
          @foreach($activeColumns as $colKey => $col)
            @php
              $val = $r[$colKey] ?? '-';
              $align = $col['align'] ?? 'left';
              $isWrap = (bool) ($resolvedWrapCols[$colKey] ?? false) || str_contains((string) $val, "\n");
              $wrapCss = $isWrap
                ? 'white-space: normal; word-wrap: break-word; mso-wrap-style: square; vertical-align: middle;'
                : 'white-space: nowrap; vertical-align: middle;';
              $numFormatCss = $colKey === 'jumlah_ekor' ? ' mso-number-format:\@;' : '';
            @endphp
            <td class="text-{{ $align }}" style="{{ $wrapCss }}{{ $numFormatCss }}">
              @if($colKey === 'status_penanganan')
                <span class="{{ str_contains(strtolower($val), 'telah') ? 'badge-status-selesai' : 'badge-status-belum' }}">
                  {{ $val }}
                </span>
              @elseif($colKey === 'jumlah_ekor')
                <strong>{{ $val }}</strong>
              @else
                {{ $val }}
              @endif
            </td>
          @endforeach
        </tr>
      @empty
        <tr style="height: 28pt; mso-height-source: userset;">
          <td colspan="{{ count($activeColumns) }}" class="text-center" style="padding: 16px; color: #64748b; white-space: nowrap;">
            Tidak ada rekaman data logbook satwa liar yang sesuai dengan kriteria filter terpilih.
          </td>
        </tr>
      @endforelse

      <!-- BARIS TOTAL & REKAPITULASI FORMULA STANDAR DKPPU -->
      @if($includeSummary && count($rows) > 0)
        <tr class="summary-row" style="height: 24pt; mso-height-source: userset;">
          @php $isFirst = true; @endphp
          @foreach($activeColumns as $colKey => $col)
            @if($isFirst)
              <td class="text-center" style="font-weight: bold; background-color: #d1f2f5; white-space: nowrap; vertical-align: middle;">
                TOTAL
              </td>
              @php $isFirst = false; @endphp
            @elseif($colKey === 'jumlah_ekor')
              <td class="text-right" style="font-weight: bold; background-color: #d1f2f5; white-space: nowrap; vertical-align: middle;">
                {{ $summaryData['total_ekor'] }} Ekor
              </td>
            @elseif($colKey === 'amunisi_terpakai')
              <td class="text-center" style="font-weight: bold; background-color: #d1f2f5; white-space: nowrap; vertical-align: middle;">
                {{ $summaryData['total_amunisi'] }} Butir
              </td>
            @elseif($colKey === 'status_penanganan')
              <td class="text-center" style="font-weight: bold; background-color: #d1f2f5; white-space: nowrap; vertical-align: middle;">
                {{ $summaryData['persen_selesai'] }}% Selesai
              </td>
            @elseif($colKey === 'no' || $colKey === 'id_laporan')
              <td class="text-center" style="background-color: #d1f2f5; white-space: nowrap; vertical-align: middle;">-</td>
            @else
              <td style="background-color: #d1f2f5; white-space: nowrap; vertical-align: middle;"></td>
            @endif
          @endforeach
        </tr>
      @endif
    </tbody>
  </table>

  <!-- LEMBAR TANDA TANGAN VALIDASI AUDIT KESELAMATAN -->
  <table class="sign-table" style="width: {{ $resolvedTotalWidth }}px;">
    <tr>
      <td colspan="{{ $signLeftSpan }}" style="vertical-align: top;">
        <p style="margin: 0; font-size: 10px; color: #64748b;">Diterbitkan secara elektronik oleh:</p>
        <p style="margin: 4px 0 0 0; font-weight: bold;">Sistem Logbook Bahaya Satwa Liar (WHMS)</p>
        <p style="margin: 0; font-size: 10px; color: #64748b;">PT Angkasa Pura Indonesia</p>
      </td>
      <td colspan="{{ $signRightSpan }}" style="text-align: right; vertical-align: top;">
        <p style="margin: 0; font-size: 10px; color: #64748b;">Mengetahui / Memvalidasi,</p>
        <p style="margin: 4px 0 0 0; font-weight: bold;">Airside Safety & Wildlife Hazard Lead</p>
        <div style="height: 40px;"></div>
        <p style="margin: 0; font-weight: bold; text-decoration: underline;">Safety Management Unit</p>
        <p style="margin: 2px 0 0 0; font-size: 10px; color: #64748b;">Airport Operation Division</p>
      </td>
    </tr>
  </table>

</body>
</html>
