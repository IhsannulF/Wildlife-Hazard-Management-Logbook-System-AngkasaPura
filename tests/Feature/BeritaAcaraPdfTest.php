<?php

namespace Tests\Feature;

use App\Models\DetailSatwa;
use App\Models\FotoLaporan;
use App\Models\Laporan;
use App\Models\Tanggapan;
use App\Models\User;
use App\Services\BeritaAcaraPdfService;
use App\Services\PdfMediaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BeritaAcaraPdfTest extends TestCase
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
        $unique = uniqid($role.'_pdf_', false);

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
            'tanggal' => '2026-11-11',
            'jam' => '09:45:00',
            'kondisi_cuaca' => 'Cerah Berawan 30C',
            'unit_kerja' => 'Apron Movement Control (AMC)',
            'area_inspeksi' => 'Runway 10/28',
            'grid_lokasi' => 'K-10',
            'gps_lat' => -7.379812,
            'gps_lng' => 112.787104,
            'risk_score' => 9,
            'risk_level' => 'tinggi',
            'tanda_tangan' => self::SAMPLE_PNG_DATA_URI,
            'ciri_ukuran' => 'Burung air sedang paruh panjang',
            'kondisi_apron' => 'Kering dan terpantau',
            'aktivitas_satwa' => 'Bertengger dan melintas di bahu landasan',
            'tindak_lanjut' => 'Pengusiran aktif dengan flare gun',
            'detail_pengusiran' => '2 butir flare gun + sirene kendaraan AMC',
            'status' => 'belum',
        ], $overrides));

        $this->createdLaporanIds[] = $laporan->id;

        return $laporan;
    }

    private function createBlankJpegBytes(int $width, int $height): string
    {
        $canvas = imagecreatetruecolor($width, $height);
        $lightTone = imagecolorallocate($canvas, 240, 246, 250);
        imagefilledrectangle($canvas, 0, 0, $width, $height, $lightTone);

        ob_start();
        imagejpeg($canvas, null, 90);
        $binary = (string) ob_get_clean();
        unset($canvas);

        return $binary;
    }

    public function test_pdf_media_service_renders_watermarked_jpeg_without_mutating_original_image(): void
    {
        Storage::fake('public');

        $pegawai = $this->createTestUser('pegawai');
        $laporan = $this->createTestLaporan($pegawai);

        $detail = DetailSatwa::create([
            'laporan_id' => $laporan->id,
            'nama_satwa' => 'Burung Blekok Sawah',
            'jumlah' => 4,
            'grid' => 'K-10',
        ]);

        $originalBinary = $this->createBlankJpegBytes(1200, 900);
        $originalHash = md5($originalBinary);

        Storage::disk('public')->put('uploads/satwa/evidence_1200.jpg', $originalBinary);

        $foto = FotoLaporan::create([
            'laporan_id' => $laporan->id,
            'detail_satwa_id' => $detail->id,
            'nama_file' => 'evidence_1200.jpg',
            'tipe' => 'satwa',
            'foto_path' => 'uploads/satwa/evidence_1200.jpg',
        ]);

        $mediaService = app(PdfMediaService::class);
        $dataUri = $mediaService->renderWatermarkedPhotoFromRecord($foto, [
            'no_laporan' => $laporan->no_laporan,
            'tanggal_jam' => '11 November 2026 • 09:45 WIB',
            'lokasi' => 'Runway 10/28 [Grid K-10]',
            'unit' => 'AMC',
            'gps' => '-7.379812, 112.787104',
        ]);

        $this->assertNotNull($dataUri);
        $this->assertStringStartsWith('data:image/jpeg;base64,', $dataUri);

        $decoded = base64_decode(substr($dataUri, strlen('data:image/jpeg;base64,')), true);
        $this->assertIsString($decoded);

        $imageInfo = getimagesizefromstring($decoded);
        $this->assertIsArray($imageInfo);
        $this->assertSame(900, $imageInfo[0]);
        $this->assertSame(675, $imageInfo[1]);

        // Pastikan watermark strip gelap benar-benar tergambar pada sisi bawah gambar
        $rendered = imagecreatefromstring($decoded);
        $bottomColor = imagecolorat($rendered, 20, 660);
        $r = ($bottomColor >> 16) & 0xFF;
        $g = ($bottomColor >> 8) & 0xFF;
        $b = $bottomColor & 0xFF;
        unset($rendered);

        $this->assertLessThan(60, $r);
        $this->assertLessThan(90, $g);
        $this->assertLessThan(110, $b);

        // File asli di penyimpanan tetap utuh tidak berubah ukuran atau hash-nya
        $storedBytes = Storage::disk('public')->get('uploads/satwa/evidence_1200.jpg');
        $this->assertSame($originalHash, md5((string) $storedBytes));
    }

    public function test_pdf_media_service_handles_small_images_for_legible_watermark(): void
    {
        Storage::fake('public');

        $pegawai = $this->createTestUser('pegawai');
        $laporan = $this->createTestLaporan($pegawai);

        $smallUpload = UploadedFile::fake()->image('small_evidence.jpg', 300, 200);
        Storage::disk('public')->put('uploads/extra/small_evidence.jpg', (string) file_get_contents($smallUpload->getRealPath()));

        $foto = FotoLaporan::create([
            'laporan_id' => $laporan->id,
            'detail_satwa_id' => null,
            'nama_file' => 'small_evidence.jpg',
            'tipe' => 'extra',
            'foto_path' => 'uploads/extra/small_evidence.jpg',
        ]);

        $mediaService = app(PdfMediaService::class);
        $dataUri = $mediaService->renderWatermarkedPhotoFromRecord($foto, [
            'no_laporan' => $laporan->no_laporan,
            'tanggal_jam' => '11 November 2026 • 09:45 WIB',
            'lokasi' => 'Perimeter Timur',
            'unit' => 'AVSEC',
        ]);

        $this->assertNotNull($dataUri);
        $decoded = base64_decode(substr($dataUri, strlen('data:image/jpeg;base64,')), true);
        $info = getimagesizefromstring((string) $decoded);

        $this->assertIsArray($info);
        $this->assertSame(560, $info[0]);
    }

    public function test_berita_acara_pdf_service_renders_all_required_sections_for_validated_laporan(): void
    {
        Storage::fake('public');

        $pegawai = $this->createTestUser('pegawai', ['namalengkap' => 'Budi Santoso']);
        $admin = $this->createTestUser('admin', [
            'namalengkap' => 'Dr. Hendra Wijaya',
            'jabatan' => 'Safety Manager',
            'tanda_tangan' => self::SAMPLE_PNG_DATA_URI,
        ]);

        $laporan = $this->createTestLaporan($pegawai);
        $laporan->markAsValidated($admin);
        $laporan->save();

        $ds1 = DetailSatwa::create([
            'laporan_id' => $laporan->id,
            'nama_satwa' => 'Burung Blekok Sawah',
            'jumlah' => 3,
            'grid' => 'K-10',
        ]);
        DetailSatwa::create([
            'laporan_id' => $laporan->id,
            'nama_satwa' => 'Biawak',
            'jumlah' => 1,
            'grid' => 'L-10',
        ]);

        Storage::disk('public')->put('uploads/satwa/blekok_bukti.jpg', $this->createBlankJpegBytes(800, 600));
        FotoLaporan::create([
            'laporan_id' => $laporan->id,
            'detail_satwa_id' => $ds1->id,
            'nama_file' => 'blekok_bukti.jpg',
            'tipe' => 'satwa',
            'foto_path' => 'uploads/satwa/blekok_bukti.jpg',
        ]);

        Tanggapan::create([
            'laporan_id' => $laporan->id,
            'user_id' => $admin->id,
            'kategori_tanggapan' => 'penutupan',
            'isi' => 'Area Runway 10/28 dinyatakan steril kembali.',
        ]);

        $pdfService = app(BeritaAcaraPdfService::class);
        $html = $pdfService->renderHtml($laporan->fresh());

        $this->assertStringContainsString('PT Angkasa Pura Indonesia', $html);
        $this->assertStringContainsString('BERITA ACARA PEMANTAUAN', $html);
        $this->assertStringContainsString($laporan->no_laporan, $html);
        $this->assertStringContainsString('Skor ICAO', $html);
        $this->assertStringContainsString('Burung Blekok Sawah', $html);
        $this->assertStringContainsString('Ardeola speciosa', $html);
        $this->assertStringContainsString('Varanus salvator', $html);
        $this->assertStringContainsString('2 butir/dentuman', $html);
        $this->assertStringContainsString('Area Runway 10/28 dinyatakan steril kembali.', $html);
        $this->assertStringContainsString('data:image/jpeg;base64,', $html);
        $this->assertStringContainsString('Petugas Pelapor', $html);
        $this->assertStringContainsString('Mengetahui &amp; Mengesahkan', $html);
        $this->assertStringContainsString('Budi Santoso', $html);
        $this->assertStringContainsString('Dr. Hendra Wijaya', $html);
        $this->assertStringContainsString('Safety Manager', $html);
        $this->assertStringContainsString('alt="Tanda Tangan Pelapor"', $html);
        $this->assertStringContainsString('alt="Tanda Tangan Safety Manager"', $html);
        $this->assertStringNotContainsString('display: grid', $html);
        $this->assertStringNotContainsString('display: flex', $html);
    }

    public function test_validated_laporan_without_handwritten_signature_displays_digital_validation_seal(): void
    {
        $pegawai = $this->createTestUser('pegawai');
        $admin = $this->createTestUser('admin', [
            'namalengkap' => 'Rina Kusuma',
            'jabatan' => 'Airport Safety Manager',
            'tanda_tangan' => null,
        ]);

        $laporan = $this->createTestLaporan($pegawai);
        $laporan->markAsValidated($admin);
        $laporan->save();

        $html = app(BeritaAcaraPdfService::class)->renderHtml($laporan->fresh());

        $this->assertStringContainsString('TERVALIDASI DIGITAL SECARA SISTEM', $html);
        $this->assertStringContainsString('Rina Kusuma', $html);
        $this->assertStringNotContainsString('alt="Tanda Tangan Safety Manager"', $html);
    }

    public function test_pending_laporan_displays_waiting_validation_placeholder(): void
    {
        $pegawai = $this->createTestUser('pegawai');
        $laporan = $this->createTestLaporan($pegawai, ['status' => 'belum']);

        $html = app(BeritaAcaraPdfService::class)->renderHtml($laporan);

        $this->assertStringContainsString('MENUNGGU VALIDASI SAFETY MANAGER', $html);
        $this->assertStringContainsString('Status: Belum Divalidasi', $html);
    }

    public function test_authorized_pegawai_and_admin_can_download_valid_a4_pdf_file(): void
    {
        Storage::fake('public');

        $pegawai = $this->createTestUser('pegawai', ['namalengkap' => 'Agus Pratama']);
        $admin = $this->createTestUser('admin', [
            'namalengkap' => 'Manager Keselamatan',
            'tanda_tangan' => self::SAMPLE_PNG_DATA_URI,
        ]);

        $laporan = $this->createTestLaporan($pegawai);
        $laporan->markAsValidated($admin);
        $laporan->save();

        $ds = DetailSatwa::create([
            'laporan_id' => $laporan->id,
            'nama_satwa' => 'Burung Kuntul Kerbau',
            'jumlah' => 6,
            'grid' => 'D-09',
        ]);

        Storage::disk('public')->put('uploads/satwa/kuntul_pdf.jpg', $this->createBlankJpegBytes(800, 600));
        FotoLaporan::create([
            'laporan_id' => $laporan->id,
            'detail_satwa_id' => $ds->id,
            'nama_file' => 'kuntul_pdf.jpg',
            'tipe' => 'satwa',
            'foto_path' => 'uploads/satwa/kuntul_pdf.jpg',
        ]);

        // 1. Pegawai pemilik mengunduh via rute user
        $pegawaiRes = $this->actingAs($pegawai)->get(route('laporan.pdf', $laporan->id));
        $pegawaiRes->assertOk();
        $pegawaiRes->assertHeader('Content-Type', 'application/pdf');
        $disposition = (string) $pegawaiRes->headers->get('Content-Disposition');
        $this->assertStringContainsString('attachment', $disposition);
        $this->assertStringContainsString('.pdf', $disposition);

        $pdfBinary = $pegawaiRes->getContent();
        $this->assertIsString($pdfBinary);
        $this->assertStringStartsWith('%PDF-', $pdfBinary);
        $this->assertGreaterThan(5000, strlen($pdfBinary));

        // 2. Admin dapat mengakses via rute admin maupun mode inline
        $adminRes = $this->actingAs($admin)->get(route('admin.laporan.pdf', ['id' => $laporan->id, 'inline' => 1]));
        $adminRes->assertOk();
        $adminRes->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('inline', (string) $adminRes->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF-', (string) $adminRes->getContent());
    }

    public function test_authorization_boundaries_for_single_berita_acara_pdf(): void
    {
        $owner = $this->createTestUser('pegawai', ['username' => uniqid('owner_', false)]);
        $otherPegawai = $this->createTestUser('pegawai', ['username' => uniqid('other_', false)]);
        $laporan = $this->createTestLaporan($owner);

        // Guest ditolak ke login
        $this->get(route('laporan.pdf', $laporan->id))->assertRedirect(route('login'));

        // Pegawai lain tidak bisa mengunduh BA milik pegawai lain
        $this->actingAs($otherPegawai)->get(route('laporan.pdf', $laporan->id))->assertForbidden();

        // Pegawai tidak dapat mengakses endpoint admin PDF
        $this->actingAs($owner)->get(route('admin.laporan.pdf', $laporan->id))->assertRedirect(route('user.dashboard'));

        // Laporan tidak ditemukan -> 404
        $this->actingAs($owner)->get(route('laporan.pdf', 999999))->assertNotFound();
    }

    public function test_ui_views_expose_unduh_pdf_resmi_buttons(): void
    {
        $pegawai = $this->createTestUser('pegawai');
        $admin = $this->createTestUser('admin');
        $laporan = $this->createTestLaporan($pegawai);

        $cetakRes = $this->actingAs($pegawai)->get(route('laporan.cetak', $laporan->id));
        $cetakRes->assertOk();
        $cetakRes->assertSee(route('laporan.pdf', $laporan->id), false);
        $cetakRes->assertSee('Unduh PDF Resmi', false);

        $userDash = $this->actingAs($pegawai)->get(route('user.dashboard'));
        $userDash->assertOk();
        $userDash->assertSee(route('laporan.pdf', $laporan->id), false);
        $userDash->assertSee('id="modalDownloadPdfBtn"', false);

        $adminDash = $this->actingAs($admin)->get(route('admin.dashboard', [
            'tab' => 'verifikasi',
            'search' => $laporan->no_laporan,
        ]));
        $adminDash->assertOk();
        $adminDash->assertSee('id="modalDownloadPdfBtn"', false);
        $adminDash->assertSee('Unduh PDF Resmi', false);
        $adminDash->assertSee(route('admin.laporan.pdf', $laporan->id), false);

        $adminVerif = $this->actingAs($admin)->get(route('admin.verifikasi', [
            'search' => $laporan->no_laporan,
        ]));
        $adminVerif->assertOk();
        $adminVerif->assertSee('id="modalDownloadPdfBtn"', false);
        $adminVerif->assertSee('Unduh PDF Resmi', false);
        $adminVerif->assertSee(route('admin.laporan.pdf', $laporan->id), false);
    }
}
