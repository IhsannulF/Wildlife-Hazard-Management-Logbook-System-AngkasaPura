<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use App\Models\DetailSatwa;
use App\Models\FormField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $statusFilter = $request->query('status', '');

        $totalLaporan = Laporan::count();
        $belum        = Laporan::where('status', 'belum')->orWhereNull('status')->count();
        $ditangani    = Laporan::where('status', 'sudah')->count();

        $query = Laporan::with(['user', 'detailSatwa'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_petugas', 'like', "%{$search}%")
                  ->orWhere('unit_kerja', 'like', "%{$search}%")
                  ->orWhere('area_inspeksi', 'like', "%{$search}%")
                  ->orWhere('grid_lokasi', 'like', "%{$search}%");
            });
        }

        if (!empty($statusFilter)) {
            $query->where('status', $statusFilter);
        }

        $laporans = $query->paginate(10)->withQueryString();

        return view('admin.dashboard', compact(
            'totalLaporan',
            'belum',
            'ditangani',
            'laporans',
            'search',
            'statusFilter'
        ));
    }

    public function getDetail(int $id)
    {
        $laporan = Laporan::with(['user', 'detailSatwa.fotoLaporan', 'fotoLaporan'])->find($id);

        if (!$laporan) {
            return response()->json(['error' => 'Laporan tidak ditemukan'], 404);
        }

        $satwaData = [];
        foreach ($laporan->detailSatwa as $ds) {
            $foto = $ds->fotoLaporan->first();
            $fotoUrl = $foto ? asset('storage/uploads/satwa/' . $foto->nama_file) : asset('images/' . ($ds->foto_path ?: ''));
            $satwaData[] = [
                'detail_satwa_id' => $ds->id,
                'nama'            => $ds->nama_satwa,
                'jumlah'          => $ds->jumlah,
                'grid'            => $ds->grid,
                'foto_path'       => $fotoUrl,
            ];
        }

        $fotoExtra = $laporan->fotoLaporan
            ->where('tipe', 'extra')
            ->map(fn($f) => asset('storage/uploads/extra/' . $f->nama_file))
            ->values();

        $standardFields = [
            'nama_petugas','tanggal','kondisi_cuaca','unit_kerja','area_inspeksi',
            'tanda_tangan','grid','grid_lokasi','satwa','foto_laporan','ciri_ukuran',
            'kondisi_apron','aktivitas_satwa','tindak_lanjut','detail_pengusiran'
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
            'nama_petugas'    => 'nullable|string',
            'tanggal'         => 'nullable|date',
            'kondisi_cuaca'   => 'nullable|string',
            'unit_kerja'      => 'nullable|string',
            'area_inspeksi'   => 'nullable|string',
            'grid_lokasi'     => 'nullable|string',
            'tindak_lanjut'   => 'nullable|string',
        ]);

        $laporan->status = $request->input('status_validasi', $laporan->status);
        if ($request->filled('nama_petugas')) $laporan->nama_petugas = $request->nama_petugas;
        if ($request->filled('tanggal')) $laporan->tanggal = $request->tanggal;
        if ($request->filled('kondisi_cuaca')) $laporan->kondisi_cuaca = $request->kondisi_cuaca;
        if ($request->filled('unit_kerja')) $laporan->unit_kerja = $request->unit_kerja;
        if ($request->filled('area_inspeksi')) $laporan->area_inspeksi = $request->area_inspeksi;
        if ($request->filled('grid_lokasi')) $laporan->grid_lokasi = $request->grid_lokasi;
        if ($request->filled('tindak_lanjut')) $laporan->tindak_lanjut = $request->tindak_lanjut;

        $laporan->save();

        return redirect()->route('admin.dashboard')->with('sukses', 'Data laporan berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $laporan = Laporan::findOrFail($id);
        $laporan->delete();

        return redirect()->route('admin.dashboard')->with('sukses', 'Laporan berhasil dihapus.');
    }
}
