<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\Tanggapan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TanggapanController extends Controller
{
    /**
     * Simpan tanggapan resmi dari Admin (Safety Manager) atau balasan dari Pegawai Lapangan
     */
    public function store(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $laporan = Laporan::findOrFail($id);

        // Otorisasi: Pegawai hanya dapat membalas pada laporannya sendiri
        if ($user->role !== 'admin' && (int) $laporan->user_id !== (int) $user->id) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki hak akses untuk menanggapi laporan ini.',
                ], 403);
            }

            return back()->with('error', 'Anda tidak memiliki hak akses untuk menanggapi laporan ini.');
        }

        $validated = $request->validate([
            'isi' => 'required|string|max:2000',
            'kategori_tanggapan' => 'nullable|in:instruksi,evaluasi,klarifikasi,penutupan',
        ]);

        // Default kategori berdasarkan role pengguna
        $kategori = 'klarifikasi';
        if ($user->role === 'admin') {
            $kategori = $request->input('kategori_tanggapan', 'instruksi') ?: 'instruksi';
        }

        $tanggapan = Tanggapan::create([
            'laporan_id' => $laporan->id,
            'user_id' => $user->id,
            'kategori_tanggapan' => $kategori,
            'isi' => trim($validated['isi']),
        ]);

        // Jika Admin memilih kategori penutupan, otomatis tandai status laporan selesai
        if ($user->role === 'admin' && $kategori === 'penutupan') {
            $laporan->status = 'sudah';
            $laporan->save();
        }

        $tanggapan->load('user');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $user->role === 'admin' ? 'Instruksi dinas berhasil dikirimkan.' : 'Tanggapan dinas berhasil dikirimkan.',
                'tanggapan' => [
                    'id' => $tanggapan->id,
                    'laporan_id' => $tanggapan->laporan_id,
                    'user_id' => $tanggapan->user_id,
                    'kategori_tanggapan' => $tanggapan->kategori_tanggapan,
                    'isi' => $tanggapan->isi,
                    'created_at' => $tanggapan->created_at?->format('Y-m-d H:i:s'),
                    'created_at_human' => $tanggapan->created_at?->translatedFormat('d M Y, H:i').' WIB',
                    'author_name' => $user->namalengkap ?: $user->username,
                    'author_role' => $user->role === 'admin' ? ($user->jabatan ?: 'Safety Manager') : ($user->jabatan ?: 'Petugas Lapangan'),
                    'is_admin' => $user->role === 'admin',
                ],
                'laporan_status' => $laporan->status,
                'laporan_status_label' => $laporan->status === 'sudah' ? 'Sudah Ditangani' : 'Belum Ditangani',
            ]);
        }

        return back()->with('sukses', 'Tanggapan dinas berhasil disimpan.');
    }
}
