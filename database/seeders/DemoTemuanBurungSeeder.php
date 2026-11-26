<?php

namespace Database\Seeders;

use App\Models\Laporan;
use App\Services\IcaoRiskMatrixService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Data DEMO temuan burung 12 bulan terakhir untuk melihat tampilan heatmap & statistik dengan data
 * yang menyerupai kondisi lapangan. Semua baris ditandai `extra_data.data_demo = true`, nomor laporan
 * berawalan DEMO, dan seeder ini menghapus data demo lama sebelum mengisi ulang.
 *
 * Hapus: Laporan::where('extra_data->data_demo', true) beserta detail_satwa-nya
 * (atau jalankan seeder ini dengan DEMO_HAPUS_SAJA=true).
 */
class DemoTemuanBurungSeeder extends Seeder
{
    /**
     * Grid habitat burung beserta bobot kemunculan, diturunkan dari gridmap Juanda:
     * ujung runway (approach), regulating pond, rumput strip, kanal selatan, dan apron.
     *
     * @var array<string, int>
     */
    private const HOTSPOTS = [
        // Approach / ujung RWY 10 dan RWY 28
        'G-3' => 9, 'H-3' => 10, 'G-4' => 8, 'H-4' => 8, 'I-4' => 4, 'F-4' => 3,
        'G-26' => 8, 'H-26' => 9, 'G-27' => 6, 'H-27' => 6, 'I-26' => 3,
        // Rumput strip runway & taxiway
        'G-7' => 4, 'H-8' => 5, 'H-12' => 4, 'G-16' => 3, 'H-18' => 4, 'H-22' => 4, 'I-9' => 5, 'I-10' => 4, 'I-21' => 3,
        // Regulating pond 1 (F-9..11), 2 (C-17..19), 3 (F-23..24)
        'F-9' => 8, 'F-10' => 9, 'F-11' => 6, 'E-10' => 3,
        'C-17' => 7, 'C-18' => 8, 'B-18' => 4, 'C-19' => 4,
        'F-23' => 7, 'F-24' => 6, 'E-24' => 3,
        // Kanal / tambak selatan & utara
        'K-9' => 6, 'K-10' => 7, 'K-11' => 6, 'J-20' => 4, 'K-22' => 5, 'B-9' => 3, 'B-24' => 4, 'D-9' => 5, 'D-10' => 4,
        // Apron & terminal (burung gereja, dara)
        'E-17' => 3, 'E-19' => 3, 'J-11' => 3, 'D-19' => 2,
        // Perimeter jauh (jarang)
        'A-6' => 1, 'A-20' => 1, 'L-5' => 1, 'L-15' => 1, 'L-25' => 1, 'B-3' => 1, 'K-27' => 1,
    ];

    /**
     * Jenis burung: bobot kemunculan dan rentang ukuran kawanan.
     *
     * @var array<string, array{bobot: int, min: int, maks: int}>
     */
    private const SPECIES = [
        'Burung Kuntul Kerbau' => ['bobot' => 22, 'min' => 2, 'maks' => 22],
        'Burung Kuntul Kecil' => ['bobot' => 14, 'min' => 1, 'maks' => 15],
        'Burung Blekok Sawah' => ['bobot' => 12, 'min' => 1, 'maks' => 12],
        'Burung Cangak Abu' => ['bobot' => 7, 'min' => 1, 'maks' => 5],
        'Burung Pecuk Padi' => ['bobot' => 9, 'min' => 2, 'maks' => 14],
        'Burung Layang-layang Api' => ['bobot' => 11, 'min' => 4, 'maks' => 30],
        'Burung Dara Laut Sayap Putih' => ['bobot' => 6, 'min' => 2, 'maks' => 18],
        'Burung Gereja' => ['bobot' => 8, 'min' => 3, 'maks' => 20],
        'Cabak Kota' => ['bobot' => 4, 'min' => 1, 'maks' => 6],
        'Burung Elang Tikus' => ['bobot' => 2, 'min' => 1, 'maks' => 2],
        'Burung Walet Sapi' => ['bobot' => 5, 'min' => 5, 'maks' => 25],
    ];

    /**
     * Jam patroli (WIB) beserta faktor aktivitas burung; pagi dan sore lebih ramai.
     *
     * @var array<int, float>
     */
    private const PATROL_HOURS = [6 => 1.5, 10 => 0.8, 14 => 0.7, 17 => 1.4];

    public function run(): void
    {
        $this->deleteDemoData();

        if (filter_var(env('DEMO_HAPUS_SAJA', false), FILTER_VALIDATE_BOOL)) {
            $this->command?->info('Data demo temuan burung dihapus.');

            return;
        }

        mt_srand(20261126);
        $riskService = app(IcaoRiskMatrixService::class);
        $now = CarbonImmutable::now(config('whms_risk.zona_waktu'));
        $today = $now->startOfDay();
        $counter = 0;

        for ($date = $today->subYear()->addDay(); $date->lte($today); $date = $date->addDay()) {
            // Musim hujan (Nov–Mar) burung air lebih banyak.
            $season = in_array($date->month, [11, 12, 1, 2, 3], true) ? 1.35 : 0.85;

            foreach (self::PATROL_HOURS as $hour => $activity) {
                $patrolTime = $date->setTime($hour, mt_rand(0, 50));
                if ($patrolTime->gt($now) || mt_rand(1, 100) > 78) {
                    continue;
                }

                $sightings = $this->sightings($season * $activity);
                if ($sightings === []) {
                    continue;
                }

                $counter++;
                $this->storeLaporan($patrolTime, $sightings, $counter, $riskService);
            }
        }

        $this->command?->info("{$counter} laporan demo temuan burung dibuat.");
    }

    /**
     * @return list<array{grid: string, nama: string, jumlah: int}>
     */
    private function sightings(float $activity): array
    {
        $count = mt_rand(1, 100) <= 55 ? 1 : (mt_rand(1, 100) <= 70 ? 2 : 3);
        $sightings = [];

        for ($i = 0; $i < $count; $i++) {
            $species = $this->weightedPick(array_map(fn (array $s): int => $s['bobot'], self::SPECIES));
            $range = self::SPECIES[$species];
            $flock = (int) round(mt_rand($range['min'], $range['maks']) * $activity);

            if ($flock < 1) {
                continue;
            }

            $sightings[] = ['grid' => $this->weightedPick(self::HOTSPOTS), 'nama' => $species, 'jumlah' => $flock];
        }

        return $sightings;
    }

    /**
     * @param  list<array{grid: string, nama: string, jumlah: int}>  $sightings
     */
    private function storeLaporan(CarbonImmutable $time, array $sightings, int $counter, IcaoRiskMatrixService $riskService): void
    {
        $mainGrid = $sightings[0]['grid'];
        $row = substr($mainGrid, 0, 1);
        $area = match (true) {
            in_array($row, ['G', 'H'], true) => 'Runway 10/28',
            in_array($row, ['E', 'F', 'I'], true) => 'Taxiway & Rumput Strip',
            in_array($row, ['D', 'J'], true) => 'Apron',
            default => 'Perimeter',
        };
        $risk = $riskService->calculate(array_map(fn (array $s): array => ['nama' => $s['nama'], 'jumlah' => $s['jumlah']], $sightings), $area, $mainGrid);
        $handled = $time->lt(CarbonImmutable::now(config('whms_risk.zona_waktu'))->subDays(2)) || mt_rand(1, 100) <= 60;
        $officer = mt_rand(1, 100) <= 75 ? [2, 'Apron Movement Control (AMC)'] : [3, 'Airport Rescue & Fire Fighting (PK / ARFF)'];

        $laporanId = DB::table('laporan')->insertGetId([
            'user_id' => $officer[0],
            'no_laporan' => sprintf('DEMO/%s/%04d', $time->format('Ymd'), $counter),
            'nama_petugas' => 'Data Demo - '.$officer[1],
            'tanggal' => $time->toDateString(),
            'jam' => $time->format('H:i:s'),
            'kondisi_cuaca' => $this->weightedPick(['Cerah' => 5, 'Berawan' => 4, 'Hujan Ringan' => 2]),
            'unit_kerja' => $officer[1],
            'area_inspeksi' => $area,
            'grid_lokasi' => $mainGrid,
            'risk_score' => $risk['score'],
            'risk_level' => $risk['level'],
            'aktivitas_satwa' => 'Data demo',
            'tindak_lanjut' => $handled ? 'Telah ditangani - pengusiran sirene patroli' : 'Belum ditangani',
            'detail_pengusiran' => $handled ? 'Sirene kendaraan patroli AMC' : '-',
            'status' => $handled ? 'sudah' : 'belum',
            'extra_data' => json_encode(['data_demo' => true]),
            'created_at' => $time->utc(),
            'updated_at' => $time->utc(),
        ]);

        DB::table('detail_satwa')->insert(array_map(fn (array $s): array => [
            'laporan_id' => $laporanId,
            'nama_satwa' => $s['nama'],
            'jumlah' => $s['jumlah'],
            'grid' => $s['grid'],
            'perilaku' => $this->weightedPick(['mencari_makan' => 5, 'melintas' => 3, 'bertengger' => 2]),
            'arah_terbang' => $this->weightedPick(['U' => 1, 'TL' => 1, 'T' => 2, 'TG' => 1, 'S' => 1, 'BD' => 1, 'B' => 2, 'BL' => 1]),
            'ketinggian' => $this->weightedPick(['<50ft' => 6, '50-200ft' => 3, '>200ft' => 1]),
            'created_at' => $time->utc(),
            'updated_at' => $time->utc(),
        ], $sightings));
    }

    /**
     * @template TKey of array-key
     *
     * @param  array<TKey, int>  $weights
     * @return TKey
     */
    private function weightedPick(array $weights): int|string
    {
        $roll = mt_rand(1, array_sum($weights));

        foreach ($weights as $key => $weight) {
            $roll -= $weight;
            if ($roll <= 0) {
                return $key;
            }
        }

        return array_key_last($weights);
    }

    private function deleteDemoData(): void
    {
        $ids = Laporan::query()->where('extra_data->data_demo', true)->pluck('id');

        foreach ($ids->chunk(500) as $chunk) {
            DB::table('detail_satwa')->whereIn('laporan_id', $chunk)->delete();
            DB::table('laporan')->whereIn('id', $chunk)->delete();
        }
    }
}
