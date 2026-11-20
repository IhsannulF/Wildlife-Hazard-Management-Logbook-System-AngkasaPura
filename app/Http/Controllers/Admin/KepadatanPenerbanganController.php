<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KepadatanPenerbangan;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KepadatanPenerbanganController extends Controller
{
    public function index(Request $request): View
    {
        $selectedDate = $request->get('tanggal', Carbon::today()->toDateString());

        $existing = KepadatanPenerbangan::query()
            ->where('tanggal', $selectedDate)
            ->pluck('jumlah_pergerakan', 'jam')
            ->toArray();

        // 24 hours template 0..23
        $hoursData = [];
        $totalMovements = 0;
        $peakHour = null;
        $maxVal = 0;

        for ($h = 0; $h <= 23; $h++) {
            $val = $existing[$h] ?? 0;
            $hoursData[$h] = $val;
            $totalMovements += $val;
            if ($val > $maxVal) {
                $maxVal = $val;
                $peakHour = sprintf('%02d:00', $h);
            }
        }

        // Available dates in database
        $recordedDates = KepadatanPenerbangan::query()
            ->select('tanggal', DB::raw('SUM(jumlah_pergerakan) as total_pergerakan'))
            ->groupBy('tanggal')
            ->orderByDesc('tanggal')
            ->limit(14)
            ->get();

        return view('admin.ai.lalu-lintas.index', compact('selectedDate', 'hoursData', 'totalMovements', 'peakHour', 'recordedDates'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'tanggal' => ['required', 'date'],
            'movements' => ['required', 'array'],
            'movements.*' => ['nullable', 'integer', 'min:0', 'max:200'],
        ]);

        $tanggal = $request->tanggal;
        $movements = $request->movements;

        DB::transaction(function () use ($tanggal, $movements) {
            foreach ($movements as $hour => $count) {
                $h = (int) $hour;
                if ($h >= 0 && $h <= 23) {
                    KepadatanPenerbangan::updateOrCreate(
                        ['tanggal' => $tanggal, 'jam' => $h],
                        ['jumlah_pergerakan' => (int) ($count ?? 0)]
                    );
                }
            }
        });

        return redirect()->route('admin.ai.lalu-lintas.index', ['tanggal' => $tanggal])
            ->with('success', 'Data kepadatan lalu lintas penerbangan berhasil disimpan.');
    }

    public function template(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_kepadatan_penerbangan.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['tanggal', 'jam', 'jumlah_pergerakan']);
            $today = date('Y-m-d');
            for ($h = 5; $h <= 22; $h++) {
                fputcsv($handle, [$today, $h, rand(5, 25)]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        $rows = [];
        if (($handle = fopen($path, 'r')) !== false) {
            $header = fgetcsv($handle, 1000, ',');
            if (! $header) {
                return back()->with('error', 'File CSV kosong atau header tidak valid.');
            }

            $header[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $header[0]);
            $header = array_map('trim', $header);

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if (count($data) === count($header)) {
                    $rows[] = array_combine($header, array_map('trim', $data));
                }
            }
            fclose($handle);
        }

        if (empty($rows)) {
            return back()->with('error', 'Tidak ada baris data pada file CSV.');
        }

        $validRows = [];
        $invalid = 0;

        foreach ($rows as $row) {
            $val = Validator::make($row, [
                'tanggal' => ['required', 'date'],
                'jam' => ['required', 'integer', 'between:0,23'],
                'jumlah_pergerakan' => ['required', 'integer', 'min:0', 'max:300'],
            ]);

            if ($val->fails()) {
                $invalid++;

                continue;
            }

            $validRows[] = $val->validated();
        }

        if (empty($validRows)) {
            return back()->with('error', "Semua baris ({$invalid}) gagal divalidasi.");
        }

        DB::transaction(function () use ($validRows) {
            foreach ($validRows as $item) {
                KepadatanPenerbangan::updateOrCreate(
                    ['tanggal' => $item['tanggal'], 'jam' => (int) $item['jam']],
                    ['jumlah_pergerakan' => (int) $item['jumlah_pergerakan']]
                );
            }
        });

        $msg = sprintf('Berhasil mengimpor %d slot jam pergerakan penerbangan.', count($validRows));
        if ($invalid > 0) {
            $msg .= sprintf(' (%d baris dilewati).', $invalid);
        }

        $firstDate = $validRows[0]['tanggal'] ?? date('Y-m-d');

        return redirect()->route('admin.ai.lalu-lintas.index', ['tanggal' => $firstDate])->with('success', $msg);
    }
}
