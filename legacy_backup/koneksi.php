<?php
// ============================================================
// koneksi.php — Koneksi Database (PDO only)
// ============================================================
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'logbook_project_baru');
define('APP_NAME', 'Portal Satwa Liar');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // Tampilkan error yang jelas saat development
    die(json_encode(['error' => 'Koneksi database gagal: ' . $e->getMessage()]));
}