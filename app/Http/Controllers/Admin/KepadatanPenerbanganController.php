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
            ->get()
            ->keyBy('jam');

        // 24 hours template 0..23
        $hoursData = [];
        $totalMovements = 0;
        $totalTakeoff = 0;
        $totalLanding = 0;
        $unsplitHours = 0;
        $peakHour = null;
        $maxVal = 0;

        for ($h = 0; $h <= 23; $h++) {
            /** @var KepadatanPenerbangan|null $record */
            $record = $existing->get($h);
            $total = $record?->jumlah_pergerakan ?? 0;
            $isSplit = $record?->isSplit() ?? true;

            $hoursData[$h] = [
                'takeoff' => $record && $isSplit ? $record->jumlah_takeoff : null,
                'landing' => $record && $isSplit ? $record->jumlah_landing : null,
                'total' => $total,
                'terpisah' => $isSplit,
            ];

            $totalMovements += $total;
            $totalTakeoff += $record?->jumlah_takeoff ?? 0;
            $totalLanding += $record?->jumlah_landing ?? 0;
            if (! $isSplit) {
                $unsplitHours++;
            }
            if ($total > $maxVal) {
                $maxVal = $total;
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

        return view('admin.ai.lalu-lintas.index', compact(
            'selectedDate',
            'hoursData',
            'totalMovements',
            'totalTakeoff',
            'totalLanding',
            'unsplitHours',
            'peakHour',
            'recordedDates'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'tanggal' => ['required', 'date'],
            'takeoff' => ['array', 'required_without:movements'],
            'landing' => ['array', 'required_without:movements'],
            'takeoff.*' => ['nullable', 'integer', 'min:0', 'max:200'],
            'landing.*' => ['nullable', 'integer', 'min:0', 'max:200'],
            // Format lama: hanya total pergerakan per jam.
            'movements' => ['array', 'required_without_all:takeoff,landing'],
            'movements.*' => ['nullable', 'integer', 'min:0', 'max:200'],
        ]);

        $tanggal = $request->tanggal;

        DB::transaction(function () use ($request, $tanggal) {
            if ($request->has('takeoff') || $request->has('landing')) {
                for ($h = 0; $h <= 23; $h++) {
                    $takeoff = $request->input("takeoff.{$h}");
                    $landing = $request->input("landing.{$h}");

                    // Kosong di kedua kolom = tidak diubah, agar total lama yang belum dipisah tidak terhapus.
                    if ($takeoff === null && $landing === null) {
                        continue;
                    }

                    $this->saveSplitMovements($tanggal, $h, (int) $takeoff, (int) $landing);
                }

                return;
            }

            foreach ($request->movements as $hour => $count) {
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
            fputcsv($handle, ['tanggal', 'jam', 'jumlah_takeoff', 'jumlah_landing']);
            $today = now(config('whms_risk.zona_waktu'))->toDateString();
            for ($h = 5; $h <= 22; $h++) {
                fputcsv($handle, [$today, $h, rand(2, 12), rand(2, 12)]);
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
                'jumlah_takeoff' => ['nullable', 'integer', 'min:0', 'max:200', 'required_without:jumlah_pergerakan'],
                'jumlah_landing' => ['nullable', 'integer', 'min:0', 'max:200', 'required_without:jumlah_pergerakan'],
                // Format lama: hanya total pergerakan.
                'jumlah_pergerakan' => ['nullable', 'integer', 'min:0', 'max:300', 'required_without_all:jumlah_takeoff,jumlah_landing'],
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
                $hasSplit = ($item['jumlah_takeoff'] ?? '') !== '' || ($item['jumlah_landing'] ?? '') !== '';

                if ($hasSplit) {
                    $this->saveSplitMovements($item['tanggal'], (int) $item['jam'], (int) ($item['jumlah_takeoff'] ?? 0), (int) ($item['jumlah_landing'] ?? 0));

                    continue;
                }

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

    /**
     * Simpan rincian take-off/landing satu jam; total selalu ditulis ulang agar nol eksplisit juga tersimpan.
     */
    private function saveSplitMovements(string $tanggal, int $jam, int $takeoff, int $landing): void
    {
        KepadatanPenerbangan::updateOrCreate(
            ['tanggal' => $tanggal, 'jam' => $jam],
            [
                'jumlah_takeoff' => $takeoff,
                'jumlah_landing' => $landing,
                'jumlah_pergerakan' => $takeoff + $landing,
            ]
        );
    }
}
