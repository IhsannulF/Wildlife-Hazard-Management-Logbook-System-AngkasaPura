<?php
session_start();
// Cek login — kalau belum login, arahkan ke halaman login
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
require_once 'koneksi.php';

$tahun      = (int)($_GET['tahun'] ?? date('Y'));
$zona       = $_GET['zona'] ?? '';
$search     = trim($_GET['search'] ?? '');
$activePage = 'statistic';

$barisGrid = ['A','B','C','D','E','F','G','H','I','J','K','L'];
$kolomGrid = range(1, 29);

// Grid yang ada datanya
$stmtGrid = $pdo->prepare("
    SELECT DISTINCT ds.grid
    FROM detail_satwa ds
    JOIN laporan l ON l.id = ds.laporan_id
    WHERE ds.grid IS NOT NULL AND ds.grid != '' AND YEAR(l.tanggal) = ?
    ORDER BY ds.grid
");
$stmtGrid->execute([$tahun]);
$gridAda = $stmtGrid->fetchAll(PDO::FETCH_COLUMN);

// Jenis satwa (daftar master untuk dataset/warna chart)
$jenisList  = $pdo->prepare(
    "SELECT DISTINCT ds.nama_satwa AS nama
     FROM detail_satwa ds
     JOIN laporan l ON l.id = ds.laporan_id
     WHERE YEAR(l.tanggal) = ?
     " . ($search ? "AND ds.nama_satwa LIKE ?" : "") . "
     ORDER BY ds.nama_satwa"
);
$jenisList->execute($search ? [$tahun, "%$search%"] : [$tahun]);
$jenisList = $jenisList->fetchAll();
$paletteHex = ['#e8602c','#5bb8d4','#78c15a','#3a8a3f','#f0a070','#2255aa','#e040fb','#00bcd4'];

$whereZona   = $zona   ? "AND ds.grid = ?" : "";
$whereSearch = $search ? "AND ds.nama_satwa LIKE ?" : "";
$paramZona   = $zona   ? [$zona] : [];
$paramSearch = $search ? ["%$search%"] : [];

// ── GRAFIK 1: Stacked Bar ────────────────────────────────────
$stmtBar = $pdo->prepare("
    SELECT ds.nama_satwa AS nama, MONTH(l.tanggal) AS bulan,
           COALESCE(SUM(ds.jumlah), 0) AS total
    FROM detail_satwa ds
    JOIN laporan l ON l.id = ds.laporan_id
    WHERE YEAR(l.tanggal) = ? $whereZona $whereSearch
    GROUP BY ds.nama_satwa, MONTH(l.tanggal)
    ORDER BY ds.nama_satwa, MONTH(l.tanggal)
");
$stmtBar->execute(array_merge([$tahun], $paramZona, $paramSearch));
$rawBar = $stmtBar->fetchAll();

$mapBar = [];
foreach ($rawBar as $r) $mapBar[$r['nama']][(int)$r['bulan']] = (int)$r['total'];

$datasetsBar = [];
foreach ($jenisList as $i => $js) {
    $arr = [];
    for ($m = 1; $m <= 12; $m++) $arr[] = $mapBar[$js['nama']][$m] ?? 0;
    $datasetsBar[] = ['label'=>$js['nama'], 'data'=>$arr, 'color'=>$paletteHex[$i % count($paletteHex)]];
}

// ── RANKING: spesies paling banyak per bulan ─────────────────
// $rankPerBulan[bulan] = [ ['nama'=>..., 'total'=>...], ... ] (diurutkan desc)
$rankPerBulan = [];
for ($m = 1; $m <= 12; $m++) {
    $bulanRank = [];
    foreach ($jenisList as $js) {
        $total = $mapBar[$js['nama']][$m] ?? 0;
        if ($total > 0) {
            $bulanRank[] = ['nama' => $js['nama'], 'total' => $total];
        }
    }
    usort($bulanRank, fn($a, $b) => $b['total'] - $a['total']);
    $rankPerBulan[$m] = $bulanRank;
}

// ── RANKING TAHUNAN: top 5 spesies sepanjang tahun ───────────
$stmtTop = $pdo->prepare("
    SELECT ds.nama_satwa AS nama,
           COALESCE(SUM(ds.jumlah), 0) AS total
    FROM detail_satwa ds
    JOIN laporan l ON l.id = ds.laporan_id
    WHERE YEAR(l.tanggal) = ? $whereZona $whereSearch
    GROUP BY ds.nama_satwa
    ORDER BY total DESC
    LIMIT 5
");
$stmtTop->execute(array_merge([$tahun], $paramZona, $paramSearch));
$topSatwa = $stmtTop->fetchAll();
$maxTop   = !empty($topSatwa) ? $topSatwa[0]['total'] : 1;

// ── GRAFIK 2: Line Chart ─────────────────────────────────────
if ($zona) {
    $stmtLine = $pdo->prepare("
        SELECT ds.nama_satwa AS nama, MONTH(l.tanggal) AS bulan,
               COALESCE(SUM(ds.jumlah), 0) AS total
        FROM detail_satwa ds
        JOIN laporan l ON l.id = ds.laporan_id
        WHERE YEAR(l.tanggal) = ? AND ds.grid = ?
        GROUP BY ds.nama_satwa, MONTH(l.tanggal)
    ");
    $stmtLine->execute([$tahun, $zona]);
    $rawLine = $stmtLine->fetchAll();

    $mapLine = [];
    foreach ($rawLine as $r) $mapLine[$r['nama']][(int)$r['bulan']] = (int)$r['total'];

    $datasetsLine = [];
    foreach ($jenisList as $i => $js) {
        $arr = [];
        for ($m = 1; $m <= 12; $m++) $arr[] = $mapLine[$js['nama']][$m] ?? 0;
        if (array_sum($arr) > 0)
            $datasetsLine[] = ['label'=>$js['nama'], 'data'=>$arr, 'color'=>$paletteHex[$i % count($paletteHex)]];
    }
    $lineTitle    = "Tren per Jenis — Grid $zona";
    $lineSubtitle = "Jumlah individu per jenis burung per bulan · $tahun";
} else {
    $stmtLine = $pdo->prepare("
        SELECT ds.grid, MONTH(l.tanggal) AS bulan,
               COALESCE(SUM(ds.jumlah), 0) AS total
        FROM laporan l
        JOIN detail_satwa ds ON ds.laporan_id = l.id
        WHERE YEAR(l.tanggal) = ?
          AND ds.grid IS NOT NULL AND ds.grid != ''
        GROUP BY ds.grid, MONTH(l.tanggal)
    ");
    $stmtLine->execute([$tahun]);
    $rawLine  = $stmtLine->fetchAll();
    $mapGrid2 = [];
    foreach ($rawLine as $r) $mapGrid2[$r['grid']][(int)$r['bulan']] = (int)$r['total'];

    $datasetsLine = [];
    $idx = 0;
    foreach ($mapGrid2 as $g => $bd) {
        $arr = [];
        for ($m = 1; $m <= 12; $m++) $arr[] = $bd[$m] ?? 0;
        $datasetsLine[] = ['label'=>"Grid $g", 'data'=>$arr, 'color'=>$paletteHex[$idx++ % count($paletteHex)]];
    }
    $lineTitle    = "Tren per Grid ($tahun)";
    $lineSubtitle = "Total individu per kode grid per bulan";
}

// ── Stat cards ───────────────────────────────────────────────
$sq = $search ? "AND nama_satwa LIKE " . $pdo->quote("%$search%") : "";
if ($zona) {
    $q = $pdo->quote($zona);
    $totalLaporan   = (int)$pdo->query("SELECT COUNT(DISTINCT l.id) FROM laporan l JOIN detail_satwa ds ON ds.laporan_id=l.id WHERE YEAR(l.tanggal)=$tahun AND ds.grid=$q $sq")->fetchColumn();
    $totalSatwa     = (int)$pdo->query("SELECT COALESCE(SUM(ds.jumlah),0) FROM detail_satwa ds JOIN laporan l ON l.id=ds.laporan_id WHERE YEAR(l.tanggal)=$tahun AND ds.grid=$q $sq")->fetchColumn();
    $totalJenis     = (int)$pdo->query("SELECT COUNT(DISTINCT ds.nama_satwa) FROM detail_satwa ds JOIN laporan l ON l.id=ds.laporan_id WHERE YEAR(l.tanggal)=$tahun AND ds.grid=$q $sq")->fetchColumn();
} else {
    $totalLaporan   = (int)$pdo->query("SELECT COUNT(DISTINCT l.id) FROM laporan l JOIN detail_satwa ds ON ds.laporan_id=l.id WHERE YEAR(l.tanggal)=$tahun $sq")->fetchColumn();
    $totalSatwa     = (int)$pdo->query("SELECT COALESCE(SUM(ls.jumlah),0) FROM detail_satwa ls JOIN laporan l ON l.id=ls.laporan_id WHERE YEAR(l.tanggal)=$tahun $sq")->fetchColumn();
    $totalJenis     = (int)$pdo->query("SELECT COUNT(DISTINCT ls.nama_satwa) FROM detail_satwa ls JOIN laporan l ON l.id=ls.laporan_id WHERE YEAR(l.tanggal)=$tahun $sq")->fetchColumn();
}

// Daftar satwa di grid yang dipilih (untuk panel detail grid)
$satwaDiGrid = [];
if ($zona) {
    $stmtSatwaGrid = $pdo->query(
        "SELECT ds.nama_satwa AS nama, SUM(ds.jumlah) AS total
         FROM detail_satwa ds
         JOIN laporan l ON l.id = ds.laporan_id
         WHERE YEAR(l.tanggal)=$tahun AND ds.grid=$q
         GROUP BY ds.nama_satwa
         ORDER BY total DESC"
    );
    $satwaDiGrid = $stmtSatwaGrid->fetchAll(PDO::FETCH_ASSOC);
}

$tahunList = $pdo->query("SELECT DISTINCT YEAR(tanggal) AS y FROM laporan ORDER BY y DESC")->fetchAll(PDO::FETCH_COLUMN);
if (empty($tahunList)) $tahunList = [date('Y')];

$namaBulan = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
$medalIcon = ['1.','2.','3.','4.','5.'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Statistic — <?= APP_NAME ?></title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;display:flex;height:100vh;overflow:hidden;background:radial-gradient(circle at top left,rgba(0,169,193,.08),transparent 35%),#F7FAFC}

/* SIDEBAR */
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

/* MAIN */
.main{flex:1;display:flex;flex-direction:column;overflow:hidden}
.brand-colorbar{display:flex;height:4px;width:100%;flex-shrink:0}
.brand-colorbar span{flex:1}
.topbar{background:#ffffff;border-bottom:1px solid #e8edf2;padding:0 20px;height:52px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0}
.topbar-left{display:flex;align-items:center;gap:10px;font-size:16px;font-weight:600;color:#2a2d3a}
.topbar-right{display:flex;align-items:center;gap:8px}
.topbar-label{font-size:12px;color:#666;font-weight:500}
.tb-sel{background:#fff;border:1px solid #b0b2bc;border-radius:6px;padding:5px 9px;font-family:inherit;font-size:13px;color:#333;cursor:pointer}
.search-box{display:flex;align-items:center;gap:6px;background:#fff;border:1px solid #b0b2bc;border-radius:6px;padding:5px 10px;width:150px}
.search-box input{border:none;background:transparent;font-family:inherit;font-size:13px;color:#333;outline:none;width:100%}
.search-box input::placeholder{color:#aaa}
.btn-export{display:flex;align-items:center;gap:6px;padding:6px 14px;background:#217346;color:#fff;border:none;border-radius:6px;font-family:inherit;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;transition:background .15s}
.btn-export:hover{background:#1a5c38}

/* CONTENT */
.content{flex:1;overflow-y:auto;padding:16px 20px;display:flex;flex-direction:column;gap:14px}

/* STAT */
.stat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
.stat-card{background:#ffffff;border:1px solid #e8edf2;border-radius:10px;padding:14px 16px}
.stat-label{font-size:10.5px;font-weight:600;color:#666;text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px}
.stat-val{font-size:24px;font-weight:600;color:#2a2d3a}
.stat-sub{font-size:11.5px;color:#777;margin-top:2px}

/* GRID SELECTOR */
.filter-card{background:#ffffff;border:1px solid #e8edf2;border-radius:10px;padding:14px 18px}
.filter-top{display:flex;align-items:center;gap:12px;margin-bottom:12px;flex-wrap:wrap}
.filter-title{font-size:12px;font-weight:600;color:#555;text-transform:uppercase;letter-spacing:.06em}
.zona-badge{background:#3b82f6;color:#fff;font-size:11px;font-weight:600;padding:3px 10px;border-radius:99px}
.reset-btn{font-size:12px;color:#ef4444;text-decoration:none;font-weight:600;padding:3px 8px;border:1px solid rgba(239,68,68,.3);border-radius:6px}
.grid-wrap{overflow-x:auto;padding-bottom:4px}
.grid-table{border-collapse:collapse}
.grid-table th{font-size:10px;font-weight:700;color:#888;text-align:center;padding:3px 5px;min-width:28px}
.grid-table td{padding:2px 2px}
.grid-btn{width:28px;height:28px;border-radius:5px;border:1px solid #d0e8ee;background:#ffffff;font-size:10px;font-weight:600;color:#555;cursor:pointer;transition:all .15s;display:flex;align-items:center;justify-content:center;text-decoration:none}
.grid-btn:hover{background:#3b82f6;color:#fff;border-color:#3b82f6}
.grid-btn.active{background:#3b82f6;color:#fff;border-color:#2563eb;box-shadow:0 0 0 2px rgba(59,130,246,.3)}
.grid-btn.has-data{background:#dbeafe;border-color:#93c5fd;color:#1d4ed8}
.grid-btn.has-data:hover,.grid-btn.has-data.active{background:#3b82f6;color:#fff;border-color:#2563eb}
.grid-btn.no-data{opacity:.4;cursor:not-allowed;pointer-events:none}

/* CHARTS ROW */
.charts-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.chart-card{background:#ffffff;border:1px solid #e8edf2;border-radius:10px;padding:18px 20px}
.chart-title{font-size:14px;font-weight:600;color:#2a2d3a}
.chart-sub{font-size:11.5px;color:#777;margin-top:2px;margin-bottom:14px}
.legend{display:flex;flex-wrap:wrap;gap:8px 16px;margin-top:12px}
.leg-item{display:flex;align-items:center;gap:6px;font-size:11.5px;color:#555}
.leg-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0}
.leg-line{width:16px;height:3px;border-radius:2px;flex-shrink:0}
.empty-state{display:flex;align-items:center;justify-content:center;height:220px;color:#999;font-size:13px;flex-direction:column;gap:8px;text-align:center}

/* BOTTOM ROW */
.bottom-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}

/* TOP SPESIES TAHUNAN */
.top-card{background:#ffffff;border:1px solid #e8edf2;border-radius:10px;padding:18px 20px}
.top-item{display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid rgba(0,0,0,.06)}
.top-item:last-child{border-bottom:none}
.top-rank{font-size:16px;width:24px;text-align:center;flex-shrink:0}
.top-name{flex:1;font-size:13px;font-weight:500;color:#2a2d3a}
.top-bar-wrap{width:120px;flex-shrink:0}
.top-bar-bg{background:#ddd;border-radius:99px;height:7px;overflow:hidden}
.top-bar-fill{height:7px;border-radius:99px;transition:width .3s}
.top-count{font-size:12px;font-weight:600;color:#555;min-width:40px;text-align:right}

/* TABEL RANKING PER BULAN */
.rank-table-wrap{overflow-x:auto}
.rank-table{width:100%;border-collapse:collapse;min-width:600px}
.rank-table th{font-size:11px;font-weight:600;color:#666;text-transform:uppercase;letter-spacing:.05em;padding:8px 10px;text-align:left;border-bottom:1px solid #b8bac2;white-space:nowrap;background:#bbbdc5}
.rank-table td{padding:8px 10px;font-size:12.5px;border-bottom:1px solid rgba(0,0,0,.05);color:#2a2d3a;vertical-align:top}
.rank-table tr:last-child td{border-bottom:none}
.rank-table tr:hover td{background:rgba(0,0,0,.025)}
.rank-pills{display:flex;flex-direction:column;gap:3px}
.rank-pill{display:flex;align-items:center;gap:5px;font-size:11.5px;color:#333}
.pill-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.no-data-cell{color:#bbb;font-size:12px;font-style:italic}
.month-label{font-weight:600;color:#2a2d3a;font-size:13px}
.dominant-badge{background:#fef3c7;color:#92400e;font-size:10px;font-weight:700;padding:2px 6px;border-radius:4px;margin-left:4px}
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main">
  <header class="topbar">
    <div class="topbar-left">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#555" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/>
        <line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/>
      </svg>
      Statistic
      <?php if($zona): ?>
      <span style="display:inline-flex;align-items:center;gap:6px;background:rgba(59,130,246,.15);border:1px solid rgba(59,130,246,.3);color:#1d4ed8;border-radius:8px;padding:3px 10px;font-size:12px;font-weight:600">
        Grid <?= htmlspecialchars($zona) ?>
        <a href="?tahun=<?= $tahun ?>" style="color:#1d4ed8;text-decoration:none;font-weight:700">✕</a>
      </span>
      <?php endif; ?>
    </div>
    <div class="topbar-right">
      <a href="export_excel.php?tahun=<?= $tahun ?>&zona=<?= urlencode($zona) ?>" class="btn-export" title="Export ke Excel">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        Export Excel
      </a>
      <span class="topbar-label">Search</span>
      <form method="GET" style="display:contents" id="searchForm">
        <input type="hidden" name="zona" value="<?= htmlspecialchars($zona) ?>">
        <input type="hidden" name="tahun" value="<?= $tahun ?>">
        <div class="search-box" style="position:relative;width:160px">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" name="search" placeholder="Nama burung..." 
                 value="<?= htmlspecialchars($search) ?>"
                 onchange="this.form.submit()"
                 style="<?= $search ? 'padding-right:20px' : '' ?>">
          <?php if ($search): ?>
          <a href="admin_statistik.php?tahun=<?= $tahun ?>&zona=<?= urlencode($zona) ?>" 
             style="position:absolute;right:6px;color:#94a3b8;font-size:14px;text-decoration:none;line-height:1"
             title="Hapus pencarian">✕</a>
          <?php endif; ?>
        </div>
      </form>
      <form method="GET" style="display:contents">
        <input type="hidden" name="zona" value="<?= htmlspecialchars($zona) ?>">
        <span class="topbar-label">Tahun</span>
        <select name="tahun" class="tb-sel" onchange="this.form.submit()">
          <?php foreach($tahunList as $y): ?>
          <option value="<?= $y ?>" <?= $y==$tahun?'selected':'' ?>><?= $y ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>
  </header>
  <div style="height:4px;width:100%;background:linear-gradient(90deg,#00A9C1,#4FADC9,#88B146,#F0B14B,#D94F4F);flex-shrink:0"></div>

  <div class="content">

    <!-- STAT CARDS -->
    <div class="stat-grid">
      <div class="stat-card">
        <div class="stat-label">Total Laporan</div>
        <div class="stat-val"><?= number_format($totalLaporan) ?></div>
        <div class="stat-sub"><?= $zona ? "Grid $zona" : 'Semua grid' ?> · <?= $tahun ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Total Individu Satwa</div>
        <div class="stat-val" style="color:#1565c0"><?= number_format($totalSatwa) ?></div>
        <div class="stat-sub"><?= $zona ? "Di grid $zona" : 'Seluruh grid' ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Jenis Terpantau</div>
        <div class="stat-val" style="color:#6a1b9a"><?= $totalJenis ?></div>
        <div class="stat-sub">Jenis burung aktif <?= $tahun ?></div>
      </div>
    </div>

    <!-- GRID SELECTOR -->
    <div class="filter-card">
      <div class="filter-top">
        <span class="filter-title">Pilih Grid</span>
        <?php if($zona): ?>
          <span class="zona-badge">Grid <?= htmlspecialchars($zona) ?> aktif</span>
          <a href="?tahun=<?= $tahun ?><?= $search ? '&search='.urlencode($search) : '' ?>" class="reset-btn">✕ Reset</a>
        <?php else: ?>
          <span style="font-size:12px;color:#888">Klik grid biru untuk filter data</span>
        <?php endif; ?>
        <?php if($search): ?>
          <span style="font-size:12px;color:#00A9C1;font-weight:600;margin-left:8px">🔍 "<?= htmlspecialchars($search) ?>"</span>
        <?php endif; ?>
        <span style="font-size:11px;color:#aaa;margin-left:auto">
          <span style="display:inline-block;width:10px;height:10px;background:#dbeafe;border:1px solid #93c5fd;border-radius:2px;margin-right:3px"></span>Ada data &nbsp;
          <span style="display:inline-block;width:10px;height:10px;background:#ffffff;border:1px solid #d0e8ee;border-radius:2px;margin-right:3px"></span>Kosong
        </span>
      </div>
      <div class="grid-wrap">
        <table class="grid-table">
          <thead>
            <tr>
              <th></th>
              <?php foreach($kolomGrid as $k): ?><th><?= $k ?></th><?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach($barisGrid as $b): ?>
            <tr>
              <th><?= $b ?></th>
              <?php foreach($kolomGrid as $k): ?>
              <?php $kode=$b.$k; $kodeStrip=$b.'-'.$k; $ada=in_array($kode,$gridAda)||in_array($kodeStrip,$gridAda); $aktif=($zona===$kode||$zona===$kodeStrip); ?>
              <td>
                <?php if($ada): ?>
                <a href="?tahun=<?= $tahun ?>&zona=<?= urlencode($kodeStrip) ?>"
                   class="grid-btn <?= $aktif?'active':'has-data' ?>"
                   title="Grid <?= $kode ?>"><?= $kode ?></a>
                <?php else: ?>
                <div class="grid-btn no-data" title="Tidak ada data"><?= $kode ?></div>
                <?php endif; ?>
              </td>
              <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php if ($zona): ?>
    <div class="chart-card" style="margin-top:16px">
      <div class="chart-title">Satwa di Grid <?= htmlspecialchars($zona) ?></div>
      <div class="chart-sub"><?= $tahun ?></div>
      <?php if (empty($satwaDiGrid)): ?>
      <div style="padding:16px 0;color:#718096">Tidak ada data satwa di grid ini.</div>
      <?php else: ?>
      <div style="display:flex;flex-direction:column;gap:8px;margin-top:12px">
        <?php foreach ($satwaDiGrid as $s): ?>
        <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0">
          <span style="font-weight:600;color:#1a202c"><?= htmlspecialchars($s['nama']) ?></span>
          <span style="color:#1565c0;font-weight:700"><?= number_format((int)$s['total']) ?> ekor</span>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- CHARTS ROW -->
    <div class="charts-row">
      <!-- Stacked Bar -->
      <div class="chart-card">
        <div class="chart-title">Rata-rata jenis burung per bulan</div>
        <div class="chart-sub"><?= $zona ? "Grid <b>$zona</b>" : "Semua grid" ?> · <?= $tahun ?></div>
        <div style="position:relative;width:100%;height:230px">
          <canvas id="chartBar"></canvas>
        </div>
        <div class="legend">
          <?php foreach($datasetsBar as $ds): if(array_sum($ds['data'])>0): ?>
          <div class="leg-item">
            <div class="leg-dot" style="background:<?= $ds['color'] ?>"></div>
            <?= htmlspecialchars($ds['label']) ?>
          </div>
          <?php endif; endforeach; ?>
        </div>
      </div>

      <!-- Line Chart -->
      <div class="chart-card">
        <div class="chart-title"><?= htmlspecialchars($lineTitle) ?></div>
        <div class="chart-sub"><?= htmlspecialchars($lineSubtitle) ?></div>
        <?php if(empty($datasetsLine)): ?>
        <div class="empty-state">
          <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          Belum ada data untuk grid ini.<br>
          <span style="font-size:11.5px">Pilih grid berwarna biru.</span>
        </div>
        <?php else: ?>
        <div style="position:relative;width:100%;height:230px">
          <canvas id="chartLine"></canvas>
        </div>
        <div class="legend">
          <?php foreach($datasetsLine as $ds): ?>
          <div class="leg-item">
            <div class="leg-line" style="background:<?= $ds['color'] ?>"></div>
            <?= htmlspecialchars($ds['label']) ?>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- BOTTOM ROW: Top Spesies + Tabel Ranking per Bulan -->
    <div class="bottom-row">

      <!-- Top 5 Spesies Tahunan -->
      <div class="top-card">
        <div class="chart-title">Top Spesies Paling Banyak</div>
        <div class="chart-sub">
          Peringkat keseluruhan · <?= $zona ? "Grid $zona" : "Semua grid" ?> · <?= $tahun ?>
        </div>
        <?php if(empty($topSatwa)): ?>
        <div class="empty-state" style="height:140px">Belum ada data</div>
        <?php else: ?>
        <?php foreach($topSatwa as $i => $s):
          $jenisIdx = array_search($s['nama'], array_column($jenisList,'nama'));
          $warna    = $paletteHex[$jenisIdx !== false ? $jenisIdx % count($paletteHex) : $i];
          $pct      = $maxTop > 0 ? round($s['total']/$maxTop*100) : 0;
        ?>
        <div class="top-item">
          <div class="top-rank"><?= $medalIcon[$i] ?? ($i+1).'.' ?></div>
          <div class="top-name"><?= htmlspecialchars($s['nama']) ?></div>
          <div class="top-bar-wrap">
            <div class="top-bar-bg">
              <div class="top-bar-fill" style="width:<?= $pct ?>%;background:<?= $warna ?>"></div>
            </div>
          </div>
          <div class="top-count"><?= number_format($s['total']) ?> ekor</div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Tabel Ranking Spesies per Bulan -->
      <div class="top-card">
        <div class="chart-title">Spesies Dominan per Bulan</div>
        <div class="chart-sub">
          Urutan spesies terbanyak tiap bulan · <?= $zona ? "Grid $zona" : "Semua grid" ?> · <?= $tahun ?>
        </div>
        <div class="rank-table-wrap">
          <table class="rank-table">
            <thead>
              <tr>
                <th>Bulan</th>
                <th>#1 Dominan</th>
                <th>#2</th>
                <th>#3</th>
              </tr>
            </thead>
            <tbody>
              <?php for($m=1;$m<=12;$m++):
                $rankBulan = $rankPerBulan[$m] ?? [];
                $hasBulan  = !empty($rankBulan);
              ?>
              <tr>
                <td><span class="month-label"><?= $namaBulan[$m] ?></span></td>
                <?php if(!$hasBulan): ?>
                <td colspan="3" class="no-data-cell">Tidak ada data</td>
                <?php else: ?>
                <?php for($r=0;$r<3;$r++):
                  if(isset($rankBulan[$r])):
                    $jenisIdx2 = array_search($rankBulan[$r]['nama'], array_column($jenisList,'nama'));
                    $w2 = $paletteHex[$jenisIdx2 !== false ? $jenisIdx2 % count($paletteHex) : $r];
                ?>
                <td>
                  <div style="display:flex;align-items:center;gap:5px">
                    <div class="pill-dot" style="background:<?= $w2 ?>"></div>
                    <span style="font-size:12px"><?= htmlspecialchars($rankBulan[$r]['nama']) ?></span>
                    <?php if($r===0): ?><span class="dominant-badge"><?= number_format($rankBulan[$r]['total']) ?></span><?php endif; ?>
                  </div>
                </td>
                <?php else: ?><td class="no-data-cell">—</td><?php endif; endfor; ?>
                <?php endif; ?>
              </tr>
              <?php endfor; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div><!-- /bottom-row -->

  </div>
</div>

<script>
const bulanLabel = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];

// Stacked Bar
const barData = <?= json_encode(array_map(fn($d)=>[
  'label'           => $d['label'],
  'data'            => $d['data'],
  'backgroundColor' => $d['color'],
  'borderRadius'    => 2,
  'borderSkipped'   => false,
], $datasetsBar)) ?>;

// Hitung dominan per bulan untuk tooltip
const dominanPerBulan = <?= json_encode(array_map(function($rank) use ($namaBulan) {
    if(empty($rank)) return null;
    return ['nama' => $rank[0]['nama'], 'total' => $rank[0]['total']];
}, $rankPerBulan)) ?>;

new Chart(document.getElementById('chartBar'), {
  type: 'bar',
  data: { labels: bulanLabel, datasets: barData },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          afterBody: (items) => {
            const bulan = items[0].dataIndex + 1;
            const dom   = dominanPerBulan[bulan];
            if (dom) return [`──────────`, `Dominan: ${dom.nama}`, `   ${dom.total} ekor`];
            return [];
          },
          label: c => ` ${c.dataset.label}: ${c.parsed.y} ekor`
        }
      }
    },
    scales: {
      x: { stacked:true, grid:{display:false}, ticks:{color:'#555',font:{size:11}}, border:{color:'#999'} },
      y: { stacked:true, beginAtZero:true, ticks:{color:'#555',font:{size:11}}, grid:{color:'rgba(0,0,0,0.07)'}, border:{color:'#999'} }
    }
  }
});

// Line Chart
<?php if(!empty($datasetsLine)): ?>
new Chart(document.getElementById('chartLine'), {
  type: 'line',
  data: {
    labels: bulanLabel,
    datasets: <?= json_encode(array_map(fn($d)=>[
      'label'           => $d['label'],
      'data'            => $d['data'],
      'borderColor'     => $d['color'],
      'backgroundColor' => $d['color'].'33',
      'tension'         => 0.4,
      'pointRadius'     => 4,
      'pointHoverRadius'=> 6,
      'borderWidth'     => 2.5,
      'fill'            => false,
    ], $datasetsLine)) ?>
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: { callbacks: { label: c => ` ${c.dataset.label}: ${c.parsed.y} ekor` } }
    },
    scales: {
      x: { grid:{color:'rgba(0,0,0,0.06)'}, ticks:{color:'#555',font:{size:11}}, border:{color:'#999'} },
      y: { beginAtZero:true, ticks:{color:'#555',font:{size:11}}, grid:{color:'rgba(0,0,0,0.07)'}, border:{color:'#999'} }
    }
  }
});
<?php endif; ?>
</script>
</body>
</html>