<style>
  /* ================= BUNDEL LOGBOOK BULANAN (DOMPDF CSS 2.1) ================= */
  @page {
    size: A4 portrait;
    margin: 10mm 12mm 14mm 12mm;
  }

  .bundle-cover-banner {
    width: 100%;
    border: 1.5pt solid #003e56;
    background-color: #003e56;
    color: #ffffff;
    margin-bottom: 10pt;
  }

  .bundle-cover-accent {
    height: 4.5pt;
    background-color: #00A9C1;
    font-size: 1pt;
    line-height: 1pt;
  }

  .bundle-cover-logo-cell {
    width: 42mm;
    background-color: #ffffff;
    padding: 8pt;
    text-align: center;
    vertical-align: middle;
    border-right: 2pt solid #00A9C1;
  }

  .bundle-cover-logo-img {
    max-width: 34mm;
    max-height: 15mm;
    width: auto;
    height: auto;
  }

  .bundle-cover-inst-cell {
    padding: 8pt 11pt;
    vertical-align: middle;
  }

  .bundle-cover-inst-eyebrow {
    font-size: 7.3pt;
    font-weight: bold;
    letter-spacing: 0.8pt;
    color: #7de8f5;
    text-transform: uppercase;
    margin-bottom: 2.5pt;
  }

  .bundle-cover-inst-title {
    font-size: 10pt;
    font-weight: bold;
    color: #ffffff;
  }

  .bundle-cover-inst-sub {
    font-size: 7.6pt;
    color: #cbd5e1;
    margin-top: 2pt;
  }

  .bundle-cover-hero {
    border: 1.5pt solid #bae6fd;
    border-left: 5pt solid #007fa3;
    background-color: #f8fafc;
    padding: 13pt 14pt;
    margin-bottom: 10pt;
  }

  .bundle-cover-code {
    display: inline-block;
    background-color: #0f172a;
    color: #7de8f5;
    font-family: "DejaVu Sans Mono", monospace;
    font-size: 8pt;
    font-weight: bold;
    padding: 3pt 7pt;
    margin-bottom: 8pt;
    letter-spacing: 0.4pt;
  }

  .bundle-cover-main-title {
    font-size: 16.5pt;
    font-weight: bold;
    color: #003e56;
    line-height: 1.2;
    text-transform: uppercase;
    margin-bottom: 5pt;
  }

  .bundle-cover-subtitle {
    font-size: 9.8pt;
    font-weight: bold;
    color: #007fa3;
    line-height: 1.32;
    margin-bottom: 9pt;
  }

  .bundle-cover-period-box {
    border-top: 1pt solid #cbd5e1;
    padding-top: 8pt;
    width: 100%;
  }

  .bundle-cover-period-label {
    font-size: 7.2pt;
    font-weight: bold;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5pt;
  }

  .bundle-cover-period-value {
    font-size: 13pt;
    font-weight: bold;
    color: #0f172a;
    margin-top: 2pt;
  }

  .bundle-cover-kpi-table {
    width: 100%;
    margin-bottom: 10pt;
  }

  .bundle-cover-kpi-cell {
    width: 25%;
    padding-right: 6pt;
    vertical-align: top;
  }

  .bundle-cover-kpi-cell:last-child {
    padding-right: 0;
  }

  .bundle-cover-kpi-card {
    border: 1pt solid #cbd5e1;
    border-top: 3pt solid #00A9C1;
    background-color: #ffffff;
    padding: 6.5pt 6pt;
    text-align: center;
  }

  .bundle-cover-kpi-num {
    font-size: 14pt;
    font-weight: bold;
    color: #003e56;
    line-height: 1.15;
  }

  .bundle-cover-kpi-label {
    font-size: 6.9pt;
    font-weight: bold;
    color: #475569;
    text-transform: uppercase;
    margin-top: 2.5pt;
  }

  .bundle-cover-kpi-sub {
    font-size: 6.5pt;
    color: #64748b;
    margin-top: 1.5pt;
  }

  .bundle-cover-info-table {
    width: 100%;
    border: 1pt solid #cbd5e1;
    margin-bottom: 10pt;
  }

  .bundle-cover-info-table td {
    padding: 4.5pt 8pt;
    border-bottom: 1pt solid #e2e8f0;
    font-size: 8pt;
  }

  .bundle-cover-info-table tr:last-child td {
    border-bottom: none;
  }

  .bundle-cover-info-label {
    width: 32%;
    font-weight: bold;
    background-color: #f1f5f9;
    color: #334155;
    border-right: 1pt solid #cbd5e1;
  }

  .bundle-cover-structure {
    border: 1pt solid #bae6fd;
    background-color: #f0f9ff;
    padding: 8pt 10pt;
    margin-bottom: 0;
  }

  .bundle-cover-structure-title {
    font-size: 7.9pt;
    font-weight: bold;
    color: #005f80;
    text-transform: uppercase;
    margin-bottom: 4pt;
    letter-spacing: 0.4pt;
  }

  .bundle-cover-structure-list {
    margin: 0;
    padding-left: 14pt;
    font-size: 7.6pt;
    color: #1e293b;
    line-height: 1.4;
  }

  /* Header Section Pita untuk Bagian II & III */
  .bundle-part-banner {
    width: 100%;
    background-color: #003e56;
    color: #ffffff;
    border-bottom: 2.5pt solid #00A9C1;
    margin-bottom: 7pt;
  }

  .bundle-part-banner td {
    padding: 5pt 8pt;
    vertical-align: middle;
  }

  .bundle-part-eyebrow {
    font-size: 6.6pt;
    color: #7de8f5;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.6pt;
  }

  .bundle-part-title {
    font-size: 10.2pt;
    font-weight: bold;
    color: #ffffff;
    margin-top: 1pt;
  }

  .bundle-part-code {
    text-align: right;
    font-family: "DejaVu Sans Mono", monospace;
    font-size: 7.3pt;
    color: #bae6fd;
  }

  /* Kata Pengantar & Endorsement */
  .bundle-foreword-box {
    border: 1pt solid #cbd5e1;
    background-color: #ffffff;
    padding: 8pt 10pt;
    margin-bottom: 6.5pt;
  }

  .bundle-foreword-heading {
    font-size: 9pt;
    font-weight: bold;
    color: #003e56;
    margin-bottom: 5pt;
    border-bottom: 1pt solid #e2e8f0;
    padding-bottom: 3pt;
  }

  .bundle-foreword-p {
    font-size: 8pt;
    color: #1e293b;
    line-height: 1.42;
    text-align: justify;
    margin: 0 0 4.5pt 0;
  }

  .bundle-rec-box {
    border: 1pt solid #0284c7;
    border-left: 4pt solid #00A9C1;
    background-color: #f0f9ff;
    padding: 5.5pt 8pt;
    font-size: 7.8pt;
    color: #0c4a6e;
    margin-top: 3pt;
  }

  .bundle-endorse-table {
    width: 100%;
    border: 1pt solid #cbd5e1;
    background-color: #f8fafc;
    margin-bottom: 7pt;
  }

  .bundle-endorse-table td {
    padding: 5.5pt 8pt;
    vertical-align: middle;
    font-size: 7.7pt;
  }

  /* Daftar Isi Kronologis BA */
  .bundle-index-table {
    width: 100%;
    border: 1pt solid #94a3b8;
    margin-top: 4pt;
  }

  .bundle-index-table th {
    background-color: #007fa3;
    color: #ffffff;
    font-size: 6.5pt;
    font-weight: bold;
    padding: 2.5pt 3.5pt;
    border: 1pt solid #006684;
    text-align: left;
  }

  .bundle-index-table td {
    font-size: 6.5pt;
    line-height: 1.22;
    padding: 2pt 3.5pt;
    border: 1pt solid #cbd5e1;
    vertical-align: middle;
  }

  .bundle-index-table tr {
    page-break-inside: avoid;
  }

  .bundle-index-table tr.alt-row td {
    background-color: #f8fafc;
  }

  /* Halaman Statistik & Grafik Temporal */
  .stat-kpi-grid {
    width: 100%;
    margin-bottom: 5pt;
  }

  .stat-kpi-cell {
    width: 33.33%;
    padding: 0 4pt 4pt 0;
    vertical-align: top;
  }

  .stat-kpi-cell-last {
    padding-right: 0;
  }

  .stat-kpi-card {
    border: 1pt solid #cbd5e1;
    border-left: 3pt solid #007fa3;
    background-color: #f8fafc;
    padding: 4.5pt 6pt;
  }

  .stat-kpi-title {
    font-size: 6.5pt;
    font-weight: bold;
    color: #475569;
    text-transform: uppercase;
  }

  .stat-kpi-val {
    font-size: 10.5pt;
    font-weight: bold;
    color: #0f172a;
    margin-top: 1pt;
  }

  .stat-kpi-sub {
    font-size: 6.3pt;
    color: #64748b;
    margin-top: 1pt;
  }

  .chart-panel {
    border: 1pt solid #cbd5e1;
    background-color: #ffffff;
    padding: 5pt 7pt;
    margin-bottom: 5pt;
  }

  .chart-panel-title {
    font-size: 7.4pt;
    font-weight: bold;
    color: #003e56;
    text-transform: uppercase;
    letter-spacing: 0.3pt;
    border-bottom: 1pt solid #e2e8f0;
    padding-bottom: 2.5pt;
    margin-bottom: 4.5pt;
  }

  .vbar-table {
    width: 100%;
    border-bottom: 1.2pt solid #94a3b8;
  }

  .vbar-cell {
    vertical-align: bottom;
    text-align: center;
    height: 48pt;
    padding: 0 3pt;
  }

  .vbar-count {
    font-size: 6.8pt;
    font-weight: bold;
    color: #0f172a;
    margin-bottom: 1.5pt;
  }

  .vbar-fill {
    width: 54%;
    margin: 0 auto;
    background-color: #00A9C1;
    border-top: 1.5pt solid #005f80;
  }

  .vbar-zero {
    width: 54%;
    height: 2pt;
    margin: 0 auto;
    background-color: #e2e8f0;
  }

  .vbar-label-cell {
    text-align: center;
    padding-top: 2pt;
    font-size: 6.6pt;
    font-weight: bold;
    color: #334155;
  }

  .vbar-sub-label {
    font-size: 5.8pt;
    font-weight: normal;
    color: #64748b;
  }

  .hbar-row-table {
    width: 100%;
    margin-bottom: 2.5pt;
  }

  .hbar-track {
    width: 100%;
    background-color: #e2e8f0;
    height: 6pt;
  }

  .hbar-fill {
    height: 6pt;
    font-size: 1pt;
    line-height: 1pt;
  }

  .daily-grid-table {
    width: 100%;
    border: 1pt solid #e2e8f0;
    background-color: #f8fafc;
    margin-bottom: 0;
  }

  .daily-bar-cell {
    text-align: center;
    vertical-align: bottom;
    height: 24pt;
    padding: 2pt 0.5pt 0 0.5pt;
    border-right: 0.5pt solid #e2e8f0;
  }

  .daily-day-cell {
    text-align: center;
    font-size: 5.7pt;
    font-weight: bold;
    color: #475569;
    padding: 1.5pt 0.5pt;
    border-top: 1pt solid #cbd5e1;
    border-right: 0.5pt solid #e2e8f0;
    background-color: #ffffff;
  }

  /* Marker Lampiran BA pada Bundel & Penyesuaian Muat 1 Halaman A4 */
  .bundle-ba-marker {
    background-color: #f1f5f9;
    border: 1pt solid #cbd5e1;
    border-left: 3.5pt solid #00A9C1;
    color: #003e56;
    font-size: 6.6pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.4pt;
    padding: 2.2pt 6pt;
    margin-bottom: 3.5pt;
  }

  .bundle-ba-wrapper .ba-header-table { margin-bottom: 4pt; }
  .bundle-ba-wrapper .ba-logo-cell { padding: 4pt 6pt; }
  .bundle-ba-wrapper .ba-title-cell { padding: 4pt 8pt; }
  .bundle-ba-wrapper .ba-reg-cell { padding: 4pt 7pt; }
  .bundle-ba-wrapper .ba-subbar { margin-bottom: 4pt; }
  .bundle-ba-wrapper .ba-subbar td { padding: 2.5pt 6pt; font-size: 7.2pt; }
  .bundle-ba-wrapper .ba-summary-layout { margin-bottom: 4pt; }
  .bundle-ba-wrapper .ba-meta-table td { padding: 2.5pt 5pt; font-size: 7.4pt; }
  .bundle-ba-wrapper .ba-risk-header { padding: 2.5pt 6pt; }
  .bundle-ba-wrapper .ba-risk-score-row td { padding: 3pt 6pt; }
  .bundle-ba-wrapper .ba-risk-factor-table td { padding: 2pt 6pt; font-size: 7pt; }
  .bundle-ba-wrapper .ba-section-title { margin: 4pt 0 2.5pt 0; padding: 2pt 6pt; font-size: 7.5pt; }
  .bundle-ba-wrapper .ba-matrix-table th { padding: 3pt 5pt; font-size: 7.3pt; }
  .bundle-ba-wrapper .ba-matrix-table td { padding: 2.8pt 5pt; font-size: 7.4pt; }
  .bundle-ba-wrapper .ba-obs-table td { padding: 3.5pt 6pt; }
  .bundle-ba-wrapper .ba-obs-text { font-size: 7.4pt; line-height: 1.3; }
  .bundle-ba-wrapper .ba-empty-box { padding: 4.5pt; font-size: 7.4pt; }
  .bundle-ba-wrapper .ba-attestation { margin-top: 4pt; margin-bottom: 4pt; font-size: 7.3pt; line-height: 1.3; }
  .bundle-ba-wrapper .ba-sig-cell { padding: 4.5pt 8pt; }
  .bundle-ba-wrapper .ba-sig-img-box { height: 14mm; padding: 1pt 0; }
  .bundle-ba-wrapper .ba-sig-img { max-height: 13mm; }
  .bundle-ba-wrapper .ba-sig-name { margin-top: 2pt; padding-top: 2pt; font-size: 7.8pt; }

  .bundle-empty-appendix {
    border: 1.5pt dashed #94a3b8;
    background-color: #f8fafc;
    padding: 22pt 16pt;
    text-align: center;
    margin-top: 14pt;
  }
</style>

