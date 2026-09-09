<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DetailSatwa;
use App\Models\Laporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatistikController extends Controller
{
    public function index(Request $request)
    {
        $tahun  = (int)$request->query('tahun', date('Y'));
        $zona   = $request->query('zona', '');
        $search = trim($request->query('search', ''));

        $barisGrid = ['A','B','C','D','E','F','G','H','I','J','K','L'];
        $kolomGrid = range(1, 29);

        // Grid yang ada datanya
        $gridAda = DetailSatwa::whereHas('laporan', function ($q) use ($tahun) {
                $q->whereYear('tanggal', $tahun);
            })
            ->whereNotNull('grid')
            ->where('grid', '!=', '')
            ->distinct()
            ->orderBy('grid')
            ->pluck('grid')
            ->toArray();

        // Daftar nama satwa
        $satwaQuery = DetailSatwa::whereHas('laporan', function ($q) use ($tahun) {
                $q->whereYear('tanggal', $tahun);
            })
            ->distinct();

        if (!empty($search)) {
            $satwaQuery->where('nama_satwa', 'like', "%{$search}%");
        }

        $jenisList = $satwaQuery->orderBy('nama_satwa')->pluck('nama_satwa')->toArray();

        $paletteHex = ['#e8602c','#5bb8d4','#78c15a','#3a8a3f','#f0a070','#2255aa','#e040fb','#00bcd4'];

        // Stacked Bar Chart Query
        $barQuery = DetailSatwa::select(
                'detail_satwa.nama_satwa as nama',
                DB::raw('MONTH(laporan.tanggal) as bulan'),
                DB::raw('SUM(detail_satwa.jumlah) as total')
            )
            ->join('laporan', 'laporan.id', '=', 'detail_satwa.laporan_id')
            ->whereYear('laporan.tanggal', $tahun);

        if (!empty($zona)) {
            $barQuery->where('detail_satwa.grid', $zona);
        }
        if (!empty($search)) {
            $barQuery->where('detail_satwa.nama_satwa', 'like', "%{$search}%");
        }

        $rawBar = $barQuery->groupBy('detail_satwa.nama_satwa', DB::raw('MONTH(laporan.tanggal)'))
            ->orderBy('detail_satwa.nama_satwa')
            ->get();

        $mapBar = [];
        foreach ($rawBar as $r) {
            $mapBar[$r->nama][$r->bulan] = (int)$r->total;
        }

        // Top 5 Satwa tahun ini
        $topSatwa = DetailSatwa::select(
                'detail_satwa.nama_satwa as nama',
                DB::raw('SUM(detail_satwa.jumlah) as total')
            )
            ->join('laporan', 'laporan.id', '=', 'detail_satwa.laporan_id')
            ->whereYear('laporan.tanggal', $tahun)
            ->groupBy('detail_satwa.nama_satwa')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Total keseluruhan
        $grandTotal = DetailSatwa::whereHas('laporan', function ($q) use ($tahun) {
                $q->whereYear('tanggal', $tahun);
            })->sum('jumlah');

        $namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

        return view('admin.statistik', compact(
            'tahun',
            'zona',
            'search',
            'barisGrid',
            'kolomGrid',
            'gridAda',
            'jenisList',
            'paletteHex',
            'mapBar',
            'topSatwa',
            'grandTotal',
            'namaBulan'
        ));
    }
}
