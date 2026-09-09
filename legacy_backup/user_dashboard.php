<?php
// ============================================================
// FILE   : user_dashboard.php
// FUNGSI : Halaman selamat datang pegawai setelah login
// URUTAN : 1) Mulai session
//           2) Cek login & role (harus pegawai)
//           3) Sambungkan DB & siapkan data user
//           4) Ambil pesan notifikasi dari URL
//           5) Tampilkan HTML
// ============================================================


// ============================================================
// BAGIAN 1 — MULAI SESSION
// ============================================================
session_start();


// ============================================================
// BAGIAN 2 — CEK LOGIN & ROLE
// ============================================================
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'pegawai') {
    header("Location: login.php");
    exit;
}


// ============================================================
// BAGIAN 3 — SAMBUNGKAN DB & SIAPKAN DATA USER
// Statistik dihapus — hanya untuk admin.
// Cukup ambil nama & username dari session.
// ============================================================
require_once 'koneksi.php';

$username = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');
$nama     = htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username'], ENT_QUOTES, 'UTF-8');
$initials = strtoupper(substr($username, 0, 2));


// ============================================================
// BAGIAN 4 — AMBIL PESAN NOTIFIKASI DARI URL
// ============================================================
$pesan_sukses = !empty($_GET['sukses']) ? htmlspecialchars($_GET['sukses'], ENT_QUOTES, 'UTF-8') : '';
$pesan_error  = !empty($_GET['error'])  ? htmlspecialchars($_GET['error'],  ENT_QUOTES, 'UTF-8') : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<!--
============================================================
BAGIAN 5 — HEAD HTML
============================================================
-->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Portal Satwa Liar</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
    /*
    ============================================================
    BAGIAN 6 — CSS
    Urutan:
      6a) Reset & variabel
      6b) Body & layout fullscreen
      6c) Topbar (header atas)
      6d) Konten utama (hero)
      6e) Tombol form
      6f) Notifikasi
      6g) Footer
      6h) Responsif mobile
    ============================================================
    */

    /* -- 6a) Reset & variabel -- */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
        --teal:      #2ab3c0;
        --teal-h:    #229aa6;
        --teal-light:#e8f8fa;
        --text:      #1a2332;
        --muted:     #5a7a8a;
        --border:    #cce3f0;
        --surface:   #ffffff;
        --bg:        #eef6fb;
        --red:       #dc2626;
        --font:      'DM Sans', sans-serif;
        --radius:    12px;
    }

    body {
        font-family: var(--font);
        background:
            linear-gradient(180deg, rgba(200,232,245,0.6) 0%, rgba(180,220,235,0.7) 100%),
            url('bg_login.jpeg') center/cover no-repeat fixed;
        min-height: 100dvh;
        display: flex;
        flex-direction: column;
    }

    /* -- 6b2) Color bar InJourney brand -- */
    .brand-colorbar {
        height: 4px;
        width: 100%;
        background: linear-gradient(90deg,#00A9C1,#4FADC9,#88B146,#F0B14B,#D94F4F);
    }

    /* -- 6c) Topbar -- */
    .topbar {
        background: rgba(255,255,255,0.85);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-bottom: 1px solid var(--border);
        padding: 0 32px;
        height: 62px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: sticky; top: 0; z-index: 50;
        box-shadow: 0 1px 8px rgba(0,0,0,0.07);
    }

    /* Logo di topbar kiri */
    .topbar-logo img {
        height: 34px;
        object-fit: contain;
    }

    /* Info user + logout di kanan */
    .topbar-right {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .user-chip {
        display: flex;
        align-items: center;
        gap: 8px;
        background: var(--teal-light);
        border: 1px solid rgba(42,179,192,0.25);
        border-radius: 20px;
        padding: 5px 12px 5px 5px;
    }

    .avatar {
        width: 28px; height: 28px;
        border-radius: 50%;
        background: var(--teal);
        color: white;
        font-size: 11px; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    .user-label {
        font-size: 12.5px;
        font-weight: 500;
        color: var(--text);
        white-space: nowrap;
    }

    .logout-btn {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        background: rgba(220,38,38,0.07);
        border: 1px solid rgba(220,38,38,0.2);
        border-radius: 8px;
        color: var(--red);
        font-family: var(--font);
        font-size: 13px; font-weight: 500;
        text-decoration: none;
        transition: background 0.15s;
        white-space: nowrap;
    }
    .logout-btn:hover { background: rgba(220,38,38,0.14); }
    .logout-btn svg   { width: 14px; height: 14px; }

    /* -- 6d) Konten utama -- */
    .hero {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 48px 24px;
        text-align: center;
    }

    .hero-card {
        background: rgba(255,255,255,0.88);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255,255,255,0.7);
        border-radius: 20px;
        padding: clamp(32px, 5vw, 56px) clamp(28px, 6vw, 64px);
        max-width: 560px;
        width: 100%;
        box-shadow: 0 16px 48px rgba(0,0,0,0.12);
        animation: fadeUp 0.4s cubic-bezier(0.16,1,0.3,1) both;
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* Salam sapaan */
    .hero-greeting {
        font-size: 13px;
        font-weight: 500;
        color: var(--teal);
        letter-spacing: 0.5px;
        text-transform: uppercase;
        margin-bottom: 10px;
    }

    .hero-title {
        font-size: clamp(22px, 4vw, 30px);
        font-weight: 800;
        color: var(--text);
        line-height: 1.25;
        margin-bottom: 12px;
    }

    .hero-desc {
        font-size: 14px;
        color: var(--muted);
        line-height: 1.7;
        margin-bottom: 32px;
        max-width: 400px;
        margin-left: auto;
        margin-right: auto;
    }

    /* -- 6e) Tombol form -- */
    .btn-form {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 15px 36px;
        background: var(--teal);
        color: white;
        border: none;
        border-radius: var(--radius);
        font-family: var(--font);
        font-size: 15px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
        box-shadow: 0 4px 20px rgba(42,179,192,0.35);
    }
    .btn-form:hover {
        background: var(--teal-h);
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(42,179,192,0.4);
    }
    .btn-form svg { width: 18px; height: 18px; }


    /* -- 6f) Notifikasi -- */
    .notif-wrap {
        max-width: 560px;
        width: 100%;
        margin: 0 auto 16px;
    }
    .notif {
        display: flex; align-items: center; gap: 10px;
        padding: 12px 16px; border-radius: 10px;
        font-size: 13px;
    }
    .notif svg   { width: 16px; height: 16px; flex-shrink: 0; }
    .notif-ok    { background: #EAF3DE; color: #3B6D11; border: 1px solid #97C459; }
    .notif-err   { background: #FCEBEB; color: #791F1F; border: 1px solid #F09595; }

    /* -- 6g) Footer -- */
    .footer {
        background: rgba(255,255,255,0.7);
        border-top: 1px solid var(--border);
        padding: 14px 24px;
        text-align: center;
        font-size: 12px;
        color: var(--muted);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .footer img { height: 20px; object-fit: contain; }

    /* -- 6h) Responsif mobile -- */
    @media (max-width: 560px) {
        .topbar { padding: 0 16px; height: 56px; }
        .topbar-logo img { height: 28px; }
        .user-label { display: none; } /* sembunyikan nama di HP kecil */
        .logout-btn span { display: none; } /* hanya tampilkan ikon */
        .logout-btn { padding: 8px; }
        .hero { padding: 24px 16px; }
        .hero-card { padding: 28px 20px; }
        .btn-form { width: 100%; justify-content: center; }
    }
    </style>
</head>

<body>
<!--
============================================================
BAGIAN 7 — HTML
Urutan:
  7a) Topbar (logo + info user + logout)
  7b) Notifikasi (jika ada)
  7c) Hero card (selamat datang + tombol)
  7d) Footer
============================================================
-->

<!-- 7a) Topbar -->
<header class="topbar">
    <!-- Logo kiri -->
    <div class="topbar-logo">
        <img src="logo_injourney.jpeg" alt="InJourney Airports"
             onerror="this.style.display='none'">
    </div>

    <!-- Info user + logout kanan -->
    <div class="topbar-right">
        <div class="user-chip">
            <div class="avatar"><?= $initials ?></div>
            <div class="user-label"><?= $nama ?></div>
        </div>
        <a href="logout.php" class="logout-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            <span>Logout</span>
        </a>
    </div>
</header>

<!-- Color bar brand InJourney Airports -->
<div class="brand-colorbar" aria-hidden="true"></div>


<!-- 7b) Notifikasi sukses/error -->
<?php if ($pesan_sukses || $pesan_error): ?>
<div style="padding: 16px 24px 0; display:flex; justify-content:center;">
    <div class="notif-wrap">
        <?php if ($pesan_sukses): ?>
        <div class="notif notif-ok">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <?= $pesan_sukses ?>
        </div>
        <?php elseif ($pesan_error): ?>
        <div class="notif notif-err">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <?= $pesan_error ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>


<!-- 7c) Hero card — konten utama -->
<main class="hero">
    <div class="hero-card">

        <!-- Salam sapaan -->
        <div class="hero-greeting">Portal Pelaporan Satwa Liar</div>

        <!-- Judul -->
        <h1 class="hero-title">
            Selamat datang di Website<br>Pengaduan Satwa Liar.
        </h1>

        <!-- Deskripsi -->
        <p class="hero-desc">
            Platform ini digunakan untuk melaporkan keberadaan burung liar
            dan satwa lainnya di area bandara guna mendukung keselamatan
            operasional penerbangan.
        </p>

        <!--
            TOMBOL UTAMA — klik menuju form_pengaduan.php
        -->
        <a href="form_pengaduan.php" class="btn-form">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
            Isi Form Pengaduan
        </a>


    </div>
</main>


<!-- 7d) Footer -->
<footer class="footer">
    <img src="logo_injourney.jpeg" alt="InJourney"
         onerror="this.style.display='none'">
    Portal Internal © <?= date('Y') ?> — InJourney Airports
</footer>

</body>
</html>