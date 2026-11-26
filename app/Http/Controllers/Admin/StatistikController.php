<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DetailSatwa;
use App\Models\GridCell;
use App\Models\Laporan;
use App\Services\Risk\GridRiskService;
use App\Support\GridCode;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class StatistikController extends Controller
{
    /**
     * Helper untuk memeriksa apakah nama satwa tergolong kelompok burung (avian).
     */
    public static function isBirdSpecies(?string $nama): bool
    {
        if (empty($nama)) {
            return false;
        }

        $name = strtolower(trim($nama));
        $keywords = [
            'burung', 'blekok', 'kuntul', 'cangak', 'elang', 'layang', 'walet',
            'pecuk', 'gereja', 'dara', 'merpati', 'bebek', 'ibis', 'puyuh',
            'camar', 'gagak', 'tekukur', 'pipit', 'walik', 'koak', 'mandar',
            'belibis', 'trinil', 'cerek', 'kokokan', 'ruak', 'kareo',
            'cabak', 'jalak', 'kerak', 'bangau', 'alap', 'perkutut', 'kepodang',
        ];

        foreach ($keywords as $kw) {
            if (str_contains($name, $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Rentang tanggal laporan untuk seluruh halaman (grid, heatmap, top 5, tabel).
     * `custom` = rentang bulan pilihan pengguna (`dari`/`sampai` format Y-m), urutan terbalik ditukar.
     *
     * @return array{0: CarbonImmutable|null, 1: CarbonImmutable|null}
     */
    private function periodWindow(string $timeFilter, int $tahun, ?string $dari, ?string $sampai): array
    {
        $today = CarbonImmutable::now(config('whms_risk.zona_waktu'));

        if ($timeFilter === 'custom') {
            $start = CarbonImmutable::createFromFormat('!Y-m', $dari ?? $today->format('Y-m'), config('whms_risk.zona_waktu'));
            $end = CarbonImmutable::createFromFormat('!Y-m', $sampai ?? $dari ?? $today->format('Y-m'), config('whms_risk.zona_waktu'));
            if ($start->gt($end)) {
                [$start, $end] = [$end, $start];
            }

            return [$start->startOfMonth(), $end->endOfMonth()];
        }

        return match ($timeFilter) {
            'month' => [$today->startOfMonth(), $today],
            'quarter' => [$today->subMonths(3)->startOfDay(), $today],
            '12m' => [$today->subYear()->addDay()->startOfDay(), $today],
            'year' => [CarbonImmutable::create($tahun)->startOfYear(), CarbonImmutable::create($tahun)->endOfYear()],
            default => [null, null],
        };
    }

    /**
     * Dua jam dengan jumlah satwa terbanyak dari jam laporan, mis. "06:00–07:00 & 17:00–18:00 WIB".
     *
     * @param  array<int, int>  $hourCounts  jam (0–23) => jumlah ekor
     */
    private static function peakHours(array $hourCounts): string
    {
        if ($hourCounts === []) {
            return 'Belum ada data jam';
        }

        arsort($hourCounts);
        $peaks = array_slice(array_keys($hourCounts), 0, 2);
        sort($peaks);

        return implode(' & ', array_map(fn (int $hour): string => sprintf('%02d:00–%02d:00', $hour, ($hour + 1) % 24), $peaks)).' WIB';
    }

    /**
     * Teks periode aktif, mis. "Jan 2026 – Mar 2026 (3 bulan)".
     */
    private function periodLabel(?CarbonImmutable $from, ?CarbonImmutable $to): string
    {
        if ($from === null || $to === null) {
            return 'Semua data';
        }

        $months = ($to->year - $from->year) * 12 + $to->month - $from->month + 1;
        $format = fn (CarbonImmutable $date): string => $date->locale('id')->translatedFormat('M Y');
        $range = $months === 1 ? $format($from) : $format($from).' – '.$format($to);

        return sprintf('%s (%d bulan)', $range, $months);
    }

    /**
     * Label, warna sel, dan badge untuk kelas zonasi (FR-1.3: hijau/kuning/oranye/merah).
     *
     * @param  array{skor: float, kelas: string}|null  $zone  null bila grid tidak punya pengamatan burung
     * @return array<string, mixed>
     */
    private static function zoningPresentation(?array $zone): array
    {
        $styles = [
            GridRiskService::CLASS_LOW => ['badge' => 'bg-emerald-100 text-emerald-800', 'bg' => 'bg-emerald-950/70', 'border' => 'border-emerald-400', 'text' => 'text-emerald-200', 'bar' => 'bg-emerald-500'],
            GridRiskService::CLASS_MEDIUM => ['badge' => 'bg-yellow-100 text-yellow-900', 'bg' => 'bg-yellow-950/70', 'border' => 'border-yellow-400', 'text' => 'text-yellow-200', 'bar' => 'bg-yellow-400'],
            GridRiskService::CLASS_HIGH => ['badge' => 'bg-orange-100 text-orange-900', 'bg' => 'bg-orange-950/80', 'border' => 'border-orange-500', 'text' => 'text-orange-200', 'bar' => 'bg-orange-500'],
            GridRiskService::CLASS_VERY_HIGH => ['badge' => 'bg-error-container text-error', 'bg' => 'bg-warning-rose', 'border' => 'border-warning-rose', 'text' => 'text-white', 'bar' => 'bg-warning-rose'],
        ];
        $neutral = ['badge' => 'bg-slate-100 text-slate-600', 'bg' => 'bg-slate-800/60', 'border' => 'border-slate-500', 'text' => 'text-slate-300', 'bar' => 'bg-slate-400'];

        $kelas = $zone['kelas'] ?? null;
        $style = $kelas ? $styles[$kelas] : $neutral;
        $label = $kelas ? GridRiskService::CLASS_LABELS[$kelas] : 'Tanpa Data Burung';

        return [
            'zonasi' => $zone,
            'zonasi_kelas' => $kelas,
            'zonasi_bar' => $style['bar'],
            'risk_level' => $label,
            'risk_category' => $zone ? sprintf('%s (S = %s)', $label, number_format($zone['skor'], 1, ',', '.')) : $label,
            'risk_badge_class' => $style['badge'],
            'risk_bg' => $style['bg'],
            'risk_border' => $style['border'],
            'risk_text' => $style['text'],
        ];
    }

    public function index(Request $request, GridRiskService $gridRisk)
    {
        $request->validate([
            'time_filter' => ['nullable', 'in:month,quarter,12m,year,all,custom'],
            'dari' => ['nullable', 'date_format:Y-m'],
            'sampai' => ['nullable', 'date_format:Y-m'],
        ]);

        $tahun = (int) $request->query('tahun', date('Y'));
        $timeFilter = $request->query('time_filter', 'month');
        $kategori = $request->query('kategori', 'all');
        $zonaFilter = $request->query('zona', 'all');
        $search = trim($request->query('search', ''));

        // Satu rentang periode untuk semua bagian halaman.
        [$periodeFrom, $periodeTo] = $this->periodWindow($timeFilter, $tahun, $request->query('dari'), $request->query('sampai'));
        $periodeLabel = $this->periodLabel($periodeFrom, $periodeTo);
        $periodeDari = $periodeFrom?->format('Y-m') ?? '';
        $periodeSampai = $periodeTo?->format('Y-m') ?? '';
        $periodeQuery = array_filter([
            'time_filter' => $timeFilter,
            'tahun' => $timeFilter === 'year' ? $tahun : null,
            'dari' => $timeFilter === 'custom' ? $periodeDari : null,
            'sampai' => $timeFilter === 'custom' ? $periodeSampai : null,
        ]);
        $bulanPertamaData = Laporan::query()->min('tanggal');
        $bulanPertamaData = $bulanPertamaData ? CarbonImmutable::parse($bulanPertamaData)->format('Y-m') : null;

        // Query data detail_satwa with eager loading laporan
        $query = DetailSatwa::with('laporan')
            ->whereNotNull('grid')
            ->where('grid', '!=', '')
            ->when($periodeFrom, fn ($q) => $q->whereHas('laporan', fn ($laporan) => $laporan->whereBetween('tanggal', [$periodeFrom->toDateString(), $periodeTo->toDateString()])));

        if ($kategori !== 'all') {
            if ($kategori === 'burung') {
                // Disaring setelah query dengan isBirdSpecies() agar sama dengan skor zonasi (mis. Cabak, Jalak ikut).
            } elseif ($kategori === 'reptil') {
                $query->where(function ($q) {
                    $q->where('nama_satwa', 'like', '%Biawak%')
                        ->orWhere('nama_satwa', 'like', '%Ular%');
                });
            } elseif ($kategori === 'mamalia') {
                $query->where(function ($q) {
                    $q->where('nama_satwa', 'like', '%Kera%')
                        ->orWhere('nama_satwa', 'like', '%Monyet%')
                        ->orWhere('nama_satwa', 'like', '%Kucing%')
                        ->orWhere('nama_satwa', 'like', '%Anjing%')
                        ->orWhere('nama_satwa', 'like', '%Tikus%');
                });
            }
        }

        // Urut kronologis agar "mitigasi terakhir" dan "laporan terbaru" benar-benar yang terbaru.
        $details = $query->get()
            ->when($kategori === 'burung', fn ($rows) => $rows->filter(fn (DetailSatwa $d): bool => self::isBirdSpecies($d->nama_satwa)))
            // Kata kunci reptil/mamalia bisa mengenai nama burung (mis. "Kerak Kerbau", "Elang Tikus"), jadi burung dikeluarkan.
            ->when(in_array($kategori, ['reptil', 'mamalia'], true), fn ($rows) => $rows->reject(fn (DetailSatwa $d): bool => self::isBirdSpecies($d->nama_satwa)))
            ->sortBy(fn (DetailSatwa $d): string => ($d->laporan?->tanggal?->format('Y-m-d') ?? '').' '.($d->laporan?->jam ?? ''))
            ->values();

        // F1: skor zonasi dari data laporan pada rentang filter yang sama (tanpa data baseline hardcode).
        $zonasi = $gridRisk->scores($periodeFrom, $periodeTo);
        $ambangZonasi = $gridRisk->thresholds();
        $gridCells = GridCell::query()->get()->keyBy('grid');

        // Heatmap mengikuti filter: hanya burung (risiko bird strike) dan hanya sel di zona terpilih.
        $heatmapKeterangan = 'Heatmap: risiko bird strike dari seluruh temuan burung pada periode ini.';
        if (in_array($kategori, ['reptil', 'mamalia'], true)) {
            $heatmapZonasi = [];
            $heatmapKeterangan = 'Heatmap disembunyikan: risiko bird strike hanya dihitung dari burung, bukan '.$kategori.'.';
        } else {
            $inZone = fn (string $grid): bool => $zonaFilter === 'all' || $gridCells->get($grid)?->zona === $zonaFilter;
            $heatmapZonasi = array_filter(
                $gridRisk->heatmap(array_filter($zonasi, $inZone, ARRAY_FILTER_USE_KEY)),
                $inZone,
                ARRAY_FILTER_USE_KEY
            );
            if ($zonaFilter !== 'all') {
                $heatmapKeterangan = 'Heatmap: risiko bird strike dari temuan burung di zona '.$zonaFilter.' saja.';
            }
        }

        $gridAgg = [];

        // Overlay with live database records
        foreach ($details as $d) {
            $rawG = (string) GridCode::normalize(str_replace('=', '-', $d->grid));
            if ($rawG === '') {
                continue;
            }

            $zoneType = $gridCells->get($rawG)?->zona ?? 'perimeter';
            if ($zonaFilter !== 'all' && $zoneType !== $zonaFilter) {
                continue;
            }

            if (! isset($gridAgg[$rawG])) {
                $zoneName = $zoneType === 'runway' ? 'Bahu Runway 10/28 Juanda' : ($zoneType === 'taxiway' ? 'Taxiway & Rawa Buffer' : ($zoneType === 'apron' ? 'Apron Area Pergerakan' : 'Perimeter Sisi Udara'));

                $gridAgg[$rawG] = [
                    'grid' => $rawG,
                    'count' => 0,
                    'total_satwa' => 0,
                    'dominant_species' => $d->nama_satwa ?: 'Satwa Liar',
                    'dominant_count' => 0,
                    'species_counts' => [],
                    'bird_species_counts' => [],
                    'bird_reports_count' => 0,
                    'recent_bird_reports' => [],
                    // Menjadi "Belum Ditangani" bila ada satu saja laporan di grid ini yang belum ditangani.
                    'status' => 'Telah Ditangani',
                    'zone_name' => $zoneName,
                    'zone_type' => $zoneType,
                    'desc' => 'Zona sisi udara bandara dipantau oleh regu jaga patroli AMC.',
                    'last_mitigation' => 'Belum tercatat',
                    'hour_counts' => [],
                    'peak_hours' => 'Belum ada data jam',
                    // SOP umum (sama untuk semua grid), bukan catatan mitigasi grid ini.
                    'mitigation_sop' => [
                        'Patroli mobile rutin AMC Airside.',
                        'Pengusiran akustik menggunakan sirene mobil patroli.',
                        'Inspeksi perimeter kawat pembatas secara berkala.',
                    ],
                ];
            }

            $qty = $d->jumlah ?: 1;
            $sp = $d->nama_satwa ?: 'Satwa Liar';

            $gridAgg[$rawG]['count'] += 1;
            $gridAgg[$rawG]['total_satwa'] += $qty;
            $gridAgg[$rawG]['species_counts'][$sp] = ($gridAgg[$rawG]['species_counts'][$sp] ?? 0) + $qty;
            if ($d->laporan?->jam) {
                $hour = (int) substr((string) $d->laporan->jam, 0, 2);
                $gridAgg[$rawG]['hour_counts'][$hour] = ($gridAgg[$rawG]['hour_counts'][$hour] ?? 0) + $qty;
            }

            // Jika satwa ini tergolong burung, catat ke analitik burung
            if (self::isBirdSpecies($sp)) {
                $gridAgg[$rawG]['bird_species_counts'][$sp] = ($gridAgg[$rawG]['bird_species_counts'][$sp] ?? 0) + $qty;
                $gridAgg[$rawG]['bird_reports_count'] = ($gridAgg[$rawG]['bird_reports_count'] ?? 0) + 1;

                $baNo = $d->laporan?->no_laporan ?: '#'.$d->laporan_id;
                $tgl = $d->laporan?->tanggal ? $d->laporan->tanggal->format('d M Y') : '-';
                $jam = $d->laporan?->jam ? substr((string) $d->laporan->jam, 0, 5).' WIB' : 'Jam tidak dicatat';

                $gridAgg[$rawG]['recent_bird_reports'][] = [
                    'nomor_ba' => $baNo,
                    'tanggal' => $tgl,
                    'jam' => $jam,
                    'spesies' => $sp,
                    'jumlah' => $qty,
                    'status' => ($d->laporan && $d->laporan->status === 'belum') ? 'Belum Ditangani' : 'Telah Ditangani',
                    'tindak_lanjut' => $d->laporan?->tindak_lanjut ?: '-',
                ];
            }

            if ($d->laporan && $d->laporan->status === 'belum') {
                $gridAgg[$rawG]['status'] = 'Belum Ditangani';
            }
            if ($d->laporan && $d->laporan->tindak_lanjut) {
                $gridAgg[$rawG]['last_mitigation'] = $d->laporan->tindak_lanjut;
            }
        }

        // Post-calculate dominant species, risk category, bird stats, and percentage
        $totalIncidentsAll = 0;
        $totalHandledAll = 0;
        $runwayActiveCount = 0;

        $globalBirdSpeciesCounts = [];
        $totalBurungGlobal = 0;
        $totalLaporanBurungGlobal = 0;

        foreach ($gridAgg as $gCode => &$gItem) {
            $gItem['peak_hours'] = self::peakHours($gItem['hour_counts']);

            if (! empty($gItem['species_counts'])) {
                arsort($gItem['species_counts']);
                $gItem['dominant_species'] = array_key_first($gItem['species_counts']);
                $gItem['dominant_count'] = reset($gItem['species_counts']);
            }

            // Hitung statistik burung untuk grid ini
            $gridBirdCounts = $gItem['bird_species_counts'] ?? [];
            $gridTotalBirds = array_sum($gridBirdCounts);

            if ($gridTotalBirds > 0) {
                arsort($gridBirdCounts);
                $domBird = array_key_first($gridBirdCounts);
                $speciesBreakdown = [];
                foreach ($gridBirdCounts as $bSp => $bCnt) {
                    $speciesBreakdown[] = [
                        'species' => $bSp,
                        'count' => $bCnt,
                        'percentage' => round(($bCnt / $gridTotalBirds) * 100, 1),
                    ];

                    // Akumulasi ke total global bandara
                    $globalBirdSpeciesCounts[$bSp] = ($globalBirdSpeciesCounts[$bSp] ?? 0) + $bCnt;
                }
            } else {
                $domBird = 'Tidak Ada';
                $speciesBreakdown = [];
            }

            $totalBurungGlobal += $gridTotalBirds;
            $totalLaporanBurungGlobal += (int) ($gItem['bird_reports_count'] ?? 0);

            $gItem['bird_stats'] = [
                'total_birds' => $gridTotalBirds,
                'bird_reports_count' => (int) ($gItem['bird_reports_count'] ?? count($speciesBreakdown)),
                'dominant_bird' => $domBird,
                'species_breakdown' => $speciesBreakdown,
                'recent_reports' => array_slice(array_reverse($gItem['recent_bird_reports'] ?? []), 0, 3),
            ];

            // F1: kelas risiko dari skor zonasi S_grid, bukan lagi jumlah laporan.
            $gItem = [...$gItem, ...self::zoningPresentation($zonasi[$gCode] ?? null)];

            $totalIncidentsAll += $gItem['count'];
            if ($gItem['status'] === 'Telah Ditangani') {
                $totalHandledAll += $gItem['count'];
            }
            if ($gItem['zone_type'] === 'runway' && $gItem['status'] === 'Belum Ditangani') {
                $runwayActiveCount++;
            }
        }
        unset($gItem);

        // Sort grids by count descending
        uasort($gridAgg, fn ($a, $b) => $b['count'] <=> $a['count']);

        // Top 5 Hotspot Grids
        $top5 = array_slice($gridAgg, 0, 5, true);
        $maxCount = ! empty($top5) ? reset($top5)['count'] : 1;
        $top5Grids = [];
        $rankIdx = 1;
        foreach ($top5 as $tCode => $tVal) {
            $pctWidth = round(($tVal['count'] / max(1, $maxCount)) * 100);
            $top5Grids[] = [
                'rank' => $rankIdx++,
                'grid' => $tCode,
                'name' => $tVal['zone_name'],
                'count' => $tVal['count'],
                'dominant' => $tVal['dominant_species'],
                'bar_width' => $pctWidth,
                'color' => $tVal['zonasi_bar'],
            ];
        }

        // Top 5 Pengumpulan Spesies Burung Terbanyak di Seluruh Bandara
        arsort($globalBirdSpeciesCounts);
        $topBurungGlobal = [];
        $bRank = 1;
        $maxBirdCount = ! empty($globalBirdSpeciesCounts) ? reset($globalBirdSpeciesCounts) : 1;
        foreach (array_slice($globalBirdSpeciesCounts, 0, 5, true) as $bName => $bQty) {
            $pct = $totalBurungGlobal > 0 ? round(($bQty / $totalBurungGlobal) * 100, 1) : 0;
            $barW = round(($bQty / max(1, $maxBirdCount)) * 100);
            $topBurungGlobal[] = [
                'rank' => $bRank++,
                'nama' => $bName,
                'count' => $bQty,
                'percentage' => $pct,
                'bar_width' => $barW,
                'risk_badge' => $bQty >= 30 ? 'bg-error-container text-error' : ($bQty >= 15 ? 'bg-amber-100 text-amber-900' : 'bg-sky-100 text-sky-800'),
                'color' => $bQty >= 30 ? 'bg-warning-rose' : ($bQty >= 15 ? 'bg-alert-amber' : 'bg-injourney-teal'),
            ];
        }

        // Grid terpilih: dari URL, atau grid dengan temuan terbanyak; tanpa data = kartu kosong.
        $selectedGridCode = $request->query('selected_grid', array_key_first($gridAgg) ?: '-');
        $selectedGrid = $gridAgg[$selectedGridCode] ?? (! empty($gridAgg) ? reset($gridAgg) : [
            'grid' => '-',
            'count' => 0,
            'total_satwa' => 0,
            'dominant_species' => 'Tidak Ada',
            'dominant_count' => 0,
            'status' => 'Steril Aman',
            'risk_category' => 'Tanpa Data pada Periode Ini',
            'zone_name' => 'Area Steril Terkendali',
            'desc' => 'Tidak ada catatan insiden satwa liar pada kategori dan zona terpilih.',
            'last_mitigation' => 'Belum tercatat',
            'peak_hours' => 'Belum ada data jam',
            'mitigation_sop' => ['Pertahankan inspeksi patroli berkala regu AMC.'],
            'bird_stats' => [
                'total_birds' => 0,
                'bird_reports_count' => 0,
                'dominant_bird' => 'Tidak Ada',
                'species_breakdown' => [],
                'recent_reports' => [],
            ],
        ]);

        // Filter Grids for bottom table if search is provided
        $tableGrids = $gridAgg;
        if (! empty($search)) {
            $tableGrids = array_filter($tableGrids, function ($item) use ($search) {
                return stripos($item['grid'], $search) !== false ||
                       stripos($item['dominant_species'], $search) !== false ||
                       stripos($item['zone_name'], $search) !== false;
            });
        }
        if ($zonaFilter !== 'all') {
            $tableGrids = array_filter($tableGrids, function ($item) use ($zonaFilter) {
                return $item['zone_type'] === $zonaFilter;
            });
        }

        // KPIs calculation
        $hotspot1 = $top5Grids[0] ?? ['grid' => '-', 'count' => 0, 'name' => 'Steril'];
        $hotspot2 = $top5Grids[1] ?? ['grid' => '-', 'count' => 0, 'name' => 'Steril'];
        $gridTerpadatTitle = $hotspot1['grid'] !== '-' ? ($hotspot1['grid'].($hotspot2['grid'] !== '-' ? ' & '.$hotspot2['grid'] : '')) : 'Area Steril';
        $gridTerpadatCount = $hotspot1['count'];
        $gridTerpadatPct = $totalIncidentsAll > 0 ? round(($gridTerpadatCount / $totalIncidentsAll) * 100, 1) : 0;

        $totalTitikTerpetakan = $totalIncidentsAll;
        $totalSelAktif = count($gridAgg);
        $efektivitasDispersal = $totalIncidentsAll > 0 ? round(($totalHandledAll / $totalIncidentsAll) * 100, 1) : 100.0;

        return view('admin.statistik', compact(
            'tahun',
            'timeFilter',
            'kategori',
            'zonaFilter',
            'search',
            'gridAgg',
            'top5Grids',
            'topBurungGlobal',
            'totalBurungGlobal',
            'totalLaporanBurungGlobal',
            'selectedGrid',
            'tableGrids',
            'gridTerpadatTitle',
            'gridTerpadatCount',
            'gridTerpadatPct',
            'totalTitikTerpetakan',
            'totalSelAktif',
            'runwayActiveCount',
            'efektivitasDispersal',
            'zonasi',
            'ambangZonasi',
            'heatmapZonasi',
            'heatmapKeterangan',
            'periodeLabel',
            'periodeDari',
            'periodeSampai',
            'periodeQuery',
            'bulanPertamaData',
            'totalHandledAll'
        ));
    }
}
