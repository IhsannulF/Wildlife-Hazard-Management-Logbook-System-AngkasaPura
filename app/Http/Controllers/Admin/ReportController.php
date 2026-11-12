<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MonthlyBundlePdfRequest;
use App\Models\JenisSatwa;
use App\Models\Laporan;
use App\Services\BeritaAcaraPdfService;
use App\Services\MonthlyLogbookPdfService;
use App\Services\XlsxExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Tampilan pratinjau cetak Berita Acara dinas per laporan.
     */
    public function cetak(int $id)
    {
        $lap = $this->findAuthorizedLaporan($id);

        $satwa_list = [];
        foreach ($lap->detailSatwa as $ds) {
            $foto = $ds->fotoLaporan->first();
            $satwa_list[] = [
                'nama' => $ds->nama_satwa,
                'jumlah' => $ds->jumlah,
                'grid' => $ds->grid,
                'foto_path' => $foto ? 'storage/uploads/satwa/'.$foto->nama_file : ($ds->foto_path ? 'images/'.$ds->foto_path : ''),
            ];
        }

        $foto_extra = $lap->fotoLaporan
            ->where('tipe', 'extra')
            ->pluck('nama_file')
            ->toArray();

        return view('reports.cetak', compact('lap', 'satwa_list', 'foto_extra'));
    }

    /**
     * Menghasilkan dan mengunduh (atau men-stream) berkas Berita Acara PDF A4 langsung dari server.
     */
    public function downloadPdf(Request $request, int $id, BeritaAcaraPdfService $pdfService): Response
    {
        $lap = $this->findAuthorizedLaporan($id);
        $pdf = $pdfService->buildPdf($lap);
        $filename = $pdfService->buildFilename($lap);

        return $request->boolean('inline')
            ? $pdf->stream($filename)
            : $pdf->download($filename);
    }

    /**
     * Menghitung ringkasan jumlah BA, ekor satwa, dan foto lampiran untuk menu Bundel Logbook Bulanan.
     */
    public function countMonthlyBundle(
        MonthlyBundlePdfRequest $request,
        MonthlyLogbookPdfService $bundleService
    ): JsonResponse {
        $summary = $bundleService->countSummary(
            $request->bulan(),
            $request->tahun(),
            $request->statusFilter(),
            $request->unitKerjaFilter()
        );

        return response()->json(array_merge([
            'status' => 'success',
        ], $summary));
    }

    /**
     * Mengompilasi dan mengunduh (atau men-stream) Bundel Logbook Bulanan PDF A4 (Cover, Pengantar, Statistik, & seluruh BA).
     */
    public function downloadMonthlyBundlePdf(
        MonthlyBundlePdfRequest $request,
        MonthlyLogbookPdfService $bundleService
    ): Response {
        $binary = $bundleService->buildOrGetCachedPdfBinary(
            $request->bulan(),
            $request->tahun(),
            $request->statusFilter(),
            $request->unitKerjaFilter(),
            auth()->user()
        );

        $filename = $bundleService->buildFilename(
            $request->bulan(),
            $request->tahun(),
            $request->unitKerjaFilter()
        );

        $disposition = ($request->isInline() ? 'inline' : 'attachment').'; filename="'.$filename.'"';

        return response($binary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition,
            'Content-Length' => (string) strlen($binary),
            'Cache-Control' => 'private, max-age=0, must-revalidate',
        ]);
    }

    /**
     * Muat laporan dengan relasi lengkap serta pastikan hak akses pemilik/admin terpenuhi.
     */
    protected function findAuthorizedLaporan(int $id): Laporan
    {
        $lap = Laporan::with([
            'user:id,namalengkap,username,jabatan,role',
            'validator:id,namalengkap,username,jabatan,role,tanda_tangan',
            'detailSatwa.fotoLaporan',
            'fotoLaporan',
            'tanggapan.user:id,namalengkap,username,jabatan,role',
        ])->findOrFail($id);

        if (auth()->check() && auth()->user()->role !== 'admin' && $lap->user_id !== auth()->id()) {
            abort(403, 'Anda tidak berhak mengakses atau mencetak berita acara milik petugas lain.');
        }

        return $lap;
    }

    /**
     * Halaman Konfigurasi & Filter Ekspor Excel (Interactive Options Page).
     */
    public function index(Request $request)
    {
        $availableYears = Laporan::whereNotNull('tanggal')
            ->selectRaw('YEAR(tanggal) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->map(fn ($year): int => (int) $year)
            ->toArray();

        $currentYear = (int) date('Y');
        if (! in_array($currentYear, $availableYears, true)) {
            array_unshift($availableYears, $currentYear);
            rsort($availableYears);
        }

        $availableMonths = MonthlyLogbookPdfService::monthOptions();

        $availableUnits = Laporan::whereNotNull('unit_kerja')
            ->where('unit_kerja', '!=', '')
            ->distinct()
            ->orderBy('unit_kerja')
            ->pluck('unit_kerja')
            ->toArray();

        $availableGrids = Laporan::whereNotNull('grid_lokasi')
            ->where('grid_lokasi', '!=', '')
            ->distinct()
            ->orderBy('grid_lokasi')
            ->pluck('grid_lokasi')
            ->toArray();

        $availableSatwa = JenisSatwa::orderBy('nama')->get();

        $columns = $this->getAvailableColumns();
        $presets = $this->getPresets();

        // Parameter pra-pilih dari URL jika ada
        $preSelectedTahun = (int) $request->query('tahun', date('Y'));
        $preSelectedBulan = max(1, min(12, (int) $request->query('bulan', date('n'))));
        $preSelectedZona = $request->query('zona', '');
        $preSelectedPreset = $request->query('preset', 'dkppu');

        return view('admin.export.index', [
            'availableYears' => $availableYears,
            'availableMonths' => $availableMonths,
            'availableUnits' => $availableUnits,
            'availableGrids' => $availableGrids,
            'availableSatwa' => $availableSatwa,
            'columns' => $columns,
            'presets' => $presets,
            'preSelectedTahun' => $preSelectedTahun,
            'preSelectedBulan' => $preSelectedBulan,
            'preSelectedZona' => $preSelectedZona,
            'preSelectedPreset' => $preSelectedPreset,
        ]);
    }

    /**
     * Backward-compatibility alias jika exportExcel diakses langsung.
     */
    public function exportExcel(Request $request)
    {
        return $this->index($request);
    }

    /**
     * Endpoint API JSON untuk menghitung jumlah baris data yang cocok secara live.
     */
    public function countMatching(Request $request)
    {
        $query = $this->buildFilteredQuery($request);
        $totalLaporan = $query->count();

        // Hitung total ekor dan perkiraan baris per satwa
        $laporans = $query->with('detailSatwa')->get();
        $totalEkor = 0;
        $totalSatwaRows = 0;

        foreach ($laporans as $lap) {
            $satwaCount = $lap->detailSatwa->count();
            $totalSatwaRows += ($satwaCount > 0 ? $satwaCount : 1);
            foreach ($lap->detailSatwa as $ds) {
                $totalEkor += (int) $ds->jumlah;
            }
        }

        return response()->json([
            'status' => 'success',
            'total_laporan' => $totalLaporan,
            'total_ekor' => $totalEkor,
            'total_satwa_rows' => $totalSatwaRows,
        ]);
    }

    /**
     * Memproses dan mengunduh berkas Spreadsheet (Excel .xlsx / .csv) sesuai konfigurasi admin.
     */
    public function downloadExcel(Request $request, XlsxExportService $xlsxExportService)
    {
        $selectedColumns = $request->input('columns', []);
        $allColumnsDef = $this->getAvailableColumns();

        // Jika tidak ada kolom yang dipilih, fallback ke default DKPPU
        if (empty($selectedColumns) || ! is_array($selectedColumns)) {
            $selectedColumns = $this->getPresets()['dkppu']['columns'];
        }

        // Filter kolom yang valid
        $activeColumns = [];
        foreach ($selectedColumns as $colKey) {
            if (isset($allColumnsDef[$colKey])) {
                $activeColumns[$colKey] = $allColumnsDef[$colKey];
            }
        }

        $modeBaris = $request->input('mode_baris', 'per_satwa');
        $includeSummary = $request->boolean('include_summary', true);
        $fileFormat = $request->input('file_format', 'xls');

        $query = $this->buildFilteredQuery($request);
        $laporans = $query->with(['user', 'detailSatwa', 'tanggapan.user'])->orderBy('tanggal', 'asc')->get();

        // Siapkan baris data terstruktur
        $rows = [];
        $noUrut = 1;
        $totalEkorKumulatif = 0;
        $totalAmunisiKumulatif = 0;
        $totalSelesaiCount = 0;

        foreach ($laporans as $lap) {
            $isSelesai = strtolower($lap->status) === 'sudah';
            if ($isSelesai) {
                $totalSelesaiCount++;
            }

            // Validator info
            $validatorName = '-';
            if ($lap->tanggapan && $lap->tanggapan->isNotEmpty() && $lap->tanggapan->first()->user) {
                $validatorName = $lap->tanggapan->first()->user->namalengkap ?: $lap->tanggapan->first()->user->username;
            } elseif ($isSelesai) {
                $validatorName = 'Safety Unit / Admin';
            }

            // Amunisi info
            $amunisiExtracted = $this->extractAmunisi($lap->detail_pengusiran);
            if ($amunisiExtracted > 0) {
                $totalAmunisiKumulatif += $amunisiExtracted;
            }

            // Koordinat GPS info
            $gpsText = '-';
            if (! empty($lap->extra_data) && is_array($lap->extra_data)) {
                if (! empty($lap->extra_data['gps_lat']) && ! empty($lap->extra_data['gps_lng'])) {
                    $gpsText = $lap->extra_data['gps_lat'].', '.$lap->extra_data['gps_lng'];
                }
            }

            // Jam/waktu
            $waktuFormatted = $lap->created_at ? $lap->created_at->format('H:i').' WIB' : '08:00 WIB';

            if ($modeBaris === 'per_satwa' && $lap->detailSatwa->isNotEmpty()) {
                // Tiap temuan satwa menjadi 1 baris mandiri (Standar DKPPU)
                foreach ($lap->detailSatwa as $ds) {
                    $totalEkorKumulatif += (int) $ds->jumlah;
                    $latinName = $this->getLatinName($ds->nama_satwa);
                    $icaoRisk = $this->getIcaoRisk($ds->nama_satwa, (int) $ds->jumlah, $ds->grid ?: $lap->grid_lokasi);

                    $rowData = [
                        'no' => $noUrut++,
                        'id_laporan' => '#'.$lap->id,
                        'tanggal' => $lap->tanggal ? $lap->tanggal->format('d/m/Y') : '-',
                        'waktu' => $waktuFormatted,
                        'nama_petugas' => $lap->nama_petugas ?: ($lap->user ? $lap->user->namalengkap : '-'),
                        'unit_kerja' => $lap->unit_kerja ?: '-',
                        'validator' => $validatorName,
                        'area_inspeksi' => $lap->area_inspeksi ?: '-',
                        'grid_lokasi' => $ds->grid ?: ($lap->grid_lokasi ?: '-'),
                        'koordinat_gps' => $gpsText,
                        'kondisi_cuaca' => $lap->kondisi_cuaca ?: '-',
                        'kondisi_apron' => $lap->kondisi_apron ?: 'Hidup',
                        'nama_satwa' => $ds->nama_satwa ?: '-',
                        'nama_latin' => $latinName,
                        'jumlah_ekor' => (int) $ds->jumlah,
                        'ciri_ukuran' => $lap->ciri_ukuran ?: '-',
                        'aktivitas_satwa' => $lap->aktivitas_satwa ?: '-',
                        'kategori_risiko' => $icaoRisk,
                        'tindak_lanjut' => $lap->tindak_lanjut ?: '-',
                        'metode_pengusiran' => $lap->detail_pengusiran ?: '-',
                        'amunisi_terpakai' => $amunisiExtracted > 0 ? $amunisiExtracted.' butir' : '-',
                        'status_penanganan' => $isSelesai ? 'Telah Ditangani' : 'Belum Ditangani',
                    ];
                    $rows[] = $rowData;
                }
            } else {
                // Satu laporan satu baris
                $satwaNames = [];
                $satwaLatin = [];
                $ekorTotalLap = 0;

                if ($lap->detailSatwa->isNotEmpty()) {
                    foreach ($lap->detailSatwa as $ds) {
                        $ekorTotalLap += (int) $ds->jumlah;
                        $satwaNames[] = $ds->nama_satwa.' ('.$ds->jumlah.' ekor)';
                        $latin = $this->getLatinName($ds->nama_satwa);
                        if ($latin !== '-' && ! in_array($latin, $satwaLatin, true)) {
                            $satwaLatin[] = $latin;
                        }
                    }
                } else {
                    $satwaNames[] = 'Satwa tidak tercatat';
                }

                $totalEkorKumulatif += $ekorTotalLap;
                $primarySatwa = $lap->detailSatwa->first()?->nama_satwa ?: '';
                $icaoRisk = $this->getIcaoRisk($primarySatwa, $ekorTotalLap, $lap->grid_lokasi);

                $rowData = [
                    'no' => $noUrut++,
                    'id_laporan' => '#'.$lap->id,
                    'tanggal' => $lap->tanggal ? $lap->tanggal->format('d/m/Y') : '-',
                    'waktu' => $waktuFormatted,
                    'nama_petugas' => $lap->nama_petugas ?: ($lap->user ? $lap->user->namalengkap : '-'),
                    'unit_kerja' => $lap->unit_kerja ?: '-',
                    'validator' => $validatorName,
                    'area_inspeksi' => $lap->area_inspeksi ?: '-',
                    'grid_lokasi' => $lap->grid_lokasi ?: '-',
                    'koordinat_gps' => $gpsText,
                    'kondisi_cuaca' => $lap->kondisi_cuaca ?: '-',
                    'kondisi_apron' => $lap->kondisi_apron ?: 'Hidup',
                    'nama_satwa' => implode('; ', $satwaNames),
                    'nama_latin' => ! empty($satwaLatin) ? implode(', ', $satwaLatin) : '-',
                    'jumlah_ekor' => $ekorTotalLap,
                    'ciri_ukuran' => $lap->ciri_ukuran ?: '-',
                    'aktivitas_satwa' => $lap->aktivitas_satwa ?: '-',
                    'kategori_risiko' => $icaoRisk,
                    'tindak_lanjut' => $lap->tindak_lanjut ?: '-',
                    'metode_pengusiran' => $lap->detail_pengusiran ?: '-',
                    'amunisi_terpakai' => $amunisiExtracted > 0 ? $amunisiExtracted.' butir' : '-',
                    'status_penanganan' => $isSelesai ? 'Telah Ditangani' : 'Belum Ditangani',
                ];
                $rows[] = $rowData;
            }
        }

        $totalLaporanCount = $laporans->count();
        $persenSelesai = $totalLaporanCount > 0 ? round(($totalSelesaiCount / $totalLaporanCount) * 100, 1) : 0;

        $timestamp = date('Ymd_His');
        $filename = "Rekapitulasi_Satwa_Liar_{$timestamp}";

        // Ekspor ke CSV jika dipilih
        if ($fileFormat === 'csv') {
            return $this->generateCsvResponse($filename, $activeColumns, $rows, $includeSummary, [
                'total_ekor' => $totalEkorKumulatif,
                'total_amunisi' => $totalAmunisiKumulatif,
                'persen_selesai' => $persenSelesai,
            ]);
        }

        $summaryData = [
            'total_baris' => count($rows),
            'total_laporan' => $totalLaporanCount,
            'total_ekor' => $totalEkorKumulatif,
            'total_amunisi' => $totalAmunisiKumulatif,
            'persen_selesai' => $persenSelesai,
        ];

        $exportTime = Carbon::now()->translatedFormat('d F Y H:i:s').' WIB';
        $binary = $xlsxExportService->generate(
            $activeColumns,
            $rows,
            $includeSummary,
            $summaryData,
            $request->all(),
            $exportTime
        );

        // Default: Ekspor ke Excel (.xlsx OOXML Asli agar bebas peringatan format Excel)
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}.xlsx\"",
            'Content-Length' => (string) strlen($binary),
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response($binary, 200, $headers);
    }

    /**
     * Membangun query Eloquent dengan filter yang dipilih admin.
     */
    protected function buildFilteredQuery(Request $request)
    {
        $query = Laporan::query();

        // 1. Filter Periode Waktu
        $modePeriode = $request->input('mode_periode', 'all');

        if ($modePeriode === 'tahun' && $request->filled('tahun')) {
            $query->whereYear('tanggal', (int) $request->input('tahun'));
        } elseif ($modePeriode === 'range') {
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('tanggal', [$request->input('start_date'), $request->input('end_date')]);
            } elseif ($request->filled('start_date')) {
                $query->whereDate('tanggal', '>=', $request->input('start_date'));
            } elseif ($request->filled('end_date')) {
                $query->whereDate('tanggal', '<=', $request->input('end_date'));
            }
        } elseif ($request->filled('tahun') && ! $request->filled('mode_periode')) {
            // Fallback jika hanya param ?tahun=YYYY yang dikirim
            $query->whereYear('tanggal', (int) $request->input('tahun'));
        }

        // 2. Filter Unit Kerja
        if ($request->filled('unit_kerja') && $request->input('unit_kerja') !== 'all') {
            $query->where('unit_kerja', $request->input('unit_kerja'));
        }

        // 3. Filter Status Penanganan
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        // 4. Filter Grid Lokasi
        if ($request->filled('grid_lokasi') && $request->input('grid_lokasi') !== 'all') {
            $grid = $request->input('grid_lokasi');
            $query->where(function ($q) use ($grid) {
                $q->where('grid_lokasi', $grid)
                    ->orWhereHas('detailSatwa', function ($sub) use ($grid) {
                        $sub->where('grid', $grid);
                    });
            });
        } elseif ($request->filled('zona') && $request->input('zona') !== '') {
            // Dukungan param zona lama
            $zona = $request->input('zona');
            $query->where(function ($q) use ($zona) {
                $q->where('grid_lokasi', $zona)
                    ->orWhereHas('detailSatwa', function ($sub) use ($zona) {
                        $sub->where('grid', $zona);
                    });
            });
        }

        // 5. Filter Spesies Satwa
        if ($request->filled('nama_satwa') && $request->input('nama_satwa') !== 'all') {
            $namaSatwa = $request->input('nama_satwa');
            $query->whereHas('detailSatwa', function ($sub) use ($namaSatwa) {
                $sub->where('nama_satwa', 'like', "%{$namaSatwa}%");
            });
        }

        return $query;
    }

    /**
     * Definisi seluruh kolom yang dapat dipilih untuk diekspor.
     */
    protected function getAvailableColumns(): array
    {
        return [
            // Kategori: Identitas & Waktu
            'no' => [
                'key' => 'no',
                'label' => 'No',
                'category' => 'identitas',
                'description' => 'Nomor urut data',
                'align' => 'center',
            ],
            'id_laporan' => [
                'key' => 'id_laporan',
                'label' => 'ID Laporan',
                'category' => 'identitas',
                'description' => 'Nomor tiket laporan (#ID)',
                'align' => 'center',
            ],
            'tanggal' => [
                'key' => 'tanggal',
                'label' => 'Tanggal Pemantauan',
                'category' => 'identitas',
                'description' => 'Tanggal pengamatan (DD/MM/YYYY)',
                'align' => 'center',
            ],
            'waktu' => [
                'key' => 'waktu',
                'label' => 'Waktu / Jam',
                'category' => 'identitas',
                'description' => 'Jam pencatatan (WIB)',
                'align' => 'center',
            ],
            'nama_petugas' => [
                'key' => 'nama_petugas',
                'label' => 'Petugas Pelapor',
                'category' => 'identitas',
                'description' => 'Nama personil inspeksi sisi udara',
                'align' => 'left',
            ],
            'unit_kerja' => [
                'key' => 'unit_kerja',
                'label' => 'Unit Pelapor',
                'category' => 'identitas',
                'description' => 'Unit dinas (AMC, ARFF, Avsec, SMS)',
                'align' => 'left',
            ],
            'validator' => [
                'key' => 'validator',
                'label' => 'Pejabat Validator',
                'category' => 'identitas',
                'description' => 'Safety Manager / Verifikator',
                'align' => 'left',
            ],

            // Kategori: Lokasi & Lingkungan
            'area_inspeksi' => [
                'key' => 'area_inspeksi',
                'label' => 'Area Inspeksi',
                'category' => 'lokasi',
                'description' => 'Zona sisi udara (Runway, Apron, dll.)',
                'align' => 'left',
            ],
            'grid_lokasi' => [
                'key' => 'grid_lokasi',
                'label' => 'Lokasi & Grid Koordinat',
                'category' => 'lokasi',
                'description' => 'Kode grid bandara (misal: K-10)',
                'align' => 'center',
            ],
            'koordinat_gps' => [
                'key' => 'koordinat_gps',
                'label' => 'Koordinat GPS (Lat, Long)',
                'category' => 'lokasi',
                'description' => 'Geolokasi koordinat satelit',
                'align' => 'center',
            ],
            'kondisi_cuaca' => [
                'key' => 'kondisi_cuaca',
                'label' => 'Kondisi Cuaca (Angin/Presipitasi)',
                'category' => 'lokasi',
                'description' => 'Cuaca, arah angin & curah hujan',
                'align' => 'left',
            ],
            'kondisi_apron' => [
                'key' => 'kondisi_apron',
                'label' => 'Kondisi Fisik Satwa',
                'category' => 'lokasi',
                'description' => 'Hidup, Mati, Kering saat ditemukan',
                'align' => 'center',
            ],

            // Kategori: Detail Satwa Liar
            'nama_satwa' => [
                'key' => 'nama_satwa',
                'label' => 'Nama Spesies Satwa',
                'category' => 'satwa',
                'description' => 'Nama lokal / Bahasa Indonesia',
                'align' => 'left',
            ],
            'nama_latin' => [
                'key' => 'nama_latin',
                'label' => 'Nama Latin Ilmiah',
                'category' => 'satwa',
                'description' => 'Nomenklatur taksonomi ilmiah',
                'align' => 'left',
            ],
            'jumlah_ekor' => [
                'key' => 'jumlah_ekor',
                'label' => 'Jumlah Individu (Ekor)',
                'category' => 'satwa',
                'description' => 'Total populasi satwa yang diamati',
                'align' => 'right',
            ],
            'ciri_ukuran' => [
                'key' => 'ciri_ukuran',
                'label' => 'Ciri Morfologi & Ukuran',
                'category' => 'satwa',
                'description' => 'Ukuran perkiraan / massa satwa',
                'align' => 'left',
            ],
            'aktivitas_satwa' => [
                'key' => 'aktivitas_satwa',
                'label' => 'Perilaku Satwa',
                'category' => 'satwa',
                'description' => 'Melintas, Bertengger, Mencari Makan',
                'align' => 'left',
            ],
            'kategori_risiko' => [
                'key' => 'kategori_risiko',
                'label' => 'Kategori Risiko ICAO',
                'category' => 'satwa',
                'description' => 'Penilaian risiko ICAO Doc 9137',
                'align' => 'center',
            ],

            // Kategori: Pengendalian & Status
            'tindak_lanjut' => [
                'key' => 'tindak_lanjut',
                'label' => 'Tindak Lanjut',
                'category' => 'tindakan',
                'description' => 'Eksekusi operasional penanganan',
                'align' => 'left',
            ],
            'metode_pengusiran' => [
                'key' => 'metode_pengusiran',
                'label' => 'Metode Pengusiran yang Digunakan',
                'category' => 'tindakan',
                'description' => 'Peralatan sirene, gas cannon, jaring',
                'align' => 'left',
            ],
            'amunisi_terpakai' => [
                'key' => 'amunisi_terpakai',
                'label' => 'Amunisi Terpakai',
                'category' => 'tindakan',
                'description' => 'Konsumsi amunisi / kembang api pengusiran',
                'align' => 'center',
            ],
            'status_penanganan' => [
                'key' => 'status_penanganan',
                'label' => 'Status Penanganan',
                'category' => 'tindakan',
                'description' => 'Telah Ditangani / Belum Ditangani',
                'align' => 'center',
            ],
        ];
    }

    /**
     * Konfigurasi Preset Cepat Sesuai PRD v1 & PRD v2.
     */
    protected function getPresets(): array
    {
        return [
            'dkppu' => [
                'id' => 'dkppu',
                'name' => 'Standar Baku Ditjen Hubud (DKPPU 16 Kolom)',
                'badge' => 'ICAO Doc 9137 & PRD v2.0',
                'badge_color' => 'emerald',
                'icon' => 'verified_user',
                'description' => 'Format 16 kolom resmi standar Ditjen Perhubungan Udara untuk audit keselamatan penerbangan kelaikudaraan.',
                'mode_baris' => 'per_satwa',
                'include_summary' => true,
                'columns' => [
                    'no',
                    'tanggal',
                    'kondisi_cuaca',
                    'unit_kerja',
                    'grid_lokasi',
                    'koordinat_gps',
                    'nama_satwa',
                    'nama_latin',
                    'jumlah_ekor',
                    'aktivitas_satwa',
                    'metode_pengusiran',
                    'amunisi_terpakai',
                    'kategori_risiko',
                    'status_penanganan',
                    'nama_petugas',
                    'validator',
                ],
            ],
            'operasional' => [
                'id' => 'operasional',
                'name' => 'Rekapitulasi Operasional Ringkas',
                'badge' => 'Ringkas & Harian (PRD v1.0)',
                'badge_color' => 'cyan',
                'icon' => 'table_view',
                'description' => 'Format praktis ringkasan harian untuk koordinasi unit sisi udara (AMC, ARFF, Safety).',
                'mode_baris' => 'per_laporan',
                'include_summary' => true,
                'columns' => [
                    'no',
                    'id_laporan',
                    'tanggal',
                    'nama_petugas',
                    'unit_kerja',
                    'area_inspeksi',
                    'grid_lokasi',
                    'nama_satwa',
                    'jumlah_ekor',
                    'tindak_lanjut',
                    'status_penanganan',
                ],
            ],
            'dispersal' => [
                'id' => 'dispersal',
                'name' => 'Audit Pengusiran & Amunisi (Dispersal)',
                'badge' => 'Logistik & Dispersal',
                'badge_color' => 'amber',
                'icon' => 'crisis_alert',
                'description' => 'Khusus evaluasi efektivitas metode pengusiran satwa, konsumsi butir amunisi, dan respon satwa.',
                'mode_baris' => 'per_satwa',
                'include_summary' => true,
                'columns' => [
                    'no',
                    'tanggal',
                    'unit_kerja',
                    'grid_lokasi',
                    'nama_satwa',
                    'jumlah_ekor',
                    'aktivitas_satwa',
                    'metode_pengusiran',
                    'amunisi_terpakai',
                    'tindak_lanjut',
                    'status_penanganan',
                    'nama_petugas',
                ],
            ],
            'lengkap' => [
                'id' => 'lengkap',
                'name' => 'Seluruh Data & Kolom Lengkap',
                'badge' => '22 Kolom Penuh',
                'badge_color' => 'purple',
                'icon' => 'library_add_check',
                'description' => 'Mengekspor seluruh kolom data tanpa terkecuali untuk analisis mendalam atau backup database.',
                'mode_baris' => 'per_satwa',
                'include_summary' => true,
                'columns' => array_keys($this->getAvailableColumns()),
            ],
            'custom' => [
                'id' => 'custom',
                'name' => 'Kustomisasi Mandiri (Bebas Pilih)',
                'badge' => 'Fleksibel',
                'badge_color' => 'slate',
                'icon' => 'tune',
                'description' => 'Pilih kolom secara manual sesuai kebutuhan spesifik Anda menggunakan checklist di bawah.',
                'mode_baris' => 'per_satwa',
                'include_summary' => true,
                'columns' => [],
            ],
        ];
    }

    /**
     * Helper nama Latin satwa liar bandara.
     */
    protected function getLatinName(string $nama): string
    {
        return JenisSatwa::resolveNamaIlmiah($nama, '-');
    }

    /**
     * Helper penilaian risiko ICAO Doc 9137.
     */
    protected function getIcaoRisk(string $nama, int $jumlah = 1, ?string $grid = null): string
    {
        $namaLower = strtolower(trim($nama));
        if (str_contains($namaLower, 'sanca') || str_contains($namaLower, 'kera') || str_contains($namaLower, 'anjing') || str_contains($namaLower, 'elang') || $jumlah >= 5) {
            return 'Tinggi (High Risk)';
        }
        if (str_contains($namaLower, 'biawak') || str_contains($namaLower, 'cangak') || str_contains($namaLower, 'kuntul') || str_contains($namaLower, 'blekok') || $jumlah >= 2) {
            return 'Sedang (Medium Risk)';
        }

        return 'Rendah (Low Risk)';
    }

    /**
     * Ekstrak konsumsi butir amunisi dari deskripsi pengusiran jika ada.
     */
    protected function extractAmunisi(?string $detail): int
    {
        return BeritaAcaraPdfService::extractAmunisiCount($detail);
    }

    /**
     * Menghasilkan berkas CSV yang kompatibel penuh dengan Excel (UTF-8 BOM).
     */
    protected function generateCsvResponse(string $filename, array $activeColumns, array $rows, bool $includeSummary, array $summaryData): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($activeColumns, $rows, $includeSummary, $summaryData) {
            $handle = fopen('php://output', 'w');

            // Tulis BOM UTF-8 agar Excel mengenali karakter dengan benar
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Baris Header Kolom
            $headerRow = [];
            foreach ($activeColumns as $col) {
                $headerRow[] = $col['label'];
            }
            fputcsv($handle, $headerRow);

            // Baris Data
            foreach ($rows as $row) {
                $dataRow = [];
                foreach ($activeColumns as $colKey => $col) {
                    $dataRow[] = $row[$colKey] ?? '-';
                }
                fputcsv($handle, $dataRow);
            }

            // Baris Summary jika dipilih
            if ($includeSummary) {
                $summaryRow = [];
                $first = true;
                foreach ($activeColumns as $colKey => $col) {
                    if ($first) {
                        $summaryRow[] = 'TOTAL / REKAPITULASI';
                        $first = false;
                    } elseif ($colKey === 'jumlah_ekor') {
                        $summaryRow[] = $summaryData['total_ekor'].' Ekor';
                    } elseif ($colKey === 'amunisi_terpakai') {
                        $summaryRow[] = $summaryData['total_amunisi'].' Butir';
                    } elseif ($colKey === 'status_penanganan') {
                        $summaryRow[] = $summaryData['persen_selesai'].'% Selesai';
                    } else {
                        $summaryRow[] = '';
                    }
                }
                fputcsv($handle, $summaryRow);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
