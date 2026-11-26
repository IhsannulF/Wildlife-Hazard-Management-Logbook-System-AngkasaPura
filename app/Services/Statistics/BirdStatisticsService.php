<?php

namespace App\Services\Statistics;

use App\Http\Controllers\Admin\StatistikController;
use App\Models\DetailSatwa;
use App\Models\HistoriBirdstrike;
use App\Models\KepadatanPenerbangan;
use App\Models\Laporan;
use App\Support\GridCode;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Statistik & infografis populasi burung untuk dashboard admin (PRD F6).
 * Semua angka dihitung dari data inspeksi nyata, tanpa nilai baseline.
 */
class BirdStatisticsService
{
    public const MONTH_LABELS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    /**
     * Klasifikasi aktivitas grid: total ekor burung per grid dalam 6 bulan.
     *
     * @var array<string, string>
     */
    public const ACTIVITY_CLASSES = [
        'rendah' => 'Rendah (≤ 50 ekor)',
        'sedang' => 'Sedang (51–150 ekor)',
        'tinggi' => 'Tinggi (151–300 ekor)',
        'sangat_tinggi' => 'Sangat Tinggi (> 300 ekor)',
    ];

    public const MIN_CORRELATION_SAMPLES = 30;

    /**
     * @var array<string, string>
     */
    private const FLIGHT_PHASES = [
        'landing' => 'Landing',
        'take_off' => 'Take-off',
        'approach' => 'Approach',
        'climb' => 'Climb',
        'taxi' => 'Taxi',
    ];

    /**
     * Tahun yang memiliki data laporan, terbaru lebih dulu.
     *
     * @return list<int>
     */
    public function availableYears(): array
    {
        return Laporan::query()
            ->whereNotNull('tanggal')
            ->pluck('tanggal')
            ->map(fn ($tanggal): int => (int) $tanggal->format('Y'))
            ->unique()
            ->sortDesc()
            ->values()
            ->all();
    }

    /**
     * @return array{
     *     tahun: int,
     *     kpi: array{observasi: int, burung: int, jenis: int, kejadian: int, grid_risiko_tinggi: int},
     *     tren: array{labels: list<string>, observasi: list<int>, burung: list<int>, shannon: list<float|null>},
     *     top_jenis: list<array{nama: string, jumlah: int, persen: float}>,
     *     keanekaragaman: array{richness: int, total: int, shannon: float, simpson: float, evenness: float|null},
     *     grid: array{mulai: string, akhir: string, distribusi: array<string, int>, teratas: list<array{grid: string, jumlah: int, kelas: string}>},
     *     jam: array{labels: list<string>, burung: list<int>, tanpa_jam: int},
     *     kejadian: array{total: int, fase: array<string, int>, waktu: array<string, int>},
     *     korelasi: array{n: int, r: float|null, cukup: bool, titik: list<array{tanggal: string, x: int, y: int}>}
     * }
     */
    public function build(int $year, ?CarbonImmutable $today = null): array
    {
        $today ??= CarbonImmutable::today();

        $birdRows = $this->birdRows(
            CarbonImmutable::create($year)->startOfYear(),
            CarbonImmutable::create($year)->endOfYear()
        );
        $speciesCounts = $this->speciesCounts($birdRows);
        $grid = $this->gridActivity($year, $today);

        return [
            'tahun' => $year,
            'kpi' => [
                'observasi' => Laporan::query()->whereYear('tanggal', $year)->count(),
                'burung' => (int) $birdRows->sum('jumlah'),
                'jenis' => count($speciesCounts),
                'kejadian' => HistoriBirdstrike::query()->whereYear('tanggal_kejadian', $year)->count(),
                'grid_risiko_tinggi' => $grid['distribusi']['tinggi'] + $grid['distribusi']['sangat_tinggi'],
            ],
            'tren' => $this->monthlyTrend($year, $birdRows),
            'top_jenis' => $this->topSpecies($speciesCounts, 10),
            'keanekaragaman' => self::diversityIndices($speciesCounts),
            'grid' => $grid,
            'jam' => $this->hourlyDistribution($birdRows),
            'kejadian' => $this->birdstrikeEvents(),
            'korelasi' => $this->trafficCorrelation($year, $birdRows),
        ];
    }

    /**
     * Indeks Shannon-Wiener (H', ln), Simpson (1 − D, sampel terbatas), dan evenness Pielou (H' / ln S).
     *
     * @param  array<string, int>  $countsBySpecies
     * @return array{richness: int, total: int, shannon: float, simpson: float, evenness: float|null}
     */
    public static function diversityIndices(array $countsBySpecies): array
    {
        $counts = array_values(array_filter($countsBySpecies, fn (int $count): bool => $count > 0));
        $total = array_sum($counts);
        $richness = count($counts);

        if ($total === 0) {
            return ['richness' => 0, 'total' => 0, 'shannon' => 0.0, 'simpson' => 0.0, 'evenness' => null];
        }

        $shannon = 0.0;
        $sumPairs = 0;
        foreach ($counts as $count) {
            $proportion = $count / $total;
            $shannon -= $proportion * log($proportion);
            $sumPairs += $count * ($count - 1);
        }

        $simpson = $total > 1 ? 1 - ($sumPairs / ($total * ($total - 1))) : 0.0;

        return [
            'richness' => $richness,
            'total' => $total,
            'shannon' => round($shannon, 3),
            'simpson' => round($simpson, 3),
            'evenness' => $richness > 1 ? round($shannon / log($richness), 3) : null,
        ];
    }

    /**
     * Koefisien korelasi Pearson; null bila sampel < 3 atau salah satu variabel konstan.
     *
     * @param  list<int|float>  $x
     * @param  list<int|float>  $y
     */
    public static function pearson(array $x, array $y): ?float
    {
        $n = min(count($x), count($y));
        if ($n < 3) {
            return null;
        }

        $meanX = array_sum($x) / $n;
        $meanY = array_sum($y) / $n;
        $covariance = 0.0;
        $varianceX = 0.0;
        $varianceY = 0.0;

        for ($i = 0; $i < $n; $i++) {
            $dx = $x[$i] - $meanX;
            $dy = $y[$i] - $meanY;
            $covariance += $dx * $dy;
            $varianceX += $dx ** 2;
            $varianceY += $dy ** 2;
        }

        if ($varianceX == 0.0 || $varianceY == 0.0) {
            return null;
        }

        return round($covariance / sqrt($varianceX * $varianceY), 2);
    }

    public static function activityClass(int $totalBirds): string
    {
        return match (true) {
            $totalBirds > 300 => 'sangat_tinggi',
            $totalBirds > 150 => 'tinggi',
            $totalBirds > 50 => 'sedang',
            default => 'rendah',
        };
    }

    /**
     * Baris detail satwa berjenis burung beserta tanggal, jam, dan grid laporannya.
     *
     * @return Collection<int, array{nama: string, jumlah: int, grid: string|null, tanggal: string, jam: string|null}>
     */
    private function birdRows(CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        return DetailSatwa::query()
            ->join('laporan', 'detail_satwa.laporan_id', '=', 'laporan.id')
            ->whereBetween('laporan.tanggal', [$from->toDateString(), $to->toDateString()])
            ->toBase()
            ->get([
                'detail_satwa.nama_satwa',
                'detail_satwa.jumlah',
                'detail_satwa.grid',
                'laporan.grid_lokasi',
                'laporan.tanggal',
                'laporan.jam',
            ])
            ->filter(fn (object $row): bool => StatistikController::isBirdSpecies($row->nama_satwa))
            ->map(fn (object $row): array => [
                'nama' => Str::title(trim((string) $row->nama_satwa)),
                'jumlah' => max(0, (int) $row->jumlah),
                'grid' => GridCode::normalize(($row->grid ?: $row->grid_lokasi) ?: null),
                'tanggal' => substr((string) $row->tanggal, 0, 10),
                'jam' => $row->jam ? substr((string) $row->jam, 0, 5) : null,
            ])
            ->values();
    }

    /**
     * @param  Collection<int, array{nama: string, jumlah: int}>  $birdRows
     * @return array<string, int>
     */
    private function speciesCounts(Collection $birdRows): array
    {
        return $birdRows
            ->groupBy('nama')
            ->map(fn (Collection $rows): int => (int) $rows->sum('jumlah'))
            ->filter(fn (int $count): bool => $count > 0)
            ->sortDesc()
            ->all();
    }

    /**
     * @param  array<string, int>  $speciesCounts
     * @return list<array{nama: string, jumlah: int, persen: float}>
     */
    private function topSpecies(array $speciesCounts, int $limit): array
    {
        $total = array_sum($speciesCounts);

        return collect($speciesCounts)
            ->take($limit)
            ->map(fn (int $count, string $name): array => [
                'nama' => $name,
                'jumlah' => $count,
                'persen' => $total > 0 ? round($count / $total * 100, 1) : 0.0,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, array{nama: string, jumlah: int, tanggal: string}>  $birdRows
     * @return array{labels: list<string>, observasi: list<int>, burung: list<int>, shannon: list<float|null>}
     */
    private function monthlyTrend(int $year, Collection $birdRows): array
    {
        $observationsPerMonth = Laporan::query()
            ->whereYear('tanggal', $year)
            ->pluck('tanggal')
            ->countBy(fn ($tanggal): int => (int) $tanggal->format('n'));

        $rowsPerMonth = $birdRows->groupBy(fn (array $row): int => (int) substr($row['tanggal'], 5, 2));

        $observasi = [];
        $burung = [];
        $shannon = [];
        foreach (range(1, 12) as $month) {
            $rows = $rowsPerMonth->get($month, collect());
            $observasi[] = (int) $observationsPerMonth->get($month, 0);
            $burung[] = (int) $rows->sum('jumlah');
            $shannon[] = $rows->isEmpty() ? null : self::diversityIndices($this->speciesCounts($rows))['shannon'];
        }

        return [
            'labels' => self::MONTH_LABELS,
            'observasi' => $observasi,
            'burung' => $burung,
            'shannon' => $shannon,
        ];
    }

    /**
     * Klasifikasi aktivitas grid dalam 6 bulan terakhir dari akhir periode tahun terpilih.
     *
     * @return array{mulai: string, akhir: string, distribusi: array<string, int>, teratas: list<array{grid: string, jumlah: int, kelas: string}>}
     */
    private function gridActivity(int $year, CarbonImmutable $today): array
    {
        $end = $year === $today->year ? $today : CarbonImmutable::create($year)->endOfYear()->startOfDay();
        $start = $end->subMonthsNoOverflow(6)->addDay();

        $totalsPerGrid = $this->birdRows($start, $end)
            ->filter(fn (array $row): bool => GridCode::isValid($row['grid']))
            ->groupBy('grid')
            ->map(fn (Collection $rows): int => (int) $rows->sum('jumlah'))
            ->sortDesc();

        $distribution = array_fill_keys(array_keys(self::ACTIVITY_CLASSES), 0);
        foreach ($totalsPerGrid as $total) {
            $distribution[self::activityClass($total)]++;
        }

        return [
            'mulai' => $start->toDateString(),
            'akhir' => $end->toDateString(),
            'distribusi' => $distribution,
            'teratas' => $totalsPerGrid
                ->take(5)
                ->map(fn (int $total, string $grid): array => [
                    'grid' => $grid,
                    'jumlah' => $total,
                    'kelas' => self::activityClass($total),
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  Collection<int, array{jumlah: int, jam: string|null}>  $birdRows
     * @return array{labels: list<string>, burung: list<int>, tanpa_jam: int}
     */
    private function hourlyDistribution(Collection $birdRows): array
    {
        $perHour = array_fill(0, 24, 0);
        $withoutHour = 0;

        foreach ($birdRows as $row) {
            if ($row['jam'] === null) {
                $withoutHour += $row['jumlah'];

                continue;
            }
            $perHour[(int) substr($row['jam'], 0, 2)] += $row['jumlah'];
        }

        return [
            'labels' => array_map(fn (int $hour): string => sprintf('%02d', $hour), range(0, 23)),
            'burung' => $perHour,
            'tanpa_jam' => $withoutHour,
        ];
    }

    /**
     * Distribusi seluruh histori kejadian bird strike per fase penerbangan dan waktu.
     *
     * @return array{total: int, fase: array<string, int>, waktu: array<string, int>}
     */
    private function birdstrikeEvents(): array
    {
        $events = HistoriBirdstrike::query()->get(['fase_penerbangan', 'jam_kejadian']);

        $phases = array_fill_keys(array_values(self::FLIGHT_PHASES), 0);
        $times = ['Pagi (05–10)' => 0, 'Siang (10–15)' => 0, 'Sore (15–18)' => 0, 'Malam (18–05)' => 0, 'Tidak tercatat' => 0];

        foreach ($events as $event) {
            $phaseLabel = self::FLIGHT_PHASES[$event->fase_penerbangan] ?? null;
            if ($phaseLabel !== null) {
                $phases[$phaseLabel]++;
            }

            $hour = $event->jam_kejadian ? (int) substr((string) $event->jam_kejadian, 0, 2) : null;
            $bucket = match (true) {
                $hour === null => 'Tidak tercatat',
                $hour >= 5 && $hour < 10 => 'Pagi (05–10)',
                $hour >= 10 && $hour < 15 => 'Siang (10–15)',
                $hour >= 15 && $hour < 18 => 'Sore (15–18)',
                default => 'Malam (18–05)',
            };
            $times[$bucket]++;
        }

        if ($times['Tidak tercatat'] === 0) {
            unset($times['Tidak tercatat']);
        }

        return ['total' => $events->count(), 'fase' => $phases, 'waktu' => $times];
    }

    /**
     * Korelasi harian total pergerakan pesawat vs total ekor burung (hari yang punya keduanya).
     *
     * @param  Collection<int, array{jumlah: int, tanggal: string}>  $birdRows
     * @return array{n: int, r: float|null, cukup: bool, titik: list<array{tanggal: string, x: int, y: int}>}
     */
    private function trafficCorrelation(int $year, Collection $birdRows): array
    {
        $trafficPerDay = KepadatanPenerbangan::query()
            ->whereYear('tanggal', $year)
            ->get(['tanggal', 'jumlah_pergerakan'])
            ->groupBy(fn (KepadatanPenerbangan $row): string => $row->tanggal->toDateString())
            ->map(fn (Collection $rows): int => (int) $rows->sum('jumlah_pergerakan'));

        $birdsPerDay = $birdRows
            ->groupBy('tanggal')
            ->map(fn (Collection $rows): int => (int) $rows->sum('jumlah'));

        $points = $birdsPerDay
            ->filter(fn (int $birds, string $date): bool => $trafficPerDay->has($date))
            ->map(fn (int $birds, string $date): array => ['tanggal' => $date, 'x' => $trafficPerDay->get($date), 'y' => $birds])
            ->sortKeys()
            ->values();

        return [
            'n' => $points->count(),
            'r' => self::pearson($points->pluck('x')->all(), $points->pluck('y')->all()),
            'cukup' => $points->count() >= self::MIN_CORRELATION_SAMPLES,
            'titik' => $points->all(),
        ];
    }
}
