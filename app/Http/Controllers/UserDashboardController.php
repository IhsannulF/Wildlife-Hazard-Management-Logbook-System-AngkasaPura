<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = trim($request->query('search', ''));
        $statusFilter = $request->query('status', '');
        $startDate = $request->query('start_date', $request->query('tanggal_awal', ''));
        $endDate = $request->query('end_date', $request->query('tanggal_akhir', ''));
        $perPage = (int) $request->query('per_page', 10);
        if (! in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 10;
        }

        // Metrik Pribadi Petugas Lapangan
        $baseQuery = Laporan::where('user_id', $user->id);
        $totalLaporanSaya = (clone $baseQuery)->count();
        $menungguValidasi = (clone $baseQuery)->where(function ($q) {
            $q->where('status', 'belum')->orWhereNull('status');
        })->count();
        $sudahDitangani = (clone $baseQuery)->where('status', 'sudah')->count();

        // Query Riwayat Laporan Pribadi
        $query = Laporan::where('user_id', $user->id)
            ->with(['detailSatwa.fotoLaporan', 'fotoLaporan', 'tanggapan.user'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc');

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('no_laporan', 'like', "%{$search}%")
                    ->orWhere('area_inspeksi', 'like', "%{$search}%")
                    ->orWhere('grid_lokasi', 'like', "%{$search}%")
                    ->orWhereHas('detailSatwa', function ($sq) use ($search) {
                        $sq->where('nama_satwa', 'like', "%{$search}%")
                            ->orWhere('grid', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($statusFilter)) {
            if ($statusFilter === 'belum') {
                $query->where(function ($q) {
                    $q->where('status', 'belum')->orWhereNull('status');
                });
            } elseif ($statusFilter === 'sudah') {
                $query->where('status', 'sudah');
            }
        }

        if (! empty($startDate)) {
            $query->whereDate('tanggal', '>=', $startDate);
        }
        if (! empty($endDate)) {
            $query->whereDate('tanggal', '<=', $endDate);
        }

        $laporans = $query->paginate($perPage)->withQueryString();

        return view('user.dashboard', compact(
            'user',
            'totalLaporanSaya',
            'menungguValidasi',
            'sudahDitangani',
            'laporans',
            'search',
            'statusFilter',
            'startDate',
            'endDate',
            'perPage'
        ));
    }
}
