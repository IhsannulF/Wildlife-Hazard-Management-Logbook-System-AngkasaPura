<style>
  @page {
    size: A4 portrait;
    margin: 13mm 14mm 17mm 14mm;
  }

  * {
    box-sizing: border-box;
  }

  body {
    margin: 0;
    padding: 0;
    font-family: "DejaVu Sans", sans-serif;
    font-size: 9pt;
    line-height: 1.38;
    color: #1e293b;
    background-color: #ffffff;
  }

  table {
    border-collapse: collapse;
    border-spacing: 0;
    width: 100%;
  }

  .ba-sheet {
    width: 100%;
  }

  .ba-page-break {
    page-break-before: always;
  }

  /* Header Resmi InJourney Airports */
  .ba-header-table {
    width: 100%;
    border: 1px solid #005f80;
    background-color: #003e56;
    color: #ffffff;
    margin-bottom: 6pt;
  }

  .ba-header-accent {
    height: 3.5pt;
    background-color: #00A9C1;
    font-size: 1pt;
    line-height: 1pt;
  }

  .ba-logo-cell {
    width: 36mm;
    padding: 6pt 8pt;
    vertical-align: middle;
    text-align: center;
    background-color: #ffffff;
    border-right: 1px solid #bae6fd;
  }

  .ba-logo-img {
    max-width: 31mm;
    max-height: 13mm;
    width: auto;
    height: auto;
  }

  .ba-title-cell {
    padding: 6pt 10pt;
    vertical-align: middle;
    color: #ffffff;
  }

  .ba-instansi {
    font-size: 7.2pt;
    font-weight: bold;
    letter-spacing: 0.6pt;
    color: #7de8f5;
    text-transform: uppercase;
    margin-bottom: 2pt;
  }

  .ba-doc-title {
    font-size: 11pt;
    font-weight: bold;
    color: #ffffff;
    line-height: 1.2;
    margin-bottom: 2pt;
  }

  .ba-doc-sub {
    font-size: 7.2pt;
    color: #cbd5e1;
    line-height: 1.25;
  }

  .ba-reg-cell {
    width: 52mm;
    padding: 6pt 8pt;
    vertical-align: middle;
    text-align: right;
    background-color: #002e41;
    border-left: 1px solid #0e7490;
    color: #ffffff;
  }

  .ba-reg-label {
    font-size: 6.5pt;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5pt;
    margin-bottom: 2pt;
  }

  .ba-reg-no {
    font-family: "DejaVu Sans Mono", monospace;
    font-size: 8pt;
    font-weight: bold;
    color: #ffffff;
    margin-bottom: 4pt;
    word-wrap: break-word;
  }

  .ba-status-pill {
    display: inline-block;
    padding: 2pt 6pt;
    font-size: 6.8pt;
    font-weight: bold;
    border-radius: 2pt;
    text-transform: uppercase;
  }

  .ba-status-verified {
    background-color: #dcfce7;
    color: #166534;
    border: 1px solid #86efac;
  }

  .ba-status-pending {
    background-color: #fef3c7;
    color: #92400e;
    border: 1px solid #fcd34d;
  }

  /* Strip Status Bar */
  .ba-subbar {
    width: 100%;
    border: 1px solid #cbd5e1;
    background-color: #f0f9ff;
    margin-bottom: 8pt;
  }

  .ba-subbar td {
    padding: 4pt 8pt;
    font-size: 7.6pt;
    color: #0f172a;
  }

  /* Judul Seksi */
  .ba-section-title {
    font-size: 8.2pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.5pt;
    color: #005f80;
    border-bottom: 1.5pt solid #00A9C1;
    padding-bottom: 2.5pt;
    margin-top: 8pt;
    margin-bottom: 5pt;
  }

  .avoid-break {
    page-break-inside: avoid;
  }

  /* Grid 2 Kolom: Identitas & Skor ICAO */
  .ba-summary-layout {
    width: 100%;
    margin-bottom: 4pt;
  }

  .ba-summary-left {
    width: 63%;
    vertical-align: top;
    padding-right: 6pt;
  }

  .ba-summary-right {
    width: 37%;
    vertical-align: top;
  }

  .ba-meta-table {
    width: 100%;
    border: 1px solid #cbd5e1;
    background-color: #f8fafc;
  }

  .ba-meta-table td {
    padding: 3.5pt 6pt;
    border-bottom: 1px solid #e2e8f0;
    font-size: 7.9pt;
    vertical-align: top;
  }

  .ba-meta-table tr:last-child td {
    border-bottom: none;
  }

  .ba-meta-label {
    width: 34%;
    font-weight: bold;
    color: #475569;
    background-color: #f1f5f9;
    border-right: 1px solid #e2e8f0;
  }

  .ba-meta-val {
    color: #0f172a;
  }

  /* Kartu Risiko ICAO */
  .ba-risk-card {
    width: 100%;
    border: 1.2pt solid #cbd5e1;
  }

  .ba-risk-header {
    padding: 4.5pt 7pt;
    font-size: 7.5pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.4pt;
    color: #ffffff;
    background-color: #0f172a;
  }

  .ba-risk-score-row td {
    padding: 6pt 7pt;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: middle;
  }

  .ba-risk-badge {
    display: inline-block;
    padding: 2pt 6pt;
    font-size: 7.4pt;
    font-weight: bold;
    color: #ffffff;
    text-transform: uppercase;
  }

  .ba-risk-number {
    font-size: 13pt;
    font-weight: bold;
    line-height: 1.1;
  }

  .ba-risk-factor-table td {
    padding: 3pt 7pt;
    font-size: 7.4pt;
    border-bottom: 1px solid #e2e8f0;
    color: #334155;
  }

  .ba-risk-factor-table tr:last-child td {
    border-bottom: none;
  }

  /* Tabel Matriks Temuan Satwa */
  .ba-matrix-table {
    width: 100%;
    border: 1px solid #94a3b8;
    margin-bottom: 4pt;
  }

  .ba-matrix-table th {
    background-color: #007fa3;
    color: #ffffff;
    font-size: 7.7pt;
    font-weight: bold;
    padding: 4.5pt 6pt;
    text-align: left;
    border: 1px solid #006684;
  }

  .ba-matrix-table td {
    font-size: 7.9pt;
    padding: 4.2pt 6pt;
    border: 1px solid #cbd5e1;
    vertical-align: middle;
  }

  .ba-matrix-table tr.alt-row td {
    background-color: #f8fafc;
  }

  .ba-matrix-total td {
    background-color: #eef7fa;
    font-weight: bold;
    color: #0f172a;
    border-top: 1.2pt solid #007fa3;
  }

  /* Pengamatan & Penanganan 2x2 */
  .ba-obs-table {
    width: 100%;
    border: 1px solid #cbd5e1;
  }

  .ba-obs-table td {
    width: 50%;
    padding: 5.5pt 7pt;
    border: 1px solid #cbd5e1;
    vertical-align: top;
    background-color: #ffffff;
  }

  .ba-obs-label {
    font-size: 7.1pt;
    font-weight: bold;
    text-transform: uppercase;
    color: #007fa3;
    margin-bottom: 2.5pt;
    letter-spacing: 0.3pt;
  }

  .ba-obs-text {
    font-size: 8pt;
    color: #1e293b;
    line-height: 1.4;
  }

  /* Riwayat Tanggapan Safety Unit */
  .ba-notes-table {
    width: 100%;
    border: 1px solid #cbd5e1;
    margin-top: 5pt;
  }

  .ba-notes-table th {
    background-color: #f1f5f9;
    color: #334155;
    font-size: 7.3pt;
    font-weight: bold;
    padding: 3.5pt 6pt;
    text-align: left;
    border-bottom: 1px solid #cbd5e1;
  }

  .ba-notes-table td {
    padding: 3.5pt 6pt;
    font-size: 7.5pt;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: top;
  }

  .ba-notes-table tr:last-child td {
    border-bottom: none;
  }

  /* Foto Dokumentasi Ber-Watermark */
  .ba-photo-grid {
    width: 100%;
  }

  .ba-photo-cell {
    width: 50%;
    padding: 3pt;
    vertical-align: top;
  }

  .ba-photo-card {
    border: 1px solid #cbd5e1;
    background-color: #f8fafc;
    padding: 4pt;
    text-align: center;
  }

  .ba-photo-img {
    width: auto;
    max-width: 100%;
    height: auto;
    max-height: 55mm;
    display: block;
    margin: 0 auto;
    border: 1px solid #e2e8f0;
  }

  .ba-photo-caption {
    margin-top: 3.5pt;
    font-size: 7.6pt;
    font-weight: bold;
    color: #0f172a;
    text-align: left;
  }

  .ba-photo-sub {
    font-size: 6.9pt;
    color: #475569;
    text-align: left;
    margin-top: 1pt;
  }

  .ba-empty-box {
    border: 1px dashed #cbd5e1;
    background-color: #f8fafc;
    padding: 7pt;
    font-size: 7.8pt;
    color: #64748b;
    text-align: center;
  }

  /* Tanda Tangan Ganda */
  .ba-attestation {
    margin-top: 8pt;
    margin-bottom: 6pt;
    font-size: 7.8pt;
    color: #334155;
    line-height: 1.38;
  }

  .ba-sig-table {
    width: 100%;
    border: 1px solid #bae6fd;
    background-color: #f8fbfd;
  }

  .ba-sig-cell {
    width: 50%;
    padding: 7pt 10pt;
    text-align: center;
    vertical-align: top;
    border-right: 1px solid #bae6fd;
  }

  .ba-sig-cell:last-child {
    border-right: none;
  }

  .ba-sig-role {
    font-size: 7.5pt;
    font-weight: bold;
    color: #005f80;
    text-transform: uppercase;
    letter-spacing: 0.3pt;
    margin-bottom: 2pt;
  }

  .ba-sig-subtitle {
    font-size: 7pt;
    color: #64748b;
    margin-bottom: 4pt;
  }

  .ba-sig-img-box {
    height: 19mm;
    vertical-align: middle;
    text-align: center;
    padding: 2pt 0;
  }

  .ba-sig-img {
    max-width: 52mm;
    max-height: 17mm;
    width: auto;
    height: auto;
  }

  .ba-digital-seal {
    display: inline-block;
    border: 1.2pt solid #16a34a;
    background-color: #dcfce7;
    color: #166534;
    padding: 4pt 8pt;
    font-size: 7pt;
    font-weight: bold;
    line-height: 1.25;
  }

  .ba-pending-seal {
    display: inline-block;
    border: 1pt dashed #d97706;
    background-color: #fffbeb;
    color: #92400e;
    padding: 5pt 8pt;
    font-size: 7pt;
    font-weight: bold;
  }

  .ba-sig-name {
    border-top: 1pt solid #007fa3;
    padding-top: 3pt;
    margin-top: 4pt;
    font-size: 8.3pt;
    font-weight: bold;
    color: #0f172a;
  }

  .ba-sig-unit {
    font-size: 7.2pt;
    color: #475569;
    margin-top: 1pt;
  }

  .ba-sig-time {
    font-size: 6.7pt;
    color: #64748b;
    margin-top: 2pt;
  }

  .text-center { text-align: center; }
  .text-right { text-align: right; }
  .font-bold { font-weight: bold; }
  .italic { font-style: italic; }
  .mono { font-family: "DejaVu Sans Mono", monospace; }
</style>

