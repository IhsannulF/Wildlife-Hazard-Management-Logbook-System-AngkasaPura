<?php
ob_start();
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once 'koneksi.php';
ob_clean();

header('Content-Type: application/json');
header('Cache-Control: no-cache');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['error' => 'ID tidak valid']);
    exit;
}

$id = (int) $_GET['id'];

try {
    $stmt = $pdo->prepare(
        "SELECT l.*, u.namalengkap AS nama_user
         FROM laporan l
         LEFT JOIN users u ON l.user_id = u.id
         WHERE l.id = ?"
    );
    $stmt->execute([$id]);
    $laporan = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$laporan) {
        echo json_encode(['error' => 'Laporan tidak ditemukan (ID: ' . $id . ')']);
        exit;
    }

    // Ambil data satwa beserta grid per-satwa + foto dari jenis_satwa
    $stmt2 = $pdo->prepare("
        SELECT ds.id AS detail_satwa_id, ds.nama_satwa AS nama, ds.jumlah, ds.grid,
               COALESCE(js.foto_path, '') AS foto_path
        FROM detail_satwa ds
        LEFT JOIN jenis_satwa js ON js.nama COLLATE utf8mb4_general_ci = ds.nama_satwa
        WHERE ds.laporan_id = ? AND ds.jumlah > 0
    ");
    $stmt2->execute([$id]);
    $satwa = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // Ambil foto upload user per satwa, prioritaskan di atas foto jenis_satwa
    $stmt_foto = $pdo->prepare("
        SELECT detail_satwa_id, nama_file
        FROM foto_laporan
        WHERE laporan_id = ? AND tipe = 'satwa' AND detail_satwa_id IS NOT NULL
    ");
    $stmt_foto->execute([$id]);
    $foto_map = [];
    foreach ($stmt_foto->fetchAll(PDO::FETCH_ASSOC) as $f) {
        $foto_map[$f['detail_satwa_id']] = 'uploads/satwa/' . $f['nama_file'];
    }
    foreach ($satwa as &$s) {
        if (isset($foto_map[$s['detail_satwa_id']])) {
            $s['foto_path'] = $foto_map[$s['detail_satwa_id']];
        }
    }
    unset($s);

    $laporan['satwa'] = $satwa;

    // Ambil foto extra (satwa tidak terdaftar)
    $stmt_extra = $pdo->prepare("
        SELECT nama_file FROM foto_laporan
        WHERE laporan_id = ? AND tipe = 'extra'
        ORDER BY id ASC
    ");
    $stmt_extra->execute([$id]);
    $laporan['foto_extra'] = array_map(
        fn($r) => 'uploads/extra/' . $r['nama_file'],
        $stmt_extra->fetchAll(PDO::FETCH_ASSOC)
    );

    // Decode extra_data JSON
    if (!empty($laporan['extra_data'])) {
        $laporan['extra_data'] = json_decode($laporan['extra_data'], true) ?? [];
    } else {
        $laporan['extra_data'] = [];
    }

    // Ambil definisi field tambahan untuk label
    $field_bawaan = ['nama_petugas','tanggal','kondisi_cuaca','unit_kerja','area_inspeksi',
                     'tanda_tangan','grid','grid_lokasi','satwa','foto_laporan','ciri_ukuran',
                     'kondisi_apron','aktivitas_satwa','tindak_lanjut','detail_pengusiran'];
    $ph = implode(',', array_fill(0, count($field_bawaan), '?'));
    $ef_stmt = $pdo->prepare(
        "SELECT id, nama_field, label, tipe FROM form_fields WHERE aktif=1 AND nama_field NOT IN ($ph) ORDER BY urutan ASC"
    );
    $ef_stmt->execute($field_bawaan);
    $laporan['extra_fields_def'] = $ef_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Gabungkan semua grid per-satwa jadi grid_lokasi laporan (untuk tampilan ringkas)
    $gridList = array_filter(array_unique(array_column($satwa, 'grid')));
    $laporan['grid_lokasi'] = !empty($gridList) ? implode(', ', $gridList) : ($laporan['grid_lokasi'] ?? null);

    array_walk_recursive($laporan, function(&$val) {
        if (is_string($val)) {
            $val = mb_convert_encoding($val, 'UTF-8', 'UTF-8');
        }
    });

    ob_end_clean();
    echo json_encode($laporan);

} catch (PDOException $e) {
    ob_clean();
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}