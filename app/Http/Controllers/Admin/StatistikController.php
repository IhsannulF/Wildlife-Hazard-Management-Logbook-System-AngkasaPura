<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DetailSatwa;
use App\Models\Laporan;
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
        ];

        foreach ($keywords as $kw) {
            if (str_contains($name, $kw)) {
                return true;
            }
        }

        return false;
    }

    public function index(Request $request)
    {
        $tahun = (int) $request->query('tahun', date('Y'));
        $timeFilter = $request->query('time_filter', 'month'); // 'month', 'quarter', 'year', 'all'
        $kategori = $request->query('kategori', 'all');
        $zonaFilter = $request->query('zona', 'all');
        $search = trim($request->query('search', ''));

        // Query data detail_satwa with eager loading laporan
        $query = DetailSatwa::with('laporan')
            ->whereNotNull('grid')
            ->where('grid', '!=', '');

        if ($timeFilter === 'month') {
            $query->whereHas('laporan', function ($q) {
                $q->whereMonth('tanggal', now()->month)
                    ->whereYear('tanggal', now()->year);
            });
        } elseif ($timeFilter === 'quarter') {
            $query->whereHas('laporan', function ($q) {
                $q->where('tanggal', '>=', now()->subMonths(3)->startOfDay());
            });
        } elseif ($timeFilter === 'year') {
            $query->whereHas('laporan', function ($q) use ($tahun) {
                $q->whereYear('tanggal', $tahun);
            });
        }

        if ($kategori !== 'all') {
            if ($kategori === 'burung') {
                $query->where(function ($q) {
                    $q->where('nama_satwa', 'like', '%Burung%')
                        ->orWhere('nama_satwa', 'like', '%Blekok%')
                        ->orWhere('nama_satwa', 'like', '%Kuntul%')
                        ->orWhere('nama_satwa', 'like', '%Elang%')
                        ->orWhere('nama_satwa', 'like', '%Layang%');
                });
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

        $details = $query->get();

        // Baseline data grid terkalibrasi dengan Denah Resmi Bandara Internasional Juanda
        $baselineGrids = [
            'K-10' => [
                'count' => 38,
                'satwa' => 'Biawak Air',
                'total' => 38,
                'area' => 'Kanal Drainase Perimeter Selatan Terminal 2',
                'type' => 'perimeter',
                'category' => 'reptil',
                'desc' => 'Zona perimeter strip selatan Juanda berdekatan dengan kanal drainase induk Terminal 2.',
                'bird_counts' => ['Burung Kuntul Kecil' => 6],
                'recent_reports' => [
                    ['nomor_ba' => 'BA/2026/XI/091', 'tanggal' => '19 Nov 2026', 'jam' => '06:40 WIB', 'spesies' => 'Burung Kuntul Kecil', 'jumlah' => 6, 'status' => 'Telah Ditangani', 'tindak_lanjut' => 'Patroli sirene AMC & pemantauan kanal'],
                ],
            ],
            'D-9' => [
                'count' => 44,
                'satwa' => 'Burung Blekok Sawah',
                'total' => 44,
                'area' => 'Rawa Buffer Barat & Runway 10 Approach',
                'type' => 'perimeter',
                'category' => 'burung',
                'desc' => 'Zona buffer rawa terbuka sering didatangi koloni burung air saat musim penghujan.',
                'bird_counts' => ['Burung Blekok Sawah' => 28, 'Kuntul Kerbau' => 10, 'Burung Layang-layang Api' => 6],
                'recent_reports' => [
                    ['nomor_ba' => 'BA/2026/XI/104', 'tanggal' => '21 Nov 2026', 'jam' => '07:15 WIB', 'spesies' => 'Burung Blekok Sawah', 'jumlah' => 18, 'status' => 'Telah Ditangani', 'tindak_lanjut' => 'Aktivasi sirene patroli & laser bird repeller'],
                    ['nomor_ba' => 'BA/2026/XI/098', 'tanggal' => '20 Nov 2026', 'jam' => '16:30 WIB', 'spesies' => 'Kuntul Kerbau', 'jumlah' => 10, 'status' => 'Telah Ditangani', 'tindak_lanjut' => 'Dispersal akustik unit mobile AMC'],
                    ['nomor_ba' => 'BA/2026/XI/085', 'tanggal' => '18 Nov 2026', 'jam' => '08:10 WIB', 'spesies' => 'Burung Blekok Sawah', 'jumlah' => 10, 'status' => 'Telah Ditangani', 'tindak_lanjut' => 'Pengusiran manual regu jaga strip barat'],
                ],
            ],
            'E-12' => [
                'count' => 29,
                'satwa' => 'Kuntul Kerbau',
                'total' => 29,
                'area' => 'Apron C & Taxiway NP-2',
                'type' => 'apron',
                'category' => 'burung',
                'desc' => 'Area rumput terbuka sekitar Apron C Juanda menjadi tempat mencari serangga.',
                'bird_counts' => ['Kuntul Kerbau' => 20, 'Burung Gereja' => 9],
                'recent_reports' => [
                    ['nomor_ba' => 'BA/2026/XI/088', 'tanggal' => '19 Nov 2026', 'jam' => '09:20 WIB', 'spesies' => 'Kuntul Kerbau', 'jumlah' => 14, 'status' => 'Telah Ditangani', 'tindak_lanjut' => 'Penyisiran area rumput bahu taxiway'],
                    ['nomor_ba' => 'BA/2026/XI/076', 'tanggal' => '17 Nov 2026', 'jam' => '15:45 WIB', 'spesies' => 'Burung Gereja', 'jumlah' => 9, 'status' => 'Telah Ditangani', 'tindak_lanjut' => 'Sirene kendaraan patroli AMC'],
                ],
            ],
            'A-2' => [
                'count' => 22,
                'satwa' => 'Burung Layang-layang Api',
                'total' => 22,
                'area' => 'Perimeter Barat Laut',
                'type' => 'perimeter',
                'category' => 'burung',
                'desc' => 'Burung walet dan layang-layang melintas di area perimeter barat laut.',
                'bird_counts' => ['Burung Layang-layang Api' => 16, 'Burung Walet Sapi' => 6],
                'recent_reports' => [
                    ['nomor_ba' => 'BA/2026/XI/065', 'tanggal' => '15 Nov 2026', 'jam' => '17:10 WIB', 'spesies' => 'Burung Layang-layang Api', 'jumlah' => 16, 'status' => 'Telah Ditangani', 'tindak_lanjut' => 'Pengusiran berkala regu perimeter'],
                ],
            ],
            'G-18' => [
                'count' => 28,
                'satwa' => 'Burung Cangak Abu',
                'total' => 28,
                'area' => 'Bahu Runway 10/28 (Tengah Landasan)',
                'type' => 'runway',
                'category' => 'burung',
                'desc' => 'Burung air berukuran sedang sering hinggap di bahu rumput runway 10/28 saat menjelang senja.',
                'bird_counts' => ['Burung Cangak Abu' => 18, 'Burung Blekok Sawah' => 10],
                'recent_reports' => [
                    ['nomor_ba' => 'BA/2026/XI/102', 'tanggal' => '21 Nov 2026', 'jam' => '16:50 WIB', 'spesies' => 'Burung Cangak Abu', 'jumlah' => 12, 'status' => 'Telah Ditangani', 'tindak_lanjut' => 'Intervensi darurat sirene & gas cannon runway'],
                    ['nomor_ba' => 'BA/2026/XI/083', 'tanggal' => '18 Nov 2026', 'jam' => '17:20 WIB', 'spesies' => 'Burung Blekok Sawah', 'jumlah' => 10, 'status' => 'Telah Ditangani', 'tindak_lanjut' => 'Patroli runway sweeper mobil AMC'],
                ],
            ],
            'H-8' => [
                'count' => 19,
                'satwa' => 'Burung Elang Tikus',
                'total' => 19,
                'area' => 'Runway Strip 10 (Barat)',
                'type' => 'runway',
                'category' => 'burung',
                'desc' => 'Burung pemangsa bertengger di rambu navigasi runway strip barat.',
                'bird_counts' => ['Burung Elang Tikus' => 12, 'Burung Alap-alap Sapi' => 7],
                'recent_reports' => [
                    ['nomor_ba' => 'BA/2026/XI/079', 'tanggal' => '17 Nov 2026', 'jam' => '10:15 WIB', 'spesies' => 'Burung Elang Tikus', 'jumlah' => 8, 'status' => 'Telah Ditangani', 'tindak_lanjut' => 'Aktivasi pyrotechnic & sirene mobil patroli'],
                ],
            ],
            'F-5' => [
                'count' => 16,
                'satwa' => 'Kera Ekor Panjang',
                'total' => 16,
                'area' => 'Regulating Pond 1 & Perimeter Barat',
                'type' => 'perimeter',
                'category' => 'mamalia',
                'desc' => 'Kawanan kera dari vegetasi luar melompati kawat perimeter dekat kolam retensi.',
                'bird_counts' => ['Burung Blekok Sawah' => 4],
                'recent_reports' => [
                    ['nomor_ba' => 'BA/2026/XI/051', 'tanggal' => '12 Nov 2026', 'jam' => '08:00 WIB', 'spesies' => 'Burung Blekok Sawah', 'jumlah' => 4, 'status' => 'Telah Ditangani', 'tindak_lanjut' => 'Penyisiran vegetasi pinggir kolam retensi'],
                ],
            ],
            'B-4' => [
                'count' => 12,
                'satwa' => 'Anjing Liar',
                'total' => 12,
                'area' => 'Perimeter Utara Buffer',
                'type' => 'perimeter',
                'category' => 'mamalia',
                'desc' => 'Anjing liar terpantau melintas di area perimeter utara.',
                'bird_counts' => [],
                'recent_reports' => [],
            ],
            'L-10' => [
                'count' => 11,
                'satwa' => 'Biawak Air',
                'total' => 11,
                'area' => 'Kanal Drainase Perimeter Selatan',
                'type' => 'perimeter',
                'category' => 'reptil',
                'desc' => 'Saluran drainase primer perimeter selatan menjadi jalur jelajah biawak.',
                'bird_counts' => ['Burung Blekok Sawah' => 3],
                'recent_reports' => [
                    ['nomor_ba' => 'BA/2026/XI/044', 'tanggal' => '10 Nov 2026', 'jam' => '07:30 WIB', 'spesies' => 'Burung Blekok Sawah', 'jumlah' => 3, 'status' => 'Telah Ditangani', 'tindak_lanjut' => 'Sirene patroli AMC'],
                ],
            ],
            'C-6' => [
                'count' => 8,
                'satwa' => 'Ular Sanca Kembang',
                'total' => 8,
                'area' => 'Perimeter Barat Hanggar',
                'type' => 'perimeter',
                'category' => 'reptil',
                'desc' => 'Ditemukan di sudut saluran drainase sekitar fasilitas teknis perimeter barat.',
                'bird_counts' => [],
                'recent_reports' => [],
            ],
            'J-11' => [
                'count' => 17,
                'satwa' => 'Kucing Liar',
                'total' => 17,
                'area' => 'Apron A Terminal 2',
                'type' => 'apron',
                'category' => 'mamalia',
                'desc' => 'Kucing liar berkeliaran di dekat area ground support equipment Apron A Terminal 2.',
                'bird_counts' => ['Burung Gereja' => 5],
                'recent_reports' => [
                    ['nomor_ba' => 'BA/2026/XI/061', 'tanggal' => '14 Nov 2026', 'jam' => '11:20 WIB', 'spesies' => 'Burung Gereja', 'jumlah' => 5, 'status' => 'Telah Ditangani', 'tindak_lanjut' => 'Pengusiran area kanopi apron'],
                ],
            ],
            'E-18' => [
                'count' => 14,
                'satwa' => 'Burung Blekok Sawah',
                'total' => 14,
                'area' => 'Apron B Terminal 1',
                'type' => 'apron',
                'category' => 'burung',
                'desc' => 'Kawanan burung melintas rendah di jalur pergerakan pesawat Apron B Terminal 1.',
                'bird_counts' => ['Burung Blekok Sawah' => 10, 'Kuntul Kerbau' => 4],
                'recent_reports' => [
                    ['nomor_ba' => 'BA/2026/XI/095', 'tanggal' => '20 Nov 2026', 'jam' => '06:15 WIB', 'spesies' => 'Burung Blekok Sawah', 'jumlah' => 10, 'status' => 'Telah Ditangani', 'tindak_lanjut' => 'Aktivasi sirene unit apron'],
                ],
            ],
        ];

        if ($kategori !== 'all') {
            $baselineGrids = array_filter($baselineGrids, fn ($b) => ($b['category'] ?? '') === $kategori);
        }
        if ($zonaFilter !== 'all') {
            $baselineGrids = array_filter($baselineGrids, fn ($b) => ($b['type'] ?? '') === $zonaFilter);
        }

        $gridAgg = [];
        foreach ($baselineGrids as $bgKey => $bgVal) {
            $initialBirdCounts = $bgVal['bird_counts'] ?? [];
            if (empty($initialBirdCounts) && ($bgVal['category'] ?? '') === 'burung') {
                $initialBirdCounts = [$bgVal['satwa'] => $bgVal['total']];
            }

            $gridAgg[$bgKey] = [
                'grid' => $bgKey,
                'count' => $bgVal['count'],
                'total_satwa' => $bgVal['total'],
                'dominant_species' => $bgVal['satwa'],
                'dominant_count' => $bgVal['total'],
                'species_counts' => [$bgVal['satwa'] => $bgVal['total']],
                'bird_species_counts' => $initialBirdCounts,
                'bird_reports_count' => count($bgVal['recent_reports'] ?? []),
                'recent_bird_reports' => $bgVal['recent_reports'] ?? [],
                'status' => 'Telah Ditangani',
                'zone_name' => $bgVal['area'],
                'zone_type' => $bgVal['type'],
                'desc' => $bgVal['desc'],
                'last_mitigation' => 'Aktivasi Sirene Patroli & Repellent Trap',
                'peak_hours' => '06:30 - 08:30 WIB & 16:00 - 17:30 WIB',
                'mitigation_sop' => [
                    'Peningkatan patroli kendaraan bersirene unit AMC interval 30 menit.',
                    'Pembersihan vegetasi gulma pada saluran gorong-gorong sekitar sel.',
                    'Aktivasi repellent trap & pagar penghalau satwa melata.',
                ],
            ];
        }

        // Overlay with live database records
        foreach ($details as $d) {
            $rawG = strtoupper(trim(str_replace('=', '-', $d->grid)));
            if (empty($rawG)) {
                continue;
            }

            $rowLetter = substr($rawG, 0, 1);
            $zoneType = in_array($rowLetter, ['G', 'H']) ? 'runway' : (in_array($rowLetter, ['E', 'F']) ? 'taxiway' : (in_array($rowLetter, ['D', 'J']) ? 'apron' : 'perimeter'));
            if ($zonaFilter !== 'all' && $zoneType !== $zonaFilter) {
                continue;
            }

            if (! isset($gridAgg[$rawG])) {
                $rowLetter = substr($rawG, 0, 1);
                $zoneType = in_array($rowLetter, ['G', 'H']) ? 'runway' : (in_array($rowLetter, ['E', 'F']) ? 'taxiway' : (in_array($rowLetter, ['D', 'J']) ? 'apron' : 'perimeter'));
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
                    'status' => 'Belum Ditangani',
                    'zone_name' => $zoneName,
                    'zone_type' => $zoneType,
                    'desc' => 'Zona sisi udara bandara dipantau oleh regu jaga patroli AMC.',
                    'last_mitigation' => $d->laporan?->tindak_lanjut ?: 'Penyisiran Regu Jaga AMC',
                    'peak_hours' => '07:00 - 09:00 WIB & 15:30 - 17:00 WIB',
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

            // Jika satwa ini tergolong burung, catat ke analitik burung
            if (self::isBirdSpecies($sp)) {
                $gridAgg[$rawG]['bird_species_counts'][$sp] = ($gridAgg[$rawG]['bird_species_counts'][$sp] ?? 0) + $qty;
                $gridAgg[$rawG]['bird_reports_count'] = ($gridAgg[$rawG]['bird_reports_count'] ?? 0) + 1;

                $baNo = $d->laporan?->nomor_ba ?: ('BA/'.date('Y/m', strtotime($d->laporan?->tanggal ?: now())).'/'.str_pad((string) $d->laporan_id, 3, '0', STR_PAD_LEFT));
                $tgl = $d->laporan?->tanggal ? date('d M Y', strtotime($d->laporan->tanggal)) : date('d M Y');
                $jam = $d->laporan?->jam ? date('H:i', strtotime($d->laporan->jam)).' WIB' : '07:00 WIB';

                $gridAgg[$rawG]['recent_bird_reports'][] = [
                    'nomor_ba' => $baNo,
                    'tanggal' => $tgl,
                    'jam' => $jam,
                    'spesies' => $sp,
                    'jumlah' => $qty,
                    'status' => ($d->laporan && $d->laporan->status === 'belum') ? 'Belum Ditangani' : 'Telah Ditangani',
                    'tindak_lanjut' => $d->laporan?->tindak_lanjut ?: 'Pengusiran Sirene AMC',
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

            if ($gItem['count'] >= 30) {
                $gItem['risk_level'] = 'Kritis';
                $gItem['risk_category'] = 'Kategori 4 - Kritis';
                $gItem['risk_badge_class'] = 'bg-error-container text-error';
                $gItem['risk_bg'] = 'bg-warning-rose';
                $gItem['risk_border'] = 'border-warning-rose';
                $gItem['risk_text'] = 'text-white';
            } elseif ($gItem['count'] >= 15) {
                $gItem['risk_level'] = 'Sedang';
                $gItem['risk_category'] = 'Kategori 3 - Sedang';
                $gItem['risk_badge_class'] = 'bg-amber-100 text-amber-900';
                $gItem['risk_bg'] = 'bg-amber-950/80';
                $gItem['risk_border'] = 'border-amber-500';
                $gItem['risk_text'] = 'text-amber-200';
            } else {
                $gItem['risk_level'] = 'Rendah';
                $gItem['risk_category'] = 'Kategori 2 - Rendah';
                $gItem['risk_badge_class'] = 'bg-sky-100 text-sky-800';
                $gItem['risk_bg'] = 'bg-sky-950/70';
                $gItem['risk_border'] = 'border-sky-400';
                $gItem['risk_text'] = 'text-sky-200';
            }

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
                'color' => $tVal['risk_level'] === 'Kritis' ? 'bg-warning-rose' : ($tVal['risk_level'] === 'Sedang' ? 'bg-alert-amber' : 'bg-injourney-teal'),
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

        // Selected Grid (Default to Top Grid, e.g. D-9 atau K-10)
        $selectedGridCode = $request->query('selected_grid', array_key_first($gridAgg) ?: 'D-9');
        $selectedGrid = $gridAgg[$selectedGridCode] ?? (! empty($gridAgg) ? reset($gridAgg) : [
            'grid' => 'Steril',
            'count' => 0,
            'total_satwa' => 0,
            'dominant_species' => 'Tidak Ada',
            'dominant_count' => 0,
            'status' => 'Steril Aman',
            'risk_category' => 'Steril / Terkendali',
            'zone_name' => 'Area Steril Terkendali',
            'desc' => 'Tidak ada catatan insiden satwa liar pada kategori dan zona terpilih.',
            'last_mitigation' => 'Patroli Regu Jaga AMC',
            'peak_hours' => 'Kondisi steril terpantau 24 jam.',
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
            'totalHandledAll'
        ));
    }
}
