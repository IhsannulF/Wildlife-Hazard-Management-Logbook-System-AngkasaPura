<?php

namespace App\Services;

use App\Models\DetailSatwa;
use App\Models\JenisSatwa;
use App\Models\Laporan;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Carbon\Carbon;
use Dompdf\Canvas;
use Dompdf\FontMetrics;

class BeritaAcaraPdfService
{
    public function __construct(
        private PdfMediaService $mediaService,
        private IcaoRiskMatrixService $riskService,
    ) {}

    /**
     * Ekstrak perkiraan jumlah butir/dentuman amunisi dari uraian metode pengusiran.
     */
    public static function extractAmunisiCount(?string $detail): int
    {
        if (empty($detail)) {
            return 0;
        }

        if (preg_match('/(\d+)\s*(butir|pcs|biji|round|rounds|peluru|tembakan|dentuman)/i', $detail, $matches)) {
            return (int) $matches[1];
        }

        if (stripos($detail, 'petasan') !== false || stripos($detail, 'flare') !== false || stripos($detail, 'pyrotechnic') !== false) {
            return 1;
        }

        return 0;
    }

    /**
     * Susun payload terstruktur untuk 1 lembar Berita Acara A4 agar dapat dipakai ulang oleh Single BA & Bundel Bulanan.
     *
     * @param  array{max_width?: int, jpeg_quality?: int}  $mediaOptions
     * @return array<string, mixed>
     */
    public function buildPayload(Laporan $laporan, array $mediaOptions = []): array
    {
        $laporan->loadMissing([
            'user:id,namalengkap,username,jabatan,role',
            'validator:id,namalengkap,username,jabatan,role,tanda_tangan',
            'detailSatwa.fotoLaporan',
            'fotoLaporan',
            'tanggapan.user:id,namalengkap,username,jabatan,role',
        ]);

        $noLaporan = $laporan->no_laporan ?: Laporan::generateNoLaporan(
            $laporan->unit_kerja ?? 'AMC',
            $laporan->tanggal?->format('Y-m-d'),
            $laporan->id
        );

        $tanggalFormatted = $laporan->tanggal
            ? Carbon::parse($laporan->tanggal->format('Y-m-d'))->locale('id')->translatedFormat('d F Y')
            : '-';

        $jamFormatted = ! empty($laporan->jam)
            ? substr((string) $laporan->jam, 0, 5).' WIB'
            : ($laporan->created_at
                ? $laporan->created_at->copy()->timezone('Asia/Jakarta')->format('H:i').' WIB'
                : '-');

        $createdAtWib = $laporan->created_at
            ? $laporan->created_at->copy()->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y, H:i').' WIB'
            : '-';

        $validatedAtWib = $laporan->validated_at
            ? $laporan->validated_at->copy()->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y, H:i').' WIB'
            : null;

        $printedAtWib = now('Asia/Jakarta')->locale('id')->translatedFormat('d/m/Y H:i').' WIB';

        $satwaCalculationInput = $laporan->detailSatwa->map(fn (DetailSatwa $ds): array => [
            'nama' => $ds->nama_satwa,
            'jumlah' => (int) $ds->jumlah,
        ])->values()->all();

        $riskBreakdown = $this->riskService->calculate(
            $satwaCalculationInput,
            $laporan->area_inspeksi,
            $laporan->grid_lokasi
        );

        $recordedScore = $laporan->risk_score !== null && $laporan->risk_score > 0
            ? (int) $laporan->risk_score
            : $riskBreakdown['score'];

        $recordedLevel = ! empty($laporan->risk_level)
            ? strtolower((string) $laporan->risk_level)
            : $this->riskService->determineLevel($recordedScore);

        $riskStyle = static::resolveRiskStyle($recordedLevel);

        $satwaMatrix = [];
        $totalEkor = 0;

        foreach ($laporan->detailSatwa as $idx => $ds) {
            $jumlah = max(0, (int) $ds->jumlah);
            $totalEkor += $jumlah;

            $satwaMatrix[] = [
                'no' => $idx + 1,
                'nama_satwa' => $ds->nama_satwa ?: '-',
                'nama_ilmiah' => JenisSatwa::resolveNamaIlmiah((string) $ds->nama_satwa, '-'),
                'jumlah' => $jumlah,
                'grid' => strtoupper((string) ($ds->grid ?: ($laporan->grid_lokasi ?: '-'))),
                'has_foto' => $ds->fotoLaporan->isNotEmpty(),
            ];
        }

        $gpsCoordinates = ($laporan->gps_lat !== null && $laporan->gps_lng !== null)
            ? number_format((float) $laporan->gps_lat, 6, '.', '').', '.number_format((float) $laporan->gps_lng, 6, '.', '')
            : null;

        $amunisiCount = static::extractAmunisiCount($laporan->detail_pengusiran);

        $tanggapanHistory = $laporan->tanggapan
            ->sortBy('id')
            ->values()
            ->map(function ($t): array {
                $author = $t->user
                    ? ($t->user->namalengkap ?: $t->user->username)
                    : 'Safety Unit';

                $role = $t->user
                    ? $t->user->validator_display_title
                    : 'Petugas';

                return [
                    'kategori' => strtoupper((string) ($t->kategori_tanggapan ?: 'instruksi')),
                    'isi' => (string) ($t->isi ?: '-'),
                    'author' => $author,
                    'role' => $role,
                    'timestamp_wib' => $t->created_at
                        ? $t->created_at->copy()->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y, H:i').' WIB'
                        : '-',
                ];
            })
            ->all();

        $watermarkedPhotos = $this->collectWatermarkedPhotos($laporan, [
            'no_laporan' => $noLaporan,
            'tanggal_jam' => trim($tanggalFormatted.' • '.$jamFormatted),
            'lokasi' => trim(($laporan->area_inspeksi ?: 'Sisi Udara').' [Grid '.strtoupper((string) ($laporan->grid_lokasi ?: '-')).']'),
            'unit' => $laporan->unit_kerja ?: 'AMC',
            'gps' => $gpsCoordinates,
        ], $mediaOptions);

        $isValidated = strtolower((string) $laporan->status) === 'sudah';
        $pelaporName = $laporan->nama_petugas ?: ($laporan->user?->namalengkap ?: ($laporan->user?->username ?: 'Petugas Lapangan'));
        $pelaporUnit = $laporan->user?->jabatan ?: ($laporan->unit_kerja ?: 'Unit Airside Operations');

        $validatorName = $laporan->validator
            ? ($laporan->validator->namalengkap ?: $laporan->validator->username)
            : ($isValidated ? 'Safety Manager / Admin WHMS' : null);
        $validatorTitle = $laporan->validator?->validator_display_title ?: 'Safety Manager';

        $pelaporSignatureUri = $this->mediaService->normalizeSignatureDataUri($laporan->tanda_tangan);
        $validatorSignatureUri = $isValidated
            ? $this->mediaService->normalizeSignatureDataUri($laporan->validator_signature ?: $laporan->validator?->tanda_tangan)
            : null;

        return [
            'laporan' => $laporan,
            'logo_data_uri' => $this->mediaService->getInjourneyLogoPdfSrc(),
            'no_laporan' => $noLaporan,
            'tanggal_formatted' => $tanggalFormatted,
            'jam_formatted' => $jamFormatted,
            'created_at_wib' => $createdAtWib,
            'printed_at_wib' => $printedAtWib,
            'is_validated' => $isValidated,
            'status_label' => $isValidated ? 'TERVALIDASI / SELESAI DITANGANI' : 'MENUNGGU VALIDASI / DALAM PENANGANAN',
            'status_short' => $isValidated ? 'TERVALIDASI' : 'MENUNGGU VALIDASI',
            'gps_coordinates' => $gpsCoordinates,
            'risk' => [
                'score' => $recordedScore,
                'level' => $recordedLevel,
                'label' => $this->riskService->getLabel($recordedLevel),
                'probability' => $riskBreakdown['probability'],
                'severity' => $riskBreakdown['severity'],
                'zone_score' => $riskBreakdown['zone_score'],
                'species_score' => $riskBreakdown['species_score'],
                'total_satwa' => max($totalEkor, $riskBreakdown['total_satwa']),
                'bg' => $riskStyle['bg'],
                'border' => $riskStyle['border'],
                'text' => $riskStyle['text'],
                'badge_bg' => $riskStyle['badge_bg'],
            ],
            'satwa_matrix' => $satwaMatrix,
            'total_spesies' => count($satwaMatrix),
            'total_ekor' => $totalEkor,
            'amunisi_terpakai' => $amunisiCount,
            'tanggapan_history' => $tanggapanHistory,
            'watermarked_photos' => $watermarkedPhotos,
            'signatures' => [
                'pelapor' => [
                    'nama' => $pelaporName,
                    'jabatan' => $pelaporUnit,
                    'signature_uri' => $pelaporSignatureUri,
                    'timestamp_wib' => $createdAtWib,
                ],
                'validator' => [
                    'is_validated' => $isValidated,
                    'nama' => $validatorName,
                    'jabatan' => $validatorTitle,
                    'signature_uri' => $validatorSignatureUri,
                    'timestamp_wib' => $validatedAtWib ?: ($isValidated ? $createdAtWib : null),
                    'has_digital_seal' => $isValidated && $validatorSignatureUri === null,
                ],
            ],
        ];
    }

    /**
     * Render markup HTML siap-DomPDF untuk keperluan pratinjau struktur atau pengujian.
     *
     * @param  array{max_width?: int, jpeg_quality?: int}  $mediaOptions
     */
    public function renderHtml(Laporan $laporan, array $mediaOptions = []): string
    {
        $payload = $this->buildPayload($laporan, $mediaOptions);

        return view('reports.berita-acara-pdf', [
            'ba' => $payload,
        ])->render();
    }

    /**
     * Hasilkan instance DomPDF A4 portrait yang telah dirender lengkap dengan nomor halaman resmi.
     *
     * @param  array{max_width?: int, jpeg_quality?: int}  $mediaOptions
     */
    public function buildPdf(Laporan $laporan, array $mediaOptions = []): DomPdfWrapper
    {
        $payload = $this->buildPayload($laporan, $mediaOptions);

        $pdf = Pdf::loadView('reports.berita-acara-pdf', [
            'ba' => $payload,
        ])->setPaper('a4', 'portrait');

        $pdf->addInfo([
            'Title' => 'Berita Acara '.$payload['no_laporan'],
            'Author' => 'WHMS InJourney Airports',
            'Subject' => 'Berita Acara Pemantauan & Pengendalian Satwa Liar',
        ]);

        $pdf->render();
        $this->attachPageFooterScript(
            $pdf,
            'WHMS InJourney Airports • '.$payload['no_laporan'],
            'Dicetak '.$payload['printed_at_wib']
        );

        return $pdf;
    }

    public function buildFilename(Laporan $laporan): string
    {
        $noLaporan = $laporan->no_laporan ?: Laporan::generateNoLaporan(
            $laporan->unit_kerja ?? 'AMC',
            $laporan->tanggal?->format('Y-m-d'),
            $laporan->id
        );

        $sanitized = preg_replace('/[^A-Za-z0-9\-_]+/', '_', str_replace('/', '_', $noLaporan));

        return 'Berita_Acara_'.trim((string) $sanitized, '_').'.pdf';
    }

    /**
     * Bubuhkan footer penomoran halaman konsisten via CPDF canvas tanpa mengaktifkan inline PHP di Blade.
     */
    public function attachPageFooterScript(DomPdfWrapper $pdf, string $leftLabel, string $rightPrefix): void
    {
        $canvas = $pdf->getDomPDF()->getCanvas();
        if (! $canvas instanceof Canvas) {
            return;
        }

        $font = $pdf->getDomPDF()->getFontMetrics()->getFont('DejaVu Sans', 'normal');
        $width = (float) $canvas->get_width();
        $height = (float) $canvas->get_height();
        $marginX = 39.69; // 14mm dalam poin PDF
        $lineY = $height - 36.0;
        $textY = $height - 29.0;

        $canvas->page_script(function (
            int $pageNumber,
            int $pageCount,
            Canvas $pageCanvas,
            FontMetrics $fontMetrics
        ) use ($leftLabel, $rightPrefix, $font, $width, $marginX, $lineY, $textY): void {
            $pageCanvas->line($marginX, $lineY, $width - $marginX, $lineY, [0.80, 0.88, 0.92], 0.75);
            $pageCanvas->text($marginX, $textY, $leftLabel, $font, 7.5, [0.39, 0.45, 0.55]);

            $rightText = $rightPrefix.' • Halaman '.$pageNumber.' dari '.$pageCount;
            $rightTextWidth = $fontMetrics->getTextWidth($rightText, $font, 7.5);
            $pageCanvas->text(max($marginX, $width - $marginX - $rightTextWidth), $textY, $rightText, $font, 7.5, [0.39, 0.45, 0.55]);
        });
    }

    /**
     * Kumpulkan dan watermark seluruh foto bukti laporan secara berurutan tanpa duplikasi.
     *
     * @param  array{no_laporan: string, tanggal_jam: string, lokasi: string, unit: string, gps: ?string}  $baseMeta
     * @param  array{max_width?: int, jpeg_quality?: int}  $mediaOptions
     * @return array<int, array{caption: string, subcaption: string, data_uri: string}>
     */
    private function collectWatermarkedPhotos(Laporan $laporan, array $baseMeta, array $mediaOptions): array
    {
        $maxWidth = (int) ($mediaOptions['max_width'] ?? 900);
        $jpegQuality = (int) ($mediaOptions['jpeg_quality'] ?? 78);

        $photos = [];
        $processedIds = [];

        foreach ($laporan->detailSatwa as $ds) {
            foreach ($ds->fotoLaporan as $foto) {
                $processedIds[$foto->id] = true;
                $grid = strtoupper((string) ($ds->grid ?: ($laporan->grid_lokasi ?: '-')));
                $meta = array_merge($baseMeta, [
                    'lokasi' => trim(($laporan->area_inspeksi ?: 'Sisi Udara').' [Grid '.$grid.']'),
                ]);

                $dataUri = $this->mediaService->renderWatermarkedPhotoFromRecord($foto, $meta, $maxWidth, $jpegQuality);
                if ($dataUri !== null) {
                    $photos[] = [
                        'caption' => $ds->nama_satwa.' ('.max(1, (int) $ds->jumlah).' ekor)',
                        'subcaption' => 'Grid '.$grid.' • Bukti Temuan Satwa',
                        'data_uri' => $dataUri,
                    ];
                }
            }
        }

        foreach ($laporan->fotoLaporan as $foto) {
            if (isset($processedIds[$foto->id])) {
                continue;
            }
            $processedIds[$foto->id] = true;

            $dataUri = $this->mediaService->renderWatermarkedPhotoFromRecord($foto, $baseMeta, $maxWidth, $jpegQuality);
            if ($dataUri !== null) {
                $isExtra = $foto->tipe === 'extra';
                $photos[] = [
                    'caption' => $isExtra ? 'Dokumentasi Satwa Non-Katalog / Tambahan' : 'Dokumentasi Lapangan Sisi Udara',
                    'subcaption' => 'Grid '.strtoupper((string) ($laporan->grid_lokasi ?: '-')).' • Lampiran Resmi',
                    'data_uri' => $dataUri,
                ];
            }
        }

        return $photos;
    }

    /**
     * Petakan warna tema risiko ICAO agar konsisten antara Single BA dan Bundel Bulanan.
     *
     * @return array{bg: string, border: string, text: string, badge_bg: string}
     */
    public static function resolveRiskStyle(string $level): array
    {
        return match (strtolower($level)) {
            'kritis' => [
                'bg' => '#fff1f2',
                'border' => '#e11d48',
                'text' => '#9f1239',
                'badge_bg' => '#be123c',
            ],
            'tinggi' => [
                'bg' => '#fffbeb',
                'border' => '#d97706',
                'text' => '#92400e',
                'badge_bg' => '#d97706',
            ],
            'sedang' => [
                'bg' => '#fefce8',
                'border' => '#ca8a04',
                'text' => '#854d0e',
                'badge_bg' => '#a16207',
            ],
            default => [
                'bg' => '#ecfdf5',
                'border' => '#16a34a',
                'text' => '#166534',
                'badge_bg' => '#15803d',
            ],
        };
    }
}
