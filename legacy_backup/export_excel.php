<?php
require_once 'koneksi.php';

$tahun = (int)($_GET['tahun'] ?? date('Y'));
$zona  = $_GET['zona'] ?? '';

$jenisList = $pdo->query("SELECT DISTINCT nama_satwa AS nama FROM detail_satwa WHERE laporan_id > 0 ORDER BY nama_satwa")->fetchAll();
$whereZona = $zona ? "AND l.grid_lokasi = " . $pdo->quote($zona) : "";

$rawBar = $pdo->query("
    SELECT ls.nama_satwa, MONTH(l.tanggal) AS bulan,
           COALESCE(SUM(ls.jumlah),0) AS total
    FROM detail_satwa ls
    JOIN laporan l ON l.id = ls.laporan_id
    WHERE ls.laporan_id > 0 AND YEAR(l.tanggal) = $tahun $whereZona
    GROUP BY ls.nama_satwa, MONTH(l.tanggal)
    ORDER BY ls.nama_satwa, MONTH(l.tanggal)
")->fetchAll();

$mapBar = [];
foreach ($rawBar as $r) $mapBar[$r['nama_satwa']][(int)$r['bulan']] = (int)$r['total'];

$topSatwa = $pdo->query("
    SELECT ls.nama_satwa, COALESCE(SUM(ls.jumlah),0) AS total
    FROM detail_satwa ls
    JOIN laporan l ON l.id = ls.laporan_id
    /* detail_satwa sudah berisi nama_satwa */
    WHERE YEAR(l.tanggal) = $tahun $whereZona
    GROUP BY ls.nama_satwa ORDER BY total DESC LIMIT 5
")->fetchAll();

$rankPerBulan = [];
for ($m = 1; $m <= 12; $m++) {
    $arr = [];
    foreach ($jenisList as $js) {
        $t = $mapBar[$js['nama']][$m] ?? 0;
        if ($t > 0) $arr[] = ['nama'=>$js['nama'],'total'=>$t];
    }
    usort($arr, fn($a,$b) => $b['total'] - $a['total']);
    $rankPerBulan[$m] = $arr;
}

$laporans = $pdo->query("
    SELECT l.id, l.nama_petugas, l.tanggal, l.area_inspeksi,
           l.grid_lokasi, l.unit_kerja, l.kondisi_cuaca, l.status,
           u.username,
           COALESCE(SUM(ls.jumlah),0) AS total_satwa,
           COUNT(DISTINCT ls.id) AS jenis_satwa
    FROM laporan l
    LEFT JOIN users u ON u.id = l.user_id
    LEFT JOIN detail_satwa ls ON ls.laporan_id = l.id
    WHERE YEAR(l.tanggal) = $tahun $whereZona
    GROUP BY l.id ORDER BY l.tanggal
")->fetchAll();

$namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni',
              'Juli','Agustus','September','Oktober','November','Desember'];

$grandTotal    = 0;
$totalPerJenis = array_fill(0, count($jenisList), 0);
$totalPerBulan = array_fill(1, 12, 0);
for ($m = 1; $m <= 12; $m++) {
    foreach ($jenisList as $j => $js) {
        $v = $mapBar[$js['nama']][$m] ?? 0;
        $totalPerJenis[$j] += $v;
        $totalPerBulan[$m] += $v;
        $grandTotal += $v;
    }
}
$totalLaporan   = count($laporans);
$totalDitemukan = count(array_filter($laporans, fn($l) => $l['status']==='ditangani'));
$pctDitemukan   = $totalLaporan > 0 ? round($totalDitemukan/$totalLaporan*100,1) : 0;
$cakupan        = $zona ? "Grid $zona" : "Semua Grid";
$judulFile      = "Statistik_Satwa_{$tahun}" . ($zona ? "_Grid{$zona}" : "") . ".xls";

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$judulFile\"");
header("Pragma: no-cache");
header("Expires: 0");
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="UTF-8">
<!--[if gte mso 9]>
<xml>
  <x:ExcelWorkbook>
    <x:ExcelWorksheets>
      <x:ExcelWorksheet>
        <x:Name>Statistik Satwa <?= $tahun ?></x:Name>
        <x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>
      </x:ExcelWorksheet>
    </x:ExcelWorksheets>
  </x:ExcelWorkbook>
</xml>
<![endif]-->
<style>
* { font-family: Calibri, Arial, sans-serif; font-size: 10pt; }
table { border-collapse: collapse; }
td, th { padding: 5px 10px; }

/* Judul */
.doc-title {
  font-size: 14pt; font-weight: bold; color: #1a1a1a;
  background: #F0F0F0; border-bottom: 2px solid #333333;
  padding: 10px 12px;
}
.doc-meta {
  font-size: 9pt; color: #666666; background: #FAFAFA;
  border-bottom: 1px solid #E2E2E2; padding: 5px 12px;
}

/* Section */
.sec-title {
  font-size: 10.5pt; font-weight: bold; color: #1a1a1a;
  background: #EBEBEB; border-top: 2px solid #333333;
  border-bottom: 1px solid #CCCCCC; padding: 7px 10px;
  text-transform: uppercase; letter-spacing: 0.04em;
}

/* Ringkasan */
.sum-lbl {
  font-size: 8.5pt; color: #888888; background: #F8F8F8;
  border: 1px solid #E4E4E4; text-align: center;
  text-transform: uppercase; letter-spacing: 0.05em;
  padding: 5px 14px;
}
.sum-val {
  font-size: 20pt; font-weight: bold; color: #1a1a1a;
  background: #FFFFFF; border: 1px solid #E4E4E4;
  border-top: none; text-align: center; padding: 8px 14px;
}

/* Header tabel */
.th-dark {
  font-weight: bold; font-size: 9.5pt; color: #1a1a1a;
  background: #EEEEEE; border: 1px solid #CCCCCC;
  text-align: center; white-space: nowrap; padding: 7px 10px;
}
.th-jenis {
  font-weight: bold; font-size: 9pt; color: #333333;
  background: #F5F5F5; border: 1px solid #DDDDDD;
  text-align: center; padding: 7px 10px;
}
.th-total {
  font-weight: bold; font-size: 9.5pt; color: #1a1a1a;
  background: #E0E0E0; border: 1px solid #BBBBBB;
  text-align: center; padding: 7px 10px;
}

/* Bulan label */
.td-bulan {
  font-weight: bold; font-size: 10pt; color: #222222;
  background: #F7F7F7; border: 1px solid #DDDDDD;
  border-left: 3px solid #555555; padding: 6px 10px;
}

/* Data */
.td-val  { border: 1px solid #E8E8E8; text-align: center; color: #1a1a1a; background: #FFFFFF; }
.td-hi   { border: 1px solid #D0D0D0; text-align: center; font-weight: bold; color: #1a1a1a; background: #F7F7F7; }
.td-zero { border: 1px solid #EEEEEE; text-align: center; color: #CCCCCC; }
.td-rowtot { font-weight: bold; text-align: center; background: #F2F2F2; border: 1px solid #CCCCCC; }
.td-coltot { font-weight: bold; text-align: center; background: #F2F2F2; border: 1px solid #CCCCCC; border-bottom: 2px solid #555555; }
.td-grand  { font-weight: bold; font-size: 11pt; text-align: center; background: #E2E2E2; border: 2px solid #555555; }
.td-pct    { font-size: 8.5pt; color: #777777; text-align: center; background: #FAFAFA; border: 1px solid #E8E8E8; }

/* Zebra */
.re { background: #FAFAFA; }
.ro { background: #FFFFFF; }

/* Ranking per bulan */
.td-month-lbl {
  font-weight: bold; font-size: 10pt; color: #222222;
  background: #F5F5F5; border: 1px solid #DDDDDD;
  border-left: 3px solid #555555; padding: 6px 10px;
  vertical-align: middle;
}
.rank-1 { font-weight: bold; background: #FFFDF0; border: 1px solid #E8E4CC; padding: 5px 10px; }
.rank-2 { background: #F8F8F8; border: 1px solid #E0E0E0; padding: 5px 10px; }
.rank-3 { color: #555555; background: #FAFAFA; border: 1px solid #E4E4E4; padding: 5px 10px; }
.td-num-rank {
  text-align: center; border: 1px solid #E4E4E4;
  font-size: 9.5pt; padding: 5px 8px;
}
.td-pct-rank { text-align: center; color: #666666; font-size: 9pt; border: 1px solid #E4E4E4; padding: 5px 8px; }
.td-subtot {
  font-weight: bold; text-align: center; font-size: 9.5pt;
  background: #F2F2F2; border: 1px solid #DDDDDD; padding: 4px 10px;
}
.td-empty-rank { color: #CCCCCC; font-style: italic; font-size: 9pt; border: 1px solid #EEEEEE; text-align: center; }
.td-spacer { height: 5px; border: none; background: #FFFFFF; }

/* Top 5 */
.td-top-rank { text-align: center; border: 1px solid #E4E4E4; background: #F8F8F8; font-size: 10pt; }
.td-top-nama { font-weight: bold; border: 1px solid #E4E4E4; padding: 6px 10px; }
.td-top-num  { font-weight: bold; text-align: center; border: 1px solid #E4E4E4; padding: 6px 10px; }
.td-top-pct  { text-align: center; color: #777777; border: 1px solid #E4E4E4; padding: 6px 10px; }

/* Detail laporan */
.th-det { font-weight: bold; font-size: 9.5pt; color: #1a1a1a; background: #EEEEEE; border: 1px solid #CCCCCC; text-align: center; padding: 7px 10px; white-space: nowrap; }
.td-l  { border: 1px solid #E8E8E8; padding: 5px 9px; }
.td-c  { border: 1px solid #E8E8E8; text-align: center; padding: 5px 9px; }
.td-b  { border: 1px solid #E8E8E8; font-weight: bold; padding: 5px 9px; }
.td-bc { border: 1px solid #E8E8E8; font-weight: bold; text-align: center; padding: 5px 9px; }
.st-y  { background: #F2FAF2; color: #2D6A2D; font-weight: bold; text-align: center; border: 1px solid #C8E6C8; padding: 5px 9px; }
.st-n  { background: #FDF5F5; color: #A02020; font-weight: bold; text-align: center; border: 1px solid #F0C8C8; padding: 5px 9px; }

/* Footer */
.doc-footer { font-size: 8.5pt; color: #AAAAAA; background: #FAFAFA; border-top: 1px solid #E0E0E0; padding: 5px 12px; font-style: italic; }
</style>
</head>
<body>

<!-- JUDUL -->
<table width="980">
  <tr>
    <td class="doc-title">
      Laporan Statistik Pemantauan Satwa Burung &mdash; <?= $tahun ?>
    </td>
  </tr>
  <tr>
    <td class="doc-meta">
      Bandar Udara Internasional Juanda &nbsp;&bull;&nbsp;
      Tahun: <b><?= $tahun ?></b> &nbsp;&bull;&nbsp;
      Cakupan: <b><?= $cakupan ?></b> &nbsp;&bull;&nbsp;
      Diekspor: <b><?= date('d F Y, H:i') ?> WIB</b>
    </td>
  </tr>
  <tr><td style="height:14px"></td></tr>
</table>

<!-- RINGKASAN -->
<table width="760">
  <tr><td colspan="9" class="sec-title">Ringkasan</td></tr>
  <tr><td colspan="9" style="height:6px"></td></tr>
  <tr>
    <td class="sum-lbl" width="140">Total Laporan</td><td width="10"></td>
    <td class="sum-lbl" width="140">Sudah Ditemukan</td><td width="10"></td>
    <td class="sum-lbl" width="140">Belum Ditemukan</td><td width="10"></td>
    <td class="sum-lbl" width="140">Total Individu</td><td width="10"></td>
    <td class="sum-lbl" width="140">Persentase Temuan</td>
  </tr>
  <tr>
    <td class="sum-val"><?= $totalLaporan ?></td><td></td>
    <td class="sum-val" style="color:#2D6A2D"><?= $totalDitemukan ?></td><td></td>
    <td class="sum-val" style="color:#A02020"><?= $totalLaporan - $totalDitemukan ?></td><td></td>
    <td class="sum-val"><?= number_format($grandTotal) ?></td><td></td>
    <td class="sum-val"><?= $pctDitemukan ?>%</td>
  </tr>
  <tr><td colspan="9" style="height:22px"></td></tr>
</table>

<!-- TABEL 1: MATRIX BULAN x JENIS -->
<?php $colSpan = count($jenisList) + 3; ?>
<table>
  <tr>
    <td colspan="<?= $colSpan ?>" class="sec-title">
      1. Jumlah Individu per Bulan per Jenis Burung &mdash; <?= $tahun ?> (<?= $cakupan ?>)
    </td>
  </tr>
  <tr><td colspan="<?= $colSpan ?>" style="height:6px"></td></tr>

  <!-- Header -->
  <tr>
    <th class="th-dark" width="36" rowspan="2">No</th>
    <th class="th-dark" width="110" rowspan="2">Bulan</th>
    <th class="th-dark" colspan="<?= count($jenisList) ?>">Jenis Burung</th>
    <th class="th-total" width="80" rowspan="2">Total</th>
  </tr>
  <tr>
    <?php foreach($jenisList as $js): ?>
    <th class="th-jenis" style="min-width:100px">
      <?= htmlspecialchars($js['nama']) ?>
    </th>
    <?php endforeach; ?>
  </tr>

  <!-- Data per bulan -->
  <?php for($m=1;$m<=12;$m++):
    $rowVals = [];
    foreach($jenisList as $js) $rowVals[] = $mapBar[$js['nama']][$m] ?? 0;
    $maxRow = !empty($rowVals) ? max($rowVals) : 0;
    $rowCls = ($m%2===0) ? 're' : 'ro';
  ?>
  <tr class="<?= $rowCls ?>">
    <td class="td-c" style="border:1px solid #E8E8E8;color:#999"><?= $m ?></td>
    <td class="td-bulan"><?= $namaBulan[$m] ?></td>
    <?php foreach($jenisList as $j => $js):
      $v   = $mapBar[$js['nama']][$m] ?? 0;
      $cls = $v === 0 ? 'td-zero' : ($v === $maxRow && $maxRow > 0 ? 'td-hi' : 'td-val');
    ?>
    <td class="<?= $cls ?>"><?= $v > 0 ? number_format($v) : '&mdash;' ?></td>
    <?php endforeach; ?>
    <td class="td-rowtot"><?= $totalPerBulan[$m] > 0 ? number_format($totalPerBulan[$m]) : '&mdash;' ?></td>
  </tr>
  <?php endfor; ?>

  <!-- Total per jenis -->
  <tr>
    <td colspan="2" class="th-total" style="text-align:right;padding-right:12px">Total per Jenis</td>
    <?php foreach($totalPerJenis as $t): ?>
    <td class="td-coltot"><?= number_format($t) ?></td>
    <?php endforeach; ?>
    <td class="td-grand"><?= number_format($grandTotal) ?></td>
  </tr>

  <!-- Persentase kontribusi -->
  <tr>
    <td colspan="2" class="td-pct" style="text-align:right;padding-right:12px">% Kontribusi</td>
    <?php foreach($totalPerJenis as $t): ?>
    <td class="td-pct"><?= $grandTotal>0 ? round($t/$grandTotal*100,1).'%' : '&mdash;' ?></td>
    <?php endforeach; ?>
    <td class="td-pct" style="font-weight:bold">100%</td>
  </tr>
  <tr><td colspan="<?= $colSpan ?>" style="height:22px"></td></tr>
</table>

<!-- TABEL 2: RANKING SPESIES PER BULAN -->
<table width="820">
  <tr>
    <td colspan="6" class="sec-title">
      2. Ranking Spesies Dominan per Bulan &mdash; <?= $tahun ?> (<?= $cakupan ?>)
    </td>
  </tr>
  <tr><td colspan="6" style="height:6px"></td></tr>

  <tr>
    <th class="th-dark" width="120">Bulan</th>
    <th class="th-dark" width="46">Rank</th>
    <th class="th-dark" width="220">Nama Spesies</th>
    <th class="th-dark" width="100">Jumlah (ekor)</th>
    <th class="th-dark" width="90">% Bulan Ini</th>
    <th class="th-dark" width="90">% Tahunan</th>
  </tr>

  <?php for($m=1;$m<=12;$m++):
    $rank    = $rankPerBulan[$m] ?? [];
    $showMax = min(3, count($rank));
    $rowCls  = ($m%2===0)?'re':'ro';
    $rankCls = ['rank-1','rank-2','rank-3'];
    $rankLbl = ['1','2','3'];
  ?>

  <?php if($showMax === 0): ?>
  <tr class="<?= $rowCls ?>">
    <td class="td-month-lbl"><?= $namaBulan[$m] ?></td>
    <td colspan="5" class="td-empty-rank">Tidak ada data pengamatan</td>
  </tr>
  <tr><td colspan="6" class="td-spacer"></td></tr>

  <?php else: ?>
  <?php for($r=0;$r<$showMax;$r++):
    $sp     = $rank[$r];
    $pctBln = $totalPerBulan[$m] > 0 ? round($sp['total']/$totalPerBulan[$m]*100,1) : 0;
    $pctThn = $grandTotal > 0 ? round($sp['total']/$grandTotal*100,1) : 0;
  ?>
  <tr class="<?= $rowCls ?>">
    <?php if($r===0): ?>
    <td class="td-month-lbl" rowspan="<?= $showMax + 1 ?>" style="vertical-align:middle">
      <?= $namaBulan[$m] ?>
    </td>
    <?php endif; ?>
    <td class="td-num-rank" style="font-weight:<?= $r===0?'bold':'normal' ?>"><?= $rankLbl[$r] ?></td>
    <td class="<?= $rankCls[$r] ?>"><?= htmlspecialchars($sp['nama']) ?></td>
    <td class="td-num-rank" style="font-weight:<?= $r===0?'bold':'normal' ?>"><?= number_format($sp['total']) ?></td>
    <td class="td-pct-rank"><?= $pctBln ?>%</td>
    <td class="td-pct-rank"><?= $pctThn ?>%</td>
  </tr>
  <?php endfor; ?>

  <!-- Subtotal baris bulan -->
  <tr style="background:#F5F5F5">
    <td class="td-pct" style="text-align:right;padding-right:10px;font-style:italic;color:#999">
      Total <?= $namaBulan[$m] ?>
    </td>
    <td colspan="2" class="td-subtot" style="text-align:left">
      <?= number_format($totalPerBulan[$m]) ?> ekor &nbsp;&bull;&nbsp;
      <?= count($rank) ?> jenis teramati
    </td>
    <td colspan="2" class="td-pct"></td>
  </tr>
  <tr><td colspan="6" class="td-spacer"></td></tr>
  <?php endif; ?>

  <?php endfor; ?>
  <tr><td colspan="6" style="height:20px"></td></tr>
</table>

<!-- TABEL 3: TOP 5 SPESIES -->
<table width="560">
  <tr>
    <td colspan="4" class="sec-title">
      3. Top 5 Spesies Terbanyak &mdash; <?= $tahun ?> (<?= $cakupan ?>)
    </td>
  </tr>
  <tr><td colspan="4" style="height:6px"></td></tr>
  <tr>
    <th class="th-dark" width="50">Rank</th>
    <th class="th-dark" width="240">Nama Spesies</th>
    <th class="th-dark" width="120">Jumlah (ekor)</th>
    <th class="th-dark" width="110">Persentase</th>
  </tr>
  <?php
  $maxTop = !empty($topSatwa) ? $topSatwa[0]['total'] : 1;
  $bgTop  = ['#FFFDF0','#F8F8F8','#FAF8F6','#FAFAFA','#FAFAFA'];
  foreach($topSatwa as $i => $s):
    $pct = $grandTotal > 0 ? round($s['total']/$grandTotal*100,1) : 0;
  ?>
  <tr style="background:<?= $bgTop[$i] ?>">
    <td class="td-top-rank"><?= $i+1 ?></td>
    <td class="td-top-nama"><?= htmlspecialchars($s['nama_satwa']) ?></td>
    <td class="td-top-num"><?= number_format($s['total']) ?></td>
    <td class="td-top-pct"><?= $pct ?>%</td>
  </tr>
  <?php endforeach; ?>
  <tr><td colspan="4" style="height:22px"></td></tr>
</table>

<!-- TABEL 4: DETAIL LAPORAN -->
<table width="980">
  <tr>
    <td colspan="11" class="sec-title">
      4. Detail Laporan Inspeksi &mdash; <?= $tahun ?> (<?= $cakupan ?>)
    </td>
  </tr>
  <tr><td colspan="11" style="height:6px"></td></tr>
  <tr>
    <th class="th-det" width="36">ID</th>
    <th class="th-det" width="150">Petugas</th>
    <th class="th-det" width="100">Username</th>
    <th class="th-det" width="90">Tanggal</th>
    <th class="th-det" width="55">Grid</th>
    <th class="th-det" width="130">Area Inspeksi</th>
    <th class="th-det" width="100">Unit Kerja</th>
    <th class="th-det" width="80">Cuaca</th>
    <th class="th-det" width="65">Jenis</th>
    <th class="th-det" width="75">Individu</th>
    <th class="th-det" width="110">Status</th>
  </tr>
  <?php foreach($laporans as $i => $lap):
    $rc = ($i%2===0) ? '#FDFDFD' : '#FFFFFF';
  ?>
  <tr style="background:<?= $rc ?>">
    <td class="td-c" style="color:#999"><?= $lap['id'] ?></td>
    <td class="td-b"><?= htmlspecialchars($lap['nama_petugas']) ?></td>
    <td class="td-l"><?= htmlspecialchars($lap['username']??'') ?></td>
    <td class="td-c"><?= date('d/m/Y', strtotime($lap['tanggal'])) ?></td>
    <td class="td-bc"><?= htmlspecialchars($lap['grid_lokasi']??'&mdash;') ?></td>
    <td class="td-l"><?= htmlspecialchars($lap['area_inspeksi']) ?></td>
    <td class="td-l"><?= htmlspecialchars($lap['unit_kerja']??'&mdash;') ?></td>
    <td class="td-c"><?= htmlspecialchars($lap['kondisi_cuaca']) ?></td>
    <td class="td-c"><?= $lap['jenis_satwa'] ?></td>
    <td class="td-bc"><?= number_format($lap['total_satwa']) ?></td>
    <td class="<?= $lap['status']==='ditangani'?'st-y':'st-n' ?>">
      <?= $lap['status']==='ditangani' ? 'Ditemukan' : 'Belum Ditemukan' ?>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php if(empty($laporans)): ?>
  <tr>
    <td colspan="11" style="text-align:center;padding:20px;color:#BBBBBB;
        font-style:italic;border:1px solid #E8E8E8">
      Tidak ada data laporan untuk periode ini.
    </td>
  </tr>
  <?php endif; ?>
</table>

<!-- Footer -->
<table width="980" style="margin-top:16px">
  <tr>
    <td class="doc-footer">
      Digenerate otomatis oleh Sistem Logbook Satwa &mdash;
      <?= date('d F Y H:i') ?> WIB &nbsp;&bull;&nbsp;
      Database: logbook_project_baru
    </td>
  </tr>
</table>

</body>
</html>