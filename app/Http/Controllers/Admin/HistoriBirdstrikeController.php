<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHistoriBirdstrikeRequest;
use App\Models\HistoriBirdstrike;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HistoriBirdstrikeController extends Controller
{
    public function index(Request $request): View
    {
        $query = HistoriBirdstrike::query()->with('creator')->latest('tanggal_kejadian');

        if ($request->filled('runway')) {
            $query->where('kode_runway', $request->runway);
        }

        if ($request->filled('kerusakan')) {
            $query->where('tingkat_kerusakan', $request->kerusakan);
        }

        if ($request->filled('q')) {
            $search = '%'.trim($request->q).'%';
            $query->where(function ($q) use ($search) {
                $q->where('spesies_satwa', 'like', $search)
                    ->orWhere('jenis_pesawat', 'like', $search)
                    ->orWhere('keterangan', 'like', $search);
            });
        }

        $historiList = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => HistoriBirdstrike::count(),
            'tahun_ini' => HistoriBirdstrike::whereYear('tanggal_kejadian', date('Y'))->count(),
            'fatal_substansial' => HistoriBirdstrike::whereIn('tingkat_kerusakan', ['substansial', 'fatal'])->count(),
        ];

        return view('admin.ai.histori.index', compact('historiList', 'stats'));
    }

    public function store(StoreHistoriBirdstrikeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        HistoriBirdstrike::create($data);

        return redirect()->route('admin.ai.histori.index')->with('success', 'Data histori birdstrike berhasil ditambahkan.');
    }

    public function update(StoreHistoriBirdstrikeRequest $request, int $id): RedirectResponse
    {
        $histori = HistoriBirdstrike::findOrFail($id);
        $histori->update($request->validated());

        return redirect()->route('admin.ai.histori.index')->with('success', 'Data histori birdstrike berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $histori = HistoriBirdstrike::findOrFail($id);
        $histori->delete();

        return redirect()->route('admin.ai.histori.index')->with('success', 'Data histori birdstrike berhasil dihapus.');
    }

    public function template(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_histori_birdstrike.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'tanggal_kejadian',
                'jam_kejadian',
                'kode_runway',
                'titik_lokasi',
                'koordinat_lat',
                'koordinat_lng',
                'jenis_pesawat',
                'fase_penerbangan',
                'bagian_pesawat',
                'spesies_satwa',
                'jumlah_satwa',
                'tingkat_kerusakan',
                'keterangan',
            ]);
            fputcsv($handle, [
                date('Y-m-d', strtotime('-5 days')),
                '06:45',
                '10',
                'touchdown',
                '-7.3798350',
                '112.7875420',
                'B737-800',
                'landing',
                'engine',
                'Kuntul Kerbau',
                '2',
                'minor',
                'Ditemukan bulu di cowl engine nomor 1',
            ]);
            fclose($handle);
        }, 200, $headers);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        $rows = [];
        if (($handle = fopen($path, 'r')) !== false) {
            $header = fgetcsv($handle, 2000, ',');
            if (! $header) {
                return back()->with('error', 'File CSV kosong atau format header tidak valid.');
            }

            // Clean BOM from first header column if present
            $header[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $header[0]);
            $header = array_map('trim', $header);

            while (($data = fgetcsv($handle, 2000, ',')) !== false) {
                if (count($data) === count($header)) {
                    $rows[] = array_combine($header, array_map('trim', $data));
                }
            }
            fclose($handle);
        }

        if (empty($rows)) {
            return back()->with('error', 'Tidak ada baris data yang ditemukan pada file CSV.');
        }

        $validRows = [];
        $invalidCount = 0;
        $userId = $request->user()->id;

        foreach ($rows as $row) {
            $validator = Validator::make($row, [
                'tanggal_kejadian' => ['required', 'date', 'before_or_equal:today'],
                'jam_kejadian' => ['nullable'],
                'kode_runway' => ['required', 'in:10,28,07L,25R'],
                'titik_lokasi' => ['required', 'in:touchdown,roll,threshold,grid'],
                'koordinat_lat' => ['nullable', 'numeric'],
                'koordinat_lng' => ['nullable', 'numeric'],
                'jenis_pesawat' => ['required', 'string', 'max:50'],
                'fase_penerbangan' => ['required', 'in:take_off,landing,approach,climb,taxi'],
                'bagian_pesawat' => ['required', 'string', 'max:50'],
                'spesies_satwa' => ['required', 'string', 'max:100'],
                'jumlah_satwa' => ['required', 'integer', 'min:1'],
                'tingkat_kerusakan' => ['required', 'in:nihil,minor,substansial,fatal'],
                'keterangan' => ['nullable', 'string', 'max:1000'],
            ]);

            if ($validator->fails()) {
                $invalidCount++;

                continue;
            }

            $validated = $validator->validated();
            $validated['created_by'] = $userId;
            $validated['created_at'] = now();
            $validated['updated_at'] = now();
            $validRows[] = $validated;
        }

        if (empty($validRows)) {
            return back()->with('error', "Seluruh ({$invalidCount}) baris pada file CSV tidak valid sesuai skema.");
        }

        DB::transaction(function () use ($validRows) {
            foreach ($validRows as $item) {
                HistoriBirdstrike::create($item);
            }
        });

        $message = sprintf('Berhasil mengimpor %d baris histori birdstrike.', count($validRows));
        if ($invalidCount > 0) {
            $message .= sprintf(' (%d baris tidak valid dilewati).', $invalidCount);
        }

        return redirect()->route('admin.ai.histori.index')->with('success', $message);
    }
}
