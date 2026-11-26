<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTindakanPengusiranRequest;
use App\Models\TindakanPengusiran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class TindakanPengusiranController extends Controller
{
    /**
     * Catat tindakan pengusiran (scare action) di luar laporan, mis. dari tombol aksi cepat.
     */
    public function store(StoreTindakanPengusiranRequest $request): RedirectResponse|JsonResponse
    {
        $tindakan = TindakanPengusiran::create([
            ...$request->safe()->only(['alat_id', 'waktu', 'grid', 'hasil', 'keterangan']),
            'jumlah_digunakan' => $request->integer('jumlah_digunakan', 1) ?: 1,
            'user_id' => $request->user()->id,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'id' => $tindakan->id], 201);
        }

        return back()->with('sukses', 'Tindakan pengusiran di grid '.$tindakan->grid.' berhasil dicatat.');
    }
}
