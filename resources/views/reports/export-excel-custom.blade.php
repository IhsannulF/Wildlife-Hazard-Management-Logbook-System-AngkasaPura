<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
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
    }
    .meta-table td {
      padding: 2px 8px 2px 0;
      border: none;
    }
    table.data-table {
      border-collapse: collapse;
      width: 100%;
      margin-top: 8px;
    }
    table.data-table th, table.data-table td {
      border: 1px solid #94a3b8;
      padding: 6px 8px;
      font-size: 10.5px;
    }
    table.data-table th {
      background-color: #00A9C1;
      color: #ffffff;
      font-weight: bold;
      text-align: center;
      vertical-align: middle;
      height: 28px;
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

  <!-- METADATA REKAPITULASI -->
  <table class="meta-table">
    <tr>
      <td style="font-weight: bold; width: 130px;">Waktu Ekspor:</td>
      <td>{{ $exportTime }}</td>
      <td style="font-weight: bold; width: 130px;">Total Laporan Terkait:</td>
      <td>{{ $summaryData['total_laporan'] }} Laporan</td>
    </tr>
    <tr>
      <td style="font-weight: bold;">Mode Tampilan Baris:</td>
      <td>{{ ($requestParams['mode_baris'] ?? '') === 'per_satwa' ? 'Rincian Per Temuan Satwa (Standar DKPPU)' : 'Ringkasan Per Laporan' }}</td>
      <td style="font-weight: bold;">Tingkat Penyelesaian:</td>
      <td>{{ $summaryData['persen_selesai'] }}% Telah Ditangani</td>
    </tr>
    @if(!empty($requestParams['unit_kerja']) && $requestParams['unit_kerja'] !== 'all')
      <tr>
        <td style="font-weight: bold;">Filter Unit Kerja:</td>
        <td colspan="3">{{ $requestParams['unit_kerja'] }}</td>
      </tr>
    @endif
  </table>

  <!-- TABEL UTAMA SPREADSHEET -->
  <table class="data-table">
    <thead>
      <tr>
        @foreach($activeColumns as $colKey => $col)
          <th class="text-center" style="white-space: nowrap;">{{ $col['label'] }}</th>
        @endforeach
      </tr>
    </thead>
    <tbody>
      @forelse($rows as $idx => $r)
        <tr class="{{ $idx % 2 === 0 ? 'row-even' : 'row-odd' }}">
          @foreach($activeColumns as $colKey => $col)
            @php
              $val = $r[$colKey] ?? '-';
              $align = $col['align'] ?? 'left';
            @endphp
            <td class="text-{{ $align }}" style="{{ $colKey === 'jumlah_ekor' ? 'mso-number-format:\@;' : '' }}">
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
        <tr>
          <td colspan="{{ count($activeColumns) }}" class="text-center" style="padding: 16px; color: #64748b;">
            Tidak ada rekaman data logbook satwa liar yang sesuai dengan kriteria filter terpilih.
          </td>
        </tr>
      @endforelse

      <!-- BARIS TOTAL & REKAPITULASI FORMULA STANDAR DKPPU -->
      @if($includeSummary && count($rows) > 0)
        @php
          $dataStartRow = 9; // Baris awal data di Excel
          $dataEndRow = $dataStartRow + count($rows) - 1;
        @endphp
        <tr class="summary-row">
          @php $isFirst = true; @endphp
          @foreach($activeColumns as $colKey => $col)
            @if($isFirst)
              <td class="text-center" style="font-weight: bold; background-color: #d1f2f5;">
                REKAPITULASI TOTAL
              </td>
              @php $isFirst = false; @endphp
            @elseif($colKey === 'jumlah_ekor')
              <td class="text-right" style="font-weight: bold; background-color: #d1f2f5;">
                {{ $summaryData['total_ekor'] }} Ekor
              </td>
            @elseif($colKey === 'amunisi_terpakai')
              <td class="text-center" style="font-weight: bold; background-color: #d1f2f5;">
                {{ $summaryData['total_amunisi'] }} Butir
              </td>
            @elseif($colKey === 'status_penanganan')
              <td class="text-center" style="font-weight: bold; background-color: #d1f2f5;">
                {{ $summaryData['persen_selesai'] }}% Selesai
              </td>
            @elseif($colKey === 'no' || $colKey === 'id_laporan')
              <td class="text-center" style="background-color: #d1f2f5;">-</td>
            @else
              <td style="background-color: #d1f2f5;"></td>
            @endif
          @endforeach
        </tr>
      @endif
    </tbody>
  </table>

  <!-- LEMBAR TANDA TANGAN VALIDASI AUDIT KESELAMATAN -->
  <table class="sign-table">
    <tr>
      <td style="width: 50%; vertical-align: top;">
        <p style="margin: 0; font-size: 10px; color: #64748b;">Diterbitkan secara elektronik oleh:</p>
        <p style="margin: 4px 0 0 0; font-weight: bold;">Sistem Logbook Bahaya Satwa Liar (WHMS)</p>
        <p style="margin: 0; font-size: 10px; color: #64748b;">PT Angkasa Pura Indonesia</p>
      </td>
      <td style="width: 50%; text-align: right; vertical-align: top;">
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
