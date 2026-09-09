<?php
// ============================================================
// FILE   : proses_laporan.php
// FUNGSI : Memproses data yang dikirim dari form_pengaduan.php
//          Simpan ke tabel: laporan, detail_satwa, foto_laporan
// URUTAN : 1) Mulai session
//           2) Cek login & role (harus pegawai)
//           3) Cek metode request (harus POST)
//           4) Sambungkan database
//           5) Ambil & bersihkan semua input dari form
//           6) Validasi input wajib
//           7) Simpan ke tabel laporan (data utama)
//           8) Simpan ke tabel detail_satwa (per jenis satwa)
//           9) Upload & simpan foto per satwa
//          10) Upload & simpan foto tambahan (satwa extra)
//          11) Redirect ke dashboard dengan pesan sukses
// ============================================================


// ============================================================
// BAGIAN 1 — MULAI SESSION
// Wajib pertama sebelum apapun.
// ============================================================
session_start();


// ============================================================
// BAGIAN 2 — CEK LOGIN & ROLE
// Hanya pegawai yang boleh kirim laporan.
// ============================================================
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'pegawai') {
    header("Location: login.php");
    exit;
}


// ============================================================
// BAGIAN 3 — CEK METODE REQUEST
// File ini hanya boleh diakses via POST dari form.
// Jika diakses langsung (GET), redirect ke dashboard.
// ============================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: user_dashboard.php");
    exit;
}


// ============================================================
// BAGIAN 4 — SAMBUNGKAN DATABASE
// Harus dilakukan sebelum query apapun dijalankan.
// ============================================================
require_once 'koneksi.php';


// ============================================================
// BAGIAN 5 — AMBIL & BERSIHKAN SEMUA INPUT DARI FORM
//
// trim()           → hapus spasi di awal/akhir
// htmlspecialchars → cegah XSS jika data ditampilkan kembali
// (int)            → pastikan angka benar-benar integer
//
// Data dibagi sesuai section form:
//   5a) Data dasar (Section 1)
//   5b) Lokasi grid (Section 2)
//   5c) Data satwa (Section 3) — array
//   5d) Kondisi satwa (Section 4)
// ============================================================

// -- 5a) Data dasar (Section 1) --
$nama_petugas   = trim(htmlspecialchars($_POST['nama_petugas']   ?? '', ENT_QUOTES, 'UTF-8'));
$tanggal        = trim($_POST['tanggal']        ?? '');
$kondisi_cuaca  = trim(htmlspecialchars($_POST['kondisi_cuaca']  ?? '', ENT_QUOTES, 'UTF-8'));
$unit_kerja     = trim(htmlspecialchars($_POST['unit_kerja']     ?? '', ENT_QUOTES, 'UTF-8'));
$area_inspeksi  = trim(htmlspecialchars($_POST['area_inspeksi']  ?? '', ENT_QUOTES, 'UTF-8'));
$tanda_tangan = $_POST['tanda_tangan'] ?? '';
// Validasi tanda tangan hanya jika field aktif di DB
$ttAktif = $pdo->query("SELECT COUNT(*) FROM form_fields WHERE nama_field='tanda_tangan' AND aktif=1")->fetchColumn();
if ($ttAktif && !preg_match('/^data:image\/png;base64,/', $tanda_tangan)) {
    header("Location: form_pengaduan.php?error=" .
        urlencode("Tanda tangan tidak valid."));
    exit;
}

// -- 5b) Lokasi grid (Section 2) --
$grid_lokasi    = strtoupper(trim($_POST['grid_lokasi'] ?? ''));

// -- 5c) Data satwa (Section 3) --
// $_POST['satwa'] adalah array: [idx => ['nama'=>..., 'jumlah'=>...]]
$satwa_input    = $_POST['satwa'] ?? [];

// -- 5d) Kondisi satwa (Section 4) --
$ciri_ukuran       = trim(htmlspecialchars($_POST['ciri_ukuran']        ?? '', ENT_QUOTES, 'UTF-8'));
$kondisi_apron     = trim(htmlspecialchars($_POST['kondisi_apron']      ?? '', ENT_QUOTES, 'UTF-8'));
$aktivitas_satwa   = trim(htmlspecialchars($_POST['aktivitas_satwa']    ?? '', ENT_QUOTES, 'UTF-8'));
$tindak_lanjut     = trim(htmlspecialchars($_POST['tindak_lanjut']      ?? '', ENT_QUOTES, 'UTF-8'));
$detail_pengusiran = trim(htmlspecialchars($_POST['detail_pengusiran']  ?? '', ENT_QUOTES, 'UTF-8'));

// User ID dari session (sudah integer karena disimpan saat login)
$user_id = (int)$_SESSION['user_id'];


// ============================================================
// BAGIAN 6 — VALIDASI INPUT WAJIB
// Jika ada field kosong atau data tidak valid, tolak dan
// kembalikan user ke form dengan pesan error via URL.
// ============================================================

// Daftar field yang wajib diisi (sesuai status aktif & wajib di DB)
$aktifWajib = array_flip(array_column(
    $pdo->query("SELECT nama_field FROM form_fields WHERE aktif=1 AND wajib=1")->fetchAll(PDO::FETCH_ASSOC),
    'nama_field'
));

$wajib = [];
if (isset($aktifWajib['tanda_tangan']))     $wajib['Tanda tangan']      = $tanda_tangan;
if (isset($aktifWajib['nama_petugas']))     $wajib['Nama petugas']      = $nama_petugas;
if (isset($aktifWajib['kondisi_cuaca']))    $wajib['Kondisi cuaca']     = $kondisi_cuaca;
if (isset($aktifWajib['unit_kerja']))       $wajib['Unit kerja']        = $unit_kerja;
if (isset($aktifWajib['area_inspeksi']))    $wajib['Area inspeksi']     = $area_inspeksi;
if (isset($aktifWajib['ciri_ukuran']))      $wajib['Ciri ukuran']       = $ciri_ukuran;
if (isset($aktifWajib['kondisi_apron']))    $wajib['Kondisi apron']     = $kondisi_apron;
if (isset($aktifWajib['aktivitas_satwa']))  $wajib['Aktivitas satwa']   = $aktivitas_satwa;
if (isset($aktifWajib['tindak_lanjut']))    $wajib['Tindak lanjut']     = $tindak_lanjut;
if (isset($aktifWajib['detail_pengusiran'])) $wajib['Detail pengusiran'] = $detail_pengusiran;

foreach ($wajib as $label => $nilai) {
    if (empty($nilai)) {
        $_SESSION['form_data'] = $_POST;
        header("Location: form_pengaduan.php?error=" . urlencode("Field '$label' wajib diisi."));
        exit;
    }
}

// Validasi format tanggal (harus Y-m-d)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    header("Location: form_pengaduan.php?error=" . urlencode("Format tanggal tidak valid."));
    exit;
}

// Validasi: minimal satu satwa dipilih ATAU ada foto extra base64
$ada_satwa = false;
foreach ($satwa_input as $s) {

    foreach ($s['lokasi'] ?? [] as $lok) {
        if ((int)($lok['jumlah'] ?? 0) > 0) {
            $ada_satwa = true;
            break 2;
        }
    }
}
$ada_foto_extra = isset($_FILES['foto_lainnya'])
    && is_array($_FILES['foto_lainnya']['name'])
    && !empty($_FILES['foto_lainnya']['name'][0])
    && $_FILES['foto_lainnya']['error'][0] === UPLOAD_ERR_OK;

if (!$ada_satwa && !$ada_foto_extra) {
    $_SESSION['form_data'] = $_POST;
    header("Location: form_pengaduan.php?error=" . urlencode("Pilih minimal satu satwa atau upload foto satwa tidak terdaftar."));
    exit;
}


// ============================================================
// BAGIAN 7 — SIMPAN KE TABEL laporan (data utama)
// Satu baris baru di tabel laporan untuk satu laporan harian.
// Gunakan prepared statement untuk cegah SQL injection.
// ============================================================
$stmt = $pdo->prepare(
    "INSERT INTO laporan
        (user_id, nama_petugas, tanggal, kondisi_cuaca, unit_kerja,
         area_inspeksi, tanda_tangan, ciri_ukuran,
         kondisi_apron, aktivitas_satwa, tindak_lanjut, detail_pengusiran, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
$status_laporan = (stripos($tindak_lanjut, 'telah ditangani') !== false) ? 'ditangani' : 'belum_ditangani';
$stmt->execute([
    $user_id, $nama_petugas, $tanggal, $kondisi_cuaca, $unit_kerja,
    $area_inspeksi, $tanda_tangan, $ciri_ukuran,
    $kondisi_apron, $aktivitas_satwa, $tindak_lanjut, $detail_pengusiran, $status_laporan
]);

// Ambil ID laporan yang baru saja dibuat
$laporan_id = (int)$pdo->lastInsertId();

// Jika insert gagal (laporan_id = 0), hentikan proses
if (!$laporan_id) {
    header("Location: form_pengaduan.php?error=" . urlencode("Gagal menyimpan laporan. Coba lagi."));
    exit;
}

// ============================================================
// BAGIAN 7b -- SIMPAN FIELD TAMBAHAN (extra_data) KE JSON
// Ambil semua field aktif dari form_fields yang bukan bawaan
// ============================================================
$field_bawaan = ['nama_petugas','tanggal','kondisi_cuaca','unit_kerja','area_inspeksi',
                 'tanda_tangan','grid','satwa','foto_laporan','ciri_ukuran',
                 'kondisi_apron','aktivitas_satwa','tindak_lanjut','detail_pengusiran'];
$ph = implode(',', array_fill(0, count($field_bawaan), '?'));
$ef_stmt = $pdo->prepare(
    "SELECT nama_field, tipe FROM form_fields WHERE aktif=1 AND nama_field NOT IN ($ph) ORDER BY urutan ASC"
);
$ef_stmt->execute($field_bawaan);
$extra_fields_def = $ef_stmt->fetchAll(PDO::FETCH_ASSOC);

$extra_data = [];
foreach ($extra_fields_def as $ef) {
    $fname = $ef['nama_field'];
    if (isset($_POST[$fname])) {
        $extra_data[$fname] = trim(htmlspecialchars($_POST[$fname], ENT_QUOTES, 'UTF-8'));
    }
}

if (!empty($extra_data)) {
    try {
        $pdo->prepare("UPDATE laporan SET extra_data=? WHERE id=?")
            ->execute([json_encode($extra_data, JSON_UNESCAPED_UNICODE), $laporan_id]);
    } catch (PDOException $e) {
        // Kolom extra_data belum ada di tabel laporan, abaikan
    }
}


// ============================================================
// BAGIAN 8 — SIMPAN KE TABEL detail_satwa (per jenis satwa)
// Loop semua satwa yang dipilih (jumlah > 0).
// Simpan satu baris per jenis satwa yang ditemukan.
// ============================================================
$stmt_satwa = $pdo->prepare(
    "INSERT INTO detail_satwa (laporan_id, nama_satwa, jumlah, grid) VALUES (?, ?, ?, ?)"
);

// Simpan mapping idx → detail_satwa_id untuk upload foto nanti
$satwa_id_map = [];

foreach ($satwa_input as $idx => $satwa) {
    // Proses hanya jika ada lokasi dengan jumlah > 0
    $punya_jumlah = false;
    foreach ($satwa['lokasi'] ?? [] as $lok) {
        if ((int)($lok['jumlah'] ?? 0) > 0) { $punya_jumlah = true; break; }
    }
    if (!$punya_jumlah) continue;
    $nama_satwa = trim(htmlspecialchars($satwa['nama'] ?? '', ENT_QUOTES, 'UTF-8'));
    if (empty($nama_satwa)) continue;
    $lokasi_rows = $satwa['lokasi'] ?? [];
    foreach ($lokasi_rows as $row) {
        $jumlah = (int)($row['jumlah'] ?? 0);
        $grid_satwa = strtoupper(trim($row['grid'] ?? ''));
        if ($jumlah > 0) {
            $stmt_satwa->execute([$laporan_id, $nama_satwa, $jumlah, $grid_satwa ?: null]);
            $satwa_id_map[$idx] = (int)$pdo->lastInsertId();
        }
    }
}


// ============================================================
// BAGIAN 9 — UPLOAD & SIMPAN FOTO PER SATWA
// Setiap kartu satwa bisa punya satu foto (opsional).
// Foto disimpan ke folder 'uploads/satwa/' di server.
// Nama file = "satwa_{laporan_id}_{detail_satwa_id}_{waktu}.{ext}"
// ============================================================

// Pastikan folder upload ada
$upload_dir = 'uploads/satwa/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Tipe file yang diizinkan
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

if (isset($_FILES['foto_satwa']) && is_array($_FILES['foto_satwa']['name'])) {

    $stmt_foto = $pdo->prepare(
        "INSERT INTO foto_laporan (laporan_id, detail_satwa_id, nama_file, tipe) VALUES (?, ?, ?, 'satwa')"
    );

    foreach ($_FILES['foto_satwa']['name'] as $idx => $nama_asli) {

        if (empty($nama_asli) || $_FILES['foto_satwa']['error'][$idx] !== UPLOAD_ERR_OK) {
            continue;
        }
        if (!isset($satwa_id_map[$idx])) {
            continue;
        }

        $mime = mime_content_type($_FILES['foto_satwa']['tmp_name'][$idx]);
        if (!in_array($mime, $allowed_types)) {
            continue;
        }

        $ext       = strtolower(pathinfo($nama_asli, PATHINFO_EXTENSION));
        $nama_file = "satwa_{$laporan_id}_{$satwa_id_map[$idx]}_" . time() . ".$ext";
        $tujuan    = $upload_dir . $nama_file;

        if (move_uploaded_file($_FILES['foto_satwa']['tmp_name'][$idx], $tujuan)) {
            $detail_satwa_id = $satwa_id_map[$idx];
            $stmt_foto->execute([$laporan_id, $detail_satwa_id, $nama_file]);
        }
    }
}


// ============================================================
// BAGIAN 10 — UPLOAD & SIMPAN FOTO TAMBAHAN (satwa extra)
// Simpan juga jumlah & lokasi dari $_POST['extra']
// ============================================================
$upload_dir_extra = 'uploads/extra/';
if (!is_dir($upload_dir_extra)) mkdir($upload_dir_extra, 0755, true);

$extra_input = $_POST['extra'] ?? [];

if (isset($_FILES['foto_lainnya']) && is_array($_FILES['foto_lainnya']['name'])) {
    $stmt_extra_satwa = $pdo->prepare(
        "INSERT INTO detail_satwa (laporan_id, nama_satwa, jumlah, grid) VALUES (?, 'Satwa Tidak Terdaftar', ?, ?)"
    );
    $stmt_extra = $pdo->prepare(
        "INSERT INTO foto_laporan (laporan_id, detail_satwa_id, nama_file, tipe) VALUES (?, ?, ?, 'extra')"
    );
    foreach ($_FILES['foto_lainnya']['name'] as $i => $nama_asli) {
        if (empty($nama_asli) || $_FILES['foto_lainnya']['error'][$i] !== UPLOAD_ERR_OK) continue;
        $mime = mime_content_type($_FILES['foto_lainnya']['tmp_name'][$i]);
        if (!in_array($mime, $allowed_types)) continue;
        $ext       = strtolower(pathinfo($nama_asli, PATHINFO_EXTENSION));
        $nama_file = "extra_{$laporan_id}_{$i}_" . time() . ".$ext";
        $tujuan    = $upload_dir_extra . $nama_file;
        if (move_uploaded_file($_FILES['foto_lainnya']['tmp_name'][$i], $tujuan)) {
            // Ambil jumlah & grid dari extra[i]
            $extra_detail_id = null;
            $lokasi_extra = $extra_input[$i]['lokasi'] ?? [];
            foreach ($lokasi_extra as $lok) {
                $jml = (int)($lok['jumlah'] ?? 0);
                $grd = strtoupper(trim($lok['grid'] ?? ''));
                if ($jml > 0) {
                    $stmt_extra_satwa->execute([$laporan_id, $jml, $grd ?: null]);
                    $extra_detail_id = (int)$pdo->lastInsertId();
                    break; // ambil lokasi pertama yang valid
                }
            }
            $stmt_extra->execute([$laporan_id, $extra_detail_id, $nama_file]);
        }
    }
}


// ============================================================
// BAGIAN 11 — REDIRECT KE DASHBOARD DENGAN PESAN SUKSES
// Semua data berhasil disimpan.
// Pesan sukses dikirim via URL parameter, ditampilkan di dashboard.
// ============================================================
header("Location: form_pengaduan.php?sukses=" . urlencode("Laporan berhasil dikirim! ID laporan: #$laporan_id"));
exit;