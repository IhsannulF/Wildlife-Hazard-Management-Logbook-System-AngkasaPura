<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Satwa Liar #{{ str_pad($lap->id, 4, '0', STR_PAD_LEFT) }} — InJourney Airports</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Arial, sans-serif; background: #dff0f5; color: #1a2332; font-size: 13px; }
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
  .page-wrapper { max-width: 820px; margin: 88px auto 48px; background: #fff; border-radius: 16px; box-shadow: 0 8px 40px rgba(0,169,193,0.15), 0 4px 16px rgba(0,0,0,0.08); overflow: hidden; border: 1px solid rgba(0,169,193,0.15); }
  .doc-header {
    background: linear-gradient(135deg, #002d40 0%, #004d6e 30%, #007fa3 65%, #00A9C1 100%);
    padding: 28px 36px; display: flex; align-items: center; gap: 24px; position: relative; overflow: hidden;
  }
  .doc-header::before { content: ''; position: absolute; top: -50px; right: -50px; width: 220px; height: 220px; border-radius: 50%; background: rgba(255,255,255,0.07); }
  .doc-header-logo {
    width: 190px; height: 90px;
    background: #fff; border-radius: 12px; display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; padding: 8px 14px;
    border: 2px solid rgba(255,255,255,0.95);
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
  }
  .doc-header-logo img { width: 100%; height: 100%; object-fit: contain; }
  .doc-header-text h1 { color: #fff; font-size: 18px; font-weight: 700; }
  .doc-header-text p { color: rgba(255,255,255,.7); font-size: 12px; margin-top: 4px; }
  .doc-header-right { margin-left: auto; text-align: right; position: relative; z-index:1; }
  .doc-header-right .doc-no { color: rgba(255,255,255,.6); font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }
  .doc-header-right .doc-id { color: #fff; font-size: 26px; font-weight: 800; }
  .status-bar {
    background: {{ $lap->status === 'sudah' ? 'linear-gradient(135deg,#e8f5e9,#f1f8e9)' : 'linear-gradient(135deg,#fff3e0,#fff8f0)' }};
    border-bottom: 2px solid {{ $lap->status === 'sudah' ? '#a5d6a7' : '#ffcc80' }};
    padding: 11px 36px; display: flex; align-items: center; gap: 8px;
    font-size: 12.5px; font-weight: 700; color: {{ $lap->status === 'sudah' ? '#2e7d32' : '#e65100' }};
  }
  .doc-body { padding: 28px 36px; }
  .section-title { font-size: 11px; font-weight: 800; color: #00A9C1; text-transform: uppercase; letter-spacing: 1.2px; margin-bottom: 12px; padding-bottom: 7px; border-bottom: 2px solid #d0e8ef; }
  .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 28px; margin-bottom: 24px; background: linear-gradient(135deg, #f7fbfd, #eef7fa); border: 1.5px solid #d0e8ef; border-radius: 10px; padding: 18px; }
  .info-item label { display: block; font-size: 10px; font-weight: 700; color: #00A9C1; text-transform: uppercase; letter-spacing: .7px; margin-bottom: 3px; }
  .info-item p { font-size: 13px; font-weight: 500; color: #1a2332; }
  .text-box { background: linear-gradient(135deg, #f7fbfd, #f0f8fb); border: 1.5px solid #d0e8ef; border-radius: 8px; padding: 12px 14px; font-size: 13px; line-height: 1.6; margin-top: 4px; min-height: 44px; }
  .satwa-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; }
  .satwa-table th { background: linear-gradient(135deg, #00A9C1, #007fa3); color: #fff; padding: 10px 14px; text-align: left; font-weight: 600; font-size: 12px; }
  .satwa-table td { padding: 10px 14px; border-bottom: 1px solid #d0e8ef; }
  .satwa-table tr:nth-child(even) td { background: #f7fbfd; }
  .ttd-box { margin-top: 40px; display: flex; justify-content: center; }
  .ttd-item { text-align: center; border: 1.5px solid #b2d8e8; border-radius: 12px; padding: 16px 40px 14px; background: linear-gradient(135deg, #f0f8fb, #e4f2f7); min-width: 260px; }
  .ttd-item p { font-size: 12px; color: #4a7a90; margin-bottom: 10px; font-weight: 600; }
  .ttd-item .ttd-img-wrap { min-height: 80px; display: flex; align-items: center; justify-content: center; margin-bottom: 6px; }
  .ttd-item .ttd-img-wrap img { max-width: 220px; max-height: 90px; }
  .ttd-item strong { font-size: 14px; border-top: 2px solid #00A9C1; padding-top: 8px; display: block; color: #1a2332; }
  .doc-footer { margin-top: 28px; padding: 14px 36px; background: #f0f8fb; border-top: 2px solid #d0e8ef; display: flex; justify-content: space-between; align-items: center; }
  @media print {
    body { background: #fff; }
    .toolbar-print { display: none !important; }
    .page-wrapper { margin: 0; border-radius: 0; box-shadow: none; max-width: 100%; border: none; }
    .doc-header, .info-grid, .satwa-table th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    @page { size: A4; margin: 12mm 15mm; }
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
    <a href="{{ route('admin.dashboard') }}" class="btn-print btn-back">&larr; Kembali ke Dashboard</a>
    <button class="btn-print btn-blue" onclick="window.print()">&#128424; Cetak / Unduh PDF</button>
  </div>
</div>

<div class="page-wrapper">
  <div class="doc-header">
    <div class="doc-header-logo">
      <img src="{{ asset('images/logo_login.png') }}" alt="InJourney Airports">
    </div>
    <div class="doc-header-text">
      <h1>Laporan Pengamatan Satwa Liar</h1>
      <p>PT Angkasa Pura (Persero)</p>
    </div>
    <div class="doc-header-right">
      <div class="doc-no">No. Laporan</div>
      <div class="doc-id">#{{ str_pad($lap->id, 4, '0', STR_PAD_LEFT) }}</div>
    </div>
  </div>

  <div class="status-bar">
    Status: {{ $lap->status === 'sudah' ? 'Telah Ditangani (Terverifikasi)' : 'Belum Ditangani' }} &nbsp;&bull;&nbsp; Tanggal Laporan: {{ $lap->tanggal ? $lap->tanggal->format('d F Y') : '-' }}
  </div>

  <div class="doc-body">
    <div class="section-title">Informasi Umum Pemantauan</div>
    <div class="info-grid">
      <div class="info-item"><label>Nama Petugas</label><p>{{ $lap->nama_petugas }}</p></div>
      <div class="info-item"><label>Tanggal Inspeksi</label><p>{{ $lap->tanggal ? $lap->tanggal->format('d F Y') : '-' }}</p></div>
      <div class="info-item"><label>Kondisi Cuaca</label><p>{{ $lap->kondisi_cuaca ?: '-' }}</p></div>
      <div class="info-item"><label>Unit Kerja</label><p>{{ $lap->unit_kerja ?: '-' }}</p></div>
      <div class="info-item"><label>Area Inspeksi</label><p>{{ $lap->area_inspeksi ?: '-' }}</p></div>
      <div class="info-item"><label>Grid Koordinat Utama</label><p><strong>{{ $lap->grid_lokasi ?: '-' }}</strong></p></div>
    </div>

    <div class="section-title">Inventarisasi Satwa Liar yang Ditemukan</div>
    <table class="satwa-table">
      <thead>
        <tr>
          <th>No</th>
          <th>Nama Jenis Satwa</th>
          <th>Jumlah Ekor</th>
          <th>Titik Grid Koordinat</th>
        </tr>
      </thead>
      <tbody>
        @forelse($satwa_list as $idx => $sw)
          <tr>
            <td>{{ $idx + 1 }}</td>
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
    <br>

    <div class="section-title">Ciri-ciri & Ukuran Satwa Liar</div>
    <div class="text-box">{{ $lap->ciri_ukuran ?: '-' }}</div><br>

    <div class="section-title">Kondisi Ditemukan di Area Sisi Udara</div>
    <div class="text-box">{{ $lap->kondisi_apron ?: '-' }}</div><br>

    <div class="section-title">Aktivitas Satwa Liar di Lapangan</div>
    <div class="text-box">{{ $lap->aktivitas_satwa ?: '-' }}</div><br>

    <div class="section-title">Tindak Lanjut & Metode Pengusiran</div>
    <div class="text-box">
      <strong>Tindakan:</strong> {{ $lap->tindak_lanjut ?: '-' }}<br>
      <strong>Detail Metode:</strong> {{ $lap->detail_pengusiran ?: '-' }}
    </div>

    <div class="ttd-box">
      <div class="ttd-item">
        <p>Petugas Pelapor,</p>
        <div class="ttd-img-wrap">
          @if($lap->tanda_tangan)
            <img src="{{ $lap->tanda_tangan }}" alt="Tanda Tangan">
          @else
            <span style="color:#94a3b8; font-style:italic;">(Tidak ada tanda tangan)</span>
          @endif
        </div>
        <strong>{{ $lap->nama_petugas }}</strong>
        <span style="font-size:11px; color:#64748b;">{{ $lap->unit_kerja }}</span>
      </div>
    </div>
  </div>

  <div class="doc-footer">
    <p>Dicetak otomatis melalui Portal Satwa Liar — PT Angkasa Pura (Persero)</p>
    <p>{{ date('d/m/Y H:i') }}</p>
  </div>
</div>
</body>
</html>
