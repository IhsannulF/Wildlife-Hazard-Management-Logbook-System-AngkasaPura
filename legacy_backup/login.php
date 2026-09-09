<?php
// ============================================================
// FILE   : login.php
// FUNGSI : Halaman login untuk pegawai dan admin
// URUTAN : 1) Mulai session
//           2) Cek jika sudah login → redirect
//           3) Sambungkan database
//           4) Siapkan CSRF token & counter percobaan
//           5) Cek lockout (terlalu banyak percobaan)
//           6) Proses form jika dikirim (POST)
//           7) Tampilkan HTML (head, CSS, form, JavaScript)
// ============================================================


// ============================================================
// BAGIAN 1 — MULAI SESSION
// Wajib dipanggil paling pertama sebelum apapun,
// agar bisa menyimpan & membaca data session user.
// ============================================================
session_start();


// ============================================================
// BAGIAN 2 — CEK JIKA USER SUDAH LOGIN
// Jika session 'username' sudah ada, berarti user sudah login.
// Langsung arahkan ke dashboard sesuai role-nya.
// Tidak perlu lanjut ke form login lagi.
// ============================================================
if (isset($_SESSION['username'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: user_dashboard.php");
    }
    exit;
}


// ============================================================
// BAGIAN 3 — SAMBUNGKAN KE DATABASE
// File koneksi.php berisi konfigurasi host, user, password,
// dan nama database. Wajib ada sebelum query dijalankan.
// ============================================================
require_once 'koneksi.php';


// ============================================================
// BAGIAN 4 — SIAPKAN CSRF TOKEN & COUNTER PERCOBAAN LOGIN
//
// CSRF token : kode acak yang ditempel di form, dicocokkan
//              saat form dikirim → mencegah serangan CSRF.
//
// login_attempts     : hitungan berapa kali login gagal.
// last_attempt_time  : waktu terakhir percobaan login gagal.
// ============================================================

// Buat CSRF token baru jika belum ada di session
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Set awal counter jika belum pernah ada
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts']    = 0;
    $_SESSION['last_attempt_time'] = time();
}


// ============================================================
// BAGIAN 5 — CEK STATUS LOCKOUT
// Jika percobaan gagal sudah mencapai batas maksimal,
// user dikunci sementara selama 60 detik.
// Setelah 60 detik berlalu, counter direset otomatis.
// ============================================================
$error        = '';       // Pesan error yang ditampilkan ke user
$lockout      = false;    // Status apakah user sedang dikunci
$max_attempts = 5;        // Batas maksimal percobaan login
$lockout_secs = 60;       // Durasi kunci dalam detik

if ($_SESSION['login_attempts'] >= $max_attempts) {

    $waktu_berlalu = time() - $_SESSION['last_attempt_time'];

    if ($waktu_berlalu < $lockout_secs) {
        // Masih dalam masa kunci
        $lockout = true;
        $sisa    = $lockout_secs - $waktu_berlalu; // Sisa detik kunci
    } else {
        // Masa kunci sudah habis → reset counter
        $_SESSION['login_attempts'] = 0;
    }
}


// ============================================================
// BAGIAN 6 — PROSES FORM LOGIN (hanya jika metode POST)
// Langkah di dalam:
//   6a) Validasi CSRF token
//   6b) Ambil & bersihkan input username & password
//   6c) Cek input tidak kosong
//   6d) Query database dengan prepared statement
//   6e) Cocokkan password dengan hash di database
//   6f) Jika cocok → simpan session & redirect
//   6g) Jika tidak cocok → tambah counter percobaan
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$lockout) {

    // -- 6a) Validasi CSRF token --
    // Token dari form harus cocok dengan token di session.
    // Jika tidak cocok, tolak request (kemungkinan serangan).
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Request tidak valid. Silakan muat ulang halaman.";

    } else {

        // -- 6b) Ambil & bersihkan input --
        // trim() menghapus spasi di awal/akhir username.
        // Password tidak di-trim karena bisa mengandung spasi sengaja.
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // -- 6c) Cek input tidak boleh kosong --
        if (empty($username) || empty($password)) {
            $error = "Username dan password wajib diisi.";

        } else {

            // -- 6d) Cari user di database --
            // Gunakan prepared statement untuk mencegah SQL injection.
            // Tanda '?' adalah placeholder untuk prepared statement PDO.
            $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user) {

                // -- 6e) Cocokkan password --
                // password_verify() membandingkan input dengan hash di database.
                // Jangan pernah simpan password polos di database!
                if ($user && password_verify($password, $user['password'])) {

                    // -- 6f) LOGIN BERHASIL --

                    // Reset counter percobaan
                    $_SESSION['login_attempts'] = 0;

                    // Ganti session ID baru → mencegah session fixation attack
                    session_regenerate_id(true);

                    // Simpan data user ke session
                    $_SESSION['user_id']  = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role']     = $user['role'];

                    // Buat ulang CSRF token setelah login berhasil
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                    // Arahkan ke dashboard sesuai role
                    if ($user['role'] === 'admin') {
                        header("Location: admin_dashboard.php");
                    } else {
                        header("Location: user_dashboard.php");
                    }
                    exit;

                } else {
                    // -- 6g) PASSWORD SALAH --
                    // Pesan dibuat sama agar tidak bocorkan info
                    // (tidak kasih tahu "username benar tapi password salah")
                    $error = "Username atau password salah.";
                }

            } else {
                // -- 6g) USERNAME TIDAK DITEMUKAN --
                // Pesan error sama dengan password salah
                $error = "Username atau password salah.";
            }

            // Tambah counter percobaan gagal & catat waktunya
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt_time'] = time();

            // PDO: tidak perlu stmt_close
        }
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
<!--
============================================================
BAGIAN 7 — HEAD HTML
Berisi: charset, viewport (responsif), judul, font, CSS
============================================================
-->

    <meta charset="UTF-8">

    <!-- viewport wajib ada agar tampilan responsif di HP -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login — Portal Internal</title>

    <!-- Font dari Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
    /*
    ============================================================
    BAGIAN 8 — CSS / TAMPILAN
    Urutan:
      8a) Reset & variabel warna
      8b) Body (latar belakang foto)
      8c) Logo area atas
      8d) Spacer (jarak logo ke card, mobile saja)
      8e) Card form (kotak login)
      8f) Label & input
      8g) Tombol login
      8h) Pesan error & lockout
      8i) Responsif mobile  (max 639px)
      8j) Responsif desktop (min 640px)
    ============================================================
    */

    /* -- 8a) Reset & variabel warna -- */
    *, *::before, *::after {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    :root {
        --teal:       #00B4C8;   /* warna utama tombol & aksen */
        --teal-dark:  #0099aa;   /* warna tombol saat hover */
        --white:      #ffffff;
        --text-dark:  #1a1a2e;   /* judul & teks utama */
        --text-mid:   #444;      /* label input */
        --text-light: #777;      /* teks kecil, footer */
        --glass:      rgba(255,255,255,0.88); /* efek kaca transparan */
        --border:     rgba(255,255,255,0.5);
        --error:      #e53e3e;
        --error-bg:   #fff5f5;
        --radius:     12px;
        --font:       'Nunito', sans-serif;
    }

    /* -- 8b) Body — latar belakang foto bandara -- */
    body {
        font-family: var(--font);
        min-height: 100vh;
        min-height: 100dvh;
        height: 100vh;
        height: 100dvh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center; /* CENTER vertikal */
        background:
    linear-gradient(180deg, rgba(220,235,245,0.55) 0%, rgba(180,215,235,0.65) 100%),
    url('bg_login.jpeg')
    center/cover no-repeat fixed;
        overflow: hidden;
    }

   /* -- 8c) Logo area di bagian atas halaman -- */
.logo-area {
    padding: 0 20px 16px; /* tidak ada padding atas, jarak ke card 16px */
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* Logo gambar asli */
.logo-img {
    width: clamp(140px, 22vw, 210px); /* sedikit dikecilkan agar muat */
    height: auto;
    object-fit: contain;
}

    /* -- 8d) Spacer — tidak dipakai lagi karena body sudah center -- */
    .spacer {
        display: none;
    }

    /* -- 8e) Card — kotak form login -- */
    .card {
        width: 100%;
        max-width: 480px;
        background: var(--glass);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: clamp(24px, 4vw, 40px) clamp(24px, 5vw, 48px);
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        animation: slideUp 0.45s cubic-bezier(0.16,1,0.3,1) both;
        overflow: hidden;
        position: relative;
    }

    /* Border teal lebih tegas di sekeliling card */
    .card {
        border: 2px solid rgba(0, 180, 200, 0.75) !important;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15), 0 0 0 5px rgba(0,180,200,0.15);
    }

    .card::before { display: none; }

    /* Animasi card naik dari bawah saat halaman dibuka */
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(40px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .card-title {
        font-size: clamp(22px, 4vw, 28px);
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 4px;
    }

    .card-sub {
        font-size: 14px;
        color: var(--text-light);
        margin-bottom: 28px;
    }

    /* -- 8f) Label & input -- */

    /* Grup satu field: label + input */
    .field { margin-bottom: 16px; }

    label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-mid);
        margin-bottom: 6px;
    }

    input[type="text"],
    input[type="password"] {
        width: 100%;
        padding: 13px 16px;
        background: var(--white);
        border: 1.5px solid #d8e0ea;
        border-radius: var(--radius);
        color: var(--text-dark);
        font-family: var(--font);
        font-size: 15px;
        transition: border-color 0.2s, box-shadow 0.2s;
        outline: none;
        -webkit-appearance: none;
    }

    input::placeholder { color: #aab; }

    /* Efek border saat input difokus */
    input[type="text"]:focus,
    input[type="password"]:focus {
        border-color: var(--teal);
        box-shadow: 0 0 0 3px rgba(0,180,200,0.12);
    }

    /* Wrapper untuk menempatkan tombol mata di dalam input */
    .password-wrapper { position: relative; }

    /* Tombol ikon mata (tampilkan/sembunyikan password) */
    .toggle-pw {
        position: absolute;
        right: 12px; top: 50%;
        transform: translateY(-50%);
        background: none; border: none;
        color: #aab;
        cursor: pointer; padding: 4px;
        display: flex; align-items: center;
        transition: color 0.2s;
    }
    .toggle-pw:hover { color: var(--teal); }

    /* -- 8g) Tombol Login -- */
    .btn {
        width: 100%;
        padding: 14px;
        background: var(--teal);
        color: #fff;
        border: none;
        border-radius: var(--radius);
        font-family: var(--font);
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
        margin-top: 8px;
        box-shadow: 0 4px 16px rgba(0,180,200,0.25);
    }
    .btn:hover:not(:disabled)  { background: var(--teal-dark); }
    .btn:active:not(:disabled) { transform: scale(0.99); }
    .btn:disabled { opacity: 0.55; cursor: not-allowed; }

    /* -- 8h) Pesan error & lockout -- */
    .alert {
        background: var(--error-bg);
        border: 1px solid rgba(229,62,62,0.25);
        border-radius: 8px;
        padding: 11px 14px;
        font-size: 13.5px;
        color: var(--error);
        margin-bottom: 20px;
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }

    /* Pesan lockout (warna biru/info, bukan merah) */
    .alert-lockout {
        background: #ebf8ff;
        border-color: rgba(0,180,200,0.3);
        color: #0099aa;
    }

    /* Teks info percobaan gagal */
    .attempts-info {
        text-align: right;
        font-size: 12px;
        color: var(--text-light);
        margin-top: -8px;
        margin-bottom: 16px;
    }
    .attempts-info span { color: var(--error); font-weight: 600; }

    /* Footer bawah card */
    .footer {
        margin-top: 24px;
        font-size: 12px;
        color: var(--text-light);
        text-align: center;
    }

    /* -- 8i) Responsif MOBILE (layar ≤ 639px) -- */
    /* -- 8i) Responsif MOBILE (max 639px) -- */
    @media (max-width: 639px) {
        /* Font 16px cegah auto-zoom saat input di-tap di iOS */
        input[type="text"],
        input[type="password"] { font-size: 16px; }
        .btn { font-size: 16px; padding: 15px; }

        /* Di HP, card tetap di tengah layar dengan padding kiri-kanan */
        .card {
            max-width: 92vw;
            border-radius: 16px;
            padding: 24px 20px;
        }

        .logo-img { width: clamp(120px, 40vw, 180px); }
    }

    /* -- 8j) Responsif DESKTOP (layar ≥ 640px) -- */
    @media (min-width: 640px) {
        /* Spacer tidak dipakai */
        .spacer { display: none; }
        /* Card sudah center dari body, tidak perlu margin tambahan */
        .card {
            border-radius: 20px;
            max-width: 460px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.18);
        }
    }
    </style>

</head>
<body>

<!--
============================================================
BAGIAN 9 — STRUKTUR HTML HALAMAN
Urutan tampilan dari atas ke bawah:
  9a) Logo Injourney Airports
  9b) Spacer (jarak logo ke card, mobile saja)
  9c) Card form login
      - Judul & subjudul
      - Pesan error / lockout (jika ada)
      - Form: input username, input password, tombol login
      - Info percobaan gagal (jika ada)
      - Footer
============================================================
-->

<!-- 9a) Logo Injourney Airports — pakai file gambar asli -->
<div class="logo-area">
    <img src="logo_login.png" alt="Injourney Airports" class="logo-img">
</div>

<!-- 9b) Spacer — hanya muncul di mobile -->
<div class="spacer"></div>

<!-- 9c) Card form login -->
<div class="card">

    <!-- Judul & subjudul -->
    <h1 class="card-title">Hello again!</h1>
    <p class="card-sub">Sign in to access your account</p>

    <!--
        Tampilkan pesan sesuai kondisi:
        - Jika sedang lockout → tampilkan sisa waktu tunggu
        - Jika ada error biasa → tampilkan pesan error
        - Jika tidak ada masalah → tidak tampil apapun
    -->
    <?php if ($lockout): ?>
        <div class="alert alert-lockout">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:1px">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            Terlalu banyak percobaan. Tunggu <?= (int)$sisa ?> detik sebelum mencoba lagi.
        </div>

    <?php elseif (!empty($error)): ?>
        <div class="alert">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:1px">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- Form login -->
    <form method="POST" id="loginForm">

        <!--
            CSRF token tersembunyi.
            Dikirim bersama form, dicocokkan dengan nilai di session.
            Tujuan: mencegah serangan cross-site request forgery.
        -->
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <!-- Input Username -->
        <div class="field">
            <label for="username">Username</label>
            <input
                type="text"
                id="username"
                name="username"
                placeholder="Masukkan username"
                autocomplete="username"
                value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                <?= $lockout ? 'disabled' : '' ?>
                required
            >
        </div>

        <!-- Input Password + tombol show/hide -->
        <div class="field">
            <label for="password">Password</label>
            <div class="password-wrapper">
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Masukkan password"
                    autocomplete="current-password"
                    <?= $lockout ? 'disabled' : '' ?>
                    required
                >
                <!-- Tombol mata untuk tampilkan/sembunyikan password -->
                <button type="button" class="toggle-pw" id="togglePw" title="Tampilkan/sembunyikan password">
                    <svg id="eyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Info percobaan gagal (hanya tampil jika ada) -->
        <?php if ($_SESSION['login_attempts'] > 0 && !$lockout): ?>
            <p class="attempts-info">
                Percobaan gagal: <span><?= (int)$_SESSION['login_attempts'] ?>/<?= $max_attempts ?></span>
            </p>
        <?php endif; ?>

        <!-- Tombol Login -->
        <button type="submit" class="btn" id="submitBtn" <?= $lockout ? 'disabled' : '' ?>>
            Login
        </button>

    </form>

    <!-- Footer -->
    <div class="footer">Portal Internal &copy; <?= date('Y') ?> — Injourney Airports</div>

</div><!-- akhir .card -->


<!--
============================================================
BAGIAN 10 — JAVASCRIPT
Letakkan di bawah HTML agar semua elemen sudah dimuat
sebelum script dijalankan.

Fungsi:
  10a) Toggle tampilkan / sembunyikan password
  10b) Disable tombol saat form dikirim (cegah double submit)
============================================================
-->
<script>

    // -- 10a) Toggle show/hide password --

    const togglePw = document.getElementById('togglePw');
    const pwInput  = document.getElementById('password');
    const eyeIcon  = document.getElementById('eyeIcon');

    // SVG ikon mata terbuka (kondisi awal: password tersembunyi)
    const eyeOpen = `
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
        <circle cx="12" cy="12" r="3"/>
    `;

    // SVG ikon mata dicoret (kondisi: password terlihat)
    const eyeClosed = `
        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
        <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
        <line x1="1" y1="1" x2="23" y2="23"/>
    `;

    // Saat tombol mata diklik: ganti tipe input & ikon
    togglePw.addEventListener('click', function () {

        const passwordSedangTersembunyi = pwInput.type === 'password';

        if (passwordSedangTersembunyi) {
            pwInput.type      = 'text';      // tampilkan teks password
            eyeIcon.innerHTML = eyeClosed;   // ganti ikon jadi mata dicoret
        } else {
            pwInput.type      = 'password';  // sembunyikan kembali
            eyeIcon.innerHTML = eyeOpen;     // kembalikan ikon mata normal
        }
    });


    // -- 10b) Disable tombol saat form dikirim --
    // Tujuan: mencegah user klik tombol Login berkali-kali
    // sebelum server selesai memproses request.

    document.getElementById('loginForm').addEventListener('submit', function () {
        const tombolLogin       = document.getElementById('submitBtn');
        tombolLogin.disabled    = true;
        tombolLogin.textContent = 'Memproses...';
    });

</script>

</body>
</html>