<?php
// ============================================================
// get_detail_laporan.php — AJAX endpoint untuk modal detail & edit
// ============================================================
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']); exit;
}

require_once 'koneksi.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['error' => 'ID tidak valid']); exit;
}

$stmt = $pdo->prepare("
    SELECT l.*, u.username
    FROM laporan l
    LEFT JOIN users u ON l.user_id = u.id
    WHERE l.id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row) {
    echo json_encode(['error' => 'Data tidak ditemukan']); exit;
}

// Simpan tanggal mentah untuk input type=date di form edit
$row['tanggal_raw'] = $row['tanggal'];

// Format tanggal untuk tampilan
$row['tanggal'] = date('d/m/Y', strtotime($row['tanggal']));

// Ambil daftar satwa yang dilaporkan
$satwa_q = $pdo->prepare("
    SELECT ls.nama_satwa, ls.jumlah, ls.foto_path
    FROM detail_satwa ls
    WHERE ls.laporan_id = ? AND ls.jumlah > 0
    ORDER BY ls.jumlah DESC
");
$satwa_q->execute([$id]);

$satwa_list = [];
foreach ($satwa_q->fetchAll() as $s) {
        $satwa_list[] = [
            'nama'      => htmlspecialchars($s['nama_satwa'], ENT_QUOTES, 'UTF-8'),
            'jumlah'    => (int)$s['jumlah'],
            'foto_path' => $s['foto_path'] ? htmlspecialchars($s['foto_path'], ENT_QUOTES, 'UTF-8') : null,
        ];
}
$row['satwa_list'] = $satwa_list;

// Sanitize semua field string
$skip = ['satwa_list', 'tanggal_raw'];
foreach ($row as $key => $val) {
    if (!in_array($key, $skip) && is_string($val)) {
        $row[$key] = htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
    }
}

header('Content-Type: application/json');
echo json_encode($row);
?>