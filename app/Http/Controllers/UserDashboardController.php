<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\DetailSatwa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->query('search', '');
        $statusFilter = $request->query('status', '');

        $totalLaporan = Laporan::count();
        $belum        = Laporan::where('status', 'belum')->orWhereNull('status')->count();
        $ditangani    = Laporan::where('status', 'sudah')->count();

        // Metrik Telemetry & Bento Cards
        $belumRunway = Laporan::where(function($q) {
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
        $hotspotGrid = !empty($topGrids) ? implode(' & ', $topGrids) : 'K-10 & D-9';

        $totalSatwa = DetailSatwa::count();
        $burungCount = DetailSatwa::where(function($q) {
            $q->where('nama_satwa', 'like', '%Burung%')
              ->orWhere('nama_satwa', 'like', '%Blekok%')
              ->orWhere('nama_satwa', 'like', '%Kuntul%')
              ->orWhere('nama_satwa', 'like', '%Cangak%')
              ->orWhere('nama_satwa', 'like', '%Pecuk%');
        })->count();
        $reptilCount = DetailSatwa::where(function($q) {
            $q->where('nama_satwa', 'like', '%Biawak%')
              ->orWhere('nama_satwa', 'like', '%Ular%');
        })->count();
        $pctBurung = $totalSatwa > 0 ? round(($burungCount / $totalSatwa) * 100) : 62;
        $pctReptil = $totalSatwa > 0 ? round(($reptilCount / $totalSatwa) * 100) : 24;
        $pctMamalia = $totalSatwa > 0 ? max(0, 100 - ($pctBurung + $pctReptil)) : 14;

        $query = Laporan::with(['user', 'detailSatwa.fotoLaporan', 'fotoLaporan'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc');

        if (!empty($search)) {
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

        if (!empty($statusFilter)) {
            $query->where('status', $statusFilter);
        }

        $laporans = $query->paginate(10)->withQueryString();

        return view('admin.dashboard', compact(
            'user',
            'totalLaporan',
            'belum',
            'ditangani',
            'laporans',
            'search',
            'statusFilter',
            'hotspotGrid',
            'belumRunway',
            'belumPerimeter',
            'pctBurung',
            'pctReptil',
            'pctMamalia'
        ));
    }
}
