<?php
session_start();
// Cek login — kalau belum login, arahkan ke halaman login
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
require_once 'koneksi.php';
$activePage = 'laporan';
$tab        = $_GET['tab'] ?? 'fields';

$msg     = $_SESSION['msg']      ?? '';
$msgType = $_SESSION['msg_type'] ?? '';
unset($_SESSION['msg'], $_SESSION['msg_type']);

// ── Auto-setup tabel ─────────────────────────────────────────
$pdo->exec("CREATE TABLE IF NOT EXISTS `form_fields` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_field` VARCHAR(100) NOT NULL UNIQUE,
  `label` VARCHAR(200) NOT NULL,
  `tipe` ENUM('text','textarea','dropdown','date','file','number','grid') NOT NULL DEFAULT 'text',
  `placeholder` VARCHAR(200) DEFAULT '',
  `wajib` TINYINT(1) DEFAULT 1,
  `aktif` TINYINT(1) DEFAULT 1,
  `urutan` INT DEFAULT 0,
  `keterangan` VARCHAR(300) DEFAULT '',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB");

$pdo->exec("CREATE TABLE IF NOT EXISTS `form_field_options` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `field_id` INT NOT NULL,
  `nilai` VARCHAR(200) NOT NULL,
  `urutan` INT DEFAULT 0,
  FOREIGN KEY (`field_id`) REFERENCES `form_fields`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB");

$pdo->exec("CREATE TABLE IF NOT EXISTS `jenis_satwa` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(150) NOT NULL
) ENGINE=InnoDB");

$colsNama = $pdo->query("SHOW COLUMNS FROM jenis_satwa LIKE 'nama'")->fetchAll();
if (empty($colsNama)) $pdo->exec("ALTER TABLE jenis_satwa ADD COLUMN `nama` VARCHAR(150) NOT NULL DEFAULT ''");

$cols = $pdo->query("SHOW COLUMNS FROM jenis_satwa LIKE 'foto_path'")->fetchAll();
if (empty($cols)) $pdo->exec("ALTER TABLE jenis_satwa ADD COLUMN `foto_path` VARCHAR(255) DEFAULT NULL");

$colsGrid = $pdo->query("SHOW COLUMNS FROM form_fields LIKE 'gridmap_path'")->fetchAll();
if (empty($colsGrid)) $pdo->exec("ALTER TABLE form_fields ADD COLUMN `gridmap_path` VARCHAR(255) DEFAULT NULL");

// Pastikan semua kolom form_fields lengkap (jaga-jaga jika tabel sudah ada sebelumnya tanpa kolom ini)
$requiredCols = [
    'placeholder' => "VARCHAR(200) DEFAULT ''",
    'wajib'       => "TINYINT(1) DEFAULT 1",
    'aktif'       => "TINYINT(1) DEFAULT 1",
    'urutan'      => "INT DEFAULT 0",
    'keterangan'  => "VARCHAR(300) DEFAULT ''",
];
foreach ($requiredCols as $colName => $colDef) {
    $exists = $pdo->query("SHOW COLUMNS FROM form_fields LIKE '$colName'")->fetchAll();
    if (empty($exists)) {
        $pdo->exec("ALTER TABLE form_fields ADD COLUMN `$colName` $colDef");
    }
}

// Seed default fields jika kosong
if ((int)$pdo->query("SELECT COUNT(*) FROM form_fields")->fetchColumn() === 0) {
    $seeds = [
        ['nama_petugas','Nama Petugas','text','Nama lengkap petugas',1,1,1,''],
        ['tanggal','Tanggal Pemantauan','date','DD/MM/YY',1,1,2,''],
        ['kondisi_cuaca','Kondisi Cuaca','dropdown','Pilih kondisi cuaca',1,1,3,''],
        ['unit_kerja','Unit Kerja','text','Unit atau divisi',1,1,4,''],
        ['area_inspeksi','Area Inspeksi','dropdown','Pilih area inspeksi',1,1,5,''],
        ['grid_lokasi','Gambar Lokasi Pengamatan (Gridmap)','grid','Isi format X-Y (ex: 10-B)',1,1,6,'Klik pada peta atau isi manual'],
        ['tanda_tangan','Tanda Tangan','textarea','Tanda tangan / inisial',1,1,7,''],
        ['ciri_ukuran','Ciri-ciri Ukuran Satwa Liar','textarea','Deskripsikan ukuran satwa',1,1,8,''],
        ['kondisi_apron','Kondisi Ditemukan di Area Apron','text','Hidup, mati, tidak ditemukan',1,1,9,''],
        ['aktivitas_satwa','Aktivitas Satwa Liar di Area Apron','textarea','Jelaskan aktivitas satwa',1,1,10,''],
        ['tindak_lanjut','Tindak Lanjut','dropdown','Pilih tindak lanjut',1,1,11,''],
        ['detail_pengusiran','Detail Pengusiran','textarea','Isi "-" jika tidak ada pengusiran',0,1,12,''],
    ];
    $st = $pdo->prepare("INSERT IGNORE INTO form_fields (nama_field,label,tipe,placeholder,wajib,aktif,urutan,keterangan) VALUES (?,?,?,?,?,?,?,?)");
    foreach ($seeds as $s) $st->execute($s);

    // Seed options
    $fidCuaca  = $pdo->query("SELECT id FROM form_fields WHERE nama_field='kondisi_cuaca'")->fetchColumn();
    $fidArea   = $pdo->query("SELECT id FROM form_fields WHERE nama_field='area_inspeksi'")->fetchColumn();
    $fidTindak = $pdo->query("SELECT id FROM form_fields WHERE nama_field='tindak_lanjut'")->fetchColumn();
    $stOpt = $pdo->prepare("INSERT IGNORE INTO form_field_options (field_id,nilai,urutan) VALUES (?,?,?)");
    foreach (['Cerah','Berawan','Hujan','Berkabut'] as $i=>$v) $stOpt->execute([$fidCuaca,$v,$i+1]);
    foreach (['Runway','Apron A','Apron B','Apron C','Terminal 1','Terminal 2','Regulating Pond 1','Regulating Pond 2','Regulating Pond 3','West Scramble','East Scramble'] as $i=>$v) $stOpt->execute([$fidArea,$v,$i+1]);
    foreach (['Telah ditangani','Belum ditangani'] as $i=>$v) $stOpt->execute([$fidTindak,$v,$i+1]);
}

// ================================================================
// HANDLE POST
// ================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── USER: Tambah ─────────────────────────────────────────
    if ($action === 'tambah_user') {
        $nama     = trim($_POST['nama'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $role     = $_POST['role'] ?? 'pegawai';
        if ($nama && $username && $password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            try {
                $pdo->prepare("INSERT INTO users (namalengkap,username,password,role) VALUES (?,?,?,?)")
                    ->execute([$nama,$username,$hash,$role]);
                $_SESSION['msg']      = "User \"$username\" berhasil ditambahkan.";
                $_SESSION['msg_type'] = 'success';
            } catch (Exception $e) {
                $_SESSION['msg']      = "Username \"$username\" sudah digunakan.";
                $_SESSION['msg_type'] = 'error';
            }
        }
        header("Location: admin_manajemen.php?tab=users"); exit;
    }

    // ── USER: Edit ───────────────────────────────────────────
    if ($action === 'edit_user') {
        $id       = (int)($_POST['id'] ?? 0);
        $nama     = trim($_POST['nama'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $role     = $_POST['role'] ?? 'pegawai';
        $password = trim($_POST['password'] ?? '');
        if ($id && $nama && $username) {
            if ($password) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE users SET namalengkap=?,username=?,password=?,role=? WHERE id=?")
                    ->execute([$nama,$username,$hash,$role,$id]);
            } else {
                $pdo->prepare("UPDATE users SET namalengkap=?,username=?,role=? WHERE id=?")
                    ->execute([$nama,$username,$role,$id]);
            }
            $_SESSION['msg']      = "User berhasil diperbarui.";
            $_SESSION['msg_type'] = 'success';
        }
        header("Location: admin_manajemen.php?tab=users"); exit;
    }

    // ── USER: Toggle Aktif ───────────────────────────────────
    if ($action === 'toggle_user') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) $pdo->prepare("UPDATE users SET aktif = 1 - aktif WHERE id=?")->execute([$id]);
        header("Location: admin_manajemen.php?tab=users"); exit;
    }

    // ── USER: Hapus ──────────────────────────────────────────
    if ($action === 'hapus_user') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
        $_SESSION['msg']      = "User berhasil dihapus.";
        $_SESSION['msg_type'] = 'success';
        header("Location: admin_manajemen.php?tab=users"); exit;
    }


    // ── USER: Tambah ─────────────────────────────────────────
    if ($action === 'tambah_user') {
        $nama     = trim($_POST['nama'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $role     = $_POST['role'] ?? 'pegawai';
        if ($nama && $username && $password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            try {
                $pdo->prepare("INSERT INTO users (namalengkap,username,password,role) VALUES (?,?,?,?)")
                    ->execute([$nama,$username,$hash,$role]);
                $_SESSION['msg']      = "User \"$username\" berhasil ditambahkan.";
                $_SESSION['msg_type'] = 'success';
            } catch (Exception $e) {
                $_SESSION['msg']      = "Username \"$username\" sudah digunakan.";
                $_SESSION['msg_type'] = 'error';
            }
        }
        header("Location: admin_manajemen.php?tab=users"); exit;
    }

    // ── USER: Edit ───────────────────────────────────────────
    if ($action === 'edit_user') {
        $id       = (int)($_POST['id'] ?? 0);
        $nama     = trim($_POST['nama'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $role     = $_POST['role'] ?? 'pegawai';
        $password = trim($_POST['password'] ?? '');
        if ($id && $nama && $username) {
            if ($password) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE users SET namalengkap=?,username=?,password=?,role=? WHERE id=?")
                    ->execute([$nama,$username,$hash,$role,$id]);
            } else {
                $pdo->prepare("UPDATE users SET namalengkap=?,username=?,role=? WHERE id=?")
                    ->execute([$nama,$username,$role,$id]);
            }
            $_SESSION['msg']      = "User berhasil diperbarui.";
            $_SESSION['msg_type'] = 'success';
        }
        header("Location: admin_manajemen.php?tab=users"); exit;
    }

    // ── USER: Toggle Aktif ───────────────────────────────────
    if ($action === 'toggle_user') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) $pdo->prepare("UPDATE users SET aktif = 1 - aktif WHERE id=?")->execute([$id]);
        header("Location: admin_manajemen.php?tab=users"); exit;
    }

    // ── USER: Hapus ──────────────────────────────────────────
    if ($action === 'hapus_user') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
        $_SESSION['msg']      = "User berhasil dihapus.";
        $_SESSION['msg_type'] = 'success';
        header("Location: admin_manajemen.php?tab=users"); exit;
    }

    // ── FIELD: Tambah ────────────────────────────────────────
    if ($action === 'tambah_field') {
        $label       = trim($_POST['label'] ?? '');
        $tipe        = $_POST['tipe'] ?? 'text';
        $placeholder = trim($_POST['placeholder'] ?? '');
        $wajib       = isset($_POST['wajib']) ? 1 : 0;
        $keterangan  = trim($_POST['keterangan'] ?? '');
        if ($label) {
            $namaField = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $label)) . '_' . time();
            $maxUrut   = (int)$pdo->query("SELECT MAX(urutan) FROM form_fields")->fetchColumn();
            $pdo->prepare("INSERT INTO form_fields (nama_field,label,tipe,placeholder,wajib,aktif,urutan,keterangan) VALUES (?,?,?,?,?,1,?,?)")
                ->execute([$namaField,$label,$tipe,$placeholder,$wajib,$maxUrut+1,$keterangan]);
            $newId = $pdo->lastInsertId();
            // Simpan opsi dropdown
            if ($tipe === 'dropdown' && !empty($_POST['opsi'])) {
                $opsi = array_filter(array_map('trim', explode("\n", $_POST['opsi'])));
                $stOpt = $pdo->prepare("INSERT INTO form_field_options (field_id,nilai,urutan) VALUES (?,?,?)");
                foreach ($opsi as $i => $o) if ($o) $stOpt->execute([$newId,$o,$i+1]);
            }
            $_SESSION['msg']      = "Field \"$label\" berhasil ditambahkan.";
            $_SESSION['msg_type'] = 'success';
        }
        header("Location: admin_manajemen.php?tab=fields"); exit;
    }

    // ── FIELD: Edit ──────────────────────────────────────────
    if ($action === 'edit_field') {
        $id          = (int)$_POST['id'];
        $label       = trim($_POST['label'] ?? '');
        $tipe        = $_POST['tipe'] ?? 'text';
        $placeholder = trim($_POST['placeholder'] ?? '');
        $wajib       = isset($_POST['wajib']) ? 1 : 0;
        $keterangan  = trim($_POST['keterangan'] ?? '');
        if ($id && $label) {
            // Handle upload gambar gridmap
            $gridmapPath = null;
            if ($tipe === 'grid' && !empty($_FILES['gridmap_file']['name'])) {
                $file     = $_FILES['gridmap_file'];
                $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed  = ['jpg','jpeg','png','gif','webp'];
                if (in_array($ext, $allowed) && $file['error'] === 0) {
                    $filename    = 'gridmap_injourney.' . $ext;
                    $uploadPath  = $filename;
                    move_uploaded_file($file['tmp_name'], $uploadPath);
                    $gridmapPath = $uploadPath;
                }
            }

            if ($gridmapPath) {
                $pdo->prepare("UPDATE form_fields SET label=?,tipe=?,placeholder=?,wajib=?,keterangan=?,gridmap_path=? WHERE id=?")
                    ->execute([$label,$tipe,$placeholder,$wajib,$keterangan,$gridmapPath,$id]);
            } else {
                $pdo->prepare("UPDATE form_fields SET label=?,tipe=?,placeholder=?,wajib=?,keterangan=? WHERE id=?")
                    ->execute([$label,$tipe,$placeholder,$wajib,$keterangan,$id]);
            }

            // Update opsi dropdown
            if ($tipe === 'dropdown') {
                $pdo->prepare("DELETE FROM form_field_options WHERE field_id=?")->execute([$id]);
                if (!empty($_POST['opsi'])) {
                    $opsi  = array_filter(array_map('trim', explode("\n", $_POST['opsi'])));
                    $stOpt = $pdo->prepare("INSERT INTO form_field_options (field_id,nilai,urutan) VALUES (?,?,?)");
                    foreach ($opsi as $i => $o) if ($o) $stOpt->execute([$id,$o,$i+1]);
                }
            }
            $_SESSION['msg']      = "Field \"$label\" berhasil diperbarui.";
            $_SESSION['msg_type'] = 'success';
        }
        header("Location: admin_manajemen.php?tab=fields"); exit;
    }

    // ── FIELD: Toggle aktif ──────────────────────────────────
    if ($action === 'toggle_field') {
        $id = (int)$_POST['id'];
        $pdo->prepare("UPDATE form_fields SET aktif = 1 - aktif WHERE id=?")->execute([$id]);
        $_SESSION['msg']      = "Status field berhasil diubah.";
        $_SESSION['msg_type'] = 'success';
        header("Location: admin_manajemen.php?tab=fields"); exit;
    }

    // ── FIELD: Hapus ─────────────────────────────────────────
    if ($action === 'hapus_field') {
        $id = (int)$_POST['id'];
        $pdo->prepare("DELETE FROM form_fields WHERE id=?")->execute([$id]);
        $_SESSION['msg']      = "Field berhasil dihapus.";
        $_SESSION['msg_type'] = 'success';
        header("Location: admin_manajemen.php?tab=fields"); exit;
    }

    // ── FIELD: Ubah urutan ───────────────────────────────────
    if ($action === 'ubah_urutan') {
        $ids = $_POST['urutan'] ?? [];
        $st  = $pdo->prepare("UPDATE form_fields SET urutan=? WHERE id=?");
        foreach ($ids as $urut => $fieldId) $st->execute([$urut+1, (int)$fieldId]);
        echo 'ok'; exit;
    }

    // ── SATWA: Tambah ────────────────────────────────────────
    if ($action === 'tambah_satwa') {
        $nama = trim($_POST['nama'] ?? '');
        if ($nama) {
            $fotoPath = null;
            if (!empty($_FILES['foto']['name'])) {
                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                    $dir = 'uploads/satwa/';
                    if (!is_dir($dir)) mkdir($dir, 0755, true);
                    $fn = 'satwa_'.time().'_'.rand(100,999).'.'.$ext;
                    if (move_uploaded_file($_FILES['foto']['tmp_name'], $dir.$fn)) $fotoPath = $dir.$fn;
                }
            }
            $pdo->prepare("INSERT INTO jenis_satwa (nama,foto_path) VALUES (?,?)")->execute([$nama,$fotoPath]);
            $_SESSION['msg']      = "Satwa \"$nama\" berhasil ditambahkan.";
            $_SESSION['msg_type'] = 'success';
        }
        header("Location: admin_manajemen.php?tab=satwa"); exit;
    }

    // ── SATWA: Edit ──────────────────────────────────────────
    if ($action === 'edit_satwa') {
        $id = (int)$_POST['id']; $nama = trim($_POST['nama'] ?? '');
        if ($id && $nama) {
            $old = $pdo->prepare("SELECT foto_path FROM jenis_satwa WHERE id=?");
            $old->execute([$id]); $oldFoto = $old->fetchColumn();
            $fotoPath = $oldFoto;
            if (!empty($_FILES['foto']['name'])) {
                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                    $dir = 'uploads/satwa/';
                    if (!is_dir($dir)) mkdir($dir, 0755, true);
                    $fn = 'satwa_'.time().'_'.rand(100,999).'.'.$ext;
                    if (move_uploaded_file($_FILES['foto']['tmp_name'], $dir.$fn)) {
                        if ($oldFoto && file_exists($oldFoto)) @unlink($oldFoto);
                        $fotoPath = $dir.$fn;
                    }
                }
            }
            $pdo->prepare("UPDATE jenis_satwa SET nama=?,foto_path=? WHERE id=?")->execute([$nama,$fotoPath,$id]);
            $_SESSION['msg']      = "Satwa berhasil diperbarui.";
            $_SESSION['msg_type'] = 'success';
        }
        header("Location: admin_manajemen.php?tab=satwa"); exit;
    }

    // ── SATWA: Hapus ─────────────────────────────────────────
    if ($action === 'hapus_satwa') {
        $id = (int)$_POST['id'];
        if ($id) {
            $st = $pdo->prepare("SELECT foto_path FROM jenis_satwa WHERE id=?");
            $st->execute([$id]); $foto = $st->fetchColumn();
            if ($foto && file_exists($foto)) @unlink($foto);
            $pdo->prepare("DELETE FROM jenis_satwa WHERE id=?")->execute([$id]);
            $_SESSION['msg']      = "Satwa berhasil dihapus.";
            $_SESSION['msg_type'] = 'success';
        }
        header("Location: admin_manajemen.php?tab=satwa"); exit;
    }
}

// ── Auto-setup tabel users ───────────────────────────────────
$pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `nama`        VARCHAR(150) NOT NULL,
  `username`    VARCHAR(100) NOT NULL UNIQUE,
  `password`    VARCHAR(255) NOT NULL,
  `role`        ENUM('admin','pegawai') DEFAULT 'pegawai',
  `aktif`       TINYINT(1) DEFAULT 1,
  `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB");

// Pastikan kolom users lengkap jika tabel sudah ada sebelumnya tanpa kolom ini
$requiredUserCols = [
    'namalengkap' => "VARCHAR(150) NOT NULL DEFAULT ''",
    'role'       => "ENUM('admin','pegawai') DEFAULT 'pegawai'",
    'aktif'      => "TINYINT(1) DEFAULT 1",
    'created_at' => "DATETIME DEFAULT CURRENT_TIMESTAMP",
];
foreach ($requiredUserCols as $colName => $colDef) {
    $exists = $pdo->query("SHOW COLUMNS FROM users LIKE '$colName'")->fetchAll();
    if (empty($exists)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN `$colName` $colDef");
    }
}

// ── Ambil data ───────────────────────────────────────────────
$fields  = $pdo->query("SELECT *, COALESCE(gridmap_path,'') AS gridmap_path FROM form_fields ORDER BY urutan ASC")->fetchAll();
$satwas  = $pdo->query("SELECT * FROM jenis_satwa ORDER BY id")->fetchAll();
$users   = $pdo->query("SELECT id,namalengkap,username,role,1 AS aktif,created_at FROM users ORDER BY id ASC")->fetchAll();
$tipeList = ['text'=>'Teks Pendek','textarea'=>'Teks Panjang','dropdown'=>'Dropdown','date'=>'Tanggal','number'=>'Angka','file'=>'Upload File','grid'=>'Gridmap'];

// Ambil opsi per field dropdown
$opsiPerField = [];
$allOpsi = $pdo->query("SELECT * FROM form_field_options ORDER BY field_id, id")->fetchAll();
foreach ($allOpsi as $o) $opsiPerField[$o['field_id']][] = $o['nilai'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Report — <?= APP_NAME ?></title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
  :root{
    --primary:#00A9C1;
    --dark:#414141;

    --red:#D94F4F;
    --blue:#4FADC9;
    --yellow:#F0B14B;
    --green:#88B146;

    --bg:#F7FAFC;
    --card:#FFFFFF;
}
body{
    font-family:'DM Sans',sans-serif;
    display:flex;
    min-height:100vh;
    height:100vh;
    overflow:hidden;
    background:
        radial-gradient(
            circle at top left,
            rgba(0,169,193,.08),
            transparent 35%
        ),
        #F7FAFC;

    font-family:'DM Sans',sans-serif;
}
.sidebar{width:240px;min-width:240px;background:rgba(255,255,255,0.55);backdrop-filter:blur(20px);border-right:1px solid rgba(255,255,255,0.4);box-shadow:6px 0 30px rgba(0,0,0,.05);display:flex;flex-direction:column;height:100vh;flex-shrink:0}
.sb-user{display:flex;align-items:center;gap:12px;padding:24px 20px 20px;border-bottom:1px solid #e8edf2}
.sb-avatar{width:46px;height:46px;border-radius:50%;background:#00A9C1;display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:600;color:#fff;flex-shrink:0}
.sb-name{font-size:13.5px;font-weight:600;color:#414141}
.sb-role{font-size:12px;color:#888;margin-top:2px}
.sb-nav{flex:1;padding:16px 14px;display:flex;flex-direction:column;gap:4px}
.sb-item{display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:8px;font-size:14px;font-weight:500;color:#666;text-decoration:none;transition:all .15s}
.sb-item:hover{background:rgba(0,169,193,0.08);color:#00A9C1}
.sb-item.active{background:rgba(0,169,193,0.12);color:#00A9C1;border-left:4px solid #00A9C1}
.sb-icon{width:18px;height:18px;flex-shrink:0}
.sb-spacer{flex:1}
.sb-bottom{padding:14px 14px 20px;border-top:1px solid #e8edf2}
.sb-logout{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;color:#ef4444;font-size:14px;font-weight:500;text-decoration:none;transition:background .15s}
.sb-logout:hover{background:rgba(239,68,68,.12)}
.sb-item:focus,.sb-logout:focus{outline:none}
.sb-item:focus-visible,.sb-logout:focus-visible{outline:2px solid #00A9C1;outline-offset:2px;border-radius:8px}
.main{flex:1;display:flex;flex-direction:column;overflow:visible;min-height:0}

/* ── TOPBAR ── */
.topbar{
  background:#ffffff;
  border-bottom:1px solid #e8edf2;
  padding:0 20px;
  flex-shrink:0;
}
.topbar-top{
  display:flex;
  align-items:center;
  justify-content:space-between;
  height:52px;
}
.topbar-left{display:flex;align-items:center;gap:12px}
.page-icon{
  width:34px;height:34px;
  background:linear-gradient(135deg,#00A9C1,#4FADC9);
  border-radius:8px;
  display:flex;align-items:center;justify-content:center;
  color:#fff;flex-shrink:0;
}
.page-title{font-size:15px;font-weight:700;color:#2a2d3a;margin:0}
.page-subtitle{font-size:11px;color:#9a9a9a;margin-top:1px}
.topbar-right{display:flex;align-items:center;gap:8px}
.topbar-badge{
  display:inline-flex;align-items:center;gap:6px;
  background:#E6F7FA;color:#00A9C1;
  border:1px solid rgba(0,169,193,.25);
  border-radius:99px;padding:4px 10px;
  font-size:11px;font-weight:600;
}
.topbar-badge svg{flex-shrink:0}

/* ── TABS inside topbar ── */
.topbar-tabs{
  display:flex;
  gap:0;
  padding:0;
  height:42px;
  align-items:flex-end;
  border-top:1px solid #f0f0f0;
}
.tab-btn{
  display:flex;align-items:center;gap:7px;
  padding:0 20px;height:42px;
  border:none;border-bottom:3px solid transparent;
  font-family:inherit;font-size:13.5px;font-weight:500;
  cursor:pointer;color:#888;background:transparent;
  transition:all .18s;text-decoration:none;
  white-space:nowrap;
}
.tab-btn:hover{color:#414141;border-bottom-color:#d0d0d0}
.tab-btn.active{
  color:#00A9C1;
  border-bottom-color:#00A9C1;
  font-weight:600;
}
.badge-count{
  background:#00A9C1;color:#fff;
  font-size:10px;font-weight:700;
  padding:2px 7px;border-radius:99px;
}
.tab-btn:not(.active) .badge-count{background:#D0D0D0;color:#fff}

.content{flex:1;overflow-y:auto;padding:20px 24px;display:flex;flex-direction:column;gap:16px;height:calc(100vh - 106px)}

/* CARD */
.card{
    position:relative;
    background:white;
    border-radius:10px;
    overflow:hidden;
    border:1px solid #EEEEEE;
    box-shadow:0 2px 12px rgba(0,0,0,.06);
}
.card::before{
    content:'';
    position:absolute;
    top:0; left:0;
    width:100%; height:4px;
    background:linear-gradient(90deg,#00A9C1,#4FADC9,#88B146,#F0B14B,#D94F4F);
}
.card-header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    flex-wrap:wrap;
    gap:12px;
    padding:22px 24px 18px;
    border-bottom:1px solid #F5F5F5;
    margin-bottom:0;
}
.card-title{font-size:15px;font-weight:700;color:#414141;margin:0}
.card-sub{font-size:12.5px;color:#888;margin-top:3px}
.field-table-wrap{padding:0}
.satwa-grid-wrap{padding:20px 24px;overflow-y:auto;max-height:calc(100vh - 220px)}


/* BUTTON */
.btn{padding:7px 16px;border-radius:7px;border:none;font-family:inherit;font-size:13px;font-weight:500;cursor:pointer;transition:all .15s;display:inline-flex;align-items:center;gap:6px;text-decoration:none}
.btn-primary{
    background:
    linear-gradient(
        135deg,
        #00A9C1,
        #4FADC9
    );

    color:white;

    border:none;
}
.btn-danger{
    background:
      linear-gradient(
        135deg,
        #D94F4F,
        #C83F3F
      );

    color:white;
}
.btn-ghost{
    background:
      rgba(255,255,255,.6);

    backdrop-filter:
      blur(12px);

    border:
      1px solid rgba(255,255,255,.4);

    color:#414141;
}
.btn-warning{background:#f59e0b;color:#fff}.btn-warning:hover{background:#d97706}
.btn-sm{padding:4px 10px;font-size:12px;border-radius:6px}

/* FORM */
.form-group{display:flex;flex-direction:column;gap:5px;margin-bottom:12px}
.form-group label{font-size:12px;font-weight:500;color:#555}
.form-input{padding:8px 11px;border:1px solid #b8bac2;border-radius:7px;font-family:inherit;font-size:13px;background:#fff;color:#2a2d3a;outline:none;transition:border .15s}
.form-input:focus{border-color:#3b82f6}
.form-check{display:flex;align-items:center;gap:8px;font-size:13px;color:#444;cursor:pointer}
.form-check input{width:15px;height:15px;cursor:pointer}

/* FIELD TABLE */
.field-table-wrap{
    border-radius:0 0 10px 10px;
    overflow:hidden;
    border:none;
}
.field-table-wrap .table-scroll{
    max-height:calc(100vh - 280px);
    overflow-y:auto;
    border-radius:0 0 10px 10px;
}
/* Sticky header saat scroll */
.field-table-wrap .table-scroll thead th{
    position:sticky;
    top:0;
    z-index:2;
}
table{width:100%;border-collapse:collapse}
thead th{background:#FAFAFA;color:#555;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;padding:11px 16px;text-align:left;border-bottom:1.5px solid #EEEEEE;white-space:nowrap}
tbody td{padding:12px 16px;font-size:13px;border-bottom:1px solid #F5F5F5;color:#414141;background:white;vertical-align:middle}
tbody tr:last-child td{border-bottom:none}
tbody tr:hover td{background:#F9FFFE}

/* DRAG HANDLE */
.drag-handle{cursor:grab;color:#aaa;padding:2px 4px;border-radius:4px;display:inline-flex;align-items:center}
.drag-handle:hover{background:#ddd;color:#666}
.drag-handle:active{cursor:grabbing}
.sortable-ghost{opacity:.4;background:#dbeafe !important}

/* BADGES */
.badge{font-size:11px;font-weight:600;padding:3px 8px;border-radius:99px}
.badge-tipe{background:#e8eaf2;color:#555}
.badge-wajib{background:#fef3c7;color:#92400e}
.badge-opsional{background:#f3f4f6;color:#9ca3af}
.badge-aktif{background:#dcfce7;color:#166534}
.badge-nonaktif{background:#fee2e2;color:#991b1b}

/* TOGGLE SWITCH */
.toggle-wrap{display:flex;align-items:center;gap:8px;font-size:12px}
.toggle{position:relative;width:36px;height:20px;flex-shrink:0}
.toggle input{opacity:0;width:0;height:0}
.toggle-slider{position:absolute;cursor:pointer;inset:0;background:#ccc;border-radius:20px;transition:.3s}
.toggle-slider:before{content:'';position:absolute;width:14px;height:14px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.3s}
.toggle input:checked + .toggle-slider{background:#3b82f6}
.toggle input:checked + .toggle-slider:before{transform:translateX(16px)}

/* SATWA GRID */
.satwa-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:12px}
.satwa-card{
    background:
      rgba(255,255,255,.55);

    backdrop-filter:
      blur(20px);

    border:
      1px solid rgba(255,255,255,.35);

    border-radius:20px;

    overflow:hidden;
}
.satwa-img{height:120px;background:#e8eaee;display:flex;align-items:center;justify-content:center;overflow:hidden}
.satwa-img img{width:100%;height:100%;object-fit:cover}
.satwa-img-ph{display:flex;flex-direction:column;align-items:center;gap:4px;color:#bbb}
.satwa-img-ph span{font-size:11px}
.satwa-body{padding:10px 12px}
.satwa-nama{font-size:13px;font-weight:600;color:#2a2d3a;margin-bottom:8px}
.satwa-actions{display:flex;gap:6px}

/* MODAL */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:200;align-items:center;justify-content:center}
.modal-overlay.open{display:flex}
.modal{background:#d0d2d8;border-radius:12px;padding:24px;width:100%;max-width:500px;max-height:90vh;overflow-y:auto;box-shadow:0 10px 40px rgba(0,0,0,.25)}
.modal-title{font-size:15px;font-weight:600;color:#2a2d3a;margin-bottom:18px;display:flex;align-items:center;justify-content:space-between}
.modal-close{background:none;border:none;cursor:pointer;color:#999;font-size:22px;line-height:1}.modal-close:hover{color:#333}
.modal-actions{display:flex;gap:8px;justify-content:flex-end;margin-top:18px;padding-top:14px;border-top:1px solid #c0c2ca}
.divider{font-size:11px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.06em;margin:14px 0 8px;padding-bottom:4px;border-bottom:1px solid #c0c2ca}

/* ALERT */
.alert{padding:10px 14px;border-radius:8px;font-size:13px;display:flex;align-items:center;gap:8px}
.alert-success{background:#e8f5e9;color:#2d6a2d;border:1px solid #c8e6c8}
.alert-error{background:#fdf5f5;color:#a02020;border:1px solid #f0c8c8}

/* PREVIEW FORM */
.preview-section{background:#F8FFFE;border:1px solid #E0F5F8;border-radius:0 0 10px 10px;padding:24px 28px;overflow-y:auto;max-height:calc(100vh - 220px)}
.preview-title{font-size:13px;font-weight:600;color:#555;margin-bottom:14px;display:flex;align-items:center;gap:8px}
.preview-field{margin-bottom:14px}
.preview-label{font-size:13px;font-weight:500;color:#2a2d3a;margin-bottom:4px}
.preview-label span{color:#ef4444;margin-left:2px}
.preview-input{width:100%;padding:8px 11px;border:1px solid #c8cad4;border-radius:7px;font-family:inherit;font-size:13px;background:#fff;color:#2a2d3a;pointer-events:none}
.preview-select{width:100%;padding:8px 11px;border:1px solid #c8cad4;border-radius:7px;font-family:inherit;font-size:13px;background:#fff;color:#2a2d3a;pointer-events:none}
.preview-hint{font-size:11px;color:#888;margin-top:3px}
.preview-nonaktif{opacity:.4;position:relative}
.preview-nonaktif::after{content:'(Disembunyikan)';position:absolute;right:0;top:0;font-size:10px;color:#ef4444;font-weight:600}

/* Toggle password */
.pw-wrap{position:relative;display:flex;align-items:center}
.pw-wrap input{width:100%;padding-right:38px}
.pw-toggle{position:absolute;right:10px;background:none;border:none;cursor:pointer;color:#aaa;display:flex;align-items:center;padding:0}
.pw-toggle:hover{color:#00A9C1}
</style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main">
  <header class="topbar">
    <!-- Baris atas: judul halaman -->
    <div class="topbar-top">
      <div class="topbar-left">
        <div class="page-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14,2 14,8 20,8"/>
          </svg>
        </div>
        <div>
          <div class="page-title">Kelola Form User</div>
          <div class="page-subtitle">Manage field form, jenis satwa, dan preview tampilan</div>
        </div>
      </div>
      <div class="topbar-right">
        <span class="topbar-badge">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <?= count($fields) ?> field aktif
        </span>
        <span class="topbar-badge" style="background:#EDF7E6;color:#4A7C20;border-color:rgba(136,177,70,.25)">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="5"/><path d="M3 21v-2a7 7 0 0 1 14 0v2"/></svg>
          <?= count($satwas) ?> jenis satwa
        </span>
      </div>
    </div>

    <!-- Baris bawah: tab navigasi -->
    <div class="topbar-tabs">
      <a href="?tab=fields" class="tab-btn <?= $tab==='fields'?'active':'' ?>">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
        Field Form
        <span class="badge-count"><?= count($fields) ?></span>
      </a>
      <a href="?tab=satwa" class="tab-btn <?= $tab==='satwa'?'active':'' ?>">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
        Jenis Satwa
        <span class="badge-count"><?= count($satwas) ?></span>
      </a>
      <a href="?tab=users" class="tab-btn <?= $tab==='users'?'active':'' ?>">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Kelola User
        <span class="badge-count"><?= count($users) ?></span>
      </a>
      <a href="?tab=preview" class="tab-btn <?= $tab==='preview'?'active':'' ?>">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        Preview Form
      </a>
    </div>
  </header>

  <div class="content">

    <?php if($msg): ?>
    <div class="alert alert-<?= $msgType==='success'?'success':'error' ?>">
      <?= htmlspecialchars($msg) ?>
    </div>
    <?php endif; ?>

    <?php if($tab === 'fields'): ?>
    <!-- ════════════════════════
         TAB: FIELD FORM
    ════════════════════════ -->
    <div class="card">
      <div class="card-header">
        <div>
          <div class="card-title">Kelola Field Form User</div>
          <div class="card-sub">Tambah, edit, hapus, atau sembunyikan field. Drag untuk mengubah urutan.</div>
        </div>
        <button class="btn btn-primary" onclick="openModal('modal-tambah-field')">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Tambah Field
        </button>
      </div>

      <div class="field-table-wrap">
        <div class="table-scroll">
        <table>
          <thead>
            <tr>
              <th width="32"></th>
              <th width="36">No</th>
              <th>Label Field</th>
              <th width="110">Tipe</th>
              <th width="90">Status</th>
              <th width="70">Wajib</th>
              <th width="180">Aksi</th>
            </tr>
          </thead>
          <tbody id="sortable-fields">
            <?php foreach($fields as $i => $f): ?>
            <tr data-id="<?= $f['id'] ?>">
              <td>
                <span class="drag-handle" title="Drag untuk ubah urutan">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/>
                    <line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/>
                    <line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
                  </svg>
                </span>
              </td>
              <td style="color:#999"><?= $i+1 ?></td>
              <td>
                <div style="font-weight:600;font-size:13px"><?= htmlspecialchars($f['label']) ?></div>
                <?php if($f['keterangan']): ?>
                <div style="font-size:11px;color:#888;margin-top:2px"><?= htmlspecialchars($f['keterangan']) ?></div>
                <?php endif; ?>
                <?php if($f['tipe']==='dropdown' && !empty($opsiPerField[$f['id']])): ?>
                <div style="font-size:11px;color:#3b82f6;margin-top:2px">
                  <?= count($opsiPerField[$f['id']]) ?> opsi:
                  <?= htmlspecialchars(implode(', ', array_slice($opsiPerField[$f['id']],0,3))) ?>
                  <?= count($opsiPerField[$f['id']])>3 ? '...' : '' ?>
                </div>
                <?php endif; ?>
              </td>
              <td><span class="badge badge-tipe"><?= $f['nama_field']==='tanda_tangan' ? 'Signature' : ($tipeList[$f['tipe']] ?? $f['tipe']) ?></span></td>
              <td>
                <form method="POST" style="display:inline">
                  <input type="hidden" name="action" value="toggle_field">
                  <input type="hidden" name="id" value="<?= $f['id'] ?>">
                  <label class="toggle-wrap" style="cursor:pointer">
                    <label class="toggle">
                      <input type="checkbox" <?= $f['aktif']?'checked':'' ?> onchange="this.closest('form').submit()">
                      <span class="toggle-slider"></span>
                    </label>
                    <span style="color:<?= $f['aktif']?'#166534':'#991b1b' ?>"><?= $f['aktif']?'Aktif':'Nonaktif' ?></span>
                  </label>
                </form>
              </td>
              <td>
                <span class="badge <?= $f['wajib']?'badge-wajib':'badge-opsional' ?>">
                  <?= $f['wajib']?'Wajib':'Opsional' ?>
                </span>
              </td>
              <td>
                <div style="display:flex;gap:5px;flex-wrap:wrap">
                  <button class="btn btn-ghost btn-sm"
                    onclick="openEditField(<?= htmlspecialchars(json_encode($f)) ?>, <?= htmlspecialchars(json_encode($opsiPerField[$f['id']] ?? [])) ?>)">
                    Edit
                  </button>
                  <button class="btn btn-danger btn-sm"
                    onclick="openHapusField(<?= $f['id'] ?>, '<?= addslashes($f['label']) ?>')">
                    Hapus
                  </button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        </div>
      </div>
    </div>

    <?php elseif($tab === 'satwa'): ?>
    <!-- ════════════════════════
         TAB: JENIS SATWA
    ════════════════════════ -->
    <div class="card">
      <div class="card-header">
        <div>
          <div class="card-title">Kelola Jenis Satwa</div>
          <div class="card-sub">Tambah, edit, atau hapus jenis satwa yang tampil di form user</div>
        </div>
        <button class="btn btn-primary" onclick="openModal('modal-tambah-satwa')">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Tambah Satwa
        </button>
      </div>
      <?php if(empty($satwas)): ?>
      <div style="text-align:center;padding:40px;color:#999;font-size:13px">Belum ada jenis satwa.</div>
      <?php else: ?>
      <div class="satwa-grid-wrap"><div class="satwa-grid">
        <?php foreach($satwas as $s): ?>
        <div class="satwa-card">
          <div class="satwa-img">
            <?php if(!empty($s['foto_path']) && file_exists($s['foto_path'])): ?>
            <img src="<?= htmlspecialchars($s['foto_path']) ?>" alt="<?= htmlspecialchars($s['nama']) ?>">
            <?php else: ?>
            <div class="satwa-img-ph">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
              <span>Belum ada foto</span>
            </div>
            <?php endif; ?>
          </div>
          <div class="satwa-body">
            <div class="satwa-nama"><?= htmlspecialchars($s['nama']) ?></div>
            <div class="satwa-actions">
              <button class="btn btn-ghost btn-sm" onclick="openEditSatwa(<?= $s['id'] ?>,'<?= addslashes($s['nama']) ?>')">Edit</button>
              <button class="btn btn-danger btn-sm" onclick="openHapusSatwa(<?= $s['id'] ?>,'<?= addslashes($s['nama']) ?>')">Hapus</button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div></div>
      <?php endif; ?>
    </div>


    <?php elseif($tab === 'users'): ?>
    <!-- ════════════════════════
         TAB: KELOLA USER
    ════════════════════════ -->
    <div class="card">
      <div class="card-header" style="display:flex;align-items:center;justify-content:space-between">
        <div>
          <div class="card-title">Kelola User</div>
          <div class="card-sub">Tambah, edit, aktif/nonaktif, atau hapus akun pegawai</div>
        </div>
        <button class="btn btn-primary" onclick="openModalUser()">+ Tambah User</button>
      </div>

      <table style="width:100%;border-collapse:collapse;font-size:13px">
        <thead>
          <tr style="background:#f5f7fa;border-bottom:2px solid #e8edf2">
            <th style="padding:10px 14px;text-align:left;color:#5a7a8a;font-weight:600">NO</th>
            <th style="padding:10px 14px;text-align:left;color:#5a7a8a;font-weight:600">NAMA</th>
            <th style="padding:10px 14px;text-align:left;color:#5a7a8a;font-weight:600">USERNAME</th>
            <th style="padding:10px 14px;text-align:left;color:#5a7a8a;font-weight:600">ROLE</th>
            <th style="padding:10px 14px;text-align:center;color:#5a7a8a;font-weight:600">STATUS</th>
            <th style="padding:10px 14px;text-align:left;color:#5a7a8a;font-weight:600">DIBUAT</th>
            <th style="padding:10px 14px;text-align:center;color:#5a7a8a;font-weight:600">AKSI</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($users as $i => $u): ?>
          <tr style="border-bottom:1px solid #f0f3f7;<?= $u['aktif']?'':'background:#fafafa;opacity:.7' ?>">
            <td style="padding:10px 14px;color:#999"><?= $i+1 ?></td>
            <td style="padding:10px 14px;font-weight:600;color:#1a2332"><?= htmlspecialchars($u['namalengkap']) ?></td>
            <td style="padding:10px 14px;color:#5a7a8a"><?= htmlspecialchars($u['username']) ?></td>
            <td style="padding:10px 14px">
              <span style="padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;<?= $u['role']==='admin'?'background:#FEF3C7;color:#92400E':'background:#E0F2FE;color:#0369A1' ?>">
                <?= ucfirst($u['role']) ?>
              </span>
            </td>
            <td style="padding:10px 14px;text-align:center">
              <form method="POST" style="display:inline">
                <input type="hidden" name="action" value="toggle_user">
                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                <button type="submit" style="background:none;border:none;cursor:pointer;padding:0"
                        title="<?= $u['aktif']?'Nonaktifkan':'Aktifkan' ?>">
                  <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:600;border:1px solid;<?= $u['aktif']?'background:#DCFCE7;color:#166534;border-color:#BBF7D0':'background:#FEE2E2;color:#991B1B;border-color:#FECACA' ?>">
                    <?= $u['aktif']?'Aktif':'Nonaktif' ?>
                  </span>
                </button>
              </form>
            </td>
            <td style="padding:10px 14px;color:#999;font-size:12px"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
            <td style="padding:10px 14px;text-align:center">
              <div style="display:flex;gap:6px;justify-content:center">
                <button class="btn btn-ghost btn-sm"
                        onclick="openEditUser(<?= $u['id'] ?>,'<?= addslashes($u['namalengkap']) ?>','<?= addslashes($u['username']) ?>','<?= $u['role'] ?>')">
                  Edit
                </button>
                <form method="POST" style="display:inline"
                      onsubmit="return confirm('Hapus user <?= addslashes($u['username']) ?>?')">
                  <input type="hidden" name="action" value="hapus_user">
                  <input type="hidden" name="id" value="<?= $u['id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($users)): ?>
          <tr><td colspan="7" style="padding:24px;text-align:center;color:#999">Belum ada user terdaftar.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Modal Tambah User -->
    <div id="modalTambahUser" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:200;align-items:center;justify-content:center">
      <div style="background:#fff;border-radius:14px;padding:28px;width:100%;max-width:420px;box-shadow:0 20px 60px rgba(0,0,0,.2)">
        <div style="font-size:16px;font-weight:700;color:#1a2332;margin-bottom:20px">Tambah User Baru</div>
        <form method="POST">
          <input type="hidden" name="action" value="tambah_user">
          <div style="margin-bottom:14px">
            <label style="font-size:12px;font-weight:600;color:#5a7a8a;display:block;margin-bottom:6px">Nama Lengkap *</label>
            <input type="text" name="nama" class="form-input" placeholder="Nama lengkap pegawai" required>
          </div>
          <div style="margin-bottom:14px">
            <label style="font-size:12px;font-weight:600;color:#5a7a8a;display:block;margin-bottom:6px">Username *</label>
            <input type="text" name="username" class="form-input" placeholder="Username untuk login" required>
          </div>
          <div style="margin-bottom:14px">
            <label style="font-size:12px;font-weight:600;color:#5a7a8a;display:block;margin-bottom:6px">Password *</label>
            <div class="pw-wrap">
              <input type="password" id="pw-tambah" name="password" class="form-input" placeholder="Password" required>
              <button type="button" class="pw-toggle" onclick="togglePw('pw-tambah',this)" tabindex="-1"><svg id="eye-tambah" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
            </div>
          </div>
          <div style="margin-bottom:20px">
            <label style="font-size:12px;font-weight:600;color:#5a7a8a;display:block;margin-bottom:6px">Role *</label>
            <select name="role" class="form-input">
              <option value="pegawai">Pegawai</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div style="display:flex;gap:10px;justify-content:flex-end">
            <button type="button" class="btn btn-ghost" onclick="closeModalUser()">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Edit User -->
    <div id="modalEditUser" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:200;align-items:center;justify-content:center">
      <div style="background:#fff;border-radius:14px;padding:28px;width:100%;max-width:420px;box-shadow:0 20px 60px rgba(0,0,0,.2)">
        <div style="font-size:16px;font-weight:700;color:#1a2332;margin-bottom:20px">Edit User</div>
        <form method="POST">
          <input type="hidden" name="action" value="edit_user">
          <input type="hidden" name="id" id="eu-id">
          <div style="margin-bottom:14px">
            <label style="font-size:12px;font-weight:600;color:#5a7a8a;display:block;margin-bottom:6px">Nama Lengkap *</label>
            <input type="text" name="nama" id="eu-nama" class="form-input" required>
          </div>
          <div style="margin-bottom:14px">
            <label style="font-size:12px;font-weight:600;color:#5a7a8a;display:block;margin-bottom:6px">Username *</label>
            <input type="text" name="username" id="eu-username" class="form-input" required>
          </div>
          <div style="margin-bottom:14px">
            <label style="font-size:12px;font-weight:600;color:#5a7a8a;display:block;margin-bottom:6px">Password Baru <span style="color:#999;font-weight:400">(kosongkan jika tidak diubah)</span></label>
            <div class="pw-wrap">
              <input type="password" id="pw-edit" name="password" class="form-input" placeholder="Password baru (opsional)">
              <button type="button" class="pw-toggle" onclick="togglePw('pw-edit',this)" tabindex="-1"><svg id="eye-edit" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
            </div>
          </div>
          <div style="margin-bottom:20px">
            <label style="font-size:12px;font-weight:600;color:#5a7a8a;display:block;margin-bottom:6px">Role *</label>
            <select name="role" id="eu-role" class="form-input">
              <option value="pegawai">Pegawai</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div style="display:flex;gap:10px;justify-content:flex-end">
            <button type="button" class="btn btn-ghost" onclick="document.getElementById('modalEditUser').style.display='none'">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
          </div>
        </form>
      </div>
    </div>

    <?php elseif($tab === 'preview'): ?>
    <!-- ════════════════════════
         TAB: PREVIEW FORM
    ════════════════════════ -->
    <div class="card">
      <div class="card-header">
        <div>
          <div class="card-title">Preview Form User</div>
          <div class="card-sub">Tampilan form seperti yang dilihat user. Field nonaktif ditampilkan transparan.</div>
        </div>
      </div>
      <div class="preview-section">
        <div class="preview-title">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#555" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          Form Laporan — seperti yang tampil ke user
        </div>
        <?php foreach($fields as $f): ?>
        <div class="preview-field <?= !$f['aktif']?'preview-nonaktif':'' ?>">
          <div class="preview-label">
            <?= htmlspecialchars($f['label']) ?>
            <?php if($f['wajib']): ?><span>*</span><?php endif; ?>
          </div>
          <?php if($f['nama_field']==='tanda_tangan'): ?>
          <div style="border:1.5px dashed #cbd5e1;border-radius:8px;background:repeating-linear-gradient(135deg,#fafbfc,#fafbfc 10px,#f3f6f8 10px,#f3f6f8 20px);height:180px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;color:#9aa5b1">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#9aa5b1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 17.5c2-2 4-1 5.5.5s3.5 3 5.5 1 2-4 4-5.5"/><circle cx="18" cy="6" r="2.5"/></svg>
            <span style="font-size:12px">Area tanda tangan digital (gambar dengan jari/mouse)</span>
          </div>
          <div style="margin-top:6px"><button type="button" class="btn btn-ghost btn-sm" disabled>Hapus Tanda Tangan</button></div>
          <?php elseif($f['tipe']==='textarea'): ?>
          <textarea class="preview-input" rows="3" placeholder="<?= htmlspecialchars($f['placeholder']) ?>" readonly></textarea>
          <?php elseif($f['tipe']==='dropdown'): ?>
          <select class="preview-select" disabled>
            <option><?= htmlspecialchars($f['placeholder'] ?: 'Pilih...') ?></option>
            <?php foreach($opsiPerField[$f['id']] ?? [] as $opt): ?>
            <option><?= htmlspecialchars($opt) ?></option>
            <?php endforeach; ?>
          </select>
          <?php elseif($f['tipe']==='grid'): ?>
          <?php
            $gmPath = $f['gridmap_path'] ?? '';
            if(empty($gmPath)) {
              foreach(['uploads/gridmap_injourney.jpeg','uploads/gridmap_injourney.jpg','uploads/gridmap_injourney.png','gridmap_injourney.jpeg','gridmap_injourney.jpg','gridmap_injourney.png'] as $gp) {
                if(file_exists($gp)) { $gmPath = $gp; break; }
              }
            }
          ?>
          <?php if($gmPath && file_exists($gmPath)): ?>
          <div style="border:1px solid #d0eef5;border-radius:8px;overflow:hidden;position:relative">
            <img src="<?= htmlspecialchars($gmPath) ?>" style="width:100%;display:block">
            <div style="position:absolute;bottom:8px;right:8px;background:rgba(0,169,193,0.9);color:#fff;border-radius:6px;padding:4px 10px;font-size:11px;font-weight:600">Klik sel grid atau isi manual</div>
          </div>
          <?php else: ?>
          <div style="background:#f0fbfc;border:1px dashed #00A9C1;border-radius:7px;padding:14px;font-size:12px;color:#888;text-align:center">
            Belum ada gambar gridmap — upload di tab Field Form → Edit field Gridmap
          </div>
          <?php endif; ?>
          <?php elseif($f['tipe']==='file'): ?>
          <input class="preview-input" type="text" placeholder="Upload file..." readonly>
          <?php else: ?>
          <input class="preview-input" type="<?= $f['tipe']==='date'?'text':$f['tipe'] ?>" placeholder="<?= htmlspecialchars($f['placeholder']) ?>" readonly>
          <?php endif; ?>
          <?php if($f['keterangan']): ?>
          <div class="preview-hint"><?= htmlspecialchars($f['keterangan']) ?></div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>

        <!-- PILIH SATWA -->
        <div style="margin-top:20px;padding-top:16px;border-top:1.5px dashed #d0eef5">
          <div class="preview-label" style="font-size:14px;font-weight:700;margin-bottom:6px">Pilih jenis satwa yang ditemukan <span>*</span></div>
          <div style="font-size:12px;color:#888;margin-bottom:10px">Klik kartu untuk memilih. Setiap kartu bisa ditambahkan beberapa lokasi dengan jumlah berbeda.</div>
          <?php if(!empty($satwas)): ?>
          <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px;margin-bottom:14px">
            <?php foreach($satwas as $s): ?>
            <div style="border:1.5px solid #d0eef5;border-radius:10px;overflow:hidden">
              <div style="height:90px;background:#eef8fb;display:flex;align-items:center;justify-content:center;overflow:hidden">
                <?php if(!empty($s['foto_path']) && file_exists($s['foto_path'])): ?>
                <img src="<?= htmlspecialchars($s['foto_path']) ?>" style="width:100%;height:100%;object-fit:cover">
                <?php else: ?>
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#bbb" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
                <?php endif; ?>
              </div>
              <div style="padding:6px 8px;font-size:11px;font-weight:600;color:#414141"><?= htmlspecialchars($s['nama_satwa'] ?? $s['nama'] ?? '') ?></div>
              <div style="padding:0 8px 8px;font-size:10px;color:#888;display:flex;gap:4px">
                <span style="background:#f0f9fb;border:1px solid #d0eef5;border-radius:4px;padding:2px 6px">Jml</span>
                <span style="background:#f0f9fb;border:1px solid #d0eef5;border-radius:4px;padding:2px 6px">Lokasi</span>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php else: ?>
          <div style="background:#f0f9fb;border:1px dashed #00A9C1;border-radius:8px;padding:14px;text-align:center;font-size:12px;color:#888">Belum ada jenis satwa — tambahkan di tab Jenis Satwa</div>
          <?php endif; ?>
        </div>

        <!-- UPLOAD FOTO TAMBAHAN -->
        <div style="margin-top:16px;padding-top:14px;border-top:1.5px dashed #d0eef5">
          <div class="preview-label" style="font-size:14px;font-weight:700;margin-bottom:6px">🖼️ Tambahkan gambar jika ada jenis satwa liar yang tidak ada di daftar</div>
          <div style="font-size:12px;color:#888;margin-bottom:8px">Upload foto, lalu isi jumlah dan lokasi untuk setiap satwa baru yang ditemukan.</div>
          <div style="background:#f7fbfe;border:1.5px dashed #b0d8e8;border-radius:8px;padding:14px;display:flex;align-items:center;gap:12px">
            <div style="background:#00A9C1;color:#fff;border-radius:7px;padding:8px 14px;font-size:12px;font-weight:600">Pilih Foto</div>
            <span style="font-size:12px;color:#aaa">No file chosen</span>
          </div>
        </div>

        <!-- TOMBOL SUBMIT -->
        <div style="margin-top:20px;display:flex;justify-content:flex-end">
          <div style="background:#88B146;color:#fff;border-radius:8px;padding:10px 24px;font-size:14px;font-weight:600;display:flex;align-items:center;gap:8px">
            → Kirim Laporan
          </div>
        </div>

      </div>
    </div>
    <?php endif; ?>

  </div>
</div>

<!-- MODAL: Tambah Field -->
<div class="modal-overlay" id="modal-tambah-field">
  <div class="modal">
    <div class="modal-title">Tambah Field Baru <button class="modal-close" onclick="closeModal('modal-tambah-field')">&times;</button></div>
    <form method="POST">
      <input type="hidden" name="action" value="tambah_field">
      <div class="form-group">
        <label>Label Field *</label>
        <input type="text" name="label" class="form-input" placeholder="Contoh: Nomor Identitas" required>
      </div>
      <div class="form-group">
        <label>Tipe Input *</label>
        <select name="tipe" class="form-input" id="tambah-tipe" onchange="toggleOpsiTambah(this.value)">
          <?php foreach($tipeList as $k=>$v): ?>
          <option value="<?= $k ?>"><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Placeholder</label>
        <input type="text" name="placeholder" class="form-input" placeholder="Teks petunjuk di dalam input">
      </div>
      <div id="tambah-opsi-wrap" style="display:none">
        <div class="divider">Opsi Dropdown</div>
        <div class="form-group">
          <label>Daftar Opsi (satu per baris) *</label>
          <textarea name="opsi" class="form-input" rows="5" placeholder="Opsi 1&#10;Opsi 2&#10;Opsi 3"></textarea>
        </div>
      </div>
      <div class="form-group">
        <label>Keterangan / Petunjuk</label>
        <input type="text" name="keterangan" class="form-input" placeholder="Teks kecil di bawah field (opsional)">
      </div>
      <label class="form-check">
        <input type="checkbox" name="wajib" checked>
        Field ini wajib diisi
      </label>
      <div class="modal-actions">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-tambah-field')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Field</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL: Edit Field -->
<div class="modal-overlay" id="modal-edit-field">
  <div class="modal">
    <div class="modal-title">Edit Field <button class="modal-close" onclick="closeModal('modal-edit-field')">&times;</button></div>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="edit_field">
      <input type="hidden" name="id" id="ef-id">
      <div class="form-group">
        <label>Label Field *</label>
        <input type="text" name="label" id="ef-label" class="form-input" required>
      </div>
      <div class="form-group">
        <label>Tipe Input *</label>
        <select name="tipe" id="ef-tipe" class="form-input" onchange="toggleOpsiEdit(this.value)">
          <?php foreach($tipeList as $k=>$v): ?>
          <option value="<?= $k ?>"><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Placeholder</label>
        <input type="text" name="placeholder" id="ef-placeholder" class="form-input">
      </div>
      <div id="edit-opsi-wrap" style="display:none">
        <div class="divider">Opsi Dropdown</div>
        <div class="form-group">
          <label>Daftar Opsi (satu per baris)</label>
          <textarea name="opsi" id="ef-opsi" class="form-input" rows="5"></textarea>
        </div>
      </div>
      <!-- Upload gambar gridmap — muncul hanya saat tipe = Gridmap -->
      <div id="edit-gridmap-wrap" style="display:none">
        <div class="divider">Gambar Gridmap</div>
        <div class="form-group">
          <label>Upload Gambar Peta Gridmap</label>
          <div id="ef-gridmap-preview" style="margin-bottom:8px;display:none">
            <img id="ef-gridmap-img" src="" style="width:100%;border-radius:8px;border:1px solid #e0e0e0;max-height:160px;object-fit:cover">
            <div style="font-size:11px;color:#888;margin-top:4px">Gambar gridmap saat ini</div>
          </div>
          <input type="file" name="gridmap_file" id="ef-gridmap-file" accept="image/*" class="form-input" style="padding:6px"
                 onchange="previewGridmapEdit(this)">
          <div style="font-size:11px;color:#888;margin-top:4px">JPG, PNG, GIF, WebP. File akan disimpan sebagai <code>gridmap_injourney.jpeg</code></div>
        </div>
      </div>
      <div class="form-group">
        <label>Keterangan / Petunjuk</label>
        <input type="text" name="keterangan" id="ef-keterangan" class="form-input">
      </div>
      <label class="form-check">
        <input type="checkbox" name="wajib" id="ef-wajib">
        Field ini wajib diisi
      </label>
      <div class="modal-actions">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-edit-field')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL: Hapus Field -->
<div class="modal-overlay" id="modal-hapus-field">
  <div class="modal">
    <div class="modal-title">Hapus Field <button class="modal-close" onclick="closeModal('modal-hapus-field')">&times;</button></div>
    <p style="font-size:13px;color:#555;margin-bottom:16px">
      Yakin ingin menghapus field <b id="hf-nama"></b>? Tindakan ini tidak bisa dibatalkan.
    </p>
    <form method="POST">
      <input type="hidden" name="action" value="hapus_field">
      <input type="hidden" name="id" id="hf-id">
      <div class="modal-actions">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-hapus-field')">Batal</button>
        <button type="submit" class="btn btn-danger">Ya, Hapus</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL: Tambah Satwa -->
<div class="modal-overlay" id="modal-tambah-satwa">
  <div class="modal">
    <div class="modal-title">Tambah Jenis Satwa <button class="modal-close" onclick="closeModal('modal-tambah-satwa')">&times;</button></div>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="tambah_satwa">
      <div class="form-group">
        <label>Nama Jenis Satwa *</label>
        <input type="text" name="nama" class="form-input" placeholder="Contoh: Burung Kuntul Kerbau" required>
      </div>
      <div class="form-group">
        <label>Foto Satwa (opsional)</label>
        <input type="file" name="foto" class="form-input" accept="image/jpeg,image/png,image/webp">
        <span style="font-size:11px;color:#999">Format: JPG, PNG, WEBP</span>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-tambah-satwa')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL: Edit Satwa -->
<div class="modal-overlay" id="modal-edit-satwa">
  <div class="modal">
    <div class="modal-title">Edit Jenis Satwa <button class="modal-close" onclick="closeModal('modal-edit-satwa')">&times;</button></div>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="edit_satwa">
      <input type="hidden" name="id" id="es-id">
      <div class="form-group">
        <label>Nama Jenis Satwa *</label>
        <input type="text" name="nama" id="es-nama" class="form-input" required>
      </div>
      <div class="form-group">
        <label>Ganti Foto (opsional)</label>
        <input type="file" name="foto" class="form-input" accept="image/jpeg,image/png,image/webp">
        <span style="font-size:11px;color:#999">Kosongkan jika tidak ingin mengganti.</span>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-edit-satwa')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL: Hapus Satwa -->
<div class="modal-overlay" id="modal-hapus-satwa">
  <div class="modal">
    <div class="modal-title">Hapus Satwa <button class="modal-close" onclick="closeModal('modal-hapus-satwa')">&times;</button></div>
    <p style="font-size:13px;color:#555;margin-bottom:16px">Yakin hapus satwa <b id="hs-nama"></b>?</p>
    <form method="POST">
      <input type="hidden" name="action" value="hapus_satwa">
      <input type="hidden" name="id" id="hs-id">
      <div class="modal-actions">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-hapus-satwa')">Batal</button>
        <button type="submit" class="btn btn-danger">Ya, Hapus</button>
      </div>
    </form>
  </div>
</div>

<!-- SortableJS CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<script>
// Modal
function openModal(id){ document.getElementById(id).classList.add('open') }
function closeModal(id){ document.getElementById(id).classList.remove('open') }
document.querySelectorAll('.modal-overlay').forEach(el=>{
  el.addEventListener('click',e=>{ if(e.target===el) el.classList.remove('open') })
})

// Toggle opsi dropdown - tambah
function toggleOpsiTambah(tipe){
  document.getElementById('tambah-opsi-wrap').style.display = tipe==='dropdown'?'block':'none'
}

// Toggle opsi dropdown - edit
function toggleOpsiEdit(tipe){
  document.getElementById('edit-opsi-wrap').style.display    = tipe==='dropdown'?'block':'none'
  document.getElementById('edit-gridmap-wrap').style.display = tipe==='grid'?'block':'none'
}

// Edit Field
function openEditField(f, opsi){
  document.getElementById('ef-id').value          = f.id
  document.getElementById('ef-label').value       = f.label
  document.getElementById('ef-tipe').value        = f.tipe
  document.getElementById('ef-placeholder').value = f.placeholder || ''
  document.getElementById('ef-keterangan').value  = f.keterangan || ''
  document.getElementById('ef-wajib').checked     = f.wajib == 1
  document.getElementById('ef-opsi').value        = opsi.join('\n')
  // Reset file input
  document.getElementById('ef-gridmap-file').value = ''
  // Tampilkan preview gambar gridmap jika ada
  const preview = document.getElementById('ef-gridmap-preview')
  const img     = document.getElementById('ef-gridmap-img')
  if(f.tipe === 'grid' && f.gridmap_path){
    img.src = f.gridmap_path
    preview.style.display = 'block'
  } else {
    preview.style.display = 'none'
    img.src = ''
  }
  toggleOpsiEdit(f.tipe)
  openModal('modal-edit-field')
}

function previewGridmapEdit(input){
  if(!input.files || !input.files[0]) return
  const reader = new FileReader()
  reader.onload = e => {
    const img     = document.getElementById('ef-gridmap-img')
    const preview = document.getElementById('ef-gridmap-preview')
    img.src = e.target.result
    preview.style.display = 'block'
  }
  reader.readAsDataURL(input.files[0])
}

// Hapus Field
function openHapusField(id, nama){
  document.getElementById('hf-id').value          = id
  document.getElementById('hf-nama').textContent  = nama
  openModal('modal-hapus-field')
}

// Edit Satwa
function openEditSatwa(id, nama){
  document.getElementById('es-id').value   = id
  document.getElementById('es-nama').value = nama
  openModal('modal-edit-satwa')
}

// Hapus Satwa
function openHapusSatwa(id, nama){
  document.getElementById('hs-id').value          = id
  document.getElementById('hs-nama').textContent  = nama
  openModal('modal-hapus-satwa')
}

// Drag & Drop urutan field
const tbody = document.getElementById('sortable-fields')
if(tbody){
  Sortable.create(tbody, {
    handle: '.drag-handle',
    animation: 150,
    ghostClass: 'sortable-ghost',
    onEnd: function(){
      const rows = tbody.querySelectorAll('tr[data-id]')
      const ids  = [...rows].map(r => r.dataset.id)
      fetch('admin_manajemen.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'action=ubah_urutan&' + ids.map((id,i)=>`urutan[${i}]=${id}`).join('&')
      })
    }
  })
}

function openModalUser(){
    document.getElementById('modalTambahUser').style.display='flex';
}
function closeModalUser(){
    document.getElementById('modalTambahUser').style.display='none';
}
function openEditUser(id, nama, username, role){
    document.getElementById('eu-id').value = id;
    document.getElementById('eu-nama').value = nama;
    document.getElementById('eu-username').value = username;
    document.getElementById('eu-role').value = role;
    // Selalu kosongkan password agar tidak tampil hash
    var pw = document.getElementById('pw-edit');
    if(pw){ pw.value = ''; pw.type = 'password'; }
    // Reset ikon mata
    var btn = pw ? pw.nextElementSibling : null;
    if(btn) btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
    document.getElementById('modalEditUser').style.display='flex';
}
// Tutup modal user saat klik backdrop
['modalTambahUser','modalEditUser'].forEach(function(mid){
    document.getElementById(mid)?.addEventListener('click', function(e){
        if(e.target === this) this.style.display='none';
    });
});

function togglePw(inputId, btn) {
    const input = document.getElementById(inputId);
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    btn.innerHTML = isHidden
        ? '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>'
        : '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
    btn.style.color = isHidden ? '#00A9C1' : '#aaa';
}

// Auto scroll ke atas saat preview
<?php if($tab === 'preview'): ?>
window.addEventListener('load', function(){
    var el = document.querySelector('.content');
    if(el) el.scrollTop = 0;
    var ps = document.querySelector('.preview-section');
    if(ps) ps.scrollTop = 0;
});
<?php endif; ?>
</script>
</body>
</html>