<?php

namespace Tests\Feature;

use App\Models\DetailSatwa;
use App\Models\FotoLaporan;
use App\Models\Laporan;
use App\Models\Tanggapan;
use App\Models\User;
use App\Services\MonthlyLogbookPdfService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MonthlyLogbookBundlePdfTest extends TestCase
{
    private const SAMPLE_PNG_DATA_URI = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

    /** @var array<int, int> */
    private array $createdLaporanIds = [];

    /** @var array<int, int> */
    private array $createdUserIds = [];

    protected function tearDown(): void
    {
        if (! empty($this->createdLaporanIds)) {
            Tanggapan::whereIn('laporan_id', $this->createdLaporanIds)->delete();
            FotoLaporan::whereIn('laporan_id', $this->createdLaporanIds)->delete();
            DetailSatwa::whereIn('laporan_id', $this->createdLaporanIds)->delete();
            Laporan::whereIn('id', $this->createdLaporanIds)->delete();
        }

        if (! empty($this->createdUserIds)) {
            User::whereIn('id', $this->createdUserIds)->delete();
        }

        parent::tearDown();
    }

    private function createTestUser(string $role, array $overrides = []): User
    {
        $unique = uniqid($role.'_bundle_', false);

        $user = User::create(array_merge([
            'username' => $unique,
            'password' => Hash::make('secret123'),
            'namalengkap' => 'Tester '.strtoupper($role),
            'jabatan' => $role === 'admin' ? 'Safety Manager' : 'Unit AMC',
            'role' => $role,
            'nama' => 'Tester '.strtoupper($role),
            'aktif' => true,
        ], $overrides));

        $this->createdUserIds[] = $user->id;

        return $user;
    }

    private function createTestLaporan(User $owner, array $overrides = []): Laporan
    {
        $laporan = Laporan::create(array_merge([
            'user_id' => $owner->id,
            'nama_petugas' => $owner->namalengkap ?: 'Petugas Lapangan',
            'tanggal' => '2094-04-10',
            'jam' => '08:30:00',
            'kondisi_cuaca' => 'Cerah Berawan 30C',
            'unit_kerja' => 'Apron Movement Control (AMC)',
            'area_inspeksi' => 'Runway 10/28',
            'grid_lokasi' => 'K-10',
            'gps_lat' => -7.379812,
            'gps_lng' => 112.787104,
            'risk_score' => 8,
            'risk_level' => 'tinggi',
            'tanda_tangan' => self::SAMPLE_PNG_DATA_URI,
            'ciri_ukuran' => 'Burung air sedang',
            'kondisi_apron' => 'Kering dan terpantau',
            'aktivitas_satwa' => 'Melintas di bahu runway',
            'tindak_lanjut' => 'Pengusiran aktif dengan sirene dan flare gun',
            'detail_pengusiran' => 'Sirene patroli AMC',
            'status' => 'belum',
        ], $overrides));

        $this->createdLaporanIds[] = $laporan->id;

        return $laporan;
    }

    private function createBlankJpegBytes(int $width, int $height): string
    {
        $canvas = imagecreatetruecolor($width, $height);
        $tone = imagecolorallocate($canvas, 232, 242, 248);
        imagefilledrectangle($canvas, 0, 0, $width, $height, $tone);

        ob_start();
        imagejpeg($canvas, null, 88);
        $binary = (string) ob_get_clean();
        unset($canvas);

        return $binary;
    }

    public function test_authorization_and_validation_boundaries_for_monthly_bundle_pdf(): void
    {
        $pegawai = $this->createTestUser('pegawai');
        $admin = $this->createTestUser('admin');

        $guestResponse = $this->get(route('admin.export.bundle.pdf', [
            'bulan' => 4,
            'tahun' => 2094,
        ]));
        $guestResponse->assertRedirect('/login');

        $pegawaiDownload = $this->actingAs($pegawai)->get(route('admin.export.bundle.pdf', [
            'bulan' => 4,
            'tahun' => 2094,
        ]));
        $pegawaiDownload->assertRedirect('/dashboard');

        $pegawaiCount = $this->actingAs($pegawai)->get(route('admin.export.bundle.count', [
            'bulan' => 4,
            'tahun' => 2094,
        ]));
        $pegawaiCount->assertRedirect('/dashboard');

        $invalidValidation = $this->actingAs($admin)->getJson(route('admin.export.bundle.pdf', [
            'bulan' => 13,
            'tahun' => 1995,
            'status' => 'unknown',
        ]));
        $invalidValidation->assertStatus(422);
        $invalidValidation->assertJsonValidationErrors(['bulan', 'tahun', 'status']);
    }

    public function test_monthly_bundle_service_compiles_cover_foreword_temporal_stats_and_chronological_ba_list(): void
    {
        Storage::fake('public');

        $pegawai = $this->createTestUser('pegawai', ['namalengkap' => 'Rafi Hidayat']);
        $admin = $this->createTestUser('admin', [
            'namalengkap' => 'Irwan Setiawan',
            'jabatan' => 'Safety Manager',
            'tanda_tangan' => self::SAMPLE_PNG_DATA_URI,
        ]);

        // 1. Laporan kronologis #3 (tgl 20 April 2034 sore) - Tervalidasi + 5 ekor + 2 butir + foto
        $lap3 = $this->createTestLaporan($pegawai, [
            'tanggal' => '2034-04-20',
            'jam' => '15:30:00',
            'unit_kerja' => 'Apron Movement Control (AMC)',
            'area_inspeksi' => 'Runway 10/28',
            'grid_lokasi' => 'G-18',
            'risk_score' => 12,
            'risk_level' => 'kritis',
            'detail_pengusiran' => '2 butir flare gun dan sirene kendaraan',
            'status' => 'sudah',
            'validated_by' => $admin->id,
            'validated_at' => '2034-04-20 16:00:00',
            'validator_signature' => self::SAMPLE_PNG_DATA_URI,
        ]);
        $ds3 = DetailSatwa::create([
            'laporan_id' => $lap3->id,
            'nama_satwa' => 'Burung Blekok Sawah',
            'jumlah' => 5,
            'grid' => 'G-18',
        ]);
        Storage::disk('public')->put('uploads/satwa/bundle_blekok.jpg', $this->createBlankJpegBytes(800, 600));
        FotoLaporan::create([
            'laporan_id' => $lap3->id,
            'detail_satwa_id' => $ds3->id,
            'nama_file' => 'bundle_blekok.jpg',
            'tipe' => 'satwa',
            'foto_path' => 'uploads/satwa/bundle_blekok.jpg',
        ]);

        // 2. Laporan kronologis #1 (tgl 02 April 2034 pagi) - Menunggu validasi + ARFF + 2 ekor reptil
        $lap1 = $this->createTestLaporan($pegawai, [
            'tanggal' => '2034-04-02',
            'jam' => '07:15:00',
            'unit_kerja' => 'PK-PPK / ARFF',
            'area_inspeksi' => 'Kanal Drainase Selatan',
            'grid_lokasi' => 'K-10',
            'risk_score' => 6,
            'risk_level' => 'sedang',
            'detail_pengusiran' => 'Penangkapan manual dan relokasi luar pagar',
            'status' => 'belum',
        ]);
        DetailSatwa::create([
            'laporan_id' => $lap1->id,
            'nama_satwa' => 'Biawak Air',
            'jumlah' => 2,
            'grid' => 'K-10',
        ]);

        // 3. Laporan kronologis #2 (tgl 02 April 2034 malam) - Tervalidasi + 1 ekor mamalia
        $lap2 = $this->createTestLaporan($pegawai, [
            'tanggal' => '2034-04-02',
            'jam' => '19:40:00',
            'unit_kerja' => 'Apron Movement Control (AMC)',
            'area_inspeksi' => 'Perimeter Barat',
            'grid_lokasi' => 'B-4',
            'risk_score' => 4,
            'risk_level' => 'rendah',
            'detail_pengusiran' => 'Penggiringan menjauhi pagar sisi udara',
            'status' => 'sudah',
            'validated_by' => $admin->id,
            'validated_at' => '2034-04-02 20:10:00',
            'validator_signature' => self::SAMPLE_PNG_DATA_URI,
        ]);
        DetailSatwa::create([
            'laporan_id' => $lap2->id,
            'nama_satwa' => 'Anjing Liar',
            'jumlah' => 1,
            'grid' => 'B-4',
        ]);

        // 4. Laporan bulan berbeda (Mei 2034) - Wajib tidak masuk ke bundel April 2034
        $outOfMonthLap = $this->createTestLaporan($pegawai, [
            'tanggal' => '2034-05-03',
            'jam' => '10:00:00',
            'status' => 'sudah',
        ]);

        /** @var MonthlyLogbookPdfService $service */
        $service = app(MonthlyLogbookPdfService::class);
        $payload = $service->buildBundlePayload(4, 2034, 'all', 'all', $admin);

        // Urutan kronologis wajib $lap1 -> $lap2 -> $lap3
        $compiledOrder = array_map(fn (array $item): int => $item['laporan']->id, $payload['berita_acara_items']);
        $this->assertSame([$lap1->id, $lap2->id, $lap3->id], $compiledOrder);
        $this->assertNotContains($outOfMonthLap->id, $compiledOrder);

        // Statistik faktual bulan April 2034
        $kpi = $payload['stats']['kpi'];
        $this->assertSame(3, $kpi['total_laporan']);
        $this->assertSame(2, $kpi['total_tervalidasi']);
        $this->assertSame(1, $kpi['total_pending']);
        $this->assertSame(66.7, $kpi['persen_tervalidasi']);
        $this->assertSame(8, $kpi['total_ekor']);
        $this->assertSame(2, $kpi['total_amunisi']);
        $this->assertSame(1, $kpi['total_foto']);

        // Verifikasi agregasi temporal mingguan (Minggu I = 2 BA, Minggu III = 1 BA)
        $this->assertSame(2, $payload['stats']['weekly_trend'][0]['laporan']);
        $this->assertSame(0, $payload['stats']['weekly_trend'][1]['laporan']);
        $this->assertSame(1, $payload['stats']['weekly_trend'][2]['laporan']);

        // Verifikasi seluruh bagian HTML bundel tersedia
        $html = $service->renderHtml(4, 2034, 'all', 'all', $admin);
        $this->assertStringContainsString('BUKU LOG BULANAN', $html);
        $this->assertStringContainsString('LOG-WHMS/SUB/ALL/04/2034', $html);
        $this->assertStringContainsString('April 2034', $html);
        $this->assertStringContainsString('KATA PENGANTAR &amp; DAFTAR ISI BERITA ACARA', $html);
        $this->assertStringContainsString('REKAPITULASI STATISTIK &amp; GRAFIK BULANAN', $html);
        $this->assertStringContainsString('Grafik Tren Kejadian Mingguan', $html);
        $this->assertStringContainsString('Lampiran Berita Acara 1 dari 3', $html);
        $this->assertStringContainsString('Lampiran Berita Acara 3 dari 3', $html);
        $this->assertStringContainsString($lap1->no_laporan, $html);
        $this->assertStringContainsString($lap2->no_laporan, $html);
        $this->assertStringContainsString($lap3->no_laporan, $html);
        $this->assertStringNotContainsString($outOfMonthLap->no_laporan, $html);
        $this->assertStringContainsString('Ardeola speciosa', $html);
        $this->assertStringContainsString('Varanus salvator', $html);
        $this->assertStringContainsString('data:image/jpeg;base64,', $html);
    }

    public function test_monthly_bundle_pdf_builds_valid_multi_page_pdf_with_continuous_pagination_and_download_route(): void
    {
        Storage::fake('public');

        $pegawai = $this->createTestUser('pegawai', ['namalengkap' => 'Dina Lestari']);
        $admin = $this->createTestUser('admin', [
            'namalengkap' => 'Hendra Wijaya',
            'jabatan' => 'Safety Manager',
            'tanda_tangan' => self::SAMPLE_PNG_DATA_URI,
        ]);

        $lap1 = $this->createTestLaporan($pegawai, [
            'tanggal' => '2035-07-05',
            'jam' => '06:45:00',
            'unit_kerja' => 'Apron Movement Control (AMC)',
            'status' => 'sudah',
            'validated_by' => $admin->id,
            'validated_at' => '2035-07-05 07:30:00',
            'validator_signature' => self::SAMPLE_PNG_DATA_URI,
        ]);
        DetailSatwa::create([
            'laporan_id' => $lap1->id,
            'nama_satwa' => 'Kuntul Kerbau',
            'jumlah' => 3,
            'grid' => 'E-12',
        ]);

        $lap2 = $this->createTestLaporan($pegawai, [
            'tanggal' => '2035-07-18',
            'jam' => '16:15:00',
            'unit_kerja' => 'Safety (SMS)',
            'status' => 'belum',
        ]);
        DetailSatwa::create([
            'laporan_id' => $lap2->id,
            'nama_satwa' => 'Burung Cangak Abu',
            'jumlah' => 1,
            'grid' => 'G-18',
        ]);

        /** @var MonthlyLogbookPdfService $service */
        $service = app(MonthlyLogbookPdfService::class);
        $pdfWrapper = $service->buildPdf(7, 2035, 'all', 'all', $admin);

        // Cover (1) + Foreword/Index (2) + Statistics (3) + BA #1 (4+) + BA #2 (5+)
        $pageCount = $pdfWrapper->getDomPDF()->getCanvas()->get_page_count();
        $this->assertGreaterThanOrEqual(5, $pageCount);

        $downloadRes = $this->actingAs($admin)->get(route('admin.export.bundle.pdf', [
            'bulan' => 7,
            'tahun' => 2035,
        ]));
        $downloadRes->assertOk();
        $downloadRes->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString(
            'Bundel_Logbook_WHMS_2035_07_Juli.pdf',
            (string) $downloadRes->headers->get('Content-Disposition')
        );

        $binary = $downloadRes->getContent();
        $this->assertIsString($binary);
        $this->assertStringStartsWith('%PDF-', $binary);
        $this->assertGreaterThan(8000, strlen($binary));

        $inlineRes = $this->actingAs($admin)->get(route('admin.export.bundle.pdf', [
            'bulan' => 7,
            'tahun' => 2035,
            'inline' => 1,
        ]));
        $inlineRes->assertOk();
        $inlineRes->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('inline', strtolower((string) $inlineRes->headers->get('Content-Disposition')));
        $this->assertStringStartsWith('%PDF-', (string) $inlineRes->getContent());
        $this->assertSame(strlen($binary), (int) $inlineRes->headers->get('Content-Length'));

        // Verifikasi live count terfilter
        $countFiltered = $this->actingAs($admin)->getJson(route('admin.export.bundle.count', [
            'bulan' => 7,
            'tahun' => 2035,
            'status' => 'sudah',
        ]));
        $countFiltered->assertOk();
        $countFiltered->assertJson([
            'status' => 'success',
            'total_laporan' => 1,
            'total_tervalidasi' => 1,
            'total_ekor' => 3,
            'periode_label' => 'Juli 2035',
        ]);
    }

    public function test_empty_month_bundle_generates_valid_nihil_archive_pdf(): void
    {
        $admin = $this->createTestUser('admin');

        /** @var MonthlyLogbookPdfService $service */
        $service = app(MonthlyLogbookPdfService::class);
        $html = $service->renderHtml(2, 2036, 'all', 'all', $admin);

        $this->assertStringContainsString('NIHIL PENERBITAN BERITA ACARA', $html);
        $this->assertStringContainsString('Februari 2036', $html);

        $emptyPdf = $service->buildPdf(2, 2036, 'all', 'all', $admin);
        $this->assertSame(4, $emptyPdf->getDomPDF()->getCanvas()->get_page_count());

        $response = $this->actingAs($admin)->get(route('admin.export.bundle.pdf', [
            'bulan' => 2,
            'tahun' => 2036,
        ]));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', (string) $response->getContent());
    }

    public function test_admin_ui_exposes_unduh_buku_log_bulanan_controls(): void
    {
        $admin = $this->createTestUser('admin');

        $exportPage = $this->actingAs($admin)->get(route('admin.export.excel'));
        $exportPage->assertOk();
        $exportPage->assertSee('id="monthlyBundleSection"', false);
        $exportPage->assertSee('Unduh Buku Log Bulanan (Bundle PDF)', false);
        $exportPage->assertSee('id="btnPreviewBundlePdf"', false);
        $exportPage->assertSee('Lihat Laporan', false);
        $exportPage->assertDontSee('id="btnDownloadBundlePdf"', false);
        $exportPage->assertSee('id="bundleBulan"', false);
        $exportPage->assertSee('id="bundleTahun"', false);
        $exportPage->assertSee(route('admin.export.bundle.pdf'), false);
        $exportPage->assertSee(route('admin.export.bundle.count'), false);

        $verifPage = $this->actingAs($admin)->get(route('admin.verifikasi'));
        $verifPage->assertOk();
        $verifPage->assertSee('Buku Log Bulanan (PDF)', false);
        $verifPage->assertSee('#monthlyBundleSection', false);
        $verifPage->assertDontSee('<span class="flex-1">Export Excel</span>', false);

        $dashboardPage = $this->actingAs($admin)->get(route('admin.dashboard'));
        $dashboardPage->assertOk();
        $dashboardPage->assertSee('Buku Log Bulanan (PDF)', false);
        $dashboardPage->assertSee('#monthlyBundleSection', false);
        $dashboardPage->assertDontSee('<span class="flex-1">Export Excel</span>', false);
    }
}
