<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CuacaSnapshot;
use App\Models\DetailSatwa;
use App\Models\FormField;
use App\Models\FotoLaporan;
use App\Models\JenisSatwa;
use App\Models\Laporan;
use App\Models\User;
use App\Services\DataQuality\DataQualityService;
use App\Services\Statistics\BirdStatisticsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request, BirdStatisticsService $birdStatistics, DataQualityService $dataQuality)
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
        // Kartu "Mitigasi Telah Ditangani": dihitung dari data, bukan angka tetap.
        $rerataResponMenit = $this->averageDispersalResponseMinutes();
        $persenBaTervalidasi = $ditangani > 0
            ? round(Laporan::where('status', 'sudah')->whereNotNull('validated_at')->count() / $ditangani * 100, 1)
            : null;

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
        $hotspotGrid = ! empty($topGrids) ? implode(' & ', $topGrids) : '-';

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

        $statistikTahunTersedia = $birdStatistics->availableYears();
        $statistikTahun = (int) $request->query('tahun', (string) ($statistikTahunTersedia[0] ?? now()->year));
        if (! in_array($statistikTahun, $statistikTahunTersedia, true)) {
            $statistikTahun = $statistikTahunTersedia[0] ?? now()->year;
        }
        $statistik = $birdStatistics->build($statistikTahun);
        $peringatanKualitasData = $dataQuality->warnings();

        $query = Laporan::with(['user', 'detailSatwa.fotoLaporan', 'fotoLaporan', 'tanggapan'])
            ->orderByRaw("CASE WHEN status = 'belum' OR status IS NULL THEN 0 ELSE 1 END")
            ->orderByRaw("CASE risk_level WHEN 'kritis' THEN 1 WHEN 'tinggi' THEN 2 WHEN 'sedang' THEN 3 ELSE 4 END")
            ->orderBy('risk_score', 'desc')
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc');

        if (! empty($search) && is_scalar($search)) {
            $this->applySearchFilter($query, (string) $search);
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

        $availableGrids = Laporan::whereNotNull('grid_lokasi')
            ->where('grid_lokasi', '!=', '')
            ->distinct()
            ->pluck('grid_lokasi')
            ->merge(
                DetailSatwa::whereNotNull('grid')
                    ->where('grid', '!=', '')
                    ->distinct()
                    ->pluck('grid')
            )
            ->filter()
            ->unique()
            ->sort(fn ($a, $b) => strcmp($a, $b))
            ->values()
            ->map(fn ($code) => [
                'grid' => strtoupper($code),
                'zona' => 'Koordinat Airside Juanda',
                'deskripsi' => 'Area Grid Sisi Udara '.strtoupper($code),
                'tipe' => 'Airside Grid',
                'hotspot' => in_array(strtoupper($code), array_map('strtoupper', $topGrids)),
            ])
            ->toArray();

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
            'statistik',
            'statistikTahun',
            'statistikTahunTersedia',
            'rerataResponMenit',
            'persenBaTervalidasi',
            'peringatanKualitasData',
            'availableGrids'
        ));
    }

    /**
     * Terapkan filter pencarian laporan berdasarkan nomor BA, petugas, unit, area, grid, atau satwa.
     */
    private function applySearchFilter(Builder $query, string $search): void
    {
        $query->where(function (Builder $q) use ($search): void {
            $q->where('no_laporan', 'like', "%{$search}%")
                ->orWhere('nama_petugas', 'like', "%{$search}%")
                ->orWhere('unit_kerja', 'like', "%{$search}%")
                ->orWhere('area_inspeksi', 'like', "%{$search}%")
                ->orWhere('grid_lokasi', 'like', "%{$search}%")
                ->orWhereHas('detailSatwa', function (Builder $sq) use ($search): void {
                    $sq->where('nama_satwa', 'like', "%{$search}%")
                        ->orWhere('grid', 'like', "%{$search}%");
                });
        });
    }

    public function verifikasiIndex(Request $request)
    {
        $search = $request->query('search', '');
        $statusFilter = $request->query('status', '');
        $startDate = $request->query('start_date', $request->query('tanggal_awal', $request->query('tanggal', '')));
        $endDate = $request->query('end_date', $request->query('tanggal_akhir', ''));
        $perPage = (int) $request->query('per_page', session('verifikasi_per_page', 10));
        if (! in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 10;
        }
        if ($request->has('per_page')) {
            session(['verifikasi_per_page' => $perPage]);
        }

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
        $hotspotGrid = ! empty($topGrids) ? implode(' & ', $topGrids) : '-';
        $hotspotCount = (int) DetailSatwa::whereIn('grid', $topGrids)->sum('jumlah');

        $topArea = Laporan::selectRaw('area_inspeksi, count(*) as count')
            ->whereNotNull('area_inspeksi')
            ->where('area_inspeksi', '!=', '')
            ->groupBy('area_inspeksi')
            ->orderByDesc('count')
            ->value('area_inspeksi') ?? '-';

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

        $query = Laporan::with(['user', 'detailSatwa.fotoLaporan', 'fotoLaporan', 'tanggapan'])
            ->orderByRaw("CASE WHEN status = 'belum' OR status IS NULL THEN 0 ELSE 1 END")
            ->orderByRaw("CASE risk_level WHEN 'kritis' THEN 1 WHEN 'tinggi' THEN 2 WHEN 'sedang' THEN 3 ELSE 4 END")
            ->orderBy('risk_score', 'desc')
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc');

        if (! empty($search) && is_scalar($search)) {
            $this->applySearchFilter($query, (string) $search);
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

        $laporans = $query->paginate($perPage)->withQueryString();

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
        $detailTemuanList = $detailTemuanQuery->paginate($perPage, ['*'], 'detail_page')->withQueryString();

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
        $backupTemuanList = $backupTemuanQuery->paginate($perPage, ['*'], 'backup_page')->withQueryString();

        // Sub-data: Master Titik & Zona Juanda (RWY 10/28). Grid diperkirakan dari gridmap
        // (runway kolom 5–25, baris G–H; utara = baris A, selatan = baris L); diganti master P1–P9 di F3.
        $masterTitikList = [
            ['kode' => 'Titik 1', 'zona' => 'Threshold RWY 10 (Barat)', 'grid' => 'G-5', 'deskripsi' => 'Bahu landasan & runway strip dekat threshold RWY 10', 'tipe' => 'Airside Critical', 'status' => 'Aktif'],
            ['kode' => 'Titik 2', 'zona' => 'Taxiway Paralel Utara', 'grid' => 'F-12', 'deskripsi' => 'Persimpangan taxiway dekat Apron C & kanal drainase terbuka', 'tipe' => 'Movement Area', 'status' => 'Aktif'],
            ['kode' => 'Titik 3', 'zona' => 'Threshold RWY 28 (Timur)', 'grid' => 'G-25', 'deskripsi' => 'Bahu landasan timur & area rumput terbuka dekat threshold RWY 28', 'tipe' => 'Airside Critical', 'status' => 'Aktif'],
            ['kode' => 'Titik 4', 'zona' => 'Perimeter Selatan', 'grid' => 'K-12', 'deskripsi' => 'Pagar perimeter batas selatan berdekatan kanal & tambak', 'tipe' => 'Perimeter Strip', 'status' => 'Aktif'],
            ['kode' => 'Titik 5', 'zona' => 'Apron A / Terminal 2', 'grid' => 'J-11', 'deskripsi' => 'Pelataran parkir pesawat Terminal 2 & area GSE', 'tipe' => 'Apron Area', 'status' => 'Aktif'],
            ['kode' => 'Titik 6', 'zona' => 'Runway Strip Tengah', 'grid' => 'H-15', 'deskripsi' => 'Area rumput antara runway 10/28 dan taxiway paralel', 'tipe' => 'Airside Critical', 'status' => 'Aktif'],
            ['kode' => 'Titik 7', 'zona' => 'Regulating Pond 1', 'grid' => 'F-10', 'deskripsi' => 'Kolam retensi air hujan sisi utara taxiway (habitat burung air)', 'tipe' => 'Retention Basin', 'status' => 'Aktif'],
            ['kode' => 'Titik 8', 'zona' => 'Perimeter Timur', 'grid' => 'G-28', 'deskripsi' => 'Perimeter batas timur di luar ujung RWY 28', 'tipe' => 'Perimeter Strip', 'status' => 'Aktif'],
            ['kode' => 'Zona 1-1', 'zona' => 'Sektor Approach RWY 10', 'grid' => 'G-3', 'deskripsi' => 'Sektor pengamatan intensif kerucut approach/departure barat', 'tipe' => 'Special Sector', 'status' => 'Aktif'],
            ['kode' => 'Zona 1-2', 'zona' => 'Sektor Runway Strip Tengah', 'grid' => 'G-15', 'deskripsi' => 'Sektor pengamatan tengah runway strip', 'tipe' => 'Special Sector', 'status' => 'Aktif'],
            ['kode' => 'Zona 1-3', 'zona' => 'Sektor Approach RWY 28', 'grid' => 'G-27', 'deskripsi' => 'Sektor pengamatan kerucut approach/departure timur', 'tipe' => 'Special Sector', 'status' => 'Aktif'],
            ['kode' => 'Zona 1-4', 'zona' => 'Sektor Taxiway & Apron B', 'grid' => 'E-18', 'deskripsi' => 'Sektor jalur pergerakan taxiway & Apron B Terminal 1', 'tipe' => 'Special Sector', 'status' => 'Aktif'],
            ['kode' => 'Zona 2', 'zona' => 'Sektor Perimeter Utara', 'grid' => 'A-15', 'deskripsi' => 'Sektor batas pagar luar sisi utara bandara', 'tipe' => 'Buffer Zone', 'status' => 'Aktif'],
            ['kode' => 'Zona 2-1', 'zona' => 'Sektor Kanal Utara', 'grid' => 'B-9', 'deskripsi' => 'Sektor kanal terbuka saluran drainase sisi utara', 'tipe' => 'Buffer Zone', 'status' => 'Aktif'],
            ['kode' => 'Zona 2-5', 'zona' => 'Sektor Tambak Selatan', 'grid' => 'L-20', 'deskripsi' => 'Sektor perbatasan tambak warga sisi selatan perimeter', 'tipe' => 'Buffer Zone', 'status' => 'Aktif'],
        ];

        // Sub-data: Master Jenis
        $masterJenisQuery = JenisSatwa::query();
        if ($masterJenisQuery->count() > 0) {
            $masterJenisList = $masterJenisQuery->paginate($perPage, ['*'], 'jenis_page')->withQueryString();
        } else {
            $fallbackJenis = collect([
                (object) ['id' => 1, 'nama' => 'Burung Kuntul (Little / Cattle Egret)', 'kategori' => 'Avian / Burung', 'tingkat_risiko' => 'Tinggi', 'grid_hotspot' => 'K-10', 'deskripsi' => 'Kawanan burung pemakan serangga di bahu runway strip', 'foto_path' => 'kuntul_kecil.jpeg'],
                (object) ['id' => 2, 'nama' => 'Burung Cangak Abu (Grey Heron)', 'kategori' => 'Avian / Burung', 'tingkat_risiko' => 'Kritis', 'grid_hotspot' => 'D-9', 'deskripsi' => 'Burung air berukuran besar sering melintas di kanal drainase', 'foto_path' => 'cangak_abu.jpeg'],
                (object) ['id' => 3, 'nama' => 'Burung Cangak Merah (Purple Heron)', 'kategori' => 'Avian / Burung', 'tingkat_risiko' => 'Kritis', 'grid_hotspot' => 'K-11', 'deskripsi' => 'Burung air pemangsa ikan dengan bentang sayap lebar', 'foto_path' => 'cangak_merah.jpeg'],
                (object) ['id' => 4, 'nama' => 'Burung Pecuk Padi (Little Cormorant)', 'kategori' => 'Avian / Burung', 'tingkat_risiko' => 'Tinggi', 'grid_hotspot' => 'E-7', 'deskripsi' => 'Burung penyelam sering bertengger di kolam retensi airside', 'foto_path' => 'pecuk_padi_kecil.jpeg'],
                (object) ['id' => 5, 'nama' => 'Burung Blekok Sawah (Javan Pond Heron)', 'kategori' => 'Avian / Burung', 'tingkat_risiko' => 'Sedang', 'grid_hotspot' => 'F-5', 'deskripsi' => 'Burung pemakan katak dan serangga tanah di rumput runway', 'foto_path' => 'blekok_sawah.jpeg'],
                (object) ['id' => 6, 'nama' => 'Biawak Air Asia (Asian Water Monitor)', 'kategori' => 'Reptil', 'tingkat_risiko' => 'Sedang', 'grid_hotspot' => 'D-9', 'deskripsi' => 'Reptil besar melintas dari kanal drainase ke taxiway', 'foto_path' => 'biawak.jpeg'],
                (object) ['id' => 7, 'nama' => 'Cabak Kota (Savanna Nightjar)', 'kategori' => 'Avian / Burung', 'tingkat_risiko' => 'Kritis', 'grid_hotspot' => 'K-10', 'deskripsi' => 'Burung nokturnal sering berdiam di aspal runway saat malam/senja', 'foto_path' => null],
                (object) ['id' => 8, 'nama' => 'Kerak Kerbau (Javan Myna)', 'kategori' => 'Avian / Burung', 'tingkat_risiko' => 'Sedang', 'grid_hotspot' => 'H-3', 'deskripsi' => 'Burung pemakan serangga berkumpul di area apron & rumput', 'foto_path' => null],
            ]);
            $jenisPage = (int) $request->query('jenis_page', 1);
            $masterJenisList = new LengthAwarePaginator(
                $fallbackJenis->forPage($jenisPage, $perPage)->values(),
                $fallbackJenis->count(),
                $perPage,
                $jenisPage,
                ['path' => $request->url(), 'pageName' => 'jenis_page', 'query' => $request->query()]
            );
        }

        // Sub-data: Master Preview (Foto Dokumentasi Lapangan)
        $previewList = DetailSatwa::has('laporan')->with(['laporan.user', 'fotoLaporan'])
            ->orderBy('id', 'desc')
            ->paginate($perPage, ['*'], 'preview_page')
            ->withQueryString();

        // Daftar Grid Koordinat Airside untuk Filter Grid Cepat & Pencarian Grid
        $dbGrids = Laporan::whereNotNull('grid_lokasi')
            ->where('grid_lokasi', '!=', '')
            ->distinct()
            ->pluck('grid_lokasi')
            ->merge(
                DetailSatwa::whereNotNull('grid')
                    ->where('grid', '!=', '')
                    ->distinct()
                    ->pluck('grid')
            )
            ->filter()
            ->unique();

        $titikGridLookup = [];
        foreach ($masterTitikList as $m) {
            $titikGridLookup[strtoupper($m['grid'])] = [
                'grid' => strtoupper($m['grid']),
                'zona' => $m['zona'] ?? 'Airside',
                'deskripsi' => $m['deskripsi'] ?? '',
                'tipe' => $m['tipe'] ?? 'Airside Critical',
                'hotspot' => in_array(strtoupper($m['grid']), array_map('strtoupper', $topGrids)),
            ];
        }

        $titikPage = (int) $request->query('titik_page', 1);
        $masterTitikCollection = collect($masterTitikList);
        $masterTitikList = new LengthAwarePaginator(
            $masterTitikCollection->forPage($titikPage, $perPage)->values(),
            $masterTitikCollection->count(),
            $perPage,
            $titikPage,
            ['path' => $request->url(), 'pageName' => 'titik_page', 'query' => $request->query()]
        );

        $allGridCodes = collect(array_keys($titikGridLookup))
            ->merge($dbGrids->map(fn ($g) => strtoupper($g)))
            ->unique()
            ->sort(fn ($a, $b) => strcmp($a, $b))
            ->values();

        $availableGrids = $allGridCodes->map(function ($code) use ($titikGridLookup, $topGrids) {
            if (isset($titikGridLookup[$code])) {
                return $titikGridLookup[$code];
            }

            return [
                'grid' => $code,
                'zona' => 'Koordinat Airside Juanda',
                'deskripsi' => 'Area Grid Sisi Udara '.$code,
                'tipe' => 'Airside Grid',
                'hotspot' => in_array($code, array_map('strtoupper', $topGrids)),
            ];
        })->toArray();

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
            'previewList',
            'availableGrids',
            'perPage'
        ));
    }

    public function getDetail(int $id)
    {
        $laporan = Laporan::with([
            'user',
            'validator:id,namalengkap,username,jabatan,role,tanda_tangan',
            'detailSatwa.fotoLaporan',
            'fotoLaporan',
            'tanggapan' => function ($q) {
                $q->with('user')->orderBy('id', 'asc');
            },
        ])->find($id);

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
                'perilaku_kawanan' => $ds->flockBehaviorSummary(),
                'foto_path' => $fotoUrl,
                'foto_lokasi' => $this->photoLocation($foto, $laporan),
            ];
        }

        $fotoExtra = $laporan->fotoLaporan
            ->where('tipe', 'extra')
            ->map(fn ($f) => asset('storage/uploads/extra/'.$f->nama_file))
            ->values();

        $tanggapanList = [];
        foreach ($laporan->tanggapan as $t) {
            $author = $t->user ? ($t->user->namalengkap ?: $t->user->username) : 'Safety Officer';
            $authorRole = $t->user ? ($t->user->jabatan ?: ($t->user->role === 'admin' ? 'Safety Manager' : 'Petugas Lapangan')) : 'Validator';
            $tanggapanList[] = [
                'id' => $t->id,
                'laporan_id' => $t->laporan_id,
                'user_id' => $t->user_id,
                'kategori_tanggapan' => $t->kategori_tanggapan ?: 'instruksi',
                'isi' => $t->isi,
                'author_name' => $author,
                'author_role' => $authorRole,
                'is_admin' => $t->user ? $t->user->role === 'admin' : false,
                'created_at' => $t->created_at?->format('Y-m-d H:i:s'),
                'created_at_human' => $t->created_at ? $t->created_at->translatedFormat('d M Y, H:i').' WIB' : '-',
            ];
        }

        $standardFields = [
            'nama_petugas', 'tanggal', 'kondisi_cuaca', 'unit_kerja', 'area_inspeksi',
            'tanda_tangan', 'grid', 'grid_lokasi', 'satwa', 'foto_laporan', 'ciri_ukuran',
            'kondisi_apron', 'aktivitas_satwa', 'tindak_lanjut', 'detail_pengusiran',
        ];
        $extraFieldsDef = FormField::where('aktif', true)->whereNotIn('nama_field', $standardFields)->get();

        $data = $laporan->toArray();
        $data['nama_user'] = $laporan->user ? ($laporan->user->namalengkap ?: $laporan->user->username) : '-';
        $data['validator_name'] = $laporan->validator ? ($laporan->validator->namalengkap ?: $laporan->validator->username) : null;
        $data['validator_jabatan'] = $laporan->validator ? $laporan->validator->validator_display_title : null;
        $data['validated_at_human'] = $laporan->validated_at
            ? $laporan->validated_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i').' WIB'
            : null;
        $data['waktu_pengamatan'] = $this->observationTimeText($laporan);
        $data['cuaca_ringkas'] = $this->weatherSummary($laporan);
        $data['satwa'] = $satwaData;
        $data['foto_extra'] = $fotoExtra;
        $data['tanggapan'] = $tanggapanList;
        $data['extra_fields_def'] = $extraFieldsDef;
        $data['extra_data'] = $laporan->extra_data ?: [];

        return response()->json($data);
    }

    /**
     * Titik GPS perangkat saat foto bukti diambil/diunggah; laporan tanpa metadata foto memakai GPS laporan.
     *
     * @return array{lat: string, lng: string, keterangan: string, peta_url: string}|null
     */
    private function photoLocation(?FotoLaporan $foto, Laporan $laporan): ?array
    {
        $lat = $foto?->gps_lat ?? $laporan->gps_lat;
        $lng = $foto?->gps_lng ?? $laporan->gps_lng;
        if ($lat === null || $lng === null) {
            return null;
        }

        $parts = [sprintf('%.6f, %.6f', $lat, $lng)];
        if ($foto?->gps_lat !== null) {
            if ($foto->gps_akurasi_m !== null) {
                $parts[0] .= ' (±'.$foto->gps_akurasi_m.' m)';
            }
            $parts[] = $foto->sumber === 'galeri' ? 'lokasi saat unggah galeri' : 'lokasi saat foto diambil';
            if ($foto->diambil_at) {
                $parts[] = $foto->diambil_at->format('d/m/Y H:i').' WIB';
            }
        } else {
            $parts[] = 'lokasi laporan';
        }

        return [
            'lat' => sprintf('%.6f', $lat),
            'lng' => sprintf('%.6f', $lng),
            'keterangan' => implode(' · ', $parts),
            'peta_url' => sprintf('https://www.google.com/maps?q=%.7f,%.7f', $lat, $lng),
        ];
    }

    /**
     * Tanggal & jam pengamatan (WIB); laporan lama tanpa jam memakai waktu input yang dikonversi ke WIB.
     */
    private function observationTimeText(Laporan $laporan): string
    {
        $date = $laporan->tanggal?->locale('id')->translatedFormat('d M Y') ?? '-';

        if ($laporan->jam) {
            return $date.', '.substr((string) $laporan->jam, 0, 5).' WIB';
        }

        return $laporan->created_at
            ? $date.', '.$laporan->created_at->copy()->timezone(config('whms_risk.zona_waktu'))->format('H:i').' WIB (waktu input)'
            : $date;
    }

    /**
     * Kondisi cuaca isian petugas + snapshot server terdekat (±60 menit) bila ada; tanpa angka karangan.
     */
    private function weatherSummary(Laporan $laporan): string
    {
        $condition = $laporan->kondisi_cuaca ?: 'Kondisi cuaca tidak diisi';

        if (! $laporan->tanggal || ! $laporan->jam) {
            return $condition;
        }

        $observedAt = $laporan->tanggal->format('Y-m-d').' '.substr((string) $laporan->jam, 0, 5).':00';
        $snapshot = CuacaSnapshot::query()
            ->whereBetween('waktu', [
                date('Y-m-d H:i:s', strtotime($observedAt.' -60 minutes')),
                date('Y-m-d H:i:s', strtotime($observedAt.' +60 minutes')),
            ])
            ->orderByRaw('ABS(TIMESTAMPDIFF(SECOND, waktu, ?))', [$observedAt])
            ->first();

        if (! $snapshot) {
            return $condition;
        }

        $directions = ['U', 'TL', 'T', 'TG', 'S', 'BD', 'B', 'BL'];
        $direction = $snapshot->arah_angin_derajat !== null ? ' dari '.$directions[(int) round($snapshot->arah_angin_derajat / 45) % 8] : '';

        return sprintf(
            '%s / %s°C / Angin %s kt%s (silang %s kt)',
            $condition,
            number_format((float) $snapshot->suhu_c, 1, ',', '.'),
            number_format((float) $snapshot->kecepatan_angin_kt, 1, ',', '.'),
            $direction,
            number_format((float) $snapshot->angin_silang_kt, 1, ',', '.'),
        );
    }

    /**
     * Rerata menit dari jam pengamatan sampai tindakan pengusiran pertama yang tercatat untuk laporan itu
     * (log pengusiran F0). Null bila belum ada pasangan laporan berjam + tindakan pengusiran.
     */
    private function averageDispersalResponseMinutes(): ?float
    {
        $average = DB::table('tindakan_pengusiran as t')
            ->join('laporan as l', 'l.id', '=', 't.laporan_id')
            ->whereNotNull('l.jam')
            ->groupBy('l.id', 'l.tanggal', 'l.jam')
            ->selectRaw('TIMESTAMPDIFF(MINUTE, TIMESTAMP(l.tanggal, l.jam), MIN(t.waktu)) as menit')
            ->get()
            ->pluck('menit')
            ->filter(fn ($minutes): bool => $minutes !== null && $minutes >= 0)
            ->avg();

        return $average === null ? null : round((float) $average, 1);
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
            'validator_signature' => User::signatureDataUriRules(),
        ]);

        $nextStatus = $request->input('status_validasi', $laporan->status);
        $validator = Auth::user();

        if ($nextStatus === 'sudah') {
            if ($validator instanceof User && ($laporan->status !== 'sudah' || $laporan->validated_by === null || $request->filled('validator_signature'))) {
                $laporan->markAsValidated($validator, $validated['validator_signature'] ?? null);
            } else {
                $laporan->status = 'sudah';
            }
        } elseif ($nextStatus === 'belum') {
            $laporan->resetValidation();
        }

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
                'validated_by' => $laporan->validated_by,
                'validated_at' => $laporan->validated_at?->toIso8601String(),
                'validated_at_human' => $laporan->validated_at
                    ? $laporan->validated_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i').' WIB'
                    : null,
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
