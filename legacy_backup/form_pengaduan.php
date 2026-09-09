<?php
// ============================================================
// FILE   : form_pengaduan.php
// FUNGSI : Form pengaduan satwa liar
// ============================================================
session_start();

// Cek login — role pegawai
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'pegawai') {
    header("Location: login.php"); exit;
}

require_once 'koneksi.php';

// Ambil opsi dropdown dari database (dikelola admin di Report > Field Form)
// Fallback ke array default jika field/opsi belum diatur di database
function getDropdownOpsi($pdo, $namaField, $fallback) {
    try {
        $stmt = $pdo->prepare(
            "SELECT o.nilai
             FROM form_field_options o
             JOIN form_fields f ON f.id = o.field_id
             WHERE f.nama_field = ?
             ORDER BY o.id ASC"
        );
        $stmt->execute([$namaField]);
        $opsi = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return !empty($opsi) ? $opsi : $fallback;
    } catch (Exception $e) {
        return $fallback;
    }
}

$kondisi_cuaca_opt = getDropdownOpsi($pdo, 'kondisi_cuaca', ['Cerah','Berawan','Hujan Ringan','Hujan Lebat','Berkabut']);
$unit_kerja_opt    = getDropdownOpsi($pdo, 'unit_kerja',    ['Apron Movement Control','Airport Rescue & Fire Fighting','Airport Security','SMS & OHS']);
$area_opt          = getDropdownOpsi($pdo, 'area_inspeksi', ['Apron','Taxiway','Runway','Perimeter','Terminal','Gedung Operasional','Area Parkir']);
$kondisi_apron_opt = getDropdownOpsi($pdo, 'kondisi_apron', ['Hidup','Mati','Tidak ditemukan']);
$tindak_lanjut_opt = getDropdownOpsi($pdo, 'tindak_lanjut', ['Telah ditangani','Belum ditangani']);

// ── Auto-fill Unit Kerja sesuai divisi (jabatan) user yang login ──
$mapJabatanUnitKerja = [
    'AMC Divisi'      => 'Apron Movement Control',
    'ARFF Divisi'     => 'Airport Rescue & Fire Fighting',
    'Security Divisi' => 'Airport Security',
    'SMSOHS Divisi'   => 'SMS & OHS',
];
$stmtUser = $pdo->prepare("SELECT namalengkap FROM users WHERE username = ?");
$stmtUser->execute([$_SESSION['username']]);
$jabatanUser  = $stmtUser->fetchColumn() ?: '';
$unitKerjaUser = $mapJabatanUnitKerja[$jabatanUser] ?? '';

// Ambil label field dari database supaya sinkron dengan pengaturan admin
$fieldLabels  = [];
$aktifFields  = [];
try {
    $stmtFields = $pdo->query("SELECT nama_field, label FROM form_fields WHERE aktif=1");
    foreach ($stmtFields->fetchAll(PDO::FETCH_ASSOC) as $f) {
        $fieldLabels[$f['nama_field']] = $f['label'];
        $aktifFields[$f['nama_field']] = true;
    }
} catch (Exception $e) {}
// Fallback label default
$labelNama      = $fieldLabels['nama_petugas']   ?? 'Nama Petugas';
$labelTanggal   = $fieldLabels['tanggal']        ?? 'Tanggal Pemantauan';
$labelCuaca     = $fieldLabels['kondisi_cuaca']  ?? 'Kondisi Cuaca';
$labelUnitKerja = $fieldLabels['unit_kerja']     ?? 'Unit Kerja';
$labelArea      = $fieldLabels['area_inspeksi']  ?? 'Area Inspeksi';

// Ambil daftar satwa dari database (dikelola admin di laporan.php)
$satwa_list = $pdo->query("SELECT id, nama, foto_path FROM jenis_satwa ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

// Ambil path gambar gridmap dari DB (dikelola admin di laporan.php tab Field Form)
// Cek dulu apakah kolom gridmap_path sudah ada, jika belum fallback ke file default
try {
    $gridField    = $pdo->query("SELECT gridmap_path FROM form_fields WHERE tipe='grid' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $gridmapImage = !empty($gridField['gridmap_path']) ? $gridField['gridmap_path'] : 'gridmap_injourney.jpeg';
} catch (Exception $e) {
    $gridmapImage = 'gridmap_injourney.jpeg';
}

// Ambil path gambar gridmap dari DB (dikelola admin di laporan.php tab Field Form)
$gridField    = $pdo->query("SELECT gridmap_path FROM form_fields WHERE tipe='grid' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$gridmapImage = $gridField['gridmap_path'] ?? 'gridmap_injourney.jpeg';

$pesan_sukses = !empty($_GET['sukses']) ? htmlspecialchars($_GET['sukses'], ENT_QUOTES, 'UTF-8') : '';
$pesan_error  = !empty($_GET['error'])  ? htmlspecialchars($_GET['error'],  ENT_QUOTES, 'UTF-8') : '';

// Restore isian form jika ada error (data disimpan sementara di session)
$fd = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);

// Helper untuk ambil nilai lama saat error
function old($key, $default='') {
    global $fd;
    return htmlspecialchars($fd[$key] ?? $default, ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengaduan Satwa Liar</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --teal:    #2ab3c0;
            --teal-h:  #229aa6;
            --text:    #1a2332;
            --muted:   #5a7a8a;
            --border:  #cce3f0;
            --bg:      #eef6fb;
            --surface: #ffffff;
            --red:     #e05252;
            --green:   #7daf5b;
            --font:    'DM Sans', sans-serif;
            --radius:  10px;
        }

        body {
            font-family: var(--font);
            color: var(--text);
            min-height: 100dvh;
            /* Watermark InJourney sebagai background berulang */
            background-color: var(--bg);
            background-image: url('watermark_injourney.jpeg');
            background-repeat: repeat;
            background-size: 280px auto;
            background-attachment: fixed;
        }

        /* ── TOPBAR ── */
        .topbar {
            background: rgba(255,255,255,0.95);
            border-bottom: 1px solid var(--border);
            padding: 0 32px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky; top: 0; z-index: 50;
            box-shadow: 0 1px 6px rgba(0,0,0,0.07);
        }

        .topbar-logo img {
            height: 32px;
            object-fit: contain;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-chip {
            display: flex; align-items: center; gap: 8px;
            background: #e8f8fa;
            border: 1px solid rgba(42,179,192,0.25);
            border-radius: 20px;
            padding: 4px 12px 4px 5px;
        }

        .avatar {
            width: 26px; height: 26px; border-radius: 50%;
            background: var(--teal); color: white;
            font-size: 11px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
        }

        .user-label { font-size: 12px; font-weight: 500; color: var(--text); }

        .logout-btn {
            display: flex; align-items: center; gap: 5px;
            padding: 7px 13px;
            background: rgba(220,38,38,0.07);
            border: 1px solid rgba(220,38,38,0.2);
            border-radius: 8px;
            color: var(--red);
            font-family: var(--font); font-size: 13px; font-weight: 500;
            text-decoration: none; transition: background 0.15s;
        }
        .logout-btn:hover { background: rgba(220,38,38,0.13); }
        .logout-btn svg { width: 14px; height: 14px; }

        /* ── COLOR BAR INJOURNEY ── */
        .brand-colorbar {
            height: 4px;
            width: 100%;
            background: linear-gradient(90deg,#00A9C1,#4FADC9,#88B146,#F0B14B,#D94F4F);
        }


        /* ── KONTEN UTAMA ── */
        .page-wrap {
            max-width: 760px;
            margin: 0 auto;
            padding: 32px 20px 64px;
        }

        /* Judul halaman */
        .page-header {
            margin-bottom: 24px;
        }

        .page-header h1 {
            font-size: 22px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 4px;
        }

        .page-header p {
            font-size: 13px;
            color: var(--muted);
        }

        /* ── SECTION CARD ── */
        .section-card {
            background: rgba(255, 255, 255, 0.96);
            border: 1.5px solid rgba(42, 179, 192, 0.35);
            border-radius: 14px;
            padding: 24px 28px;
            margin-bottom: 20px;
            /* Efek glow teal bersinar di sekeliling kotak */
            box-shadow:
                0 0 0 3px rgba(42, 179, 192, 0.07),
                0 0 20px rgba(42, 179, 192, 0.12),
                0 4px 24px rgba(0, 0, 0, 0.07);
            transition: box-shadow 0.3s ease;
        }

        /* Glow lebih terang saat kotak di-hover */
        .section-card:hover {
            box-shadow:
                0 0 0 3px rgba(42, 179, 192, 0.12),
                0 0 32px rgba(42, 179, 192, 0.22),
                0 6px 32px rgba(0, 0, 0, 0.09);
            border-color: rgba(42, 179, 192, 0.55);
        }

        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--text);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: center;
            margin-bottom: 6px;
        }

        .section-hint {
            font-size: 12px;
            color: var(--muted);
            text-align: center;
            margin-bottom: 22px;
        }

        .req { color: var(--red); }

        /* ── FIELD ── */
        .field { margin-bottom: 18px; }

        .field label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--text);
            margin-bottom: 6px;
        }

        .field input[type="text"],
        .field input[type="date"],
        .field select,
        .field textarea {
            width: 100%;
            padding: 10px 13px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-family: var(--font);
            font-size: 14px;
            color: var(--text);
            background: #fff;
            outline: none;
            appearance: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .field input:focus,
        .field select:focus,
        .field textarea:focus {
            border-color: var(--teal);
            box-shadow: 0 0 0 3px rgba(42,179,192,0.15);
        }

        .field textarea { resize: vertical; min-height: 72px; }

        .field select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%235a7a8a' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 36px;
        }

        /* ── GRIDMAP ── */
        .gridmap-note { font-size: 12px; color: var(--muted); margin-top: 6px; }

        .gridmap-input-row {
            display: flex; align-items: center; gap: 10px;
            flex-wrap: wrap; margin-top: 12px;
        }

        .gridmap-input-row label { font-size: 13px; font-weight: 500; white-space: nowrap; }

        .gridmap-input-row input {
            width: 100px; padding: 9px 12px;
            border: 1px solid var(--border); border-radius: 8px;
            font-family: var(--font); font-size: 14px; color: var(--text);
            background: #fff; outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .gridmap-input-row input:focus {
            border-color: var(--teal);
            box-shadow: 0 0 0 3px rgba(42,179,192,0.15);
        }

        /* ── SATWA GRID ── */
        .satwa-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        .satwa-card {
            background: var(--surface);
            border: 2px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            transition: border-color 0.2s, box-shadow 0.15s, transform 0.15s;
            position: relative;
        }

        .satwa-card:hover {
            border-color: var(--teal);
            transform: translateY(-2px);
            box-shadow: 0 4px 14px rgba(42,179,192,0.2);
        }

        .satwa-card.selected {
            border-color: var(--teal);
            background: #f0fbfc;
            box-shadow: 0 0 0 3px rgba(42,179,192,0.18);
        }

        /* Gambar area atas kartu */
        .satwa-icon-wrap {
            width: 100%;
            height: 160px;
            background: #ddeef7;
            overflow: hidden;
            flex-shrink: 0;
        }

        .satwa-icon-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* Badge selected */
        .satwa-card.selected .satwa-check {
            display: flex !important;
        }
        .satwa-check {
            display: none;
            position: absolute; top: 6px; right: 6px;
            width: 20px; height: 20px;
            background: var(--teal); color: white;
            border-radius: 50%; font-size: 11px; font-weight: 700;
            align-items: center; justify-content: center;
            pointer-events: none;
        }

        /* Info bawah kartu */
        .satwa-info { padding: 7px 9px 4px; }
        .satwa-name { font-size: 11px; font-weight: 600; color: var(--text); margin-bottom: 0; line-height: 1.3; }

        /* ── LOKASI ROWS ── */
        .lokasi-rows { padding: 0 9px 9px; display: none; flex-direction: column; gap: 6px; }
        .satwa-card.selected .lokasi-rows { display: flex; }

        /* Baris lokasi di kartu satwa: grid agar tombol × selalu muat */
        .satwa-card .lokasi-row {
            display: grid;
            grid-template-columns: auto minmax(0,1fr) auto minmax(0,1fr) 22px;
            align-items: center; gap: 4px;
            background: #f0fbfc; border: 1px solid rgba(42,179,192,0.25);
            border-radius: 7px; padding: 5px 6px;
        }
        .satwa-card .lokasi-row input[type="number"],
        .satwa-card .lokasi-row input[type="text"] {
            border: 1px solid var(--border); border-radius: 5px;
            font-family: var(--font); font-size: 11px; color: var(--text);
            background: #fff; outline: none; padding: 3px 4px;
            transition: border-color 0.2s; width: 100%; min-width: 0;
            text-transform: uppercase;
        }
        .satwa-card .lokasi-row input:focus { border-color: var(--teal); }
        .satwa-card .lokasi-row label { font-size: 10px; color: var(--muted); white-space: nowrap; }

        /* Baris lokasi di upload extra: lebih luas, pakai flex biasa */
        .extra-upload-box .lokasi-row {
            display: flex; align-items: center; gap: 6px;
            background: #f0fbfc; border: 1px solid rgba(42,179,192,0.25);
            border-radius: 7px; padding: 5px 8px;
        }
        .extra-upload-box .lokasi-row input[type="number"] { width: 54px; }
        .extra-upload-box .lokasi-row input[type="text"]   { width: 80px; text-transform: uppercase; }
        .extra-upload-box .lokasi-row input[type="number"],
        .extra-upload-box .lokasi-row input[type="text"] {
            border: 1px solid var(--border); border-radius: 5px;
            font-family: var(--font); font-size: 12px; color: var(--text);
            background: #fff; outline: none; padding: 4px 6px;
            transition: border-color 0.2s;
        }
        .extra-upload-box .lokasi-row input:focus { border-color: var(--teal); }
        .extra-upload-box .lokasi-row label { font-size: 10px; color: var(--muted); white-space: nowrap; }

        .btn-hapus-row {
            width: 20px; height: 20px; border-radius: 50%; border: none;
            background: var(--red); color: white; font-size: 13px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; flex-shrink: 0; line-height: 1;
            transition: opacity 0.15s;
        }
        .btn-hapus-row:hover { opacity: 0.8; }

        .btn-tambah-lokasi {
            display: none; align-items: center; gap: 4px;
            margin: 4px 9px 8px; padding: 4px 10px;
            background: transparent; border: 1px dashed rgba(42,179,192,0.5);
            border-radius: 6px; color: var(--teal);
            font-family: var(--font); font-size: 11px; font-weight: 600;
            cursor: pointer; transition: background 0.15s;
        }
        .btn-tambah-lokasi:hover { background: rgba(42,179,192,0.08); }
        .satwa-card.selected .btn-tambah-lokasi { display: flex; }

        /* ── UPLOAD TAMBAHAN ── */
        .extra-upload-box {
            border: 2px dashed var(--border);
            border-radius: 10px;
            padding: 18px 20px;
            background: #f7fbfe;
            transition: border-color 0.2s;
            margin-bottom: 8px;
        }
        .extra-upload-box:hover { border-color: var(--teal); }

        .extra-upload-title { font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 4px; }
        .extra-upload-hint  { font-size: 12px; color: var(--muted); margin-bottom: 12px; line-height: 1.5; }

        .upload-trigger {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 8px 18px; background: var(--teal); color: white;
            border: none; border-radius: 7px; font-family: var(--font);
            font-size: 13px; font-weight: 500; cursor: pointer; transition: background 0.2s;
        }
        .upload-trigger:hover { background: var(--teal-h); }
        .upload-trigger svg { width: 14px; height: 14px; }

        .upload-filename { font-size: 12px; color: var(--muted); margin-left: 10px; }

        .extra-preview-wrap { margin-top: 10px; display: none; gap: 8px; flex-wrap: wrap; }
        .extra-preview-wrap img { width: 90px; height: 70px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border); }

        /* ── TOMBOL ── */
        .btn-row { display: flex; justify-content: space-between; gap: 12px; margin-top: 16px; }

        .btn-back {
            display: flex; align-items: center; gap: 6px;
            padding: 11px 24px;
            background: transparent; color: var(--muted);
            border: 1px solid var(--border); border-radius: 8px;
            font-family: var(--font); font-size: 13px; cursor: pointer;
            text-decoration: none; transition: all 0.15s;
        }
        .btn-back:hover { background: #e8f4fb; color: var(--text); }
        .btn-back svg { width: 14px; height: 14px; }

        .btn-next {
            display: flex; align-items: center; gap: 8px;
            padding: 11px 28px;
            background: var(--teal); color: white;
            border: none; border-radius: 8px;
            font-family: var(--font); font-size: 14px; font-weight: 600;
            cursor: pointer; transition: background 0.2s, transform 0.15s;
            box-shadow: 0 4px 14px rgba(42,179,192,0.3);
        }
        .btn-next:hover { background: var(--teal-h); transform: translateY(-1px); }
        .btn-next svg { width: 15px; height: 15px; }

        .btn-submit {
            display: flex; align-items: center; gap: 8px;
            padding: 12px 32px;
            background: var(--green); color: white;
            border: none; border-radius: 8px;
            font-family: var(--font); font-size: 14px; font-weight: 700;
            cursor: pointer; transition: opacity 0.2s;
            box-shadow: 0 4px 14px rgba(125,175,91,0.3);
        }
        .btn-submit:hover { opacity: 0.9; }
        .btn-submit svg { width: 15px; height: 15px; }

        /* ── FOOTER ── */
        .page-footer {
            background: linear-gradient(135deg, #c8e8f5, #d0ecf7);
            padding: 24px;
            text-align: center;
            margin-top: 16px;
        }

        .footer-logo img { height: 80px; width: auto; object-fit: contain; }

        /* ── RESPONSIF ── */
        @media (max-width: 768px) {
            .topbar { padding: 0 16px; }
            .user-label { display: none; }
            .page-wrap { padding: 20px 14px 48px; }
            .section-card { padding: 18px 16px; }
            .satwa-grid { grid-template-columns: repeat(2, 1fr); gap: 8px; }
            .btn-row { flex-direction: column-reverse; }
            .btn-back, .btn-next, .btn-submit { width: 100%; justify-content: center; }
        }

        @media (max-width: 420px) {
            .page-wrap { padding-left: 10px; padding-right: 10px; }
        }
    </style>
</head>
<body>

<!-- TOPBAR -->
<header class="topbar">
    <div class="topbar-logo">
        <img src="logo_injourney.jpeg" alt="InJourney Airports"
             onerror="this.style.display='none'">
    </div>
    <div class="topbar-right">
        <div class="user-chip">
            <div class="avatar"><?= strtoupper(substr($_SESSION['username'],0,2)) ?></div>
            <div class="user-label"><?= htmlspecialchars($_SESSION['username'],ENT_QUOTES,'UTF-8') ?></div>
        </div>
        <a href="logout.php" class="logout-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            <span>Logout</span>
        </a>
    </div>
</header>

<!-- Color bar brand InJourney Airports -->
<div class="brand-colorbar" aria-hidden="true"></div>








<!-- KONTEN -->
<div class="page-wrap">

    <!-- Judul halaman -->
    <div class="page-header">
        <h1>Form Pengaduan Satwa Liar</h1>
        <p>Isi semua field yang wajib diisi dengan benar sebelum mengirim laporan.</p>
    </div>

    <form method="POST" action="proses_laporan.php" enctype="multipart/form-data" id="mainForm">

        <!-- ═══ SECTION 1: ISI LAPORAN ═══ -->
        <div class="section-card">
            <div class="section-title">ISI LAPORAN</div>
            <div class="section-hint">Field dengan tanda "<span class="req">*</span>" wajib untuk diisi</div>

            <?php if(isset($aktifFields['nama_petugas'])): ?>
            <div class="field">
                <label><?= htmlspecialchars($labelNama) ?> <span class="req">*</span></label>
                <input type="text" name="nama_petugas"
                       placeholder="Masukkan nama lengkap petugas" value="<?= old('nama_petugas') ?>" required>
            </div>
            <?php endif; ?>

            <?php if(isset($aktifFields['tanggal'])): ?>
            <div class="field">
                <label><?= htmlspecialchars($labelTanggal) ?> <span class="req">*</span></label>
                <input type="date" name="tanggal"
                       value="<?= date('Y-m-d') ?>" required>
            </div>
            <?php endif; ?>

            <?php if(isset($aktifFields['kondisi_cuaca'])): ?>
            <div class="field">
                <label><?= htmlspecialchars($labelCuaca) ?> <span class="req">*</span></label>
                <select name="kondisi_cuaca" required>
                    <option value="" disabled selected>-- Pilih kondisi cuaca --</option>
                    <?php foreach($kondisi_cuaca_opt as $k): ?>
                    <option><?= $k ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <?php if(isset($aktifFields['unit_kerja'])): ?>
            <div class="field">
                <label><?= htmlspecialchars($labelUnitKerja) ?><span class="req">*</span></label>
                <?php if ($unitKerjaUser): ?>
                <select name="unit_kerja" disabled style="background:#f0f4f7;color:#555;cursor:not-allowed;">
                    <option selected><?= htmlspecialchars($unitKerjaUser) ?></option>
                </select>
                <input type="hidden" name="unit_kerja" value="<?= htmlspecialchars($unitKerjaUser) ?>">
                <?php else: ?>
                <select name="unit_kerja" value="<?= old('unit_kerja') ?>" required>
                    <option value="" disabled selected>-- Pilih unit kerja --</option>
                    <?php foreach($unit_kerja_opt as $u): ?>
                    <option><?= $u ?></option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if(isset($aktifFields['area_inspeksi'])): ?>
            <div class="field">
                <label><?= htmlspecialchars($labelArea) ?> <span class="req">*</span></label>
                <select name="area_inspeksi" required>
                    <option value="" disabled selected>-- Pilih area inspeksi --</option>
                    <?php foreach($area_opt as $a): ?>
                    <option><?= $a ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <?php
            // Render field tambahan dari DB yang bukan field bawaan
            $fieldBawaan = ['nama_petugas','tanggal','kondisi_cuaca','unit_kerja','area_inspeksi','tanda_tangan','ciri_ukuran','kondisi_apron','aktivitas_satwa','tindak_lanjut','detail_pengusiran'];
            $stmtExtra = $pdo->query("SELECT * FROM form_fields WHERE aktif=1 AND tipe NOT IN ('grid','gridmap') AND nama_field NOT IN ('".implode("','", $fieldBawaan)."') ORDER BY urutan ASC");
            $extraFields = $stmtExtra->fetchAll(PDO::FETCH_ASSOC);
            foreach ($extraFields as $ef):
                $efName  = htmlspecialchars($ef['nama_field'], ENT_QUOTES, 'UTF-8');
                $efLabel = htmlspecialchars($ef['label'], ENT_QUOTES, 'UTF-8');
                $efPlaceholder = htmlspecialchars($ef['placeholder'] ?? '', ENT_QUOTES, 'UTF-8');
                $efWajib = $ef['wajib'] ? 'required' : '';
                $efOldVal = htmlspecialchars(old($ef['nama_field']), ENT_QUOTES, 'UTF-8');
            ?>
            <div class="field">
                <label><?= $efLabel ?><?= $ef['wajib'] ? ' <span class="req">*</span>' : '' ?></label>
                <?php if ($ef['tipe'] === 'dropdown'): ?>
                    <?php $efOpsi = getDropdownOpsi($pdo, $ef['nama_field'], []); ?>
                    <select name="<?= $efName ?>" <?= $efWajib ?>>
                        <option value="" disabled selected>-- Pilih <?= strtolower($efLabel) ?> --</option>
                        <?php foreach ($efOpsi as $opsi): ?>
                        <option><?= htmlspecialchars($opsi) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php elseif ($ef['tipe'] === 'textarea' || $ef['tipe'] === 'teks_panjang'): ?>
                    <textarea name="<?= $efName ?>" placeholder="<?= $efPlaceholder ?>" <?= $efWajib ?>><?= $efOldVal ?></textarea>
                <?php elseif ($ef['tipe'] === 'date'): ?>
                    <input type="date" name="<?= $efName ?>" value="<?= $efOldVal ?>" <?= $efWajib ?>>
                <?php elseif ($ef['tipe'] === 'number'): ?>
                    <input type="number" name="<?= $efName ?>" placeholder="<?= $efPlaceholder ?>" value="<?= $efOldVal ?>" <?= $efWajib ?>>
                <?php else: ?>
                    <input type="text" name="<?= $efName ?>" placeholder="<?= $efPlaceholder ?>" value="<?= $efOldVal ?>" <?= $efWajib ?>>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>

          <?php if(isset($aktifFields['tanda_tangan'])): ?>
          <div class="field">
    <label>Tanda Tangan <span class="req">*</span></label>

    <canvas id="signature-pad"
        style="
        width:100%;
        height:200px;
        border:1px solid #cce3f0;
        border-radius:8px;
        background:#fff;">
    </canvas>

    <input type="hidden"
           name="tanda_tangan"
           id="tanda_tangan">

    <div style="margin-top:10px">
        <button type="button"
                id="clearSignature"
                class="btn-back">
            Hapus Tanda Tangan
        </button>
    </div>
</div>
          <?php endif; ?>
        <!-- ═══ SECTION 2: GRIDMAP ═══ -->
        <div class="section-card">
            <div style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:4px">GRIDMAP</div>
            <div style="font-size:13px;color:var(--muted);margin-bottom:14px">Gambar lokasi pengamatan <span class="req">*</span></div>

            <!-- Gridmap statis dari developer -->
            <div style="position:relative;width:100%;border-radius:8px;overflow:hidden;border:1px solid var(--border)">
                <img src="<?= htmlspecialchars($gridmapImage, ENT_QUOTES, 'UTF-8') ?>" alt="Gridmap" style="width:100%;display:block;">
                <svg id="gridSvg" viewBox="0 0 780 320" xmlns="http://www.w3.org/2000/svg"
                     style="position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none">
                    <?php
                    $cols = array_merge(range('A','Z'),(array)['AA','AB','AC','AD']);
                    $tc = count($cols); $cw = 780/$tc;
                    $rows = 10; $rh = 280/$rows;
                    for($i=0;$i<=$tc;$i++) echo "<line x1='".($i*$cw)."' y1='20' x2='".($i*$cw)."' y2='300' stroke='rgba(42,179,192,0.55)' stroke-width='0.8'/>";
                    for($i=0;$i<=$rows;$i++) echo "<line x1='0' y1='".(20+$i*$rh)."' x2='780' y2='".(20+$i*$rh)."' stroke='rgba(42,179,192,0.55)' stroke-width='0.8'/>";
                    foreach($cols as $idx=>$col) echo "<text x='".($idx+0.5)*$cw."' y='14' text-anchor='middle' fill='#2ab3c0' font-size='6' font-family='DM Sans,sans-serif' font-weight='700'>$col</text>";
                    for($i=1;$i<=$rows;$i++) echo "<text x='4' y='".(20+($i-0.5)*$rh+2)."' fill='#2ab3c0' font-size='7' font-family='DM Sans,sans-serif' font-weight='700'>$i</text>";
                    ?>
                    <rect id="cellHL" x="0" y="0" width="0" height="0" fill="rgba(42,179,192,0.35)" stroke="#2ab3c0" stroke-width="1.5"/>
                    <circle id="pinDot" cx="-99" cy="-99" r="6" fill="#e05252" stroke="white" stroke-width="1.5"/>
                </svg>
            </div>

            <div class="gridmap-note">Noted : Isi lokasi dengan format "X-Y" (ex: "K-5")</div>
        </div>
        <!-- ═══ SECTION 3: PILIH SATWA ═══ -->
        <div class="section-card" id="satwaSection">
            <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:4px">
                Pilih jenis satwa yang ditemukan <span class="req">*</span>
            </div>
            <div style="font-size:12px;color:var(--muted);margin-bottom:14px;line-height:1.6">
                Klik kartu untuk memilih. Setiap kartu bisa ditambahkan beberapa lokasi dengan jumlah berbeda.
            </div>

            <div class="satwa-grid">
                <?php foreach($satwa_list as $idx => $satwa): ?>
                <div class="satwa-card" id="satwaCard<?= $idx ?>" onclick="toggleSatwa(<?= $idx ?>)">
                    <!-- Badge centang -->
                    <div class="satwa-check">✓</div>

                    <!-- Gambar satwa -->
                    <div class="satwa-icon-wrap">
                        <?php if(!empty($satwa['foto_path'])): ?>
                        <img src="<?= htmlspecialchars($satwa['foto_path'],ENT_QUOTES,'UTF-8') ?>"
                             alt="<?= htmlspecialchars($satwa['nama'],ENT_QUOTES,'UTF-8') ?>"
                             onerror="this.style.display='none'">
                        <?php else: ?>
                        <div style="display:flex;align-items:center;justify-content:center;height:100%;font-size:32px">🐦</div>
                        <?php endif; ?>
                    </div>

                    <!-- Nama -->
                    <div class="satwa-info">
                        <div class="satwa-name"><?= htmlspecialchars($satwa['nama'],ENT_QUOTES,'UTF-8') ?></div>
                    </div>

                    <!-- Daftar lokasi (muncul saat selected) -->
                    <div class="lokasi-rows" id="lokasiRows<?= $idx ?>">
                        <!-- Baris pertama default -->
                        <div class="lokasi-row">
                            <label>Jml</label>
                            <input type="number" min="1" value=""
                                   name="satwa[<?= $idx ?>][lokasi][0][jumlah]"
                                   onclick="event.stopPropagation()"
                                   oninput="event.stopPropagation(); autoSelectSatwa(<?= $idx ?>)"
                                   placeholder="0">
                            <label>Lokasi</label>
                            <input type="text"
                                   name="satwa[<?= $idx ?>][lokasi][0][grid]"
                                   onclick="event.stopPropagation()"
                                   oninput="event.stopPropagation(); autoSelectSatwa(<?= $idx ?>)"
                                   placeholder="K-5">
                            <button type="button" class="btn-hapus-row"
                                    onclick="event.stopPropagation();hapusLokasiSatwa(this,<?= $idx ?>)"
                                    style="visibility:hidden">×</button>
                        </div>
                    </div>

                    <!-- Tombol tambah lokasi -->
                    <button type="button" class="btn-tambah-lokasi"
                            onclick="event.stopPropagation();tambahLokasi(<?= $idx ?>)">
                        + Tambah Lokasi
                    </button>

                    <!-- Hidden: nama satwa -->
                    <input type="hidden" name="satwa[<?= $idx ?>][id]"
                           value="<?= $satwa['id'] ?>">
                    <input type="hidden" name="satwa[<?= $idx ?>][nama]"
                           value="<?= htmlspecialchars($satwa['nama'],ENT_QUOTES,'UTF-8') ?>">
                    <input type="hidden" name="satwa[<?= $idx ?>][aktif]"
                           id="hiddenAktif<?= $idx ?>" value="0">
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Upload foto satwa tidak terdaftar -->
            <div class="extra-upload-box">
                <div class="extra-upload-title">Tambahkan gambar jika ada jenis satwa liar yang tidak ada di daftar di atas</div>
                <div class="extra-upload-hint">Upload foto, lalu isi jumlah dan lokasi untuk setiap satwa baru yang ditemukan.</div>

                <!-- Preview & input lokasi per foto tambahan -->
                <div id="extraItemsWrap" style="display:none;flex-direction:column;gap:10px;margin-bottom:12px"></div>

                <div style="display:flex;align-items:center;flex-wrap:wrap;gap:8px">
                    <button type="button" class="upload-trigger" onclick="document.getElementById('extraFoto').click()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        Pilih Foto
                    </button>
                    <span class="upload-filename" id="extraFilename">No file chosen</span>
                </div>
                <input type="file" id="extraFoto" name=""
                       accept="image/*" multiple style="display:none"
                       onchange="onExtraPhoto(this)">
                <input type="file" id="extraFotoReal" name="foto_lainnya[]"
                       accept="image/*" multiple style="display:none">
            </div>
        </div>

        </div>

        <!-- ═══ SECTION 4: KONDISI SATWA ═══ -->
        <div class="section-card">
            <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:4px">
                Isi form di bawah sesuai dengan kondisi burung atau satwa liar yang ditemukan
            </div>
            <div style="font-size:12px;color:var(--muted);margin-bottom:16px">
                Semua field wajib diisi <span class="req">*</span>
            </div>

            <?php if(isset($aktifFields['ciri_ukuran'])): ?>
            <div class="field">
                <label><?= htmlspecialchars($fieldLabels['ciri_ukuran'] ?? 'Ciri-ciri Ukuran Satwa Liar') ?> <span class="req">*</span></label>
                <textarea name="ciri_ukuran" placeholder="Deskripsikan ukuran, warna, ciri fisik satwa..." required><?= old('ciri_ukuran') ?></textarea>
            </div>
            <?php endif; ?>

            <?php if(isset($aktifFields['kondisi_apron'])): ?>
            <?php $kondisi_apron_opt_db = getDropdownOpsi($pdo, 'kondisi_apron', ['Hidup','Mati','Tidak ditemukan']); ?>
            <div class="field">
                <label><?= htmlspecialchars($fieldLabels['kondisi_apron'] ?? 'Kondisi Ditemukan di Lokasi') ?> <span class="req">*</span></label>
                <select name="kondisi_apron" required>
                    <option value="">-- Pilih kondisi --</option>
                    <?php foreach($kondisi_apron_opt_db as $o): ?>
                    <option value="<?= htmlspecialchars($o) ?>"><?= htmlspecialchars($o) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <?php if(isset($aktifFields['aktivitas_satwa'])): ?>
            <div class="field">
                <label><?= htmlspecialchars($fieldLabels['aktivitas_satwa'] ?? 'Aktivitas Satwa Liar di Lokasi') ?> <span class="req">*</span></label>
                <textarea name="aktivitas_satwa"
                    placeholder="Terbang, berjalan, berkelompok, dll..." required><?= old('aktivitas_satwa') ?></textarea>
            </div>
            <?php endif; ?>

            <?php if(isset($aktifFields['tindak_lanjut'])): ?>
            <?php $tindak_lanjut_opt_db = getDropdownOpsi($pdo, 'tindak_lanjut', ['Telah ditangani','Belum ditangani']); ?>
            <div class="field">
                <label><?= htmlspecialchars($fieldLabels['tindak_lanjut'] ?? 'Tindak Lanjut yang Dilakukan') ?> <span class="req">*</span></label>
                <select name="tindak_lanjut" required>
                    <option value="">-- Pilih tindak lanjut --</option>
                    <?php foreach($tindak_lanjut_opt_db as $o): ?>
                    <option value="<?= htmlspecialchars($o) ?>"><?= htmlspecialchars($o) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <?php if(isset($aktifFields['detail_pengusiran'])): ?>
            <div class="field">
                <label><?= htmlspecialchars($fieldLabels['detail_pengusiran'] ?? 'Detail Pengusiran yang Dilakukan') ?> <span class="req">*</span></label>
                <textarea name="detail_pengusiran"
                    placeholder='Jelaskan secara detail pengusiran yang dilakukan. Isi "-" jika belum dilakukan.' required><?= old('detail_pengusiran') ?></textarea>
            </div>
            <?php endif; ?>

            <div class="btn-row">
                <a href="user_dashboard.php" class="btn-back">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                    Kembali
                </a>
                <button type="submit" class="btn-submit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><polyline points="12 5 19 12 12 19"/></svg>
                    Kirim Laporan
                </button>
            </div>
        </div>

    </form>
</div>

<!-- FOOTER -->
<footer class="page-footer">
    <div class="footer-logo">
        <img src="logo_login.png" alt="Logo">
    </div>
</footer>

<script>
// ── GRIDMAP ──
const COLS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('').concat(['AA','AB','AC','AD']);
const TC = COLS.length, CW = 780/TC, RH = 280/10;

function highlightCell(val){
    const hl=document.getElementById('cellHL');
    const pin=document.getElementById('pinDot');
    const m=val.trim().toUpperCase().match(/^([A-Z]{1,2})-(\d+)$/);
    if(!m){hl.setAttribute('width','0');pin.setAttribute('cx','-99');return;}
    const ci=COLS.indexOf(m[1]),rn=parseInt(m[2]);
    if(ci===-1||rn<1||rn>10){hl.setAttribute('width','0');pin.setAttribute('cx','-99');return;}
    const x=ci*CW,y=20+(rn-1)*RH;
    hl.setAttribute('x',x);hl.setAttribute('y',y);
    hl.setAttribute('width',CW);hl.setAttribute('height',RH);
    pin.setAttribute('cx',x+CW/2);pin.setAttribute('cy',y+RH/2);
}



// ── SATWA MULTI-LOKASI ──
const lokasiCounts = {};

function toggleSatwa(idx){
    const card = document.getElementById('satwaCard' + idx);
    const aktif = document.getElementById('hiddenAktif' + idx);
    if(card.classList.contains('selected')){
        card.classList.remove('selected');
        aktif.value = '0';
    } else {
        card.classList.add('selected');
        aktif.value = '1';
        if(!lokasiCounts[idx]) lokasiCounts[idx] = 1;
        // Otomatis isi jumlah = 1 jika masih kosong
        const firstJumlah = document.querySelector(`input[name="satwa[${idx}][lokasi][0][jumlah]"]`);
        if(firstJumlah && firstJumlah.value === '') firstJumlah.value = '1';
    }
}

// Otomatis tandai kartu sebagai terpilih saat user mengisi jumlah/lokasi
// langsung, tanpa perlu klik kartu terlebih dahulu.
function autoSelectSatwa(idx){
    const card = document.getElementById('satwaCard' + idx);
    const aktif = document.getElementById('hiddenAktif' + idx);
    if(!card.classList.contains('selected')){
        card.classList.add('selected');
        aktif.value = '1';
        if(!lokasiCounts[idx]) lokasiCounts[idx] = 1;
    }
}

function tambahLokasi(idx){
    if(!lokasiCounts[idx]) lokasiCounts[idx] = 1;
    const rowIdx = lokasiCounts[idx];
    lokasiCounts[idx]++;
    const wrap = document.getElementById('lokasiRows' + idx);
    const row = document.createElement('div');
    row.className = 'lokasi-row';
    row.innerHTML = `
        <label>Jml</label>
        <input type="number" min="1" value=""
               name="satwa[${idx}][lokasi][${rowIdx}][jumlah]"
               onclick="event.stopPropagation()" oninput="event.stopPropagation(); autoSelectSatwa(${idx})" placeholder="0">
        <label>Lokasi</label>
        <input type="text"
               name="satwa[${idx}][lokasi][${rowIdx}][grid]"
               onclick="event.stopPropagation()" oninput="event.stopPropagation(); autoSelectSatwa(${idx})" placeholder="K-5">
        <button type="button" class="btn-hapus-row"
                onclick="event.stopPropagation();hapusLokasiSatwa(this,${idx})">×</button>
    `;
    wrap.appendChild(row);
    _refreshHapusSatwa(idx);
}

// Hapus baris lokasi kartu satwa — minimal 1 harus tersisa
function hapusLokasiSatwa(btn, idx){
    const wrap = document.getElementById('lokasiRows' + idx);
    if(wrap.children.length > 1) btn.closest('.lokasi-row').remove();
    _refreshHapusSatwa(idx);
}

// Tampilkan/sembunyikan tombol hapus: visible jika >1 baris, hidden jika hanya 1
function _refreshHapusSatwa(idx){
    const wrap = document.getElementById('lokasiRows' + idx);
    const rows = wrap.querySelectorAll('.lokasi-row');
    rows.forEach(r => {
        const b = r.querySelector('.btn-hapus-row');
        if(b) b.style.visibility = rows.length > 1 ? 'visible' : 'hidden';
    });
}

// ── UPLOAD FOTO TAMBAHAN — setiap klik tambah foto baru, tidak replace ──
let extraGlobalIdx = 0;
const extraLokCounts = {};

function onExtraPhoto(input){
    const wrap = document.getElementById('extraItemsWrap');
    if(!input.files || !input.files.length) return;

    // Simpan file ke extraFotoReal pakai DataTransfer agar tidak hilang saat reset
    const realInput = document.getElementById('extraFotoReal');
    const dt = new DataTransfer();
    if(realInput.files){ Array.from(realInput.files).forEach(f => dt.items.add(f)); }
    Array.from(input.files).forEach(f => dt.items.add(f));
    realInput.files = dt.files;

    wrap.style.display = 'flex';
    Array.from(input.files).forEach(file => {
        const ei = extraGlobalIdx++;
        extraLokCounts[ei] = 1;
        const reader = new FileReader();
        reader.onload = ev => {
            const item = document.createElement('div');
            item.id = 'extraItem' + ei;
            item.dataset.filename = file.name;
            item.style.cssText = 'display:flex;gap:10px;align-items:flex-start;background:#f7fbfe;border:1px solid var(--border);border-radius:8px;padding:10px;position:relative;';
            item.innerHTML = `
                <button type="button" title="Hapus foto ini"
                    onclick="hapusExtraItem(${ei})"
                    style="position:absolute;top:6px;right:6px;width:22px;height:22px;border-radius:50%;border:none;background:var(--red);color:white;font-size:14px;font-weight:700;display:flex;align-items:center;justify-content:center;cursor:pointer;line-height:1;">×</button>
                <img src="${ev.target.result}" style="width:70px;height:55px;object-fit:cover;border-radius:6px;border:1px solid var(--border);flex-shrink:0">
                <div style="flex:1;padding-right:28px">
                    <div style="font-size:11px;font-weight:600;color:var(--text);margin-bottom:6px;word-break:break-all">${file.name}</div>
                    <div id="extraLokasiRows${ei}" style="display:flex;flex-direction:column;gap:5px;margin-bottom:6px">
                        <div class="lokasi-row">
                            <label>Jml</label>
                            <input type="number" min="1" value="1" name="extra[${ei}][lokasi][0][jumlah]" onclick="event.stopPropagation()" placeholder="0">
                            <label>Lokasi</label>
                            <input type="text" name="extra[${ei}][lokasi][0][grid]" onclick="event.stopPropagation()" placeholder="K-5" style="text-transform:uppercase">
                        </div>
                    </div>
                    <button type="button" class="btn-tambah-lokasi" style="display:flex"
                            onclick="tambahLokasiExtra(${ei})">+ Tambah Lokasi</button>
                </div>
            `;
            wrap.appendChild(item);
            _updateExtraLabel();
        };
        reader.readAsDataURL(file);
    });
    input.value = ''; // reset trigger agar bisa pilih file sama lagi
}

function hapusExtraItem(ei){
    const el = document.getElementById('extraItem' + ei);
    if(el) el.remove();
    const wrap = document.getElementById('extraItemsWrap');
    if(!wrap.querySelector('[id^="extraItem"]')) wrap.style.display = 'none';
    _updateExtraLabel();
}

function _updateExtraLabel(){
    const wrap = document.getElementById('extraItemsWrap');
    const total = wrap.querySelectorAll('[id^="extraItem"]').length;
    document.getElementById('extraFilename').textContent = total > 0 ? total + ' foto dipilih' : 'No file chosen';
}

function tambahLokasiExtra(ei){
    if(!extraLokCounts[ei]) extraLokCounts[ei] = 1;
    const rowIdx = extraLokCounts[ei]++;
    const wrap = document.getElementById('extraLokasiRows' + ei);
    const row = document.createElement('div');
    row.className = 'lokasi-row';
    row.innerHTML = `
        <label>Jml</label>
        <input type="number" min="1" value="1" name="extra[${ei}][lokasi][${rowIdx}][jumlah]" onclick="event.stopPropagation()" placeholder="0">
        <label>Lokasi</label>
        <input type="text" name="extra[${ei}][lokasi][${rowIdx}][grid]" onclick="event.stopPropagation()" placeholder="K-5" style="text-transform:uppercase">
        <button type="button" class="btn-hapus-row" onclick="event.stopPropagation();hapusLokasiExtra(this,${ei})">×</button>
    `;
    wrap.appendChild(row);
    _refreshHapusExtra(ei);
}

function hapusLokasiExtra(btn, ei){
    const wrap = document.getElementById('extraLokasiRows' + ei);
    if(wrap.children.length > 1) btn.closest('.lokasi-row').remove();
    _refreshHapusExtra(ei);
}

function _refreshHapusExtra(ei){
    const wrap = document.getElementById('extraLokasiRows' + ei);
    const rows = wrap.querySelectorAll('.lokasi-row');
    rows.forEach(r => {
        const b = r.querySelector('.btn-hapus-row');
        if(b) b.style.visibility = rows.length > 1 ? 'visible' : 'hidden';
    });
}

// ── VALIDASI SUBMIT ──
document.getElementById('mainForm').addEventListener('submit', function(e){
    const anySelected = document.querySelectorAll('.satwa-card.selected').length > 0;
    const extraHasFiles = document.getElementById('extraFotoReal').files.length > 0;
    if(!anySelected && !extraHasFiles){
        e.preventDefault();
        alert('Pilih minimal satu jenis satwa yang ditemukan, atau upload foto satwa tidak terdaftar.');
        document.getElementById('satwaSection').scrollIntoView({behavior:'smooth', block:'start'});
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

<script>
const canvas = document.getElementById('signature-pad');

canvas.width = canvas.offsetWidth;
canvas.height = 200;

const signaturePad = new SignaturePad(canvas);

document.getElementById('clearSignature')
.addEventListener('click', function(){
    signaturePad.clear();
});

document.getElementById('mainForm')
.addEventListener('submit', function(e){

    if(signaturePad.isEmpty()){
        alert('Silakan tanda tangan terlebih dahulu');
        e.preventDefault();
        return;
    }

    document.getElementById('tanda_tangan').value =
        signaturePad.toDataURL('image/png');
});
</script>
<!-- ═══ POPUP TERIMA KASIH ═══ -->

<!-- Error toast -->
<?php if($pesan_error): ?>
<div id="errorToast" style="
    position:fixed;top:20px;left:50%;transform:translateX(-50%);
    background:#fff;border:1px solid #fca5a5;border-left:4px solid #ef4444;
    border-radius:10px;padding:14px 20px;
    display:flex;align-items:center;gap:10px;
    box-shadow:0 8px 24px rgba(0,0,0,0.12);
    z-index:9999;font-size:13px;color:#991b1b;
    animation:popupUp .3s ease;max-width:90%;white-space:nowrap">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <?= $pesan_error ?>
    <button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;color:#991b1b;font-size:16px;margin-left:8px;padding:0">×</button>
</div>
<?php endif; ?>

<!-- ═══ POPUP TERIMA KASIH ═══ -->
<?php if($pesan_sukses): ?>
<div id="popupTerimakasih" style="
    position:fixed;inset:0;z-index:9999;
    background:rgba(0,0,0,0.45);
    display:flex;align-items:center;justify-content:center;
    backdrop-filter:blur(6px);
    -webkit-backdrop-filter:blur(6px);
    animation:fadeInBg .3s ease">

  <div style="
      background:#fff;border-radius:24px;
      padding:44px 40px 36px;
      max-width:440px;width:90%;
      text-align:center;
      box-shadow:0 32px 80px rgba(0,0,0,0.18);
      animation:popupUp .4s cubic-bezier(.16,1,.3,1);
      position:relative;overflow:hidden">

    <!-- Gradasi atas InJourney -->
    <div style="position:absolute;top:0;left:0;right:0;height:5px;background:linear-gradient(90deg,#00A9C1,#4FADC9,#88B146,#F0B14B,#D94F4F)"></div>

    <!-- Ikon centang -->
    <div style="
        width:80px;height:80px;
        background:linear-gradient(135deg,#00A9C1,#0090a6);
        border-radius:50%;
        display:flex;align-items:center;justify-content:center;
        margin:0 auto 22px;
        box-shadow:0 10px 30px rgba(0,169,193,0.35)">
      <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="20 6 9 17 4 12"/>
      </svg>
    </div>

    <!-- Judul -->
    <div style="font-size:24px;font-weight:800;color:#1a2332;margin-bottom:8px">Terima Kasih!</div>

    <!-- Sub judul -->
    <div style="font-size:14px;color:#5a7a8a;line-height:1.75;margin-bottom:6px">
      Laporan Anda telah berhasil dikirim<br>dan sedang diproses oleh admin.
    </div>

    <!-- Nomor / pesan sukses -->
    <div style="
        display:inline-block;
        background:#e8f8fa;border:1px solid #b2e8f0;
        border-radius:8px;padding:6px 16px;
        font-size:12px;color:#00838f;font-weight:600;
        margin-bottom:28px">
      <?= $pesan_sukses ?>
    </div>

    <!-- Progress bar countdown -->
    <div style="background:#e8f0f4;border-radius:99px;height:5px;overflow:hidden;margin-bottom:10px">
      <div id="progressBar" style="height:100%;background:linear-gradient(90deg,#00A9C1,#0090a6);border-radius:99px;width:0%;transition:width 4s linear"></div>
    </div>
    <div style="font-size:12px;color:#aaa;margin-bottom:22px">
      Kembali ke dashboard dalam <span id="countdown" style="font-weight:700;color:#00A9C1">4</span> detik...
    </div>

    <!-- Tombol -->
    <button onclick="goToDashboard()" style="
        background:linear-gradient(135deg,#00A9C1,#0090a6);
        color:white;border:none;border-radius:12px;
        padding:13px 32px;font-size:14px;font-weight:600;
        cursor:pointer;font-family:inherit;
        box-shadow:0 4px 18px rgba(0,169,193,0.35);
        transition:opacity .15s;width:100%">
      Ke Dashboard Sekarang →
    </button>

  </div>
</div>

<style>
@keyframes fadeInBg { from{opacity:0} to{opacity:1} }
@keyframes popupUp  { from{opacity:0;transform:translateY(30px) scale(.97)} to{opacity:1;transform:translateY(0) scale(1)} }
</style>
<script>
function goToDashboard(){ window.location.href = 'user_dashboard.php'; }
window.addEventListener('load', function(){
    setTimeout(function(){ document.getElementById('progressBar').style.width='100%'; }, 100);
    var sisa = 4;
    var timer = setInterval(function(){
        sisa--;
        var el = document.getElementById('countdown');
        if(el) el.textContent = sisa;
        if(sisa <= 0){ clearInterval(timer); goToDashboard(); }
    }, 1000);
});
</script>
<?php endif; ?>


<script>
// Restore textarea values from session (after error redirect)
<?php if(!empty($fd)): ?>
<?php $textareas = ['ciri_ukuran','kondisi_apron','aktivitas_satwa','tindak_lanjut','detail_pengusiran']; ?>
<?php foreach($textareas as $tf): ?>
<?php if(!empty($fd[$tf])): ?>
(function(){
  var el = document.querySelector('[name="<?= $tf ?>"]');
  if(el) el.value = <?= json_encode($fd[$tf]) ?>;
})();
<?php endif; ?>
<?php endforeach; ?>
<?php if(!empty($fd['kondisi_cuaca'])): ?>
(function(){
  var el = document.querySelector('[name="kondisi_cuaca"]');
  if(el) el.value = <?= json_encode($fd['kondisi_cuaca']) ?>;
})();
<?php endif; ?>
<?php if(!empty($fd['area_inspeksi'])): ?>
(function(){
  var el = document.querySelector('[name="area_inspeksi"]');
  if(el) el.value = <?= json_encode($fd['area_inspeksi']) ?>;
})();
<?php endif; ?>
<?php endif; ?>
</script>
</body>
</html>