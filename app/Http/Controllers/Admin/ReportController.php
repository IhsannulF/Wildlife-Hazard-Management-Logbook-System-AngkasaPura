<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DetailSatwa;
use App\Models\Laporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function cetak(int $id)
    {
        $lap = Laporan::with(['user', 'detailSatwa', 'fotoLaporan'])->findOrFail($id);

        $satwa_list = [];
        foreach ($lap->detailSatwa as $ds) {
            $foto = $ds->fotoLaporan->first();
            $satwa_list[] = [
                'nama'      => $ds->nama_satwa,
                'jumlah'    => $ds->jumlah,
                'grid'      => $ds->grid,
                'foto_path' => $foto ? 'storage/uploads/satwa/' . $foto->nama_file : ($ds->foto_path ? 'images/' . $ds->foto_path : ''),
            ];
        }

        $foto_extra = $lap->fotoLaporan
            ->where('tipe', 'extra')
            ->pluck('nama_file')
            ->toArray();

        return view('reports.cetak', compact('lap', 'satwa_list', 'foto_extra'));
    }

    public function exportExcel(Request $request)
    {
        $tahun = (int)$request->query('tahun', date('Y'));
        $zona  = $request->query('zona', '');

        $jenisList = DetailSatwa::whereHas('laporan', function ($q) use ($tahun) {
                $q->whereYear('tanggal', $tahun);
            })
            ->distinct()
            ->orderBy('nama_satwa')
            ->pluck('nama_satwa')
            ->toArray();

        $query = Laporan::with(['user', 'detailSatwa'])
            ->whereYear('tanggal', $tahun);

        if (!empty($zona)) {
            $query->where('grid_lokasi', $zona);
        }

        $laporans = $query->orderBy('tanggal', 'asc')->get();

        $headers = [
            "Content-Type"        => "application/vnd.ms-excel",
            "Content-Disposition" => "attachment; filename=\"Laporan_Satwa_Liar_{$tahun}.xls\"",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        return response()->view('reports.export-excel', [
            'tahun'     => $tahun,
            'zona'      => $zona,
            'jenisList' => $jenisList,
            'laporans'  => $laporans,
        ], 200, $headers);
    }
}
