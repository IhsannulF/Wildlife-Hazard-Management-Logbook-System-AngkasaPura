<?php
// ============================================================
// admin_dashboard.php — Dashboard Admin
// Fitur:
//   - Stat cards: total, belum ditangani, telah ditangani
//   - Tabel semua laporan + search
//   - Modal Detail 2 halaman (info petugas | kondisi+satwa+PDF)
//   - Modal Edit form lengkap + validasi status oleh admin
//   - Modal Hapus konfirmasi
// ============================================================
session_start();
// Cek login — kalau belum login, arahkan ke halaman login
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once 'koneksi.php';
$username = htmlspecialchars($_SESSION['username'] ?? 'Admin', ENT_QUOTES, 'UTF-8');

// ── Statistik ─────────────────────────────────────────────
$total_laporan = (int)$pdo->query("SELECT COUNT(*) FROM laporan")->fetchColumn();
$belum         = (int)$pdo->query("SELECT COUNT(*) FROM laporan WHERE status='belum' OR status IS NULL OR status=''")->fetchColumn();
$ditangani     = (int)$pdo->query("SELECT COUNT(*) FROM laporan WHERE status='sudah'")->fetchColumn();

$diproses = 0; // tidak dipakai, enum hanya belum_ditangani/ditangani

// ── Handle POST: hapus / update status / edit form ────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    $lid  = (int)($_POST['laporan_id'] ?? 0);

    if ($aksi === 'hapus' && $lid > 0) {
        $pdo->prepare("DELETE FROM laporan WHERE id=?")->execute([$lid]);
        header("Location: admin_dashboard.php?msg=hapus"); exit;
    }

    if ($aksi === 'edit_full' && $lid > 0) {
        $nama       = $_POST['nama_petugas']      ?? '';
        $tgl        = $_POST['tanggal']            ?? '';
        $cuaca      = $_POST['kondisi_cuaca']      ?? '';
        $unit       = $_POST['unit_kerja']         ?? '';
        $area       = $_POST['area_inspeksi']      ?? '';
        $ttd        = $_POST['tanda_tangan']       ?? '';
        $grid       = $_POST['grid_lokasi']        ?? '';
        $ciri       = $_POST['ciri_ukuran']        ?? '';
        $kondisi    = $_POST['kondisi_apron']      ?? '';
        $aktivitas  = $_POST['aktivitas_satwa']    ?? '';
        $tindak     = $_POST['tindak_lanjut']      ?? '';
        $detail     = $_POST['detail_pengusiran']  ?? '';
        $status_baru= $_POST['status_validasi']    ?? 'belum';

        // DEBUG SEMENTARA
        if (isset($_GET['debug'])) {
            echo '<pre>';
            echo 'POST size: ' . strlen(file_get_contents('php://input')) . ' bytes\n';
            echo 'post_max_size: ' . ini_get('post_max_size') . '\n';
            echo 'status_validasi: ' . ($status_baru ?? 'NULL') . '\n';
            echo 'laporan_id: ' . $lid . '\n';
            echo 'POST count: ' . count($_POST) . '\n';
            echo '</pre>'; exit;
        }

        $stmt = $pdo->prepare("
            UPDATE laporan SET
                nama_petugas=?, tanggal=?, kondisi_cuaca=?,
                unit_kerja=?, area_inspeksi=?, tanda_tangan=?,
                grid_lokasi=?, ciri_ukuran=?, kondisi_apron=?,
                aktivitas_satwa=?, tindak_lanjut=?,
                detail_pengusiran=?, status=?
            WHERE id=?
        ");
        $stmt->execute([$nama,$tgl,$cuaca,$unit,$area,$ttd,$grid,$ciri,$kondisi,$aktivitas,$tindak,$detail,$status_baru,$lid]);
        header("Location: admin_dashboard.php?msg=edit"); exit;
    }
}

// ── Ambil semua laporan ───────────────────────────────────
$search = trim($_GET['search'] ?? '');
$where  = $search ? "WHERE (l.nama_petugas LIKE ? OR l.area_inspeksi LIKE ? OR l.kondisi_cuaca LIKE ? OR l.grid_lokasi LIKE ?)" : '';

$stmt = $pdo->prepare("
    SELECT l.id, 
           COALESCE(NULLIF(l.nama_petugas,''), u.namalengkap, '-') AS nama_petugas,
           l.tanggal, l.area_inspeksi,
           l.kondisi_cuaca, l.status, l.created_at,
           u.namalengkap AS nama_user,
           (SELECT GROUP_CONCAT(DISTINCT ds.grid SEPARATOR ', ')
            FROM detail_satwa ds
            WHERE ds.laporan_id = l.id AND ds.grid IS NOT NULL AND ds.grid != '') AS grid_lokasi
    FROM laporan l
    LEFT JOIN users u ON l.user_id = u.id
    $where
    ORDER BY l.created_at DESC
");
if ($search) {
    $s = "%$search%";
    $stmt->execute([$s, $s, $s, $s]);
} else {
    $stmt->execute();
}
$rows = $stmt->fetchAll();

// Ambil opsi dropdown dari database (konsisten dengan form user)
function getOpsi($pdo, $nama_field) {
    $stmt = $pdo->prepare("SELECT ffo.nilai FROM form_field_options ffo JOIN form_fields ff ON ff.id = ffo.field_id WHERE ff.nama_field = ? ORDER BY ffo.id");
    $stmt->execute([$nama_field]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
$kondisi_cuaca_opt = getOpsi($pdo, 'kondisi_cuaca');
$unit_kerja_opt    = getOpsi($pdo, 'unit_kerja');
$area_opt          = getOpsi($pdo, 'area_inspeksi');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin — Pengaduan Satwa Liar</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --sidebar-bg: #ffffff;
            --sidebar-w:  240px;
            --topbar-h:   60px;
            --bg:         #eaf6f8;
            --surface:    #ffffff;
            --border:     #d0e8ef;
            --text:       #1a202c;
            --muted:      #718096;
            --accent:     #00A9C1;
            --font:       'DM Sans', sans-serif;
            --radius:     12px;
        }

        body { font-family: var(--font); background: linear-gradient(160deg, #eaf6f8 0%, #f0f9fb 40%, #e8f4f0 100%); color: var(--text); min-height: 100dvh; }

        /* ── OVERLAY ── */
        .overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:99; }
        .overlay.show { display:block; }

        /* ── TOPBAR mobile ── */
        .topbar { display:none; position:fixed; top:0; left:0; right:0; height:var(--topbar-h); background:linear-gradient(135deg,#00A9C1,#007fa3); align-items:center; justify-content:space-between; padding:0 16px; z-index:98; }
        .topbar-brand { display:flex; align-items:center; gap:8px; color:#fff; font-weight:600; font-size:14px; }
        .hamburger { width:40px; height:40px; background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.3); border-radius:8px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:5px; cursor:pointer; padding:0; }
        .hamburger span { display:block; width:18px; height:2px; background:#fff; border-radius:2px; transition:transform 0.25s,opacity 0.25s; }
        .hamburger.open span:nth-child(1) { transform:translateY(7px) rotate(45deg); }
        .hamburger.open span:nth-child(2) { opacity:0; }
        .hamburger.open span:nth-child(3) { transform:translateY(-7px) rotate(-45deg); }

        /* ── SIDEBAR ── */
        .sidebar { position:fixed; left:0; top:0; bottom:0; width:var(--sidebar-w); background:rgba(255,255,255,0.55); backdrop-filter:blur(20px); display:flex; flex-direction:column; z-index:100; border-right:1px solid rgba(255,255,255,0.4); box-shadow:6px 0 30px rgba(0,0,0,.05); }
        .sb-user{display:flex;align-items:center;gap:12px;padding:24px 20px 20px;border-bottom:1px solid #e8edf2}
        .sb-avatar{width:46px;height:46px;background:#00A9C1;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:15px;font-weight:600;color:#fff}
        .sb-userinfo .sb-name{font-size:13.5px;font-weight:600;color:#414141}
        .sb-userinfo .sb-role{font-size:12px;color:#888;margin-top:2px}
        .sb-nav{flex:1;padding:16px 14px;display:flex;flex-direction:column;gap:4px}
        .sb-item{display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:8px;font-size:14px;font-weight:500;color:#666;text-decoration:none;transition:all .15s}
        .sb-item:hover  { background:#f0f9fb; color:#00A9C1; }
        .sb-item.active{background:rgba(0,169,193,0.12);color:#00A9C1;border-left:4px solid #00A9C1}
        .sb-icon { width:18px; height:18px; flex-shrink:0; }
        .sb-spacer { flex:1; }
        .sb-bottom { padding:16px 12px; border-top:1px solid #d0e8ef; }
        .sb-logout { display:flex; align-items:center; gap:12px; padding:12px 14px; border-radius:8px; color:#ef4444; font-size:14px; font-weight:500; text-decoration:none; transition:background 0.15s; min-height:44px; }
        .sb-logout:hover { background:rgba(239,68,68,.12); }
        .sb-logout svg { width:18px; height:18px; flex-shrink:0; }
        .sb-item:focus, .sb-logout:focus { outline:none; }
        .sb-item:focus-visible, .sb-logout:focus-visible { outline:2px solid var(--accent); outline-offset:2px; border-radius:8px; }

        /* ── MAIN ── */
        .main { margin-left:var(--sidebar-w); padding:32px 36px; min-height:100vh; }
        .breadcrumb { font-size:13px; color:#007fa3; margin-bottom:24px; font-weight:500; }
        .breadcrumb a { color:#00A9C1; text-decoration:none; font-weight:600; }

        /* ── STAT CARDS ── */
        .stats-row { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:24px; }
        .stat-card { border-radius:var(--radius); padding:22px 24px; color:#fff; transition:all 0.25s ease; cursor:default; position:relative; overflow:hidden; }
        .stat-card::after { content:''; position:absolute; top:-20px; right:-20px; width:80px; height:80px; border-radius:50%; background:rgba(255,255,255,0.08); }
        .stat-card.blue  { background:linear-gradient(135deg, #00C4DF 0%, #00A9C1 50%, #0087a3 100%); box-shadow:0 4px 18px rgba(0,169,193,0.4); }
        .stat-card.red   { background:linear-gradient(135deg, #f07a7a 0%, #d94f4f 60%, #b83535 100%); box-shadow:0 4px 18px rgba(217,79,79,0.35); }
        .stat-card.green { background:linear-gradient(135deg, #98c875 0%, #6aa84f 60%, #4e8a35 100%); box-shadow:0 4px 18px rgba(106,168,79,0.35); }
        .stat-card.blue:hover  { box-shadow:0 8px 28px rgba(0,169,193,0.6), 0 0 40px rgba(0,169,193,0.25); transform:translateY(-3px); }
        .stat-card.red:hover   { box-shadow:0 8px 28px rgba(217,79,79,0.6), 0 0 40px rgba(217,79,79,0.25); transform:translateY(-3px); }
        .stat-card.green:hover { box-shadow:0 8px 28px rgba(106,168,79,0.6), 0 0 40px rgba(106,168,79,0.25); transform:translateY(-3px); }
        .stat-num        { font-size:40px; font-weight:800; margin-bottom:4px; line-height:1; }
        .stat-main-label { font-size:15px; font-weight:600; margin-bottom:4px; }
        .stat-sub-label  { font-size:12px; opacity:0.8; }

        /* ── TABLE ── */
        .table-section { background:var(--surface); border-radius:var(--radius); border:1px solid var(--border); overflow:hidden; box-shadow:0 4px 20px rgba(0,169,193,0.08); }
        .table-header  { display:flex; align-items:center; justify-content:space-between; padding:16px 20px; border-bottom:2px solid var(--border); gap:12px; flex-wrap:wrap; background:linear-gradient(135deg,#f7fbfd,#eef7fa); }
        .table-title   { display:flex; align-items:center; gap:8px; font-size:14px; font-weight:700; color:#007fa3; }
        .table-title svg { width:18px; height:18px; color:#00A9C1; }
        .search-wrap   { display:flex; align-items:center; gap:8px; }
        .search-wrap label { font-size:13px; color:var(--muted); white-space:nowrap; }
        .search-wrap input { padding:7px 12px; border:1.5px solid var(--border); border-radius:8px; font-family:var(--font); font-size:13px; color:var(--text); outline:none; width:180px; transition:border-color 0.2s,box-shadow 0.2s; }
        .search-wrap input:focus { border-color:var(--accent); box-shadow:0 0 0 3px rgba(0,169,193,0.12); }
        .tbl-wrap { overflow-x:auto; }
        table { width:100%; border-collapse:collapse; }
        thead th { background:linear-gradient(135deg,#b8e8ef,#c8f0f5); padding:12px 16px; text-align:left; font-size:13px; font-weight:700; color:#005f82; white-space:nowrap; }

        tbody tr { border-bottom:1px solid #e8f4f7; transition:background 0.15s; }
        tbody tr:last-child { border-bottom:none; }
        tbody tr:nth-child(odd)  { background:#f7fbfd; }
        tbody tr:nth-child(even) { background:#fff; }
        tbody tr:hover { background:#eef7fa; }
        tbody td { padding:13px 16px; font-size:13px; color:var(--text); vertical-align:middle; }
        .badge { display:inline-flex; align-items:center; justify-content:center; padding:5px 10px; border-radius:6px; font-size:11px; font-weight:700; white-space:nowrap; min-width:76px; text-align:center; }
        .badge-green { background:linear-gradient(135deg,#6aa84f,#4e8a35); color:#fff; box-shadow:0 2px 6px rgba(106,168,79,0.3); }
        .badge-red   { background:linear-gradient(135deg,#e05252,#c03030); color:#fff; box-shadow:0 2px 6px rgba(224,82,82,0.3); }
        .badge-blue  { background:linear-gradient(135deg,#00A9C1,#007fa3); color:#fff; box-shadow:0 2px 6px rgba(0,169,193,0.3); }
        .btn-group { display:flex; gap:6px; flex-wrap:wrap; }
        .btn { padding:6px 14px; border:none; border-radius:6px; font-family:var(--font); font-size:12px; font-weight:600; cursor:pointer; transition:all 0.2s ease; white-space:nowrap; min-height:30px; }
        .btn-detail { background:linear-gradient(135deg,#00A9C1,#007fa3); color:#fff; box-shadow:0 2px 6px rgba(0,169,193,0.3); }
        .btn-edit   { background:linear-gradient(135deg,#f0a830,#d4841e); color:#fff; box-shadow:0 2px 6px rgba(240,168,48,0.3); }
        .btn-hapus  { background:linear-gradient(135deg,#e05252,#c03030); color:#fff; box-shadow:0 2px 6px rgba(224,82,82,0.3); }
        .btn-detail:hover { box-shadow:0 4px 14px rgba(0,169,193,0.6); transform:translateY(-1px); }
        .btn-edit:hover   { box-shadow:0 4px 14px rgba(240,168,48,0.6); transform:translateY(-1px); }
        .btn-hapus:hover  { box-shadow:0 4px 14px rgba(224,82,82,0.6); transform:translateY(-1px); }
        .empty-state { text-align:center; padding:48px; color:var(--muted); font-size:14px; }
        .flash { padding:12px 16px; border-radius:8px; margin-bottom:20px; font-size:13px; font-weight:500; }
        .flash-success { background:linear-gradient(135deg,#d4edda,#c8f0d4); color:#155724; border:1px solid #a5d6a7; }

        /* ── MODAL BASE ── */
        .modal-bg { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:200; align-items:center; justify-content:center; padding:16px; overflow-y:auto; }
        .modal-bg.show { display:flex; }

        /* ── MODAL DETAIL — InJourney style ── */
        .modal-detail-wrap {
        background-image: url('Watermark_injourney.jpeg');
        background-repeat: repeat;
        background-size: 450px;
        background-attachment: local;
        border-radius:16px;
        width:100%; max-width:860px;
        max-height:90vh;
        overflow-y:auto;
        position:relative;
        box-shadow:0 24px 80px rgba(0,169,193,0.18), 0 8px 32px rgba(0,0,0,0.15);
        border:1px solid rgba(0,169,193,0.15);
        }

        .detail-watermark {
        display:none;
        }

        .detail-watermark-text { white-space:nowrap; transform:rotate(-15deg); text-align:center; }
        .detail-watermark-text .wm-injourney { display:block; font-size:40px; font-weight:500; color:#414141; letter-spacing:-1px; }
        .detail-watermark-text .wm-airports { display:block; font-size:14px; font-weight:800; color:#00A9C1; letter-spacing:5px; }

        .detail-header-bar {
        background:linear-gradient(135deg, #00A9C1 0%, #007fa3 60%, #005f82 100%);
        border-radius:16px 16px 0 0;
        padding:20px 48px 20px;
        position:relative; z-index:2;
        box-shadow:0 4px 16px rgba(0,169,193,0.3);
        }

        .detail-close-btn {
        position:absolute; top:14px; right:18px;
        background:rgba(255,255,255,0.2); border:none; font-size:18px; font-weight:700;
        color:#fff; cursor:pointer; z-index:10; line-height:1;
        width:32px; height:32px; border-radius:50%;
        display:flex; align-items:center; justify-content:center;
        transition:background 0.2s;
        }
        .detail-close-btn:hover { background:rgba(255,255,255,0.35); }

        .detail-title {
        text-align:center;
        font-size:20px; font-weight:800;
        color:#fff; padding:0;
        position:relative; z-index:1;
        letter-spacing:0.3px;
        }

        .detail-divider { height:1px; background:linear-gradient(90deg, transparent, rgba(0,169,193,0.3), transparent); margin:0 32px 8px; }

        .detail-pages { overflow:hidden; position:relative; }
        .detail-page  { padding:20px 40px 28px; display:none; position:relative; z-index:1; }
        .detail-page.active { display:block; }

        .rf-group { margin-bottom:14px; }
        .rf-label {
        font-size:12px; font-weight:600; color:#00A9C1;
        margin-bottom:5px; display:block;
        text-transform:uppercase; letter-spacing:0.4px;
        }
        .rf-label .req { color:#e05252; }
        .rf-box {
        width:100%; padding:10px 14px;
        border:1.5px solid #d0e8ef; border-radius:8px;
        font-family:var(--font); font-size:14px; color:var(--text);
        background:#f7fbfd; min-height:40px;
        word-break:break-word; line-height:1.5;
        box-shadow:0 1px 4px rgba(0,169,193,0.06);
        }
        .rf-box.tall { min-height:72px; }

        .satwa-found-title {
        text-align:center; font-size:13px; font-weight:700;
        color:#00A9C1; margin:20px 0 12px;
        letter-spacing:0.8px; text-transform:uppercase;
        }
        .satwa-found-grid {
        display:grid; grid-template-columns:repeat(auto-fill,minmax(120px,1fr));
        gap:12px; border:1.5px solid #d0e8ef; border-radius:10px; padding:16px;
        background:linear-gradient(135deg,#f7fbfd,#eef7fa);
        }
        .satwa-found-card { text-align:center; }
        .satwa-found-img  { width:100%; aspect-ratio:4/3; object-fit:cover; border-radius:6px; background:#e8f0f5; display:block; font-size:28px; line-height:1; }
        .satwa-found-img-placeholder { width:100%; aspect-ratio:4/3; background:linear-gradient(135deg,#c8eef5,#a8dde8); border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:28px; }
        .satwa-found-name { font-size:11px; font-weight:700; color:var(--text); margin-top:5px; line-height:1.3; }
        .satwa-found-meta { font-size:11px; color:var(--muted); }

        .detail-footer {
        display:flex; align-items:center; justify-content:flex-end;
        gap:10px; padding:14px 40px 20px;
        border-top:1px solid #d0e8ef; position:relative; z-index:1;
        background:#f0f8fb; border-radius:0 0 16px 16px;
        }
        .btn-kembali { background:linear-gradient(135deg,#00A9C1,#007fa3); color:#fff; padding:9px 22px; border:none; border-radius:8px; font-family:var(--font); font-size:13px; font-weight:600; cursor:pointer; transition:all 0.2s; box-shadow:0 3px 10px rgba(0,169,193,0.3); }
        .btn-kembali:hover { box-shadow:0 5px 16px rgba(0,169,193,0.5); transform:translateY(-1px); }
        .btn-pdf { background:linear-gradient(135deg,#e05252,#c03030); color:#fff; padding:9px 22px; border:none; border-radius:8px; font-family:var(--font); font-size:13px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:6px; transition:all 0.2s; box-shadow:0 3px 10px rgba(224,82,82,0.3); }
        .btn-pdf:hover { box-shadow:0 5px 16px rgba(224,82,82,0.5); transform:translateY(-1px); }
        .btn-next-detail { background:linear-gradient(135deg,#00A9C1,#007fa3); color:#fff; padding:9px 28px; border:none; border-radius:8px; font-family:var(--font); font-size:13px; font-weight:600; cursor:pointer; transition:all 0.2s; box-shadow:0 3px 10px rgba(0,169,193,0.3); }
        .btn-next-detail:hover { box-shadow:0 5px 16px rgba(0,169,193,0.5); transform:translateY(-1px); }
        
        /* ── MODAL EDIT ── */
        .modal-edit-wrap {
        background:#fff; border-radius:16px;
        width:100%; max-width:680px;
        max-height:92vh; overflow-y:auto;
        box-shadow:0 24px 80px rgba(0,180,200,0.18), 0 8px 32px rgba(0,0,0,0.15);
        border:1px solid rgba(0,169,193,0.15);
        }

        .modal-edit-header {
        padding:24px 32px 18px;
        background:linear-gradient(135deg, #00A9C1 0%, #007fa3 60%, #005f82 100%);
        border-radius:16px 16px 0 0;
        display:flex; align-items:center; justify-content:space-between;
        box-shadow:0 4px 16px rgba(0,169,193,0.3);
}
        .modal-edit-header h3 { font-size:18px; font-weight:700; color:#fff; letter-spacing:0.3px; }
        .modal-edit-close { background:rgba(255,255,255,0.2); border:none; font-size:18px; cursor:pointer; color:#fff; border-radius:50%; width:32px; height:32px; display:flex; align-items:center; justify-content:center; transition:background 0.2s; }
        .modal-edit-close:hover { background:rgba(255,255,255,0.35); }

        .modal-edit-body { padding:24px 32px; background:#fafcfe; }

        .edit-section-title {
        font-size:12px; font-weight:700;
        color:#00A9C1;
        text-transform:uppercase; letter-spacing:0.8px;
        margin:22px 0 14px; padding:8px 14px;
        background:linear-gradient(135deg, rgba(0,169,193,0.08), rgba(0,127,163,0.05));
        border-left:3px solid #00A9C1;
        border-radius:0 6px 6px 0;
}
        .edit-section-title:first-child { margin-top:0; }

        .edit-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
        .edit-grid.one { grid-template-columns:1fr; }

        .ef-group { margin-bottom:0; }
        .ef-label { display:block; font-size:13px; font-weight:500; color:#2d4a5a; margin-bottom:5px; }
        .ef-label .req { color:#e05252; }
        .ef-input, .ef-select, .ef-textarea {
        width:100%; padding:10px 13px;
        border:1.5px solid #d0e8ef; border-radius:8px;
        font-family:var(--font); font-size:13px; color:var(--text);
        background:#fff; outline:none; appearance:none;
        transition:border-color 0.2s, box-shadow 0.2s;
        box-shadow:0 1px 4px rgba(0,169,193,0.06);
}
        .ef-input:focus, .ef-select:focus, .ef-textarea:focus {
    border-color:#00A9C1;
    box-shadow:0 0 0 3px rgba(0,169,193,0.15), 0 2px 8px rgba(0,169,193,0.1);
}
        .ef-textarea { resize:vertical; min-height:72px; }
        .ef-select {
        background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2300A9C1' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
        background-repeat:no-repeat; background-position:right 10px center; padding-right:30px;
}

        .status-validasi-box {
        background:linear-gradient(135deg, rgba(0,169,193,0.06), rgba(0,127,163,0.04));
        border:2px solid #00A9C1;
        border-radius:12px; padding:18px 20px; margin-top:8px;
        box-shadow:0 2px 12px rgba(0,169,193,0.1);
}
        .status-validasi-title {
        font-size:13px; font-weight:700; color:#00A9C1;
        margin-bottom:12px; display:flex; align-items:center; gap:6px;
}
        .status-validasi-title svg { width:16px; height:16px; }

        .status-radio-group { display:flex; flex-direction:column; gap:8px; }
        .status-radio-label {
        display:flex; align-items:center; gap:10px;
        padding:11px 14px; border-radius:9px; cursor:pointer;
        border:2px solid #d0e8ef; transition:all 0.18s;
        font-size:13px; font-weight:500; background:#fff;
}
        .status-radio-label:hover { border-color:#00A9C1; background:#f0fbfc; box-shadow:0 2px 8px rgba(0,169,193,0.1); }
        .status-radio-label input[type="radio"] { accent-color:#00A9C1; width:16px; height:16px; }
        .status-radio-label.checked-belum { border-color:#e05252; background:linear-gradient(135deg,#fff5f5,#fff0f0); color:#c0392b; box-shadow:0 2px 8px rgba(224,82,82,0.15); }
        .status-radio-label.checked-sudah { border-color:#6aa84f; background:linear-gradient(135deg,#f0f9f0,#eaf7ea); color:#4e8a35; box-shadow:0 2px 8px rgba(106,168,79,0.15); }
        .status-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; }
        .dot-red   { background:#e05252; }
        .dot-blue  { background:#00A9C1; }
        .dot-green { background:#6aa84f; }

        .modal-edit-footer {
        padding:16px 32px 24px;
        border-top:1px solid #d0e8ef;
        background:#f0f8fb;
        border-radius:0 0 16px 16px;
        display:flex; gap:10px; justify-content:flex-end;
}
        .btn-batal-edit { background:#fff; color:#2d4a5a; padding:10px 22px; border:1.5px solid #d0e8ef; border-radius:8px; font-family:var(--font); font-size:13px; font-weight:500; cursor:pointer; transition:all 0.15s; }
        .btn-batal-edit:hover { border-color:#00A9C1; color:#00A9C1; }
        .btn-simpan-edit { background:linear-gradient(135deg, #00A9C1 0%, #007fa3 100%); color:#fff; padding:10px 28px; border:none; border-radius:8px; font-family:var(--font); font-size:14px; font-weight:600; cursor:pointer; transition:all 0.2s; box-shadow:0 4px 14px rgba(0,169,193,0.35); }
        .btn-simpan-edit:hover { box-shadow:0 6px 20px rgba(0,169,193,0.5); transform:translateY(-1px); }

        /* ── MODAL HAPUS ── */
        .modal-hapus-wrap { background:#fff; border-radius:12px; padding:28px 32px; max-width:420px; width:100%; box-shadow:0 20px 60px rgba(0,0,0,0.2); }
        .modal-hapus-wrap h3 { font-size:17px; font-weight:700; margin-bottom:8px; }
        .modal-hapus-wrap p  { font-size:14px; color:var(--muted); margin-bottom:24px; line-height:1.6; }
        .hapus-actions { display:flex; gap:10px; justify-content:flex-end; }
        .btn-batal-hapus  { background:#f1f5f9; color:var(--text); padding:9px 20px; border:1px solid var(--border); border-radius:7px; font-family:var(--font); font-size:13px; cursor:pointer; }
        .btn-confirm-hapus { background:#e05252; color:#fff; padding:9px 20px; border:none; border-radius:7px; font-family:var(--font); font-size:13px; font-weight:600; cursor:pointer; }

        /* ── RESPONSIVE ── */
        @media (max-width:1024px) and (min-width:769px) {
            :root { --sidebar-w:200px; }
            .main { padding:24px 20px; }
        }
        @media (max-width:768px) {
            .topbar { display:flex; }
            .sidebar { transform:translateX(-100%); }
            .sidebar.open { transform:translateX(0); }
            .main { margin-left:0; padding:calc(var(--topbar-h)+20px) 14px 32px; }
            .stats-row { grid-template-columns:1fr; gap:10px; }
            .edit-grid { grid-template-columns:1fr; }
            .detail-page { padding:0 20px 24px; }
            .detail-footer { padding:14px 20px 20px; }
            .modal-edit-body { padding:20px; }
            .modal-edit-footer { padding:14px 20px 20px; }
        }
    </style>
</head>
<body>

<div class="overlay" id="overlay" onclick="closeSidebar()"></div>

<!-- Topbar mobile -->
<header class="topbar">
    <div class="topbar-brand">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        Admin Panel
    </div>
    <button class="hamburger" id="hamburger" onclick="toggleSidebar()" aria-label="Buka menu">
        <span></span><span></span><span></span>
    </button>
</header>

<!-- SIDEBAR -->
<?php $activePage = 'dashboard'; include 'sidebar.php'; ?>

<!-- MAIN -->
<main class="main">
    <div class="breadcrumb">
        <a href="admin_dashboard.php">Dashboard</a>
        <span> / My Dashboard / Super Dashboard</span>
    </div>

    <?php if (isset($_GET['msg'])): ?>
    <div class="flash flash-success">
        <?= $_GET['msg']==='hapus' ? '✓ Laporan berhasil dihapus.' : '✓ Laporan berhasil diperbarui.' ?>
    </div>
    <?php endif; ?>

    <!-- Stat Cards -->
    <div class="stats-row">
        <div class="stat-card blue">
            <div class="stat-num"><?= $total_laporan ?></div>
            <div class="stat-main-label">Laporan masuk</div>
            <div class="stat-sub-label">Total Laporan Masuk</div>
        </div>
        <div class="stat-card red">
            <div class="stat-num"><?= $belum ?></div>
            <div class="stat-main-label">Belum Ditangani</div>
            <div class="stat-sub-label">Belum Ditangani</div>
        </div>
        <div class="stat-card green">
            <div class="stat-num"><?= $ditangani ?></div>
            <div class="stat-main-label">Telah Ditangani</div>
            <div class="stat-sub-label">Telah Ditangani</div>
        </div>
    </div>

    <?php if ($diproses > 0): ?>
    <div style="margin-bottom:16px;font-size:13px;color:var(--muted);background:#fff;border:1px solid var(--border);border-radius:8px;padding:10px 16px;display:inline-flex;align-items:center;gap:8px">
        <span style="width:10px;height:10px;background:#5baee0;border-radius:50%;display:inline-block;flex-shrink:0"></span>
        <span><strong style="color:var(--text)"><?= $diproses ?> laporan</strong> sedang dalam proses penanganan</span>
    </div>
    <?php endif; ?>

    <!-- Table -->
    <div class="table-section">
        <div class="table-header">
            <div class="table-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                Semua Laporan
            </div>
            <form method="GET" class="search-wrap" id="searchForm">
                <label>Search</label>
                <div style="position:relative;display:inline-flex;align-items:center">
                    <input type="text" name="search" placeholder="Text" id="searchInput"
                           value="<?= htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           style="padding-right:28px">
                    <?php if (!empty($_GET['search'])): ?>
                    <a href="admin_dashboard.php" 
                       style="position:absolute;right:8px;color:#94a3b8;font-size:16px;text-decoration:none;line-height:1"
                       title="Hapus pencarian">✕</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <div class="tbl-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Area Inspeksi</th>
                        <th>Kondisi Cuaca</th>
                        <th>Grid</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                    <tr><td colspan="7"><div class="empty-state">Belum ada laporan masuk.</div></td></tr>
                    <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($row['nama_petugas'] ?: ($row['nama'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></strong></td>
                        <td><?= htmlspecialchars($row['area_inspeksi'],ENT_QUOTES,'UTF-8') ?></td>
                        <td><?= htmlspecialchars($row['kondisi_cuaca'],ENT_QUOTES,'UTF-8') ?></td>
                        <td style="font-family:monospace;font-size:12px"><?= htmlspecialchars($row['grid_lokasi']??'-',ENT_QUOTES,'UTF-8') ?></td>
                        <td style="white-space:nowrap"><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                        <td>
                            <?php
                            $st = $row['status'];
                            if ($st==='sudah') echo '<span class="badge badge-green">Ditangani</span>';
                            else echo '<span class="badge badge-red">Belum<br>Ditangani</span>';
                            ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-detail" onclick="openDetail(<?= $row['id'] ?>)">Detail</button>
                                <button class="btn btn-edit"   onclick="openEdit(<?= $row['id'] ?>)">Edit</button>
                                <button class="btn btn-hapus"  onclick="openHapus(<?= $row['id'] ?>,'<?= htmlspecialchars($row['nama_petugas'],ENT_QUOTES,'UTF-8') ?>')">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- ═══════════════════════════════════════════════
     MODAL DETAIL — 2 halaman sesuai gambar referensi
═══════════════════════════════════════════════ -->
<div class="modal-bg" id="modalDetail">
    <div class="modal-detail-wrap">
        <!-- Watermark InJourney -->
        <div class="detail-watermark" aria-hidden="true"></div>

        <div class="detail-header-bar">
        <button class="detail-close-btn" onclick="closeModal('modalDetail')" aria-label="Tutup">✕</button>
        <div class="detail-title" id="detailTitle">Detail Laporan</div>
        </div>
        <div class="detail-divider"></div>

        <!-- Halaman 1: Info Petugas -->
        <div class="detail-page active" id="detailPage1">
            <div class="rf-group">
                <label class="rf-label">Nama Petugas :</label>
                <div class="rf-box" id="d_nama">—</div>
            </div>
            <div class="rf-group">
                <label class="rf-label">Tanggal Pemantauan :</label>
                <div class="rf-box" id="d_tanggal">—</div>
            </div>
            <div class="rf-group">
                <label class="rf-label">Kondisi Cuaca :</label>
                <div class="rf-box" id="d_cuaca">—</div>
            </div>
            <div class="rf-group">
                <label class="rf-label">Unit Kerja :</label>
                <div class="rf-box" id="d_unit">—</div>
            </div>
            <div class="rf-group">
                <label class="rf-label">Area Inspeksi :</label>
                <div class="rf-box" id="d_area">—</div>
            </div>
            <div class="rf-group">
                <label class="rf-label">Tanda Tangan :</label>
                <div class="rf-box tall" id="d_ttd">—</div>
            </div>
        </div>

        <!-- Halaman 2: Satwa + Kondisi + PDF -->
        <div class="detail-page" id="detailPage2">
            <!-- JENIS SATWA -->
            <div class="satwa-found-title">JENIS SATWA YANG DITEMUKAN</div>
            <div class="satwa-found-grid" id="d_satwa_grid">
                <div style="text-align:center;padding:16px;color:var(--muted);font-size:13px;grid-column:1/-1">Memuat...</div>
            </div>
            <div class="rf-group" style="margin-top:16px">
                <label class="rf-label">Ciri-ciri &amp; Ukuran Satwa Liar yang Ditemukan :</label>
                <div class="rf-box" id="d_ciri">—</div>
            </div>
            <div class="rf-group">
                <label class="rf-label">Kondisi Ditemukan di Area Apron :</label>
                <div class="rf-box" id="d_kondisi">—</div>
            </div>
            <div class="rf-group">
                <label class="rf-label">Aktivitas Satwa Liar di Area Apron :</label>
                <div class="rf-box" id="d_aktivitas">—</div>
            </div>
            <div class="rf-group">
                <label class="rf-label">Tindak Lanjut yang Dilakukan di Area Apron :</label>
                <div class="rf-box" id="d_tindak">—</div>
            </div>
            <div class="rf-group">
                <label class="rf-label">Detail Pengusiran yang Dilakukan :</label>
                <div class="rf-box" id="d_pengusiran">—</div>
            </div>
        </div>

        <!-- Loading -->
        <div id="detailLoading" style="text-align:center;padding:40px;color:var(--muted);display:none">
            <div style="font-size:14px">Memuat data laporan...</div>
        </div>

        <!-- Footer navigasi -->
        <div class="detail-footer" id="detailFooter1">
            <button class="btn-next-detail" onclick="detailNextPage()">
                Next →
            </button>
        </div>
        <div class="detail-footer" id="detailFooter2" style="display:none">
            <button class="btn-kembali" onclick="detailPrevPage()">← Kembali</button>
            <button class="btn-pdf" onclick="unduhPDF()" id="btnPDF">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Unduh PDF
            </button>
        </div>
    </div>
</div><!-- /modal-bg modalDetail -->

<!-- ═══════════════════════════════════════════════
     MODAL EDIT — Form lengkap + validasi status admin
═══════════════════════════════════════════════ -->
<div class="modal-bg" id="modalEdit">
    <div class="modal-edit-wrap">
        <div class="modal-edit-header">
            <h3>Edit & Validasi Laporan</h3>
            <button class="modal-edit-close" onclick="closeModal('modalEdit')">✕</button>
        </div>
        <form method="POST" id="editForm">
            <input type="hidden" name="aksi" value="edit_full">
            <input type="hidden" name="laporan_id" id="editId">

            <div class="modal-edit-body">

                <!-- Seksi 1: Identitas -->
                <div class="edit-section-title">Identitas Laporan</div>
                <div class="edit-grid">
                    <div class="ef-group">
                        <label class="ef-label">Nama Petugas <span class="req">*</span></label>
                        <input type="text" class="ef-input" name="nama_petugas" id="e_nama" required>
                    </div>
                    <div class="ef-group">
                        <label class="ef-label">Tanggal Pemantauan <span class="req">*</span></label>
                        <input type="date" class="ef-input" name="tanggal" id="e_tanggal" required>
                    </div>
                    <div class="ef-group">
                        <label class="ef-label">Kondisi Cuaca <span class="req">*</span></label>
                        <select class="ef-select" name="kondisi_cuaca" id="e_cuaca" required>
                            <option value="">-- Pilih kondisi cuaca --</option>
                            <?php foreach($kondisi_cuaca_opt as $k): ?>
                            <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($k) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="ef-group">
                        <label class="ef-label">Unit Kerja <span style="color:#888;font-weight:400;font-size:11px">(otomatis sesuai divisi pelapor)</span></label>
                        <input type="text" class="ef-input" id="e_unit_display" readonly style="background:#f0f4f7;color:#555;cursor:not-allowed;">
                        <input type="hidden" name="unit_kerja" id="e_unit">
                    </div>
                    <div class="ef-group">
                        <label class="ef-label">Area Inspeksi <span class="req">*</span></label>
                        <select class="ef-select" name="area_inspeksi" id="e_area" required>
                            <option value="">-- Pilih area inspeksi --</option>
                            <?php foreach($area_opt as $a): ?>
                            <option value="<?= htmlspecialchars($a) ?>"><?= htmlspecialchars($a) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Tanda Tangan (preview saja, tidak bisa diedit manual) -->
                <div class="ef-group" style="margin-top:14px">
                    <label class="ef-label">Tanda Tangan</label>
                    <div id="e_ttd_preview" style="display:flex;align-items:center;justify-content:center;min-height:80px;background:#f7fbfd;border:1.5px solid #d0e8ef;border-radius:8px;padding:8px;">
                        <span style="color:#aaa;font-size:12px">Tidak ada tanda tangan</span>
                    </div>
                    <input type="hidden" name="tanda_tangan" id="e_ttd">
                </div>

                <!-- Seksi 1b: Daftar Satwa (read-only) -->
                <div class="edit-section-title">Jenis Satwa yang Ditemukan (Read-only)</div>
                <div id="e_satwa_list" style="margin-bottom:8px;">
                    <div style="color:#aaa;font-size:13px;padding:8px 0;">Memuat data satwa...</div>
                </div>

                <!-- Seksi 2: Data Satwa -->
                <div class="edit-section-title">Data Satwa yang Ditemukan</div>
                <div class="edit-grid one">
                    <div class="ef-group">
                        <label class="ef-label">Ciri-ciri &amp; Ukuran Satwa Liar yang Ditemukan <span class="req">*</span></label>
                        <textarea class="ef-textarea" name="ciri_ukuran" id="e_ciri" placeholder="Deskripsikan ukuran, warna, ciri fisik satwa..." required></textarea>
                    </div>
                    <div class="ef-group">
                        <label class="ef-label">Kondisi Ditemukan di Area Apron (Hidup, Mati, Tidak Ditemukan) <span class="req">*</span></label>
                        <textarea class="ef-textarea" name="kondisi_apron" id="e_kondisi" placeholder="Deskripsikan kondisi satwa saat ditemukan..." required></textarea>
                    </div>
                    <div class="ef-group">
                        <label class="ef-label">Aktivitas Satwa Liar di Area Apron <span class="req">*</span></label>
                        <textarea class="ef-textarea" name="aktivitas_satwa" id="e_aktivitas" placeholder="Terbang, berjalan, berkelompok, dll..." required></textarea>
                    </div>
                    <div class="ef-group">
                        <label class="ef-label">Tindak Lanjut yang Dilakukan di Area Apron (Telah ditangani / Belum ditangani) <span class="req">*</span></label>
                        <textarea class="ef-textarea" name="tindak_lanjut" id="e_tindak" placeholder="Deskripsikan tindak lanjut..." required></textarea>
                    </div>
                    <div class="ef-group">
                        <label class="ef-label">Detail Pengusiran yang Dilakukan (isi "-" jika belum melakukan pengusiran) <span class="req">*</span></label>
                        <textarea class="ef-textarea" name="detail_pengusiran" id="e_pengusiran" placeholder='Metode pengusiran. Isi "-" jika belum dilakukan.' required></textarea>
                    </div>
                </div>

                <!-- Seksi 3: VALIDASI STATUS — area utama admin -->
                <div class="edit-section-title">Validasi Status oleh Admin</div>
                <div class="status-validasi-box">
                    <div class="status-validasi-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                        Ubah status laporan ini:
                    </div>
                    <div class="status-radio-group">
                        <label class="status-radio-label" id="lbl_belum" onclick="highlightStatus('belum')">
                            <input type="radio" name="status_validasi" value="belum" id="r_belum">
                            <span class="status-dot dot-red"></span>
                            <span>
                                <strong>Belum Ditangani</strong><br>
                                <span style="font-size:11px;opacity:0.7">Laporan baru masuk, belum ada tindakan</span>
                            </span>
                        </label>
                        <label class="status-radio-label" id="lbl_sudah" onclick="highlightStatus('sudah')">
                            <input type="radio" name="status_validasi" value="sudah" id="r_sudah">
                            <span class="status-dot dot-green"></span>
                            <span>
                                <strong>Telah Ditangani ✓</strong><br>
                                <span style="font-size:11px;opacity:0.7">Laporan sudah selesai ditangani</span>
                            </span>
                        </label>
                    </div>
                </div>

            </div><!-- /modal-edit-body -->

            <div class="modal-edit-footer">
                <button type="button" class="btn-batal-edit" onclick="closeModal('modalEdit')">Batal</button>
                <button type="submit" class="btn-simpan-edit">Simpan & Validasi</button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════
     MODAL HAPUS
═══════════════════════════════════════════════ -->
<div class="modal-bg" id="modalHapus">
    <div class="modal-hapus-wrap">
        <h3>⚠️ Hapus Laporan?</h3>
        <p>Laporan dari <strong id="hapusNama"></strong> akan dihapus permanen dan tidak dapat dikembalikan.</p>
        <form method="POST">
            <input type="hidden" name="aksi" value="hapus">
            <input type="hidden" name="laporan_id" id="hapusId">
            <div class="hapus-actions">
                <button type="button" class="btn-batal-hapus" onclick="closeModal('modalHapus')">Batal</button>
                <button type="submit" class="btn-confirm-hapus">Ya, Hapus</button>
            </div>
        </form>
    </div>
</div>

<script>
// ══ SIDEBAR ══════════════════════════════════════
function toggleSidebar(){
    const s=document.getElementById('sidebar'),o=document.getElementById('overlay'),h=document.getElementById('hamburger');
    s.classList.contains('open')?closeSidebar():(s.classList.add('open'),o.classList.add('show'),h.classList.add('open'),document.body.style.overflow='hidden');
}
function closeSidebar(){
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('overlay').classList.remove('show');
    document.getElementById('hamburger').classList.remove('open');
    document.body.style.overflow='';
}
window.addEventListener('resize',()=>{ if(window.innerWidth>768) closeSidebar(); });

// ══ MODAL HELPERS ════════════════════════════════
function closeModal(id){
    document.getElementById(id).classList.remove('show');
}
document.querySelectorAll('.modal-bg').forEach(m=>{
    m.addEventListener('click',e=>{ if(e.target===m) m.classList.remove('show'); });
});

// ══ MODAL DETAIL ═════════════════════════════════
let currentDetailId = null;
let currentDetailData = null;

function openDetail(id){
    currentDetailId = id;
    // Reset ke halaman 1
    document.getElementById('detailPage1').classList.add('active');
    document.getElementById('detailPage2').classList.remove('active');
    document.getElementById('detailFooter1').style.display='flex';
    document.getElementById('detailFooter2').style.display='none';
    document.getElementById('modalDetail').classList.add('show');

    // Set loading
    ['d_tanggal','d_nama','d_cuaca','d_unit','d_area','d_ttd',
     'd_ciri','d_kondisi','d_aktivitas','d_tindak','d_pengusiran'].forEach(id=>{
        document.getElementById(id).textContent='Memuat...';
    });
    document.getElementById('d_satwa_grid').innerHTML='<div style="text-align:center;padding:16px;color:#718096;grid-column:1/-1">Memuat...</div>';

    fetch('get_detail_laporan.php?id='+id)
        .then(r=>r.json())
        .then(d=>{
            if(!d||d.error){ alert('Error: ' + (d ? d.error : 'Respon tidak valid')); return; }
            currentDetailData = d;

            function decodeHTML(str) {
                const txt = document.createElement('textarea');
                txt.innerHTML = str || '';
                return txt.value;
            }

            // Isi halaman 1
            document.getElementById('d_nama').textContent    = decodeHTML(d.nama_petugas)  || '-';
            document.getElementById('d_tanggal').textContent = decodeHTML(d.tanggal)       || '-';
            document.getElementById('d_cuaca').textContent   = decodeHTML(d.kondisi_cuaca) || '-';
            document.getElementById('d_unit').textContent    = decodeHTML(d.unit_kerja)    || '-';
            document.getElementById('d_area').textContent    = decodeHTML(d.area_inspeksi) || '-';

            const ttdVal = d.tanda_tangan || '';
            document.getElementById('d_ttd').innerHTML = ttdVal.startsWith('data:image')
                ? `<img src="${ttdVal}" alt="Tanda tangan" style="max-width:100%;max-height:120px;background:#fff;border-radius:8px">`
                : (decodeHTML(ttdVal) || '-');

            // Isi halaman 2
            document.getElementById('d_ciri').textContent       = decodeHTML(d.ciri_ukuran)       || '-';
            document.getElementById('d_kondisi').textContent    = decodeHTML(d.kondisi_apron)     || '-';
            document.getElementById('d_aktivitas').textContent  = decodeHTML(d.aktivitas_satwa)   || '-';
            document.getElementById('d_tindak').textContent     = decodeHTML(d.tindak_lanjut)     || '-';
            document.getElementById('d_pengusiran').textContent = decodeHTML(d.detail_pengusiran) || '-';

            // Satwa grid
            const emojiMap = {
                'Ular': '🐍', 'Biawak': '🦎', 'Tikus': '🐭', 'Kucing': '🐱',
                'Anjing': '🐶', 'Kelinci': '🐰', 'Kerbau': '🐃', 'Sapi': '🐄', 'Kambing': '🐐',
                'Burung Kuntul Kecil': '🦢', 'Burung Blekok Sawah': '🦢',
                'Burung Pecuk Padi Kecil': '🦆', 'Burung Bangau': '🦢',
                'Burung Elang': '🦅', 'Burung Gagak': '🐦‍⬛',
                'Burung Merpati': '🕊️', 'Burung Hantu': '🦉',
            };
            function getIcon(nama) {
                if (emojiMap[nama]) return emojiMap[nama];
                const n = (nama || '').toLowerCase();
                if (n.includes('burung') || n.includes('bangau') || n.includes('kuntul') || n.includes('blekok') || n.includes('pecuk') || n.includes('elang') || n.includes('gagak') || n.includes('merpati')) return '🦅';
                if (n.includes('ular')) return '🐍';
                if (n.includes('biawak') || n.includes('buaya')) return '🦎';
                if (n.includes('tikus')) return '🐭';
                if (n.includes('kucing')) return '🐱';
                if (n.includes('anjing')) return '🐶';
                return '🐾';
            }
            const satwaArr = (d.satwa || []).filter(s => s.nama !== 'Satwa Tidak Terdaftar');
            const fotoExtra = d.foto_extra || [];
            const satwaCards = satwaArr.map(s=>`
                <div class="satwa-found-card">
                    <div class="satwa-found-img-placeholder">${s.foto_path ? `<img src="${s.foto_path}" alt="${s.nama}" style="width:100%;height:100%;object-fit:cover;border-radius:6px;">` : getIcon(s.nama)}</div>
                    <div class="satwa-found-name">${s.nama}</div>
                    <div class="satwa-found-meta">${s.jumlah} ekor</div>
                    <div class="satwa-found-meta">Lokasi ${s.grid || '-'}</div>
                </div>`).join('');
            const extraSatwaList = (d.satwa || []).filter(s => s.nama === 'Satwa Tidak Terdaftar');
            const extraCards = fotoExtra.map((path,i) => {
                const es = extraSatwaList[i] || null;
                return `
                <div class="satwa-found-card">
                    <div class="satwa-found-img-placeholder" style="overflow:hidden;">
                        <img src="${path}" style="width:100%;height:100%;object-fit:cover;border-radius:6px;" onerror="this.parentElement.innerHTML='[foto]'">
                    </div>
                    <div class="satwa-found-name">Satwa Tidak Terdaftar</div>
                    <div class="satwa-found-meta">${es ? es.jumlah + ' ekor' : 'Foto #' + (i+1)}</div>
                    <div class="satwa-found-meta">Lokasi ${es ? (es.grid || '-') : '-'}</div>
                </div>`;}).join('');
            const allCards = satwaCards + extraCards;
            document.getElementById('d_satwa_grid').innerHTML = allCards ||
                '<div style="text-align:center;padding:16px;color:#718096;grid-column:1/-1">Tidak ada data satwa.</div>';
        })
        .catch(()=>{ alert('Gagal terhubung ke server.'); });
}

function detailNextPage(){
    document.getElementById('detailPage1').classList.remove('active');
    document.getElementById('detailPage2').classList.add('active');
    document.getElementById('detailFooter1').style.display='none';
    document.getElementById('detailFooter2').style.display='flex';
    document.querySelector('.modal-detail-wrap').scrollTop = 0;
}

function detailPrevPage(){
    document.getElementById('detailPage2').classList.remove('active');
    document.getElementById('detailPage1').classList.add('active');
    document.getElementById('detailFooter2').style.display='none';
    document.getElementById('detailFooter1').style.display='flex';
    document.querySelector('.modal-detail-wrap').scrollTop = 0;
}

// Unduh PDF — redirect ke halaman cetak
function unduhPDF(){
    if(!currentDetailId) return;
    window.open('cetak_laporan.php?id='+currentDetailId, '_blank');
}

// ══ MODAL EDIT ═══════════════════════════════════
function openEdit(id){
    document.getElementById('editId').value = id;

    // Reset radio
    document.querySelectorAll('.status-radio-label').forEach(l=>l.className='status-radio-label');

    fetch('get_detail_laporan.php?id='+id)
        .then(r=>r.json())
        .then(d=>{
            if(!d||d.error){ alert('Gagal memuat data.'); return; }

            document.getElementById('e_nama').value      = d.nama_petugas || '';
            document.getElementById('e_tanggal').value   = d.tanggal      || '';
            document.getElementById('e_cuaca').value     = d.kondisi_cuaca || '';
            document.getElementById('e_unit_display').value = d.unit_kerja || '-';
            document.getElementById('e_unit').value          = d.unit_kerja || '';
            document.getElementById('e_area').value      = d.area_inspeksi || '';
            document.getElementById('e_ciri').value      = d.ciri_ukuran  || '';
            document.getElementById('e_kondisi').value   = d.kondisi_apron || '';
            document.getElementById('e_aktivitas').value = d.aktivitas_satwa || '';
            document.getElementById('e_tindak').value    = d.tindak_lanjut || '';
            document.getElementById('e_pengusiran').value= d.detail_pengusiran || '';

            // Tanda tangan — preview gambar, simpan value asli di hidden input
            const ttdInput = document.getElementById('e_ttd');
            ttdInput.value = d.tanda_tangan || '';
            const ttdPrev = document.getElementById('e_ttd_preview');
            if (ttdPrev) {
                ttdPrev.innerHTML = (d.tanda_tangan || '').startsWith('data:image')
                    ? `<img src="${d.tanda_tangan}" style="max-width:100%;max-height:90px;">`
                    : '<span style="color:#aaa;font-size:12px">Tidak ada tanda tangan</span>';
            }

            // Daftar satwa read-only
            const satwaListEl = document.getElementById('e_satwa_list');
            if (satwaListEl) {
                const satwaArr2 = d.satwa || [];
                satwaListEl.innerHTML = satwaArr2.length > 0
                    ? satwaArr2.map(s => `
                        <div style="display:flex;align-items:center;justify-content:space-between;background:#f7fbfd;border:1.5px solid #d0e8ef;border-radius:8px;padding:10px 14px;margin-bottom:6px;">
                            <span style="font-weight:600;font-size:13px;color:#1a202c;">${s.nama}</span>
                            <span style="font-size:12px;color:#718096;">Jumlah: ${s.jumlah} ekor &nbsp;·&nbsp; Grid: ${s.grid || '-'}</span>
                        </div>
                    `).join('')
                    : '<div style="color:#aaa;font-size:13px;padding:8px 0;">Tidak ada data satwa.</div>';
            }

            // Set radio status
            const st = (d.status === 'sudah') ? 'sudah' : 'belum';
            document.getElementById('r_'+st).checked = true;
            highlightStatus(st);
        });

    document.getElementById('modalEdit').classList.add('show');
}

function highlightStatus(val){
    ['belum','sudah'].forEach(s=>{
        const lbl = document.getElementById('lbl_'+s);
        if(lbl) lbl.className = 'status-radio-label' + (s===val ? ' checked-'+s : '');
    });
    const radio = document.getElementById('r_'+val);
    if(radio) radio.checked = true;
}
document.getElementById('r_belum')?.addEventListener('change', ()=>highlightStatus('belum'));
document.getElementById('r_sudah')?.addEventListener('change', ()=>highlightStatus('sudah'));

// ══ MODAL HAPUS ══════════════════════════════════
function openHapus(id, nama){
    document.getElementById('hapusId').value = id;
    document.getElementById('hapusNama').textContent = nama;
    document.getElementById('modalHapus').classList.add('show');
}
</script>
</body>
</html>