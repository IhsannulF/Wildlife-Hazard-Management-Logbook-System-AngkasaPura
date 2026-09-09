<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
require_once 'koneksi.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID laporan tidak valid.');
}

$id = (int) $_GET['id'];

$stmt = $pdo->prepare(
    "SELECT l.*, u.nama AS nama_user
     FROM laporan l
     LEFT JOIN users u ON l.user_id = u.id
     WHERE l.id = ?"
);
$stmt->execute([$id]);
$lap = $stmt->fetch();

if (!$lap) {
    die('Laporan tidak ditemukan.');
}

$stmt2 = $pdo->prepare("
    SELECT ds.nama_satwa AS nama, ds.jumlah, ds.grid,
           COALESCE(js.foto_path, '') AS foto_path
    FROM detail_satwa ds
    LEFT JOIN jenis_satwa js ON js.nama COLLATE utf8mb4_general_ci = ds.nama_satwa
    WHERE ds.laporan_id = ? AND ds.jumlah > 0
");
$stmt2->execute([$id]);
$satwa_list = $stmt2->fetchAll();

// Ambil foto extra (satwa tidak terdaftar)
$stmt3 = $pdo->prepare("SELECT nama_file FROM foto_laporan WHERE laporan_id = ? AND tipe = 'extra' ORDER BY id ASC");
$stmt3->execute([$id]);
$foto_extra = $stmt3->fetchAll(PDO::FETCH_COLUMN);

function valField($v) {
    $v = html_entity_decode($v ?? '', ENT_QUOTES, 'UTF-8');
    return $v !== '' ? htmlspecialchars($v) : '<span style="color:#aaa">—</span>';
}
$tgl_fmt = $lap['tanggal'] ? date('d F Y', strtotime($lap['tanggal'])) : '—';
$status_label = $lap['status'] === 'sudah' ? 'Sudah Ditangani' : 'Belum Ditangani';
$status_color = $lap['status'] === 'sudah' ? '#2e7d32' : '#e65100';
$ttd = $lap['tanda_tangan'] ?? '';
$ttd_is_image = strpos($ttd, 'data:image') === 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Satwa Liar #<?= $id ?> — Injourney Airports</title>
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
  .btn-print { padding: 9px 22px; border-radius: 8px; font-family: inherit; font-size: 13px; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 7px; transition: all 0.2s; }
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
  .doc-header::after { content: ''; position: absolute; bottom: -60px; right: 100px; width: 160px; height: 160px; border-radius: 50%; background: rgba(0,169,193,0.15); }
  .doc-header-logo {
    width: 190px; height: 100px;
    background: linear-gradient(135deg, #ffffff 60%, #e8f8fb 100%);
    border-radius: 12px; display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; padding: 8px 14px;
    border: 2px solid rgba(255,255,255,0.95); overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.2), inset 0 1px 0 rgba(255,255,255,0.8);
  }
  .doc-header-logo img { width: 100%; height: 100%; object-fit: contain; display: block; }
  .doc-header-text h1 { color: #fff; font-size: 17px; font-weight: 700; }
  .doc-header-text p { color: rgba(255,255,255,.7); font-size: 12px; margin-top: 4px; }
  .doc-header-right { margin-left: auto; text-align: right; position: relative; z-index:1; }
  .doc-header-right .doc-no { color: rgba(255,255,255,.6); font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }
  .doc-header-right .doc-id { color: #fff; font-size: 26px; font-weight: 800; }
  .status-bar {
    background: <?= $lap['status'] === 'sudah' ? 'linear-gradient(135deg,#e8f5e9,#f1f8e9)' : 'linear-gradient(135deg,#fff3e0,#fff8f0)' ?>;
    border-bottom: 2px solid <?= $lap['status'] === 'sudah' ? '#a5d6a7' : '#ffcc80' ?>;
    padding: 11px 36px; display: flex; align-items: center; gap: 8px;
    font-size: 12.5px; font-weight: 700; color: <?= $status_color ?>;
  }
  .status-dot { width: 8px; height: 8px; border-radius: 50%; background: <?= $status_color ?>; flex-shrink: 0; }
  .doc-body { padding: 28px 36px; }
  .section-title { font-size: 10px; font-weight: 800; color: #00A9C1; text-transform: uppercase; letter-spacing: 1.2px; margin-bottom: 12px; padding-bottom: 7px; border-bottom: 2px solid #d0e8ef; display: flex; align-items: center; gap: 8px; }
  .section-title::before { content: ''; display: inline-block; width: 3px; height: 12px; background: linear-gradient(135deg, #00A9C1, #007fa3); border-radius: 2px; }
  .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 28px; margin-bottom: 24px; background: linear-gradient(135deg, #f7fbfd, #eef7fa); border: 1.5px solid #d0e8ef; border-radius: 10px; padding: 18px; }
  .info-item label { display: block; font-size: 10px; font-weight: 700; color: #00A9C1; text-transform: uppercase; letter-spacing: .7px; margin-bottom: 3px; }
  .info-item p { font-size: 13px; font-weight: 500; color: #1a2332; }
  .text-box { background: linear-gradient(135deg, #f7fbfd, #f0f8fb); border: 1.5px solid #d0e8ef; border-radius: 8px; padding: 12px 14px; font-size: 13px; line-height: 1.6; margin-top: 4px; min-height: 44px; }
  .satwa-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; }
  .satwa-table th { background: linear-gradient(135deg, #00A9C1, #007fa3); color: #fff; padding: 10px 14px; text-align: left; font-weight: 600; font-size: 12px; }
  .satwa-table td { padding: 10px 14px; border-bottom: 1px solid #d0e8ef; }
  .satwa-table tr:last-child td { border-bottom: none; }
  .satwa-table tr:nth-child(even) td { background: #f7fbfd; }
  .ttd-box { margin-top: 40px; display: flex; justify-content: center; }
  .ttd-item { text-align: center; border: 1.5px solid #b2d8e8; border-radius: 12px; padding: 16px 40px 14px; background: linear-gradient(135deg, #f0f8fb, #e4f2f7); min-width: 260px; }
  .ttd-item p { font-size: 12px; color: #4a7a90; margin-bottom: 10px; font-weight: 600; letter-spacing: 0.3px; }
  .ttd-item .ttd-img-wrap { min-height: 80px; display: flex; align-items: center; justify-content: center; margin-bottom: 6px; }
  .ttd-item .ttd-img-wrap img { max-width: 220px; max-height: 90px; }
  .ttd-item strong { font-size: 14px; border-top: 2px solid #00A9C1; padding-top: 8px; display: block; color: #1a2332; }
  .doc-footer { margin-top: 28px; padding: 14px 36px; background: linear-gradient(135deg, #f0f8fb, #e8f4f8); border-top: 2px solid #d0e8ef; display: flex; justify-content: space-between; align-items: center; }
  .doc-footer p { font-size: 11px; color: #6b7a90; }
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
    <span>Pratinjau Dokumen</span>
    <h2>Laporan Satwa Liar #<?= str_pad($id, 4, '0', STR_PAD_LEFT) ?></h2>
  </div>
  <div class="btn-group">
    <button class="btn-print btn-back" onclick="window.location.href='admin_dashboard.php'">← Kembali</button>
    <button class="btn-print btn-blue" onclick="window.print()">⬇ Unduh PDF</button>
  </div>
</div>

<div class="page-wrapper">
  <div class="doc-header">
    <div class="doc-header-logo">
      <img src="logo_login.png" alt="Injourney Airports">
    </div>
    <div class="doc-header-text">
      <h1>Laporan Satwa Liar di Area Bandara</h1>
      <p>Injourney Airports</p>
    </div>
    <div class="doc-header-right">
      <div class="doc-no">No. Laporan</div>
      <div class="doc-id">#<?= str_pad($id, 4, '0', STR_PAD_LEFT) ?></div>
    </div>
  </div>

  <div class="status-bar">
    <div class="status-dot"></div>
    Status: <?= $status_label ?> &nbsp;·&nbsp; Tanggal Laporan: <?= $tgl_fmt ?>
  </div>

  <div class="doc-body">
    <div class="section-title">Informasi Umum</div>
    <div class="info-grid">
      <div class="info-item"><label>Nama Petugas</label><p><?= valField($lap['nama_petugas']) ?></p></div>
      <div class="info-item"><label>Tanggal Inspeksi</label><p><?= $tgl_fmt ?></p></div>
      <div class="info-item"><label>Kondisi Cuaca</label><p><?= valField($lap['kondisi_cuaca']) ?></p></div>
      <div class="info-item"><label>Unit Kerja</label><p><?= valField($lap['unit_kerja']) ?></p></div>
      <div class="info-item"><label>Area Inspeksi</label><p><?= valField($lap['area_inspeksi']) ?></p></div>
    </div>

    <div class="section-title">Ciri-ciri & Ukuran Satwa Liar</div>
    <div class="text-box"><?= valField($lap['ciri_ukuran']) ?></div><br>

    <div class="section-title">Kondisi Ditemukan di Area Apron</div>
    <div class="text-box"><?= valField($lap['kondisi_apron']) ?></div><br>

    <div class="section-title">Aktivitas Satwa Liar di Area Apron</div>
    <div class="text-box"><?= valField($lap['aktivitas_satwa']) ?></div><br>

    <div class="section-title">Tindak Lanjut yang Dilakukan</div>
    <div class="text-box"><?= valField($lap['tindak_lanjut']) ?></div><br>

    <div class="section-title">Detail Pengusiran yang Dilakukan</div>
    <div class="text-box"><?= valField($lap['detail_pengusiran']) ?></div><br>

    <?php if (!empty($satwa_list)): ?>
    <div class="section-title">Jenis Satwa yang Ditemukan</div>
      <table class="satwa-table">
        <thead><tr><th>#</th><th>Nama Satwa</th><th>Jumlah</th><th>Lokasi Grid</th></tr></thead>
        <tbody>
          <?php foreach ($satwa_list as $i => $s): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($s['nama']) ?></td>
            <td><?= htmlspecialchars($s['jumlah']) ?></td>
            <td><?= htmlspecialchars($s['grid'] ?: '-') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <?php if (!empty($foto_extra)): ?>
    <br>
    <div class="section-title">Foto Satwa Tidak Terdaftar</div>
    <div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:8px;">
        <?php foreach ($foto_extra as $fname): ?>
        <div style="width:160px;height:130px;border-radius:8px;overflow:hidden;border:1.5px solid #d0e8ef;">
            <img src="uploads/extra/<?= htmlspecialchars($fname) ?>"
                 style="width:100%;height:100%;object-fit:cover;"
                 alt="Foto Satwa Tidak Terdaftar">
        </div>
        <?php endforeach; ?>
    </div>
    <br>
    <?php endif; ?>

    <div class="ttd-box">
      <div class="ttd-item">
        <p>Petugas Pelaksana</p>
        <div class="ttd-img-wrap">
          <?php if ($ttd_is_image): ?>
            <img src="<?= htmlspecialchars($ttd) ?>" alt="Tanda Tangan">
          <?php else: ?>
            <span style="color:#aaa;font-size:12px">Belum ada tanda tangan</span>
          <?php endif; ?>
        </div>
        <strong><?= valField($lap['nama_petugas']) ?></strong>
      </div>
    </div>
  </div>

  <div class="doc-footer">
    <p>Dicetak pada: <?= date('d F Y, H:i') ?> WIB</p>
    <p>Injourney Airports — Sistem Logbook Satwa Liar</p>
    <p>Dokumen ini bersifat resmi</p>
  </div>
</div>
<script>
  const params = new URLSearchParams(window.location.search);
  if (params.get('print') === '1') {
    window.addEventListener('load', () => setTimeout(() => window.print(), 500));
  }
</script>
</body>
</html>