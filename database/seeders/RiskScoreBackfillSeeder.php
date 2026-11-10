<?php

namespace Database\Seeders;

use App\Models\Laporan;
use App\Services\IcaoRiskMatrixService;
use Illuminate\Database\Seeder;

class RiskScoreBackfillSeeder extends Seeder
{
    public function run(): void
    {
        $service = new IcaoRiskMatrixService;
        $laporans = Laporan::with('detailSatwa')->get();

        foreach ($laporans as $laporan) {
            $satwaList = [];
            foreach ($laporan->detailSatwa as $ds) {
                $satwaList[] = [
                    'nama' => $ds->nama_satwa,
                    'jumlah' => $ds->jumlah,
                ];
            }

            // Fallback nama satwa dari ciri_ukuran / aktivitas jika detailSatwa kosong
            if (empty($satwaList) && ! empty($laporan->ciri_ukuran) && $laporan->ciri_ukuran !== '-') {
                $satwaList[] = [
                    'nama' => $laporan->ciri_ukuran,
                    'jumlah' => 1,
                ];
            }

            $res = $service->calculate(
                $satwaList,
                $laporan->area_inspeksi,
                $laporan->grid_lokasi
            );

            $laporan->update([
                'risk_score' => $res['score'],
                'risk_level' => $res['level'],
            ]);
        }
    }
}
