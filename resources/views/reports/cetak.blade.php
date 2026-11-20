<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Satwa Liar #{{ str_pad($lap->id, 4, '0', STR_PAD_LEFT) }} — InJourney Airports</title>
<!-- Favicon InJourney Airports -->
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
<link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Arial, sans-serif; background: #dff0f5; color: #1a2332; font-size: 12px; }
  
  .toolbar-print {
    position: fixed; top: 0; left: 0; right: 0; z-index: 100;
    background: linear-gradient(135deg, #003d52 0%, #005f82 100%);
    padding: 12px 32px; display: flex; align-items: center; justify-content: space-between;
    box-shadow: 0 2px 16px rgba(0,0,0,0.25);
  }
  .toolbar-print span { color: rgba(255,255,255,.6); font-size: 11px; text-transform:uppercase; letter-spacing:1px; }
  .toolbar-print h2 { color: #fff; font-size: 15px; font-weight: 700; margin-top:2px; }
  .btn-group { display: flex; gap: 10px; }
  .btn-print { padding: 9px 22px; border-radius: 8px; font-family: inherit; font-size: 13px; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 7px; transition: all 0.2s; text-decoration: none; }
  .btn-blue { background: linear-gradient(135deg, #00A9C1, #007fa3); color: #fff; box-shadow: 0 3px 10px rgba(0,169,193,0.4); }
  .btn-blue:hover { box-shadow: 0 5px 16px rgba(0,169,193,0.6); transform: translateY(-1px); }
  .btn-back { background: rgba(255,255,255,.1); color: #fff; border: 1px solid rgba(255,255,255,.2) !important; }
  .btn-back:hover { background: rgba(255,255,255,.2); }

  /* Page Wrapper */
  .page-wrapper {
    max-width: 840px;
    min-height: 1050px;
    margin: 70px auto 40px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 8px 40px rgba(0,169,193,0.15), 0 4px 16px rgba(0,0,0,0.08);
    overflow: hidden;
    border: 1px solid rgba(0,169,193,0.15);
  }

  /* Structural Table Wrapper untuk Menjamin Header & Footer Selalu Muncul di Tiap Halaman */
  .report-table-wrapper {
    width: 100%;
    border-collapse: collapse;
    border-spacing: 0;
    border: none;
    margin: 0;
    padding: 0;
  }
  .report-table-wrapper > thead {
    display: table-header-group;
  }
  .report-table-wrapper > tfoot {
    display: table-footer-group;
  }
  .report-table-wrapper > thead > tr > td,
  .report-table-wrapper > tbody > tr > td,
  .report-table-wrapper > tfoot > tr > td {
    padding: 0;
    margin: 0;
    border: none;
    text-align: left;
    font-weight: normal;
  }

  /* Header Dokumen Resmi */
  .doc-header {
    background: linear-gradient(135deg, #002d40 0%, #004d6e 30%, #007fa3 65%, #00A9C1 100%);
    padding: 20px 32px; display: flex; align-items: center; gap: 20px; position: relative; overflow: hidden;
  }
  .doc-header::before { content: ''; position: absolute; top: -50px; right: -50px; width: 180px; height: 180px; border-radius: 50%; background: rgba(255,255,255,0.07); }
  .doc-header-logo {
    width: 170px; height: 75px;
    background: #fff; border-radius: 10px; display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; padding: 6px 12px;
    border: 2px solid rgba(255,255,255,0.95);
    box-shadow: 0 4px 16px rgba(0,0,0,0.18);
  }
  .doc-header-logo img { width: 100%; height: 100%; object-fit: contain; }
  .doc-header-text h1 { color: #fff; font-size: 18px; font-weight: 700; line-height: 1.25; }
  .doc-header-text p { color: rgba(255,255,255,.8); font-size: 11.5px; margin-top: 3px; }
  .doc-header-right { margin-left: auto; text-align: right; position: relative; z-index:1; }
  .doc-header-right .doc-no { color: rgba(255,255,255,.7); font-size: 10px; text-transform: uppercase; letter-spacing: 1px; }
  .doc-header-right .doc-id { color: #fff; font-size: 17px; font-weight: 800; font-family: monospace; }
  
  /* Status Bar */
  .status-bar {
    background: {{ $lap->status === 'sudah' ? 'linear-gradient(135deg,#e8f5e9,#f1f8e9)' : 'linear-gradient(135deg,#fff3e0,#fff8f0)' }};
    border-bottom: 2px solid {{ $lap->status === 'sudah' ? '#a5d6a7' : '#ffcc80' }};
    padding: 9px 32px; display: flex; align-items: center; justify-content: space-between;
    font-size: 12px; font-weight: 700; color: {{ $lap->status === 'sudah' ? '#2e7d32' : '#e65100' }};
  }

  /* Body Dokumen */
  .doc-body {
    padding: 20px 32px 16px;
    display: flex;
    flex-direction: column;
    gap: 16px;
  }
  
  .main-sections {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .section-title {
    font-size: 11px;
    font-weight: 800;
    color: #008fa3;
    text-transform: uppercase;
    letter-spacing: .8px;
    margin-bottom: 8px;
    padding-bottom: 4px;
    border-bottom: 1.5px solid #d0e8ef;
  }

  /* 1. Info Grid */
  .info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px 28px;
    background: linear-gradient(135deg, #f7fbfd, #eef7fa);
    border: 1.5px solid #cce5ee;
    border-radius: 8px;
    padding: 12px 18px;
  }
  .info-item label { display: block; font-size: 9.5px; font-weight: 700; color: #008fa3; text-transform: uppercase; letter-spacing: .6px; margin-bottom: 2px; }
  .info-item p { font-size: 12px; font-weight: 600; color: #1a2332; }

  /* 2. Satwa Table */
  .satwa-table { width: 100%; border-collapse: collapse; font-size: 12px; }
  .satwa-table th { background: linear-gradient(135deg, #00A9C1, #007fa3); color: #fff; padding: 8px 12px; text-align: left; font-weight: 600; font-size: 11px; }
  .satwa-table td { padding: 8px 12px; border-bottom: 1px solid #d0e8ef; }
  .satwa-table tr:nth-child(even) td { background: #f7fbfd; }

  /* 3. Kotak Terpadu 4 Grid (2x2) */
  .section-obs {
    display: flex;
    flex-direction: column;
  }
  .obs-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-template-rows: 1fr 1fr;
    gap: 12px 18px;
    background: linear-gradient(135deg, #f7fbfd, #eef7fa);
    border: 1.5px solid #cce5ee;
    border-radius: 8px;
    padding: 14px 18px;
    min-height: 240px;
  }
  .obs-item {
    background: #ffffff;
    border: 1.5px solid #dcecf2;
    border-radius: 7px;
    padding: 12px 16px;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    min-height: 105px;
  }
  .obs-item label {
    display: block;
    font-size: 10px;
    font-weight: 800;
    color: #008fa3;
    text-transform: uppercase;
    letter-spacing: .7px;
    margin-bottom: 6px;
  }
  .obs-item p {
    font-size: 12px;
    font-weight: 500;
    color: #1a2332;
    line-height: 1.55;
  }

  /* 4. Area Bawah: Autentikasi Sistem & Tanda Tangan */
  .bottom-row {
    margin-top: 6px;
    display: flex;
    justify-content: space-between;
    align-items: stretch;
    gap: 20px;
  }
  .auth-box {
    flex: 1;
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    padding: 12px 18px;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }
  .auth-header {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 10px;
    font-weight: 800;
    color: #007fa3;
    letter-spacing: .7px;
    margin-bottom: 7px;
    border-bottom: 1px dashed #cbd5e1;
    padding-bottom: 5px;
  }
  .auth-content p {
    font-size: 11px;
    color: #334155;
    margin-bottom: 4px;
    line-height: 1.4;
  }
  .auth-content p:last-child {
    margin-bottom: 0;
  }
  .auth-status {
    display: inline-block;
    padding: 2px 7px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 700;
  }
  .auth-status.auth-verified {
    background: #dcfce7;
    color: #15803d;
  }
  .auth-status.auth-pending {
    background: #fef3c7;
    color: #b45309;
  }

  /* Tanda Tangan di Pojok Kanan Bawah */
  .ttd-item {
    width: 250px;
    text-align: center;
    border: 1.5px solid #b2d8e8;
    border-radius: 10px;
    padding: 10px 24px;
    background: linear-gradient(135deg, #f0f8fb, #e4f2f7);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    flex-shrink: 0;
  }
  .ttd-item p { font-size: 11px; color: #3b6d85; margin-bottom: 4px; font-weight: 600; }
  .ttd-item .ttd-img-wrap { min-height: 56px; height: 56px; display: flex; align-items: center; justify-content: center; margin-bottom: 4px; }
  .ttd-item .ttd-img-wrap img { max-width: 180px; max-height: 54px; object-fit: contain; }
  .ttd-item strong { font-size: 12px; border-top: 1.5px solid #00A9C1; padding-top: 5px; display: block; color: #1a2332; }
  .ttd-item span { font-size: 10px; color: #64748b; display: block; margin-top: 1px; }

  /* Footer Dokumen */
  .doc-footer {
    padding: 10px 32px;
    background: #f0f8fb;
    border-top: 1.5px solid #d0e8ef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 10.5px;
    color: #64748b;
  }

  /* Print Media Rules: Pengulangan Header, No Berita Acara, & Footer di Setiap Halaman */
  @media print {
    @page {
      size: A4 portrait;
      margin: 8mm 10mm;
    }
    html, body {
      height: auto !important;
      min-height: auto !important;
      margin: 0 !important;
      padding: 0 !important;
      background: #fff !important;
      font-size: 11.5px;
    }
    .toolbar-print { display: none !important; }
    .page-wrapper {
      margin: 0 !important;
      border-radius: 0 !important;
      box-shadow: none !important;
      max-width: 100% !important;
      width: 100% !important;
      height: auto !important;
      min-height: auto !important;
      border: none !important;
      overflow: visible !important;
      background: #fff !important;
    }
    .report-table-wrapper {
      width: 100% !important;
      border-collapse: collapse !important;
    }
    .report-table-wrapper > thead {
      display: table-header-group !important;
    }
    .report-table-wrapper > tfoot {
      display: table-footer-group !important;
    }
    .report-table-wrapper > thead > tr > td {
      page-break-inside: avoid !important;
    }
    .report-table-wrapper > tfoot > tr > td {
      page-break-inside: avoid !important;
    }

    .doc-header, .status-bar, .info-grid, .satwa-table th, .obs-grid, .obs-item, .auth-box, .ttd-item, .doc-footer {
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }

    .doc-body {
      padding: 14px 20px 10px !important;
    }

    /* Tabel Satwa: Paginated secara natural jika baris banyak */
    .satwa-table {
      width: 100% !important;
      page-break-inside: auto !important;
      margin-bottom: 14px !important;
    }
    .satwa-table thead {
      display: table-header-group !important;
    }
    .satwa-table tr {
      page-break-inside: avoid !important;
      page-break-after: auto !important;
    }
    .satwa-table th {
      padding: 7px 10px !important;
      font-size: 11px !important;
    }
    .satwa-table td {
      padding: 6px 10px !important;
      font-size: 11.5px !important;
    }

    /* Grid 2x2 Pengamatan */
    .section-obs {
      page-break-inside: avoid !important;
      margin-top: 14px !important;
      margin-bottom: 14px !important;
    }
    .obs-grid {
      display: grid !important;
      grid-template-columns: 1fr 1fr !important;
      gap: 10px 16px !important;
      min-height: 200px !important;
      page-break-inside: avoid !important;
    }
    .obs-item {
      min-height: 95px !important;
      padding: 10px 14px !important;
      page-break-inside: avoid !important;
    }

    /* Area Bawah: Autentikasi & TTD */
    .bottom-row {
      display: flex !important;
      justify-content: space-between !important;
      gap: 16px !important;
      page-break-inside: avoid !important;
      margin-top: 14px !important;
      margin-bottom: 12px !important;
    }
    .auth-box, .ttd-item {
      page-break-inside: avoid !important;
    }

    /* Footer Dokumen */
    .doc-footer {
      page-break-inside: avoid !important;
    }
  }
</style>
</head>
<body>
<div class="toolbar-print">
  <div>
    <span>Pratinjau Dokumen Dinas</span>
    <h2>Laporan Satwa Liar #{{ str_pad($lap->id, 4, '0', STR_PAD_LEFT) }}</h2>
  </div>
  <div class="btn-group">
    <a href="{{ auth()->check() && auth()->user()->role === 'pegawai' ? route('user.dashboard') : route('admin.dashboard') }}" class="btn-print btn-back">&larr; Kembali</a>
    <a href="{{ auth()->check() && auth()->user()->role === 'admin' ? route('admin.laporan.pdf', $lap->id) : route('laporan.pdf', $lap->id) }}" class="btn-print btn-blue" style="background: linear-gradient(135deg, #059669, #047857);">Unduh PDF Resmi</a>
    <button class="btn-print btn-blue" onclick="window.print()">&#128424; Cetak / Pratinjau</button>
  </div>
</div>

<div class="page-wrapper">
  <table class="report-table-wrapper">
    <!-- REPEATED HEADER: Muncul di Setiap Halaman Cetak Lengkap dengan No. Berita Acara -->
    <thead>
      <tr>
        <td>
          <div class="doc-header">
            <div class="doc-header-logo">
              <img src="{{ asset('images/logo_login.png') }}" alt="InJourney Airports">
            </div>
            <div class="doc-header-text">
              <h1>Laporan Pengamatan Satwa Liar</h1>
              <p>PT Angkasa Pura (Persero) &bull; Wildlife Hazard Management System (WHMS)</p>
            </div>
            <div class="doc-header-right">
              <div class="doc-no">No. Berita Acara</div>
              <div class="doc-id">{{ $lap->no_laporan ?: '#' . str_pad($lap->id, 4, '0', STR_PAD_LEFT) }}</div>
            </div>
          </div>

          <div class="status-bar">
            <div>
              Status: {{ $lap->status === 'sudah' ? 'Telah Ditangani (Terverifikasi)' : 'Belum Ditangani' }} &nbsp;&bull;&nbsp; Tanggal Laporan: {{ $lap->tanggal ? $lap->tanggal->format('d F Y') : '-' }}
            </div>
            <div>
              Tingkat Risiko: <strong>{{ strtoupper($lap->risk_level ?: 'Rendah') }} (Skor {{ $lap->risk_score ?: 1 }}/12)</strong>
            </div>
          </div>
        </td>
      </tr>
    </thead>

    <!-- REPEATED FOOTER: Muncul di Setiap Halaman Cetak -->
    <tfoot>
      <tr>
        <td>
          <div class="doc-footer">
            <p>Dicetak resmi via Wildlife Hazard Management System &bull; PT Angkasa Pura (Persero)</p>
            <p>Dokumen Sah &bull; {{ date('d/m/Y H:i') }} WIB</p>
          </div>
        </td>
      </tr>
    </tfoot>

    <!-- CONTENT BODY: Mengalir secara natural antar halaman -->
    <tbody>
      <tr>
        <td>
          <div class="doc-body">
            <div class="main-sections">
              <!-- 1. Informasi Umum Pemantauan -->
              <div>
                <div class="section-title">Informasi Umum Pemantauan</div>
                <div class="info-grid">
                  <div class="info-item"><label>Nama Petugas</label><p>{{ $lap->nama_petugas }}</p></div>
                  <div class="info-item"><label>Tanggal & Jam Inspeksi</label><p>{{ $lap->tanggal ? $lap->tanggal->format('d F Y') : '-' }} {{ $lap->jam ? '('.substr($lap->jam, 0, 5).' WIB)' : '' }}</p></div>
                  <div class="info-item"><label>Kondisi Cuaca</label><p>{{ $lap->kondisi_cuaca ?: '-' }}</p></div>
                  <div class="info-item"><label>Unit Kerja</label><p>{{ $lap->unit_kerja ?: '-' }}</p></div>
                  <div class="info-item"><label>Area Inspeksi</label><p>{{ $lap->area_inspeksi ?: '-' }}</p></div>
                  <div class="info-item"><label>Grid Koordinat Utama</label><p><strong>{{ $lap->grid_lokasi ?: '-' }}</strong></p></div>
                </div>
              </div>

              <!-- 2. Inventarisasi Satwa Liar -->
              <div>
                <div class="section-title">Inventarisasi Satwa Liar yang Ditemukan</div>
                <table class="satwa-table">
                  <thead>
                    <tr>
                      <th style="width: 40px; text-align: center;">No</th>
                      <th>Nama Jenis Satwa</th>
                      <th style="width: 120px;">Jumlah Ekor</th>
                      <th style="width: 140px;">Titik Grid Koordinat</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($satwa_list as $idx => $sw)
                      <tr>
                        <td style="text-align: center;">{{ $idx + 1 }}</td>
                        <td><strong>{{ $sw['nama'] }}</strong></td>
                        <td>{{ $sw['jumlah'] }} ekor</td>
                        <td><strong>{{ $sw['grid'] ?: '-' }}</strong></td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="4" style="text-align:center; color:#64748b;">Tidak ada satwa yang dilaporkan.</td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>

              <!-- 3. Kotak Terpadu 4 Grid (2x2): Ciri, Kondisi, Aktivitas & Tindak Lanjut -->
              <div class="section-obs">
                <div class="section-title">Pengamatan Lapangan & Tindak Lanjut Mitigasi</div>
                <div class="obs-grid">
                  <!-- Grid 1: Ciri-ciri & Ukuran -->
                  <div class="obs-item">
                    <label>Ciri-ciri & Ukuran Satwa Liar</label>
                    <p>{{ $lap->ciri_ukuran ?: '-' }}</p>
                  </div>

                  <!-- Grid 2: Kondisi Ditemukan -->
                  <div class="obs-item">
                    <label>Kondisi Ditemukan di Sisi Udara</label>
                    <p>{{ $lap->kondisi_apron ?: '-' }}</p>
                  </div>

                  <!-- Grid 3: Aktivitas Satwa -->
                  <div class="obs-item">
                    <label>Aktivitas Satwa di Lapangan</label>
                    <p>{{ $lap->aktivitas_satwa ?: '-' }}</p>
                  </div>

                  <!-- Grid 4: Tindak Lanjut & Metode -->
                  <div class="obs-item">
                    <label>Tindak Lanjut & Metode Pengusiran</label>
                    <p>
                      <strong>Tindakan:</strong> {{ $lap->tindak_lanjut ?: '-' }}<br>
                      <strong>Metode:</strong> {{ $lap->detail_pengusiran ?: '-' }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- 4. Area Bawah: Autentikasi Dokumen Sistem (Kiri) & Tanda Tangan Petugas (Pojok Kanan Bawah) -->
            <div class="bottom-row">
              <!-- Kolom Kiri: Verifikasi & Integritas Dokumen Sistem -->
              <div class="auth-box">
                <div class="auth-header">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#007fa3" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                  <span>AUTENTIKASI & VALIDASI DOKUMEN SISTEM</span>
                </div>
                <div class="auth-content">
                  <p><strong>Nomor Registrasi:</strong> {{ $lap->no_laporan ?: \App\Models\Laporan::generateNoLaporan($lap->unit_kerja ?? 'AMC', $lap->tanggal?->format('Y-m-d'), $lap->id) }}</p>
                  <p><strong>Waktu Terbit:</strong> {{ $lap->created_at ? $lap->created_at->format('d/m/Y H:i') : date('d/m/Y H:i') }} WIB</p>
                  <p><strong>Status Validasi:</strong> <span class="auth-status {{ $lap->status === 'sudah' ? 'auth-verified' : 'auth-pending' }}">{{ $lap->status === 'sudah' ? 'Terverifikasi (Selesai Ditangani)' : 'Tercatat (Menunggu Verifikasi)' }}</span></p>
                  <p><strong>Klasifikasi Risiko:</strong> ICAO Level {{ ucfirst($lap->risk_level ?: 'Rendah') }} (Skor {{ $lap->risk_score ?: 1 }}/12)</p>
                </div>
              </div>

              <!-- Kolom Kanan: Tanda Tangan Petugas di Pojok Kanan Bawah -->
              <div class="ttd-item">
                <p>Petugas Pelapor,</p>
                <div class="ttd-img-wrap">
                  @if($lap->tanda_tangan)
                    <img src="{{ $lap->tanda_tangan }}" alt="Tanda Tangan">
                  @else
                    <span style="color:#94a3b8; font-style:italic;">(Tanda tangan digital terekam)</span>
                  @endif
                </div>
                <strong>{{ $lap->nama_petugas }}</strong>
                <span>{{ $lap->unit_kerja ?: 'Unit Airside Operations' }}</span>
              </div>
            </div>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</div>
</body>
</html>
