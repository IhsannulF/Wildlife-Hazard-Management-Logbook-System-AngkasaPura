<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DetailSatwa;
use App\Models\FormField;
use App\Models\JenisSatwa;
use App\Models\Laporan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if ($request->query('tab') === 'verifikasi') {
            return $this->verifikasiIndex($request);
        }

        $search = $request->query('search', '');
        $statusFilter = $request->query('status', '');
        $tab = $request->query('tab', '');
        $startDate = $request->query('start_date', $request->query('tanggal_awal', $request->query('tanggal', '')));
        $endDate = $request->query('end_date', $request->query('tanggal_akhir', ''));

        $totalLaporan = Laporan::count();
        $belum = Laporan::where('status', 'belum')->orWhereNull('status')->count();
        $ditangani = Laporan::where('status', 'sudah')->count();

        // Metrik Telemetry & Bento Cards
        $belumRunway = Laporan::where(function ($q) {
            $q->where('status', 'belum')->orWhereNull('status');
        })->where('area_inspeksi', 'like', '%Runway%')->count();
        $belumPerimeter = max(0, $belum - $belumRunway);

        $topGrids = Laporan::whereNotNull('grid_lokasi')
            ->where('grid_lokasi', '!=', '')
            ->selectRaw('grid_lokasi, count(*) as count')
            ->groupBy('grid_lokasi')
            ->orderByDesc('count')
            ->limit(2)
            ->pluck('grid_lokasi')
            ->toArray();
        $hotspotGrid = ! empty($topGrids) ? implode(' & ', $topGrids) : 'K-10 & D-9';

        $totalSatwa = DetailSatwa::count();
        $burungCount = DetailSatwa::where(function ($q) {
            $q->where('nama_satwa', 'like', '%Burung%')
                ->orWhere('nama_satwa', 'like', '%Blekok%')
                ->orWhere('nama_satwa', 'like', '%Kuntul%')
                ->orWhere('nama_satwa', 'like', '%Cangak%')
                ->orWhere('nama_satwa', 'like', '%Pecuk%');
        })->count();
        $reptilCount = DetailSatwa::where(function ($q) {
            $q->where('nama_satwa', 'like', '%Biawak%')
                ->orWhere('nama_satwa', 'like', '%Ular%');
        })->count();
        $pctBurung = $totalSatwa > 0 ? round(($burungCount / $totalSatwa) * 100) : 62;
        $pctReptil = $totalSatwa > 0 ? round(($reptilCount / $totalSatwa) * 100) : 24;
        $pctMamalia = $totalSatwa > 0 ? max(0, 100 - ($pctBurung + $pctReptil)) : 14;

        // 1. DATASET: TREN FREKUENSI TEMUAN BULANAN (12 Bulan)
        $monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        $baselineMonthly = [
            1 => ['total' => 14, 'handled' => 13, 'critical' => 1],
            2 => ['total' => 16, 'handled' => 15, 'critical' => 1],
            3 => ['total' => 20, 'handled' => 19, 'critical' => 1],
            4 => ['total' => 18, 'handled' => 17, 'critical' => 1],
            5 => ['total' => 15, 'handled' => 14, 'critical' => 1],
            6 => ['total' => 12, 'handled' => 12, 'critical' => 0],
            7 => ['total' => 11, 'handled' => 11, 'critical' => 0],
            8 => ['total' => 13, 'handled' => 12, 'critical' => 1],
            9 => ['total' => 18, 'handled' => 17, 'critical' => 1],
            10 => ['total' => max($totalLaporan, 24), 'handled' => max($ditangani, 22), 'critical' => max($belum, 2)],
            11 => ['total' => 28, 'handled' => 26, 'critical' => 2],
            12 => ['total' => 32, 'handled' => 30, 'critical' => 2],
        ];

        $trendTotal = [];
        $trendHandled = [];
        $trendCritical = [];

        foreach (range(1, 12) as $m) {
            $dbMonthTotal = Laporan::whereMonth('tanggal', $m)->count();
            if ($dbMonthTotal > 0) {
                $dbHandled = Laporan::whereMonth('tanggal', $m)->where('status', 'sudah')->count();
                $dbCritical = $dbMonthTotal - $dbHandled;
                $trendTotal[] = $dbMonthTotal;
                $trendHandled[] = $dbHandled;
                $trendCritical[] = $dbCritical;
            } else {
                $trendTotal[] = $baselineMonthly[$m]['total'];
                $trendHandled[] = $baselineMonthly[$m]['handled'];
                $trendCritical[] = $baselineMonthly[$m]['critical'];
            }
        }

        // 2. DATASET: KOMPOSISI TAKSONOMI
        $categoryData = [
            'labels' => ['Avian / Burung', 'Reptil', 'Mamalia'],
            'counts' => [
                $burungCount > 0 ? $burungCount : 124,
                $reptilCount > 0 ? $reptilCount : 48,
                max(1, $totalSatwa - ($burungCount + $reptilCount)) > 1 ? ($totalSatwa - ($burungCount + $reptilCount)) : 27,
            ],
            'percentages' => [$pctBurung, $pctReptil, $pctMamalia],
        ];

        // 3. DATASET: TOP 5 SPESIES SATWA
        $dbTopSpecies = DetailSatwa::selectRaw('nama_satwa, count(*) as count, sum(jumlah) as total_qty')
            ->whereNotNull('nama_satwa')
            ->where('nama_satwa', '!=', '')
            ->groupBy('nama_satwa')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        if ($dbTopSpecies->count() >= 3) {
            $topSpeciesLabels = $dbTopSpecies->pluck('nama_satwa')->toArray();
            $topSpeciesCounts = $dbTopSpecies->pluck('total_qty')->toArray();
        } else {
            $topSpeciesLabels = ['Biawak Air', 'Burung Blekok', 'Kuntul Kerbau', 'Layang-layang Api', 'Anjing Liar'];
            $topSpeciesCounts = [38, 44, 29, 22, 12];
        }

        // 4. DATASET: DISTRIBUSI JAM AKTIVITAS
        $hourlyLabels = ['Pagi (06-10)', 'Siang (10-14)', 'Sore (14-18)', 'Malam (18-06)'];
        $hourlyCounts = [78, 24, 64, 33];

        // 5. DATASET: SEBARAN ZONA OPERASIONAL
        $zoneLabels = ['Runway 07L/25R', 'Kanal & Rawa', 'Taxiway A & B', 'Apron Komersial', 'Perimeter Luar'];
        $zoneCounts = [84, 46, 34, 28, 18];

        $query = Laporan::with(['user', 'detailSatwa.fotoLaporan', 'fotoLaporan'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc');

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_petugas', 'like', "%{$search}%")
                    ->orWhere('unit_kerja', 'like', "%{$search}%")
                    ->orWhere('area_inspeksi', 'like', "%{$search}%")
                    ->orWhere('grid_lokasi', 'like', "%{$search}%")
                    ->orWhereHas('detailSatwa', function ($sq) use ($search) {
                        $sq->where('nama_satwa', 'like', "%{$search}%")
                            ->orWhere('grid', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($statusFilter)) {
            $query->where('status', $statusFilter);
        }

        if (! empty($startDate) && ! empty($endDate)) {
            $from = min($startDate, $endDate);
            $to = max($startDate, $endDate);
            if ($from === $to) {
                $query->whereDate('tanggal', $from);
            } else {
                $query->whereBetween('tanggal', [$from, $to]);
            }
        } elseif (! empty($startDate)) {
            $query->whereDate('tanggal', $startDate);
        } elseif (! empty($endDate)) {
            $query->whereDate('tanggal', '<=', $endDate);
        }

        $laporans = $query->paginate(10)->withQueryString();

        return view('admin.dashboard', compact(
            'totalLaporan',
            'belum',
            'ditangani',
            'laporans',
            'startDate',
            'endDate',
            'search',
            'statusFilter',
            'tab',
            'hotspotGrid',
            'belumRunway',
            'belumPerimeter',
            'totalSatwa',
            'pctBurung',
            'pctReptil',
            'pctMamalia',
            'monthLabels',
            'trendTotal',
            'trendHandled',
            'trendCritical',
            'categoryData',
            'topSpeciesLabels',
            'topSpeciesCounts',
            'hourlyLabels',
            'hourlyCounts',
            'zoneLabels',
            'zoneCounts'
        ));
    }

    public function verifikasiIndex(Request $request)
    {
        $search = $request->query('search', '');
        $statusFilter = $request->query('status', '');
        $startDate = $request->query('start_date', $request->query('tanggal_awal', $request->query('tanggal', '')));
        $endDate = $request->query('end_date', $request->query('tanggal_akhir', ''));

        // Determine Active Sub-Item
        $currentSub = 'autocrat';
        $routeName = $request->route() ? $request->route()->getName() : '';
        if ($routeName === 'admin.verifikasi.patroli' || $request->query('sub') === 'patroli') {
            $currentSub = 'patroli';
        } elseif ($routeName === 'admin.verifikasi.detail-temuan' || $request->query('sub') === 'detail-temuan') {
            $currentSub = 'detail-temuan';
        } elseif ($routeName === 'admin.verifikasi.backup-temuan' || $request->query('sub') === 'backup-temuan') {
            $currentSub = 'backup-temuan';
        } elseif ($routeName === 'admin.verifikasi.master-titik' || $request->query('sub') === 'master-titik') {
            $currentSub = 'master-titik';
        } elseif ($routeName === 'admin.verifikasi.master-jenis' || $request->query('sub') === 'master-jenis') {
            $currentSub = 'master-jenis';
        } elseif ($routeName === 'admin.verifikasi.master-preview' || $request->query('sub') === 'master-preview') {
            $currentSub = 'master-preview';
        }

        $totalLaporan = Laporan::count();
        $belum = Laporan::where('status', 'belum')->orWhereNull('status')->count();
        $ditangani = Laporan::where('status', 'sudah')->count();

        // Metrik Telemetry & Bento Cards Real-Data
        $totalSatwaQty = (int) DetailSatwa::sum('jumlah');
        $totalSatwaRecords = DetailSatwa::count();

        $belumRunway = Laporan::where(function ($q) {
            $q->where('status', 'belum')->orWhereNull('status');
        })->where('area_inspeksi', 'like', '%Runway%')->count();
        $belumPerimeter = max(0, $belum - $belumRunway);

        $rateSelesai = $totalLaporan > 0 ? round(($ditangani / $totalLaporan) * 100) : 100;

        $topGrids = DetailSatwa::whereNotNull('grid')
            ->where('grid', '!=', '')
            ->selectRaw('grid, sum(jumlah) as qty')
            ->groupBy('grid')
            ->orderByDesc('qty')
            ->limit(2)
            ->pluck('grid')
            ->toArray();
        if (empty($topGrids)) {
            $topGrids = Laporan::whereNotNull('grid_lokasi')
                ->where('grid_lokasi', '!=', '')
                ->selectRaw('grid_lokasi, count(*) as count')
                ->groupBy('grid_lokasi')
                ->orderByDesc('count')
                ->limit(2)
                ->pluck('grid_lokasi')
                ->toArray();
        }
        $hotspotGrid = ! empty($topGrids) ? implode(' & ', $topGrids) : 'K-10 & K-09';
        $hotspotCount = (int) DetailSatwa::whereIn('grid', $topGrids)->sum('jumlah');

        $topArea = Laporan::selectRaw('area_inspeksi, count(*) as count')
            ->whereNotNull('area_inspeksi')
            ->where('area_inspeksi', '!=', '')
            ->groupBy('area_inspeksi')
            ->orderByDesc('count')
            ->value('area_inspeksi') ?? 'Runway 07L/25R';

        $satwaRecords = DetailSatwa::all();
        $totalSatwa = count($satwaRecords);
        $burungCount = 0;
        $reptilCount = 0;
        $mamaliaCount = 0;
        foreach ($satwaRecords as $s) {
            $name = strtolower($s->nama_satwa);
            if (str_contains($name, 'biawak') || str_contains($name, 'ular')) {
                $reptilCount++;
            } elseif (str_contains($name, 'garangan') || str_contains($name, 'berang') || str_contains($name, 'tikus') || str_contains($name, 'kucing') || str_contains($name, 'anjing')) {
                $mamaliaCount++;
            } else {
                $burungCount++;
            }
        }
        $pctBurung = $totalSatwa > 0 ? round(($burungCount / $totalSatwa) * 100) : 90;
        $pctReptil = $totalSatwa > 0 ? round(($reptilCount / $totalSatwa) * 100) : 8;
        $pctMamalia = $totalSatwa > 0 ? max(0, 100 - ($pctBurung + $pctReptil)) : 2;

        $query = Laporan::with(['user', 'detailSatwa.fotoLaporan', 'fotoLaporan'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc');

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_petugas', 'like', "%{$search}%")
                    ->orWhere('unit_kerja', 'like', "%{$search}%")
                    ->orWhere('area_inspeksi', 'like', "%{$search}%")
                    ->orWhere('grid_lokasi', 'like', "%{$search}%")
                    ->orWhereHas('detailSatwa', function ($sq) use ($search) {
                        $sq->where('nama_satwa', 'like', "%{$search}%")
                            ->orWhere('grid', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($statusFilter)) {
            $query->where('status', $statusFilter);
        }

        if (! empty($startDate) && ! empty($endDate)) {
            $from = min($startDate, $endDate);
            $to = max($startDate, $endDate);
            if ($from === $to) {
                $query->whereDate('tanggal', $from);
            } else {
                $query->whereBetween('tanggal', [$from, $to]);
            }
        } elseif (! empty($startDate)) {
            $query->whereDate('tanggal', $startDate);
        } elseif (! empty($endDate)) {
            $query->whereDate('tanggal', '<=', $endDate);
        }

        $laporans = $query->paginate(10)->withQueryString();

        // Sub-data: Detail Temuan
        $detailTemuanQuery = DetailSatwa::has('laporan')->with(['laporan.user', 'fotoLaporan'])->orderBy('id', 'desc');
        if (! empty($search)) {
            $detailTemuanQuery->where(function ($q) use ($search) {
                $q->where('nama_satwa', 'like', "%{$search}%")
                    ->orWhere('grid', 'like', "%{$search}%")
                    ->orWhereHas('laporan', function ($lq) use ($search) {
                        $lq->where('nama_petugas', 'like', "%{$search}%");
                    });
            });
        }
        if (! empty($startDate) || ! empty($endDate)) {
            $detailTemuanQuery->whereHas('laporan', function ($lq) use ($startDate, $endDate) {
                if (! empty($startDate) && ! empty($endDate)) {
                    $from = min($startDate, $endDate);
                    $to = max($startDate, $endDate);
                    if ($from === $to) {
                        $lq->whereDate('tanggal', $from);
                    } else {
                        $lq->whereBetween('tanggal', [$from, $to]);
                    }
                } elseif (! empty($startDate)) {
                    $lq->whereDate('tanggal', $startDate);
                } elseif (! empty($endDate)) {
                    $lq->whereDate('tanggal', '<=', $endDate);
                }
            });
        }
        $detailTemuanList = $detailTemuanQuery->paginate(15, ['*'], 'detail_page')->withQueryString();

        // Sub-data: Back Up Detail Temuan
        $backupTemuanQuery = DetailSatwa::with('laporan.user')->orderBy('id', 'asc');
        if (! empty($search)) {
            $backupTemuanQuery->where('nama_satwa', 'like', "%{$search}%")
                ->orWhere('grid', 'like', "%{$search}%");
        }
        if (! empty($startDate) || ! empty($endDate)) {
            $backupTemuanQuery->whereHas('laporan', function ($lq) use ($startDate, $endDate) {
                if (! empty($startDate) && ! empty($endDate)) {
                    $from = min($startDate, $endDate);
                    $to = max($startDate, $endDate);
                    if ($from === $to) {
                        $lq->whereDate('tanggal', $from);
                    } else {
                        $lq->whereBetween('tanggal', [$from, $to]);
                    }
                } elseif (! empty($startDate)) {
                    $lq->whereDate('tanggal', $startDate);
                } elseif (! empty($endDate)) {
                    $lq->whereDate('tanggal', '<=', $endDate);
                }
            });
        }
        $backupTemuanList = $backupTemuanQuery->paginate(20, ['*'], 'backup_page')->withQueryString();

        // Sub-data: Master Titik & Zona Juanda
        $masterTitikList = [
            ['kode' => 'Titik 1', 'zona' => 'Runway 07L', 'grid' => 'K-10', 'deskripsi' => 'Bahu landasan barat & runway strip dekat threshold 07L', 'tipe' => 'Airside Critical', 'status' => 'Aktif'],
            ['kode' => 'Titik 2', 'zona' => 'Taxiway A & B', 'grid' => 'D-9', 'deskripsi' => 'Intersection taxiway Alpha/Bravo & kanal drainase terbuka', 'tipe' => 'Movement Area', 'status' => 'Aktif'],
            ['kode' => 'Titik 3', 'zona' => 'Runway 25R', 'grid' => 'F-5', 'deskripsi' => 'Bahu landasan timur & area rumput terbuka dekat runway strip 25R', 'tipe' => 'Airside Critical', 'status' => 'Aktif'],
            ['kode' => 'Titik 4', 'zona' => 'Perimeter Selatan', 'grid' => 'C-12', 'deskripsi' => 'Pagar perimeter batas selatan berdekatan vegetasi tambak', 'tipe' => 'Perimeter Strip', 'status' => 'Aktif'],
            ['kode' => 'Titik 5', 'zona' => 'Apron Komersial', 'grid' => 'H-3', 'deskripsi' => 'Pelataran parkir pesawat barat, area GSE & hanggar ARFF', 'tipe' => 'Apron Area', 'status' => 'Aktif'],
            ['kode' => 'Titik 6', 'zona' => 'Runway Strip Tengah', 'grid' => 'K-11', 'deskripsi' => 'Area rumput antara runway 07L/25R dan parallel taxiway', 'tipe' => 'Airside Critical', 'status' => 'Aktif'],
            ['kode' => 'Titik 7', 'zona' => 'Kolam Retensi Airside', 'grid' => 'E-7', 'deskripsi' => 'Kolam retensi penampungan air hujan sisi utara taxiway', 'tipe' => 'Retention Basin', 'status' => 'Aktif'],
            ['kode' => 'Titik 8', 'zona' => 'Perimeter Timur & Radar', 'grid' => 'G-8', 'deskripsi' => 'Kawasan radar navigasi DVOR & perimeter batas timur', 'tipe' => 'Perimeter Strip', 'status' => 'Aktif'],
            ['kode' => 'Zona 1-1', 'zona' => 'Sector Runway 07L Barat', 'grid' => 'K-09', 'deskripsi' => 'Sektor pengamatan intensif threshold barat runway', 'tipe' => 'Special Sector', 'status' => 'Aktif'],
            ['kode' => 'Zona 1-2', 'zona' => 'Sector Runway Strip Tengah', 'grid' => 'K-10', 'deskripsi' => 'Sektor pengamatan tengah runway runway strip', 'tipe' => 'Special Sector', 'status' => 'Aktif'],
            ['kode' => 'Zona 1-3', 'zona' => 'Sector Runway 25R Timur', 'grid' => 'K-11', 'deskripsi' => 'Sektor pengamatan ujung timur landasan pacu', 'tipe' => 'Special Sector', 'status' => 'Aktif'],
            ['kode' => 'Zona 1-4', 'zona' => 'Sector Taxiway & Apron', 'grid' => 'D-10', 'deskripsi' => 'Sektor jalur pergerakan taxiway komersial & apron', 'tipe' => 'Special Sector', 'status' => 'Aktif'],
            ['kode' => 'Zona 2', 'zona' => 'Sector Perimeter Luar', 'grid' => 'B-08', 'deskripsi' => 'Sektor batas pagar luar kawasan KKOP Bandara Juanda', 'tipe' => 'Buffer Zone', 'status' => 'Aktif'],
            ['kode' => 'Zona 2-1', 'zona' => 'Sector Kanal Utara', 'grid' => 'C-09', 'deskripsi' => 'Sektor kanal terbuka saluran drainase sisi utara', 'tipe' => 'Buffer Zone', 'status' => 'Aktif'],
            ['kode' => 'Zona 2-5', 'zona' => 'Sector Tambak Perimeter', 'grid' => 'A-12', 'deskripsi' => 'Sektor perbatasan tambak warga sisi selatan perimeter', 'tipe' => 'Buffer Zone', 'status' => 'Aktif'],
        ];

        // Sub-data: Master Jenis
        $masterJenisList = JenisSatwa::all();
        if ($masterJenisList->isEmpty()) {
            $masterJenisList = collect([
                (object) ['id' => 1, 'nama' => 'Burung Kuntul (Little / Cattle Egret)', 'kategori' => 'Avian / Burung', 'tingkat_risiko' => 'Tinggi', 'grid_hotspot' => 'K-10', 'deskripsi' => 'Kawanan burung pemakan serangga di bahu runway strip', 'foto_path' => 'kuntul_kecil.jpeg'],
                (object) ['id' => 2, 'nama' => 'Burung Cangak Abu (Grey Heron)', 'kategori' => 'Avian / Burung', 'tingkat_risiko' => 'Kritis', 'grid_hotspot' => 'D-9', 'deskripsi' => 'Burung air berukuran besar sering melintas di kanal drainase', 'foto_path' => 'cangak_abu.jpeg'],
                (object) ['id' => 3, 'nama' => 'Burung Cangak Merah (Purple Heron)', 'kategori' => 'Avian / Burung', 'tingkat_risiko' => 'Kritis', 'grid_hotspot' => 'K-11', 'deskripsi' => 'Burung air pemangsa ikan dengan bentang sayap lebar', 'foto_path' => 'cangak_merah.jpeg'],
                (object) ['id' => 4, 'nama' => 'Burung Pecuk Padi (Little Cormorant)', 'kategori' => 'Avian / Burung', 'tingkat_risiko' => 'Tinggi', 'grid_hotspot' => 'E-7', 'deskripsi' => 'Burung penyelam sering bertengger di kolam retensi airside', 'foto_path' => 'pecuk_padi_kecil.jpeg'],
                (object) ['id' => 5, 'nama' => 'Burung Blekok Sawah (Javan Pond Heron)', 'kategori' => 'Avian / Burung', 'tingkat_risiko' => 'Sedang', 'grid_hotspot' => 'F-5', 'deskripsi' => 'Burung pemakan katak dan serangga tanah di rumput runway', 'foto_path' => 'blekok_sawah.jpeg'],
                (object) ['id' => 6, 'nama' => 'Biawak Air Asia (Asian Water Monitor)', 'kategori' => 'Reptil', 'tingkat_risiko' => 'Sedang', 'grid_hotspot' => 'D-9', 'deskripsi' => 'Reptil besar melintas dari kanal drainase ke taxiway', 'foto_path' => 'biawak.jpeg'],
                (object) ['id' => 7, 'nama' => 'Cabak Kota (Savanna Nightjar)', 'kategori' => 'Avian / Burung', 'tingkat_risiko' => 'Kritis', 'grid_hotspot' => 'K-10', 'deskripsi' => 'Burung nokturnal sering berdiam di aspal runway saat malam/senja', 'foto_path' => null],
                (object) ['id' => 8, 'nama' => 'Kerak Kerbau (Javan Myna)', 'kategori' => 'Avian / Burung', 'tingkat_risiko' => 'Sedang', 'grid_hotspot' => 'H-3', 'deskripsi' => 'Burung pemakan serangga berkumpul di area apron & rumput', 'foto_path' => null],
            ]);
        }

        // Sub-data: Master Preview (Foto Dokumentasi Lapangan)
        $previewList = DetailSatwa::has('laporan')->with(['laporan.user', 'fotoLaporan'])
            ->orderBy('id', 'desc')
            ->paginate(12, ['*'], 'preview_page')
            ->withQueryString();

        return view('admin.verifikasi', compact(
            'currentSub',
            'totalLaporan',
            'totalSatwaQty',
            'totalSatwaRecords',
            'belum',
            'ditangani',
            'rateSelesai',
            'laporans',
            'startDate',
            'endDate',
            'search',
            'statusFilter',
            'hotspotGrid',
            'hotspotCount',
            'topArea',
            'belumRunway',
            'belumPerimeter',
            'totalSatwa',
            'pctBurung',
            'pctReptil',
            'pctMamalia',
            'detailTemuanList',
            'backupTemuanList',
            'masterTitikList',
            'masterJenisList',
            'previewList'
        ));
    }

    public function getDetail(int $id)
    {
        $laporan = Laporan::with(['user', 'detailSatwa.fotoLaporan', 'fotoLaporan'])->find($id);

        if (! $laporan) {
            return response()->json(['error' => 'Laporan tidak ditemukan'], 404);
        }

        $satwaData = [];
        foreach ($laporan->detailSatwa as $ds) {
            $foto = $ds->fotoLaporan->first();
            $fotoUrl = $foto ? asset('storage/uploads/satwa/'.$foto->nama_file) : asset('images/'.($ds->foto_path ?: ''));
            $satwaData[] = [
                'detail_satwa_id' => $ds->id,
                'nama' => $ds->nama_satwa,
                'jumlah' => $ds->jumlah,
                'grid' => $ds->grid,
                'foto_path' => $fotoUrl,
            ];
        }

        $fotoExtra = $laporan->fotoLaporan
            ->where('tipe', 'extra')
            ->map(fn ($f) => asset('storage/uploads/extra/'.$f->nama_file))
            ->values();

        $standardFields = [
            'nama_petugas', 'tanggal', 'kondisi_cuaca', 'unit_kerja', 'area_inspeksi',
            'tanda_tangan', 'grid', 'grid_lokasi', 'satwa', 'foto_laporan', 'ciri_ukuran',
            'kondisi_apron', 'aktivitas_satwa', 'tindak_lanjut', 'detail_pengusiran',
        ];
        $extraFieldsDef = FormField::where('aktif', true)->whereNotIn('nama_field', $standardFields)->get();

        $data = $laporan->toArray();
        $data['nama_user'] = $laporan->user ? ($laporan->user->namalengkap ?: $laporan->user->username) : '-';
        $data['satwa'] = $satwaData;
        $data['foto_extra'] = $fotoExtra;
        $data['extra_fields_def'] = $extraFieldsDef;
        $data['extra_data'] = $laporan->extra_data ?: [];

        return response()->json($data);
    }

    public function update(Request $request, int $id)
    {
        $laporan = Laporan::findOrFail($id);

        $validated = $request->validate([
            'status_validasi' => 'nullable|in:belum,sudah',
            'nama_petugas' => 'nullable|string',
            'tanggal' => 'nullable|date',
            'kondisi_cuaca' => 'nullable|string',
            'unit_kerja' => 'nullable|string',
            'area_inspeksi' => 'nullable|string',
            'grid_lokasi' => 'nullable|string',
            'tindak_lanjut' => 'nullable|string',
        ]);

        $laporan->status = $request->input('status_validasi', $laporan->status);
        if ($request->filled('nama_petugas')) {
            $laporan->nama_petugas = $request->nama_petugas;
        }
        if ($request->filled('tanggal')) {
            $laporan->tanggal = $request->tanggal;
        }
        if ($request->filled('kondisi_cuaca')) {
            $laporan->kondisi_cuaca = $request->kondisi_cuaca;
        }
        if ($request->filled('unit_kerja')) {
            $laporan->unit_kerja = $request->unit_kerja;
        }
        if ($request->filled('area_inspeksi')) {
            $laporan->area_inspeksi = $request->area_inspeksi;
        }
        if ($request->filled('grid_lokasi')) {
            $laporan->grid_lokasi = $request->grid_lokasi;
        }
        if ($request->filled('tindak_lanjut')) {
            $laporan->tindak_lanjut = $request->tindak_lanjut;
        }

        $laporan->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data laporan berhasil diperbarui.',
                'status' => $laporan->status,
                'status_label' => $laporan->status === 'sudah' ? 'Sudah Ditangani' : 'Belum Ditangani',
            ]);
        }

        return redirect()->route('admin.dashboard')->with('sukses', 'Data laporan berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $laporan = Laporan::findOrFail($id);
        $laporan->delete();

        return redirect()->route('admin.dashboard')->with('sukses', 'Laporan berhasil dihapus.');
    }
}
