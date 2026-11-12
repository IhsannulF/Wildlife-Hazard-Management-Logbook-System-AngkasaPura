<?php

namespace App\Services;

use App\Models\JenisSatwa;
use App\Models\Laporan;
use App\Models\Tanggapan;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class MonthlyLogbookPdfService
{
    /**
     * Pemetaan nomor bulan ke nama bulan baku bahasa Indonesia.
     *
     * @var array<int, string>
     */
    private const BULAN_INDONESIA = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    public function __construct(
        private PdfMediaService $mediaService,
        private BeritaAcaraPdfService $baPdfService,
    ) {}

    /**
     * Daftar pilihan bulan Indonesia untuk kontrol antarmuka admin.
     *
     * @return array<int, string>
     */
    public static function monthOptions(): array
    {
        return self::BULAN_INDONESIA;
    }

    public static function monthName(int $bulan): string
    {
        return self::BULAN_INDONESIA[$bulan] ?? 'Bulan '.$bulan;
    }

    /**
     * Muat seluruh laporan pada bulan dan tahun terpilih secara kronologis beserta relasinya tanpa query N+1.
     *
     * @return Collection<int, Laporan>
     */
    public function queryMonthlyLaporans(
        int $bulan,
        int $tahun,
        ?string $status = 'all',
        ?string $unitKerja = 'all'
    ): Collection {
        $query = Laporan::with([
            'user:id,namalengkap,username,jabatan,role',
            'validator:id,namalengkap,username,jabatan,role,tanda_tangan',
            'detailSatwa.fotoLaporan',
            'fotoLaporan',
            'tanggapan.user:id,namalengkap,username,jabatan,role',
        ]);

        return $this->applyMonthlyFilters($query, $bulan, $tahun, $status, $unitKerja)
            ->orderBy('tanggal', 'asc')
            ->orderBy('jam', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Hitung ringkasan cepat jumlah BA, ekor satwa, dan lampiran foto untuk pratinjau UI sebelum unduh PDF.
     *
     * @return array{total_laporan: int, total_tervalidasi: int, total_pending: int, total_ekor: int, total_foto: int, periode_label: string}
     */
    public function countSummary(
        int $bulan,
        int $tahun,
        ?string $status = 'all',
        ?string $unitKerja = 'all'
    ): array {
        $query = Laporan::query()
            ->select(['id', 'status'])
            ->with([
                'detailSatwa:id,laporan_id,jumlah',
                'fotoLaporan:id,laporan_id',
            ]);

        $laporans = $this->applyMonthlyFilters($query, $bulan, $tahun, $status, $unitKerja)->get();

        $totalLaporan = $laporans->count();
        $totalTervalidasi = $laporans->filter(fn (Laporan $lap): bool => strtolower((string) $lap->status) === 'sudah')->count();
        $totalEkor = (int) $laporans->sum(fn (Laporan $lap): int => (int) $lap->detailSatwa->sum('jumlah'));
        $totalFoto = (int) $laporans->sum(fn (Laporan $lap): int => $lap->fotoLaporan->count());

        return [
            'total_laporan' => $totalLaporan,
            'total_tervalidasi' => $totalTervalidasi,
            'total_pending' => max(0, $totalLaporan - $totalTervalidasi),
            'total_ekor' => $totalEkor,
            'total_foto' => $totalFoto,
            'periode_label' => static::monthName($bulan).' '.$tahun,
        ];
    }

    /**
     * Susun seluruh struktur data bundel bulanan: cover, kata pengantar, indeks BA, statistik temporal, dan daftar BA.
     *
     * @return array<string, mixed>
     */
    public function buildBundlePayload(
        int $bulan,
        int $tahun,
        ?string $status = 'all',
        ?string $unitKerja = 'all',
        ?User $issuer = null
    ): array {
        $normalizedStatus = in_array($status, ['sudah', 'belum'], true) ? $status : 'all';
        $normalizedUnit = (! empty($unitKerja) && $unitKerja !== 'all') ? trim($unitKerja) : 'all';

        $laporans = $this->queryMonthlyLaporans($bulan, $tahun, $normalizedStatus, $normalizedUnit);

        $baMediaOptions = [
            'max_width' => 600,
            'jpeg_quality' => 68,
        ];

        $baPayloads = [];
        foreach ($laporans as $laporan) {
            $baPayloads[] = $this->baPdfService->buildPayload($laporan, $baMediaOptions);
        }

        $stats = $this->buildStatistics($bulan, $tahun, $baPayloads);
        $indexRows = $this->buildIndexRows($baPayloads);

        $monthName = static::monthName($bulan);
        $periodeLabel = $monthName.' '.$tahun;
        $daysInMonth = Carbon::createFromDate($tahun, $bulan, 1)->daysInMonth;
        $dateRangeLabel = sprintf('01 %s %d – %02d %s %d', $monthName, $tahun, $daysInMonth, $monthName, $tahun);

        $bundleCode = $this->buildBundleCode($bulan, $tahun, $normalizedUnit);
        $printedAtWib = now('Asia/Jakarta')->locale('id')->translatedFormat('d F Y, H:i').' WIB';

        $issuerName = $issuer
            ? ($issuer->namalengkap ?: $issuer->username)
            : 'Safety Manager / Admin WHMS';
        $issuerTitle = $issuer?->validator_display_title ?: 'Safety Manager Unit WHMS';
        $issuerSignatureUri = $issuer
            ? $this->mediaService->normalizeSignatureDataUri($issuer->tanda_tangan)
            : null;

        $filterSummary = [
            'status_label' => match ($normalizedStatus) {
                'sudah' => 'Hanya Berita Acara Tervalidasi',
                'belum' => 'Hanya Berita Acara Menunggu Validasi',
                default => 'Seluruh Status (Tervalidasi & Dalam Penanganan)',
            },
            'unit_label' => $normalizedUnit === 'all' ? 'Semua Unit Kerja Sisi Udara' : $normalizedUnit,
        ];

        $foreword = $this->buildForeword($periodeLabel, $filterSummary['unit_label'], $stats);

        return [
            'bundle_code' => $bundleCode,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'month_name' => $monthName,
            'periode_label' => $periodeLabel,
            'date_range_label' => $dateRangeLabel,
            'days_in_month' => $daysInMonth,
            'printed_at_wib' => $printedAtWib,
            'logo_data_uri' => $this->mediaService->getInjourneyLogoPdfSrc(),
            'filter_summary' => $filterSummary,
            'issuer' => [
                'nama' => $issuerName,
                'jabatan' => $issuerTitle,
                'signature_uri' => $issuerSignatureUri,
            ],
            'stats' => $stats,
            'foreword' => $foreword,
            'index_rows' => $indexRows,
            'berita_acara_items' => $baPayloads,
        ];
    }

    /**
     * Render markup HTML lengkap bundel logbook bulanan siap-DomPDF.
     */
    public function renderHtml(
        int $bulan,
        int $tahun,
        ?string $status = 'all',
        ?string $unitKerja = 'all',
        ?User $issuer = null
    ): string {
        $payload = $this->buildBundlePayload($bulan, $tahun, $status, $unitKerja, $issuer);

        return view('reports.monthly-logbook-pdf', [
            'bundle' => $payload,
        ])->render();
    }

    /**
     * Pastikan batas memori dan waktu eksekusi PHP mencukupi untuk mengompilasi bundel PDF ratusan halaman.
     */
    public function ensurePdfRuntimeHeadroom(): void
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(300);
        }

        $currentLimit = (string) ini_get('memory_limit');
        $limitBytes = $this->parseIniBytes($currentLimit);
        $targetBytes = 1024 * 1024 * 1024;

        if ($limitBytes > 0 && $limitBytes < $targetBytes) {
            @ini_set('memory_limit', '1024M');
        }
    }

    /**
     * Hasilkan dokumen PDF A4 portrait Bundel Logbook Bulanan lengkap dengan penomoran halaman berkelanjutan.
     */
    public function buildPdf(
        int $bulan,
        int $tahun,
        ?string $status = 'all',
        ?string $unitKerja = 'all',
        ?User $issuer = null
    ): DomPdfWrapper {
        $this->ensurePdfRuntimeHeadroom();

        $payload = $this->buildBundlePayload($bulan, $tahun, $status, $unitKerja, $issuer);
        $periodeLabel = $payload['periode_label'];
        $bundleCode = $payload['bundle_code'];
        $printedAtWib = $payload['printed_at_wib'];

        $html = view('reports.monthly-logbook-pdf', [
            'bundle' => $payload,
        ])->render();
        unset($payload);

        $pdf = Pdf::loadHTML($html, 'UTF-8')->setPaper('a4', 'portrait');
        unset($html);

        $pdf->addInfo([
            'Title' => 'Bundel Logbook Bulanan WHMS - '.$periodeLabel,
            'Author' => 'WHMS InJourney Airports',
            'Subject' => 'Kompilasi Berita Acara & Statistik Pengendalian Satwa Liar '.$periodeLabel,
        ]);

        $pdf->render();
        $this->baPdfService->attachPageFooterScript(
            $pdf,
            'WHMS InJourney Airports • '.$bundleCode.' ('.$periodeLabel.')',
            'Dicetak '.$printedAtWib
        );

        return $pdf;
    }

    /**
     * Ambil biner PDF bundel bulanan dari cache jika data belum berubah, atau kompilasi ulang lalu simpan ke cache.
     */
    public function buildOrGetCachedPdfBinary(
        int $bulan,
        int $tahun,
        ?string $status = 'all',
        ?string $unitKerja = 'all',
        ?User $issuer = null
    ): string {
        $this->ensurePdfRuntimeHeadroom();

        $cacheDir = storage_path('app/private/pdf-bundle-cache');
        File::ensureDirectoryExists($cacheDir);

        $cacheKey = $this->buildBundleCacheKey($bulan, $tahun, $status, $unitKerja, $issuer);
        $cachePath = $cacheDir.'/'.$cacheKey.'.pdf';

        if (is_file($cachePath) && is_readable($cachePath) && filesize($cachePath) > 0) {
            $cached = @file_get_contents($cachePath);
            if (is_string($cached) && str_starts_with($cached, '%PDF-')) {
                return $cached;
            }
        }

        $pdf = $this->buildPdf($bulan, $tahun, $status, $unitKerja, $issuer);
        $binary = $pdf->output();
        unset($pdf);

        if (is_string($binary) && $binary !== '') {
            @file_put_contents($cachePath, $binary, LOCK_EX);
            $this->pruneBundleCache($cacheDir, $cachePath);
        }

        return (string) $binary;
    }

    private function buildBundleCacheKey(
        int $bulan,
        int $tahun,
        ?string $status,
        ?string $unitKerja,
        ?User $issuer
    ): string {
        $normalizedStatus = in_array($status, ['sudah', 'belum'], true) ? $status : 'all';
        $normalizedUnit = (! empty($unitKerja) && $unitKerja !== 'all') ? trim($unitKerja) : 'all';

        $statsRow = $this->applyMonthlyFilters(Laporan::query(), $bulan, $tahun, $normalizedStatus, $normalizedUnit)
            ->selectRaw('COUNT(*) as total_rows, MAX(id) as max_id, MAX(updated_at) as max_updated')
            ->first();

        $matchingIds = $this->applyMonthlyFilters(Laporan::query(), $bulan, $tahun, $normalizedStatus, $normalizedUnit)
            ->pluck('id');

        $tanggapanMax = $matchingIds->isNotEmpty()
            ? Tanggapan::whereIn('laporan_id', $matchingIds)->max('updated_at')
            : null;

        $templateFiles = [
            resource_path('views/reports/monthly-logbook-pdf.blade.php'),
            resource_path('views/reports/partials/monthly-bundle-styles.blade.php'),
            resource_path('views/reports/partials/monthly-cover.blade.php'),
            resource_path('views/reports/partials/monthly-foreword-index.blade.php'),
            resource_path('views/reports/partials/monthly-statistics.blade.php'),
            resource_path('views/reports/partials/berita-acara-sheet.blade.php'),
            resource_path('views/reports/partials/berita-acara-styles.blade.php'),
        ];

        $templateMtimes = array_map(
            static fn (string $path): int => is_file($path) ? (int) filemtime($path) : 0,
            $templateFiles
        );

        return sha1(json_encode([
            'v' => 1,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'status' => $normalizedStatus,
            'unit' => $normalizedUnit,
            'issuer_id' => $issuer?->id,
            'issuer_sig' => $issuer?->tanda_tangan ? md5($issuer->tanda_tangan) : null,
            'total_rows' => (int) ($statsRow?->total_rows ?? 0),
            'max_id' => (int) ($statsRow?->max_id ?? 0),
            'max_updated' => (string) ($statsRow?->max_updated ?? ''),
            'tanggapan_max' => (string) ($tanggapanMax ?? ''),
            'templates' => $templateMtimes,
        ]));
    }

    private function pruneBundleCache(string $cacheDir, string $keepPath): void
    {
        $files = glob($cacheDir.'/*.pdf') ?: [];
        if (count($files) <= 10) {
            return;
        }

        usort($files, static fn (string $a, string $b): int => (@filemtime($a) ?: 0) <=> (@filemtime($b) ?: 0));
        $excess = count($files) - 10;

        foreach (array_slice($files, 0, $excess) as $oldFile) {
            if ($oldFile !== $keepPath) {
                @unlink($oldFile);
            }
        }
    }

    private function parseIniBytes(string $value): int
    {
        $trimmed = trim($value);
        if ($trimmed === '' || $trimmed === '-1') {
            return -1;
        }

        $unit = strtolower(substr($trimmed, -1));
        $number = (int) $trimmed;

        return match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }

    public function buildFilename(int $bulan, int $tahun, ?string $unitKerja = 'all'): string
    {
        $monthLabel = static::monthName($bulan);
        $base = sprintf('Bundel_Logbook_WHMS_%04d_%02d_%s', $tahun, $bulan, $monthLabel);

        if (! empty($unitKerja) && $unitKerja !== 'all') {
            $unitSlug = preg_replace('/[^A-Za-z0-9]+/', '_', $unitKerja);
            $unitSlug = trim((string) $unitSlug, '_');
            if ($unitSlug !== '') {
                $base .= '_'.$unitSlug;
            }
        }

        return $base.'.pdf';
    }

    private function applyMonthlyFilters(
        Builder $query,
        int $bulan,
        int $tahun,
        ?string $status,
        ?string $unitKerja
    ): Builder {
        $query->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan);

        if ($status === 'sudah') {
            $query->where('status', 'sudah');
        } elseif ($status === 'belum') {
            $query->where(function (Builder $q): void {
                $q->where('status', 'belum')->orWhereNull('status');
            });
        }

        if (! empty($unitKerja) && $unitKerja !== 'all') {
            $query->where('unit_kerja', trim($unitKerja));
        }

        return $query;
    }

    private function buildBundleCode(int $bulan, int $tahun, string $unitKerja): string
    {
        $unitCode = 'ALL';
        if ($unitKerja !== 'all') {
            $upper = strtoupper($unitKerja);
            if (str_contains($upper, 'ARFF') || str_contains($upper, 'PK') || str_contains($upper, 'RESCUE')) {
                $unitCode = 'ARFF';
            } elseif (str_contains($upper, 'SEC') || str_contains($upper, 'AVSEC')) {
                $unitCode = 'AVSEC';
            } elseif (str_contains($upper, 'SMS') || str_contains($upper, 'SAFETY')) {
                $unitCode = 'SMS';
            } elseif (str_contains($upper, 'AMC') || str_contains($upper, 'APRON')) {
                $unitCode = 'AMC';
            } else {
                $sanitized = preg_replace('/[^A-Z0-9]/', '', $upper);
                $unitCode = substr((string) $sanitized, 0, 6) ?: 'UNIT';
            }
        }

        return sprintf('LOG-WHMS/SUB/%s/%02d/%04d', $unitCode, $bulan, $tahun);
    }

    /**
     * Susun tabel daftar isi kronologis untuk seluruh lembar Berita Acara di dalam bundel.
     *
     * @param  array<int, array<string, mixed>>  $baPayloads
     * @return array<int, array<string, mixed>>
     */
    private function buildIndexRows(array $baPayloads): array
    {
        $rows = [];

        foreach ($baPayloads as $idx => $ba) {
            /** @var Laporan $lap */
            $lap = $ba['laporan'];
            $speciesNames = [];
            foreach ($ba['satwa_matrix'] as $item) {
                $speciesNames[] = $item['nama_satwa'].' ('.$item['jumlah'].')';
            }

            $rows[] = [
                'no' => $idx + 1,
                'no_laporan' => $ba['no_laporan'],
                'tanggal_short' => $lap->tanggal ? $lap->tanggal->format('d/m/Y') : '-',
                'jam' => $ba['jam_formatted'],
                'petugas' => $ba['signatures']['pelapor']['nama'],
                'unit' => $lap->unit_kerja ?: '-',
                'area' => $lap->area_inspeksi ?: '-',
                'grid' => strtoupper((string) ($lap->grid_lokasi ?: '-')),
                'spesies_ringkas' => ! empty($speciesNames) ? implode(', ', array_slice($speciesNames, 0, 2)).(count($speciesNames) > 2 ? ' (+lainnya)' : '') : 'Lampiran Non-Katalog',
                'ekor' => $ba['total_ekor'],
                'amunisi' => $ba['amunisi_terpakai'],
                'risk_score' => $ba['risk']['score'],
                'risk_level' => strtoupper((string) $ba['risk']['level']),
                'risk_style' => BeritaAcaraPdfService::resolveRiskStyle((string) $ba['risk']['level']),
                'is_validated' => $ba['is_validated'],
                'status_short' => $ba['status_short'],
            ];
        }

        return $rows;
    }

    /**
     * Hitung statistik temporal, distribusi risiko, taksonomi, dan hotspot dari data aktual database bulan terpilih.
     *
     * @param  array<int, array<string, mixed>>  $baPayloads
     * @return array<string, mixed>
     */
    private function buildStatistics(int $bulan, int $tahun, array $baPayloads): array
    {
        $daysInMonth = Carbon::createFromDate($tahun, $bulan, 1)->daysInMonth;
        $catalogCategories = JenisSatwa::query()
            ->select(['nama', 'kategori'])
            ->get()
            ->mapWithKeys(fn (JenisSatwa $item): array => [strtolower(trim((string) $item->nama)) => strtolower(trim((string) $item->kategori))])
            ->all();

        $weeklyBuckets = $this->initializeWeeklyBuckets($daysInMonth);
        $dailyBuckets = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dailyBuckets[$d] = [
                'day' => $d,
                'laporan' => 0,
                'ekor' => 0,
                'kritis_tinggi' => 0,
            ];
        }

        $timeSlots = [
            'pagi' => ['label' => 'Pagi (06:00–09:59 WIB)', 'laporan' => 0, 'ekor' => 0, 'color' => '#00A9C1'],
            'siang' => ['label' => 'Siang (10:00–13:59 WIB)', 'laporan' => 0, 'ekor' => 0, 'color' => '#0284c7'],
            'sore' => ['label' => 'Sore (14:00–17:59 WIB)', 'laporan' => 0, 'ekor' => 0, 'color' => '#d97706'],
            'malam' => ['label' => 'Malam/Dini Hari (18:00–05:59 WIB)', 'laporan' => 0, 'ekor' => 0, 'color' => '#334155'],
        ];

        $riskBuckets = [
            'kritis' => ['label' => 'Kritis (High / Critical)', 'count' => 0, 'style' => BeritaAcaraPdfService::resolveRiskStyle('kritis')],
            'tinggi' => ['label' => 'Tinggi (Elevated)', 'count' => 0, 'style' => BeritaAcaraPdfService::resolveRiskStyle('tinggi')],
            'sedang' => ['label' => 'Sedang (Moderate)', 'count' => 0, 'style' => BeritaAcaraPdfService::resolveRiskStyle('sedang')],
            'rendah' => ['label' => 'Rendah (Low Risk)', 'count' => 0, 'style' => BeritaAcaraPdfService::resolveRiskStyle('rendah')],
        ];

        $taxonomyBuckets = [
            'burung' => ['label' => 'Avian / Burung', 'ekor' => 0, 'temuan' => 0, 'color' => '#007fa3'],
            'reptil' => ['label' => 'Reptil', 'ekor' => 0, 'temuan' => 0, 'color' => '#d97706'],
            'mamalia' => ['label' => 'Mamalia', 'ekor' => 0, 'temuan' => 0, 'color' => '#475569'],
            'lainnya' => ['label' => 'Satwa Lainnya', 'ekor' => 0, 'temuan' => 0, 'color' => '#64748b'],
        ];

        $speciesTotals = [];
        $gridTotals = [];
        $unitTotals = [];

        $totalLaporan = count($baPayloads);
        $totalTervalidasi = 0;
        $totalEkor = 0;
        $totalAmunisi = 0;
        $totalFoto = 0;
        $scoreSum = 0;
        $maxRiskScore = 0;

        foreach ($baPayloads as $ba) {
            /** @var Laporan $lap */
            $lap = $ba['laporan'];
            $isValidated = (bool) $ba['is_validated'];
            $ekor = (int) $ba['total_ekor'];
            $amunisi = (int) $ba['amunisi_terpakai'];
            $riskLevel = strtolower((string) $ba['risk']['level']);
            $riskScore = (int) $ba['risk']['score'];
            $isHighOrCritical = in_array($riskLevel, ['kritis', 'tinggi'], true);

            if ($isValidated) {
                $totalTervalidasi++;
            }
            $totalEkor += $ekor;
            $totalAmunisi += $amunisi;
            $totalFoto += count($ba['watermarked_photos']);
            $scoreSum += $riskScore;
            $maxRiskScore = max($maxRiskScore, $riskScore);

            if (isset($riskBuckets[$riskLevel])) {
                $riskBuckets[$riskLevel]['count']++;
            } else {
                $riskBuckets['rendah']['count']++;
            }

            if ($lap->tanggal) {
                $day = (int) $lap->tanggal->format('j');
                if (isset($dailyBuckets[$day])) {
                    $dailyBuckets[$day]['laporan']++;
                    $dailyBuckets[$day]['ekor'] += $ekor;
                    if ($isHighOrCritical) {
                        $dailyBuckets[$day]['kritis_tinggi']++;
                    }
                }

                $weekIndex = (int) min(count($weeklyBuckets) - 1, intdiv($day - 1, 7));
                $weeklyBuckets[$weekIndex]['laporan']++;
                $weeklyBuckets[$weekIndex]['ekor'] += $ekor;
                $weeklyBuckets[$weekIndex]['amunisi'] += $amunisi;
                if ($isValidated) {
                    $weeklyBuckets[$weekIndex]['tervalidasi']++;
                } else {
                    $weeklyBuckets[$weekIndex]['pending']++;
                }
                if ($isHighOrCritical) {
                    $weeklyBuckets[$weekIndex]['kritis_tinggi']++;
                }
            }

            $hourWib = $this->resolveHourWib($lap);
            $slotKey = $this->resolveTimeSlotKey($hourWib);
            $timeSlots[$slotKey]['laporan']++;
            $timeSlots[$slotKey]['ekor'] += $ekor;

            $unitName = trim((string) ($lap->unit_kerja ?: 'Unit Airside'));
            if (! isset($unitTotals[$unitName])) {
                $unitTotals[$unitName] = [
                    'unit' => $unitName,
                    'laporan' => 0,
                    'tervalidasi' => 0,
                    'ekor' => 0,
                    'amunisi' => 0,
                ];
            }
            $unitTotals[$unitName]['laporan']++;
            if ($isValidated) {
                $unitTotals[$unitName]['tervalidasi']++;
            }
            $unitTotals[$unitName]['ekor'] += $ekor;
            $unitTotals[$unitName]['amunisi'] += $amunisi;

            $laporanGrid = strtoupper(trim((string) ($lap->grid_lokasi ?: '-')));
            $laporanArea = trim((string) ($lap->area_inspeksi ?: 'Sisi Udara'));
            if ($lap->detailSatwa->isEmpty()) {
                $this->accumulateGrid($gridTotals, $laporanGrid, $laporanArea, 0);
            } else {
                foreach ($lap->detailSatwa as $ds) {
                    $qty = max(0, (int) $ds->jumlah);
                    $speciesName = trim((string) ($ds->nama_satwa ?: 'Satwa Liar'));
                    $taxKey = $this->classifyTaxonomy($speciesName, $catalogCategories);

                    $taxonomyBuckets[$taxKey]['temuan']++;
                    $taxonomyBuckets[$taxKey]['ekor'] += $qty;

                    $speciesKey = strtolower($speciesName);
                    if (! isset($speciesTotals[$speciesKey])) {
                        $speciesTotals[$speciesKey] = [
                            'nama_satwa' => $speciesName,
                            'nama_ilmiah' => JenisSatwa::resolveNamaIlmiah($speciesName, '-'),
                            'kategori' => $taxonomyBuckets[$taxKey]['label'],
                            'kejadian' => 0,
                            'ekor' => 0,
                        ];
                    }
                    $speciesTotals[$speciesKey]['kejadian']++;
                    $speciesTotals[$speciesKey]['ekor'] += $qty;

                    $gridCode = strtoupper(trim((string) ($ds->grid ?: $laporanGrid)));
                    $this->accumulateGrid($gridTotals, $gridCode, $laporanArea, $qty);
                }
            }
        }

        $maxWeeklyLaporan = max(1, ...array_column($weeklyBuckets, 'laporan'));
        $maxWeeklyEkor = max(1, ...array_column($weeklyBuckets, 'ekor'));
        foreach ($weeklyBuckets as &$wb) {
            $wb['bar_height_pt'] = $wb['laporan'] > 0 ? max(6, (int) round(($wb['laporan'] / $maxWeeklyLaporan) * 38)) : 0;
            $wb['ekor_bar_pct'] = $wb['ekor'] > 0 ? max(8, (int) round(($wb['ekor'] / $maxWeeklyEkor) * 100)) : 0;
        }
        unset($wb);

        $maxDailyLaporan = max(1, ...array_column($dailyBuckets, 'laporan'));
        $peakDayNumber = null;
        $peakDayCount = 0;
        foreach ($dailyBuckets as &$db) {
            $db['bar_height_pt'] = $db['laporan'] > 0 ? max(4, (int) round(($db['laporan'] / $maxDailyLaporan) * 18)) : 0;
            if ($db['laporan'] > $peakDayCount) {
                $peakDayCount = $db['laporan'];
                $peakDayNumber = $db['day'];
            }
        }
        unset($db);

        foreach ($timeSlots as &$slot) {
            $slot['pct'] = $totalLaporan > 0 ? round(($slot['laporan'] / $totalLaporan) * 100, 1) : 0.0;
        }
        unset($slot);

        foreach ($riskBuckets as &$rb) {
            $rb['pct'] = $totalLaporan > 0 ? round(($rb['count'] / $totalLaporan) * 100, 1) : 0.0;
        }
        unset($rb);

        $totalTaxonomyEkor = max(1, $totalEkor);
        foreach ($taxonomyBuckets as &$tb) {
            $tb['pct'] = $totalEkor > 0 ? round(($tb['ekor'] / $totalTaxonomyEkor) * 100, 1) : 0.0;
        }
        unset($tb);

        usort($speciesTotals, fn (array $a, array $b): int => $b['ekor'] <=> $a['ekor'] ?: $b['kejadian'] <=> $a['kejadian']);
        $topSpecies = array_slice($speciesTotals, 0, 5);
        $maxSpeciesEkor = ! empty($topSpecies) ? max(1, $topSpecies[0]['ekor']) : 1;
        foreach ($topSpecies as &$sp) {
            $sp['pct_of_top'] = $sp['ekor'] > 0 ? max(8, (int) round(($sp['ekor'] / $maxSpeciesEkor) * 100)) : 0;
        }
        unset($sp);

        usort($gridTotals, fn (array $a, array $b): int => $b['ekor'] <=> $a['ekor'] ?: $b['temuan'] <=> $a['temuan']);
        $topGrids = array_slice($gridTotals, 0, 5);

        usort($unitTotals, fn (array $a, array $b): int => $b['laporan'] <=> $a['laporan'] ?: $b['ekor'] <=> $a['ekor']);
        foreach ($unitTotals as &$ut) {
            $ut['pct_validasi'] = $ut['laporan'] > 0 ? round(($ut['tervalidasi'] / $ut['laporan']) * 100, 1) : 0.0;
        }
        unset($ut);

        $totalPending = max(0, $totalLaporan - $totalTervalidasi);

        return [
            'kpi' => [
                'total_laporan' => $totalLaporan,
                'total_tervalidasi' => $totalTervalidasi,
                'total_pending' => $totalPending,
                'persen_tervalidasi' => $totalLaporan > 0 ? round(($totalTervalidasi / $totalLaporan) * 100, 1) : 0.0,
                'total_ekor' => $totalEkor,
                'total_amunisi' => $totalAmunisi,
                'total_foto' => $totalFoto,
                'avg_risk_score' => $totalLaporan > 0 ? round($scoreSum / $totalLaporan, 1) : 0.0,
                'max_risk_score' => $maxRiskScore,
                'hotspot_grid' => $topGrids[0]['grid'] ?? '-',
                'hotspot_area' => $topGrids[0]['area'] ?? '-',
                'dominant_species' => $topSpecies[0]['nama_satwa'] ?? '-',
                'peak_day' => $peakDayNumber,
                'peak_day_count' => $peakDayCount,
            ],
            'weekly_trend' => $weeklyBuckets,
            'daily_trend' => array_values($dailyBuckets),
            'time_slots' => array_values($timeSlots),
            'risk_breakdown' => $riskBuckets,
            'taxonomy_breakdown' => array_values($taxonomyBuckets),
            'top_species' => $topSpecies,
            'top_grids' => $topGrids,
            'unit_breakdown' => $unitTotals,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function initializeWeeklyBuckets(int $daysInMonth): array
    {
        $buckets = [
            ['label' => 'Minggu I', 'range' => '1–7', 'laporan' => 0, 'tervalidasi' => 0, 'pending' => 0, 'ekor' => 0, 'kritis_tinggi' => 0, 'amunisi' => 0],
            ['label' => 'Minggu II', 'range' => '8–14', 'laporan' => 0, 'tervalidasi' => 0, 'pending' => 0, 'ekor' => 0, 'kritis_tinggi' => 0, 'amunisi' => 0],
            ['label' => 'Minggu III', 'range' => '15–21', 'laporan' => 0, 'tervalidasi' => 0, 'pending' => 0, 'ekor' => 0, 'kritis_tinggi' => 0, 'amunisi' => 0],
            ['label' => 'Minggu IV', 'range' => '22–28', 'laporan' => 0, 'tervalidasi' => 0, 'pending' => 0, 'ekor' => 0, 'kritis_tinggi' => 0, 'amunisi' => 0],
        ];

        if ($daysInMonth > 28) {
            $buckets[] = [
                'label' => 'Minggu V',
                'range' => $daysInMonth === 29 ? '29' : '29–'.$daysInMonth,
                'laporan' => 0,
                'tervalidasi' => 0,
                'pending' => 0,
                'ekor' => 0,
                'kritis_tinggi' => 0,
                'amunisi' => 0,
            ];
        }

        return $buckets;
    }

    private function resolveHourWib(Laporan $laporan): ?int
    {
        if (! empty($laporan->jam)) {
            $parts = explode(':', (string) $laporan->jam);
            if (is_numeric($parts[0])) {
                return max(0, min(23, (int) $parts[0]));
            }
        }

        if ($laporan->created_at) {
            return (int) $laporan->created_at->copy()->timezone('Asia/Jakarta')->format('G');
        }

        return null;
    }

    private function resolveTimeSlotKey(?int $hourWib): string
    {
        if ($hourWib === null) {
            return 'pagi';
        }

        if ($hourWib >= 6 && $hourWib < 10) {
            return 'pagi';
        }

        if ($hourWib >= 10 && $hourWib < 14) {
            return 'siang';
        }

        if ($hourWib >= 14 && $hourWib < 18) {
            return 'sore';
        }

        return 'malam';
    }

    /**
     * @param  array<string, string>  $catalogCategories
     */
    private function classifyTaxonomy(string $namaSatwa, array $catalogCategories): string
    {
        $normalized = strtolower(trim($namaSatwa));
        if (isset($catalogCategories[$normalized])) {
            $cat = $catalogCategories[$normalized];
            if (in_array($cat, ['burung', 'reptil', 'mamalia'], true)) {
                return $cat;
            }
        }

        if (str_contains($normalized, 'biawak') || str_contains($normalized, 'ular') || str_contains($normalized, 'sanca') || str_contains($normalized, 'kadal')) {
            return 'reptil';
        }

        if (str_contains($normalized, 'kera') || str_contains($normalized, 'monyet') || str_contains($normalized, 'garangan') || str_contains($normalized, 'berang') || str_contains($normalized, 'tikus') || str_contains($normalized, 'kucing') || str_contains($normalized, 'anjing')) {
            return 'mamalia';
        }

        if (str_contains($normalized, 'burung') || str_contains($normalized, 'blekok') || str_contains($normalized, 'kuntul') || str_contains($normalized, 'cangak') || str_contains($normalized, 'pecuk') || str_contains($normalized, 'elang') || str_contains($normalized, 'layang') || str_contains($normalized, 'walet') || str_contains($normalized, 'gereja') || str_contains($normalized, 'dara') || str_contains($normalized, 'merpati')) {
            return 'burung';
        }

        return 'lainnya';
    }

    /**
     * @param  array<string, array{grid: string, area: string, temuan: int, ekor: int}>  $gridTotals
     */
    private function accumulateGrid(array &$gridTotals, string $gridCode, string $area, int $qty): void
    {
        $key = $gridCode !== '' ? $gridCode : '-';
        if (! isset($gridTotals[$key])) {
            $gridTotals[$key] = [
                'grid' => $key,
                'area' => $area,
                'temuan' => 0,
                'ekor' => 0,
            ];
        }

        $gridTotals[$key]['temuan']++;
        $gridTotals[$key]['ekor'] += $qty;
    }

    /**
     * Susun narasi Kata Pengantar resmi berbasis metrik aktual bulan berjalan untuk lampiran audit kelaikudaraan.
     *
     * @param  array<string, mixed>  $stats
     * @return array{has_data: bool, headline: string, paragraphs: array<int, string>, recommendation: string}
     */
    private function buildForeword(string $periodeLabel, string $unitLabel, array $stats): array
    {
        $kpi = $stats['kpi'];

        if ($kpi['total_laporan'] === 0) {
            return [
                'has_data' => false,
                'headline' => 'Keterangan Rekapitulasi Nihil Periode '.$periodeLabel,
                'paragraphs' => [
                    'Buku Log Bulanan Wildlife Hazard Management System (WHMS) ini diterbitkan secara resmi oleh unit pengelola keselamatan operasi sisi udara Bandar Udara Internasional Juanda sebagai dokumentasi kepatuhan terhadap ICAO Annex 14 Bab 9, ICAO Doc 9137 Part 3, dan ketentuan Direktorat Kelaikudaraan dan Pengoperasian Pesawat Udara (DKPPU).',
                    sprintf(
                        'Berdasarkan verifikasi pangkalan data operasional untuk periode %s dengan cakupan %s, tercatat NIHIL (0) penerbitan Berita Acara temuan satwa liar pada parameter filter yang dipilih.',
                        $periodeLabel,
                        $unitLabel
                    ),
                ],
                'recommendation' => 'Pertahankan jadwal patroli rutin preventif sisi udara pada jam sibuk penerbangan serta lakukan pencatatan berkala apabila ditemukan pergerakan satwa di sekitar koridor landasan maupun drainase perimeter.',
            ];
        }

        $paragraph1 = sprintf(
            'Puji syukur kami panjatkan atas tersusunnya Buku Log Bulanan Pemantauan dan Pengendalian Bahaya Satwa Liar (WHMS) Bandar Udara Internasional Juanda periode %s (%s). Dokumen kompilasi ini disusun sebagai arsip resmi audit keselamatan penerbangan sesuai standar ICAO Annex 14 Bab 9, ICAO Doc 9137 Part 3, dan regulasi DKPPU Ditjen Hubud.',
            $periodeLabel,
            $unitLabel
        );

        $paragraph2 = sprintf(
            'Sepanjang bulan %s, tercatat sebanyak %d Berita Acara kejadian dengan akumulasi populasi terpantau sebesar %d ekor satwa liar dan %d foto dokumentasi terverifikasi. Dari total laporan tersebut, %d dokumen (%s%%) telah disahkan oleh Pejabat Validator / Safety Manager dengan rata-rata skor risiko ICAO sebesar %s/12 serta total konsumsi %d butir/dentuman amunisi dispersal.',
            $periodeLabel,
            $kpi['total_laporan'],
            $kpi['total_ekor'],
            $kpi['total_foto'],
            $kpi['total_tervalidasi'],
            number_format((float) $kpi['persen_tervalidasi'], 1),
            number_format((float) $kpi['avg_risk_score'], 1),
            $kpi['total_amunisi']
        );

        $highRiskCount = ($stats['risk_breakdown']['kritis']['count'] ?? 0) + ($stats['risk_breakdown']['tinggi']['count'] ?? 0);
        $paragraph3 = sprintf(
            'Secara spasial dan biologis, konsentrasi temuan tertinggi teridentifikasi pada Grid %s (%s) dengan spesies paling dominan yaitu %s. Terdapat %d kejadian berkategori Risiko Kritis/Tinggi yang menuntut pengawasan intensif petugas sisi udara bersama Tower ATC.',
            $kpi['hotspot_grid'],
            $kpi['hotspot_area'],
            $kpi['dominant_species'],
            $highRiskCount
        );

        $recommendation = $kpi['total_pending'] > 0
            ? sprintf('Segera selesaikan verifikasi %d Berita Acara berstatus Menunggu Validasi serta fokuskan patroli preventif dan pemeliharaan vegetasi/drainase di sekitar Grid %s.', $kpi['total_pending'], $kpi['hotspot_grid'])
            : sprintf('Seluruh %d Berita Acara pada periode ini telah tuntas divalidasi. Lanjutkan pengawasan berkala di titik hotspot Grid %s dan evaluasi efektivitas alat dispersal.', $kpi['total_laporan'], $kpi['hotspot_grid']);

        return [
            'has_data' => true,
            'headline' => 'Kata Pengantar & Ringkasan Eksekutif Keselamatan Sisi Udara',
            'paragraphs' => [$paragraph1, $paragraph2, $paragraph3],
            'recommendation' => $recommendation,
        ];
    }
}
