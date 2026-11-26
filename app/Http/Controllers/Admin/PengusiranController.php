<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAlatDispersalRequest;
use App\Models\MasterAlatDispersal;
use App\Models\TindakanPengusiran;
use App\Support\GridCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Halaman admin: log seluruh tindakan pengusiran dan pengelolaan master alat dispersal (PRD v2.1 FR-0.3).
 */
class PengusiranController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
            'alat_id' => ['nullable', 'integer'],
            'grid' => ['nullable', 'string', 'max:20'],
            'hasil' => ['nullable', Rule::in(array_keys(TindakanPengusiran::HASIL))],
        ]);

        $query = TindakanPengusiran::query()
            ->with(['alat:id,nama_alat,kategori', 'user:id,username,namalengkap', 'laporan:id,no_laporan'])
            ->when($filters['dari'] ?? null, fn ($q, $dari) => $q->where('waktu', '>=', $dari.' 00:00:00'))
            ->when($filters['sampai'] ?? null, fn ($q, $sampai) => $q->where('waktu', '<=', $sampai.' 23:59:59'))
            ->when($filters['alat_id'] ?? null, fn ($q, $alatId) => $q->where('alat_id', $alatId))
            ->when($filters['grid'] ?? null, fn ($q, $grid) => $q->where('grid', GridCode::normalize($grid)))
            ->when($filters['hasil'] ?? null, fn ($q, $hasil) => $q->where('hasil', $hasil));

        $stats = [
            'total' => (clone $query)->count(),
            'tiga_puluh_hari' => TindakanPengusiran::query()
                ->where('waktu', '>=', now(config('whms_risk.zona_waktu'))->subDays(30)->format('Y-m-d H:i:s'))
                ->count(),
            'alat_terbanyak' => (clone $query)->reorder()
                ->selectRaw('alat_id, COUNT(*) as total')
                ->groupBy('alat_id')
                ->orderByDesc('total')
                ->with('alat:id,nama_alat')
                ->first(),
            'grid_terbanyak' => (clone $query)->reorder()
                ->selectRaw('grid, COUNT(*) as total')
                ->groupBy('grid')
                ->orderByDesc('total')
                ->first(),
        ];

        $tindakanList = $query->latest('waktu')->paginate(20)->withQueryString();

        $alatList = MasterAlatDispersal::query()
            ->withCount('tindakanPengusiran')
            ->orderByDesc('aktif')
            ->orderBy('nama_alat')
            ->get();

        return view('admin.ai.pengusiran.index', compact('tindakanList', 'alatList', 'stats', 'filters'));
    }

    public function storeAlat(StoreAlatDispersalRequest $request): RedirectResponse
    {
        $alat = MasterAlatDispersal::create([
            ...$request->validated(),
            'stok_tersedia' => $request->integer('stok_tersedia'),
        ]);

        return redirect()->route('admin.ai.pengusiran.index')->with('success', "Alat \"{$alat->nama_alat}\" berhasil ditambahkan.");
    }

    public function updateAlat(StoreAlatDispersalRequest $request, int $id): RedirectResponse
    {
        $alat = MasterAlatDispersal::findOrFail($id);
        $alat->update([
            ...$request->validated(),
            'stok_tersedia' => $request->integer('stok_tersedia'),
        ]);

        return redirect()->route('admin.ai.pengusiran.index')->with('success', "Alat \"{$alat->nama_alat}\" berhasil diperbarui.");
    }

    public function toggleAlat(int $id): RedirectResponse
    {
        $alat = MasterAlatDispersal::findOrFail($id);
        $alat->update(['aktif' => ! $alat->aktif]);

        $status = $alat->aktif ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.ai.pengusiran.index')->with('success', "Alat \"{$alat->nama_alat}\" {$status}.");
    }

    /**
     * Alat yang sudah tercatat di log tidak dihapus agar riwayat tetap utuh; nonaktifkan saja.
     */
    public function destroyAlat(int $id): RedirectResponse
    {
        $alat = MasterAlatDispersal::withCount('tindakanPengusiran')->findOrFail($id);

        if ($alat->tindakan_pengusiran_count > 0) {
            return redirect()->route('admin.ai.pengusiran.index')
                ->with('error', "Alat \"{$alat->nama_alat}\" sudah dipakai di {$alat->tindakan_pengusiran_count} log pengusiran. Nonaktifkan saja agar riwayat tetap utuh.");
        }

        $alat->delete();

        return redirect()->route('admin.ai.pengusiran.index')->with('success', "Alat \"{$alat->nama_alat}\" berhasil dihapus.");
    }
}
