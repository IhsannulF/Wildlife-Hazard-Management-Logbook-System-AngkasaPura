<?php

namespace App\Services;

use App\Models\FotoLaporan;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PdfMediaService
{
    private ?string $cachedLogoDataUri = null;

    private bool $logoResolved = false;

    private ?string $cachedLogoPdfSrc = null;

    private bool $logoPdfSrcResolved = false;

    /**
     * Memoisasi hasil normalisasi data URI tanda tangan agar bundel PDF multi-halaman tidak mengulang decode GD.
     *
     * @var array<string, ?string>
     */
    private array $normalizedSignatureCache = [];

    /**
     * Ambil path lokal logo resmi InJourney Airports agar DomPDF melakukan cache satu kali di memori tanpa mengulang base64.
     */
    public function getInjourneyLogoPdfSrc(): ?string
    {
        if ($this->logoPdfSrcResolved) {
            return $this->cachedLogoPdfSrc;
        }

        $this->logoPdfSrcResolved = true;

        $logoPath = public_path('images/logo_login.png');
        if (is_file($logoPath) && is_readable($logoPath)) {
            $realLogo = realpath($logoPath);
            $realBase = realpath(base_path());
            if ($realLogo !== false && $realBase !== false) {
                $normalizedLogo = str_replace('\\', '/', $realLogo);
                $normalizedBase = rtrim(str_replace('\\', '/', $realBase), '/').'/';
                if (str_starts_with(strtolower($normalizedLogo), strtolower($normalizedBase))) {
                    return $this->cachedLogoPdfSrc = $normalizedLogo;
                }
            }
        }

        return $this->cachedLogoPdfSrc = $this->getInjourneyLogoDataUri();
    }

    /**
     * Ambil logo resmi InJourney Airports dalam format base64 data URI agar aman di DomPDF tanpa akses HTTP.
     */
    public function getInjourneyLogoDataUri(): ?string
    {
        if ($this->logoResolved) {
            return $this->cachedLogoDataUri;
        }

        $this->logoResolved = true;

        $logoPath = public_path('images/logo_login.png');
        if (! is_file($logoPath) || ! is_readable($logoPath)) {
            return null;
        }

        $raw = file_get_contents($logoPath);
        if (! is_string($raw) || $raw === '') {
            return null;
        }

        $this->cachedLogoDataUri = 'data:image/png;base64,'.base64_encode($raw);

        return $this->cachedLogoDataUri;
    }

    /**
     * Normalisasi spesimen tanda tangan digital agar selalu kompatibel dengan renderer gambar DomPDF.
     */
    public function normalizeSignatureDataUri(?string $signature): ?string
    {
        if (! User::isValidSignatureDataUri($signature)) {
            return null;
        }

        $trimmed = trim((string) $signature);
        $cacheKey = md5($trimmed);

        if (array_key_exists($cacheKey, $this->normalizedSignatureCache)) {
            return $this->normalizedSignatureCache[$cacheKey];
        }

        if (str_starts_with(strtolower($trimmed), 'data:image/webp;base64,')) {
            $commaPos = strpos($trimmed, ',');
            if ($commaPos === false) {
                return $this->normalizedSignatureCache[$cacheKey] = null;
            }

            $binary = base64_decode(substr($trimmed, $commaPos + 1), true);
            if ($binary === false || ! function_exists('imagecreatefromstring')) {
                return $this->normalizedSignatureCache[$cacheKey] = null;
            }

            $src = @imagecreatefromstring($binary);
            if ($src === false) {
                return $this->normalizedSignatureCache[$cacheKey] = null;
            }

            ob_start();
            imagepng($src, null, 6);
            $pngBinary = ob_get_clean();
            unset($src);

            return $this->normalizedSignatureCache[$cacheKey] = (is_string($pngBinary) && $pngBinary !== '')
                ? 'data:image/png;base64,'.base64_encode($pngBinary)
                : null;
        }

        return $this->normalizedSignatureCache[$cacheKey] = $trimmed;
    }

    /**
     * Temukan path berkas fisik lokal untuk entri FotoLaporan tanpa bergantung pada permintaan HTTP.
     */
    public function resolveFotoPath(FotoLaporan $foto): ?string
    {
        $disk = Storage::disk('public');
        $candidates = [];

        if (! empty($foto->foto_path)) {
            $normalized = ltrim(str_replace('\\', '/', (string) $foto->foto_path), '/');
            if (str_starts_with($normalized, 'storage/')) {
                $normalized = substr($normalized, strlen('storage/'));
            }
            if (! str_contains($normalized, '..')) {
                $candidates[] = $normalized;
            }
        }

        if (! empty($foto->nama_file)) {
            $safeName = basename(str_replace('\\', '/', (string) $foto->nama_file));
            if ($safeName !== '' && $safeName !== '.' && $safeName !== '..') {
                $folder = $foto->tipe === 'extra' ? 'extra' : 'satwa';
                $candidates[] = "uploads/{$folder}/{$safeName}";
            }
        }

        foreach (array_unique($candidates) as $relativePath) {
            if ($disk->exists($relativePath)) {
                $fullPath = $disk->path($relativePath);
                if (is_file($fullPath) && is_readable($fullPath)) {
                    return $fullPath;
                }
            }

            $publicStoragePath = public_path('storage/'.$relativePath);
            if (is_file($publicStoragePath) && is_readable($publicStoragePath)) {
                return $publicStoragePath;
            }
        }

        if (! empty($foto->nama_file)) {
            $safeName = basename(str_replace('\\', '/', (string) $foto->nama_file));
            $fallbackImage = public_path('images/'.$safeName);
            if (is_file($fallbackImage) && is_readable($fallbackImage)) {
                return $fallbackImage;
            }
        }

        return null;
    }

    /**
     * Proses foto dokumentasi langsung dari entri FotoLaporan lengkap dengan watermark resmi.
     *
     * @param  array{no_laporan?: string, tanggal_jam?: string, lokasi?: string, unit?: string, gps?: ?string}  $meta
     */
    public function renderWatermarkedPhotoFromRecord(
        FotoLaporan $foto,
        array $meta = [],
        int $maxWidth = 900,
        int $jpegQuality = 78
    ): ?string {
        $path = $this->resolveFotoPath($foto);
        if ($path === null) {
            return null;
        }

        return $this->renderWatermarkedPhotoDataUri($path, $meta, $maxWidth, $jpegQuality);
    }

    /**
     * Membaca gambar lokal, menyesuaikan ukuran untuk PDF A4, dan membubuhkan watermark metadata resmi via GD
     * tanpa mengubah berkas asli di penyimpanan.
     *
     * @param  array{no_laporan?: string, tanggal_jam?: string, lokasi?: string, unit?: string, gps?: ?string}  $meta
     */
    public function renderWatermarkedPhotoDataUri(
        string $absolutePath,
        array $meta = [],
        int $maxWidth = 900,
        int $jpegQuality = 78
    ): ?string {
        if (! is_file($absolutePath) || ! is_readable($absolutePath) || ! function_exists('imagecreatefromstring')) {
            return null;
        }

        $raw = file_get_contents($absolutePath);
        if (! is_string($raw) || $raw === '') {
            return null;
        }

        $src = @imagecreatefromstring($raw);
        if ($src === false) {
            return null;
        }

        $src = $this->applyExifOrientation($src, $absolutePath);
        $srcW = max(1, imagesx($src));
        $srcH = max(1, imagesy($src));

        $effectiveMinWidth = min(560, $maxWidth);
        if ($srcW > $maxWidth) {
            $dstW = $maxWidth;
        } elseif ($srcW < $effectiveMinWidth) {
            $dstW = $effectiveMinWidth;
        } else {
            $dstW = $srcW;
        }

        $dstH = max(140, (int) round($srcH * ($dstW / $srcW)));

        $canvas = imagecreatetruecolor($dstW, $dstH);
        imagealphablending($canvas, true);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $dstW, $dstH, $white);
        imagecopyresampled($canvas, $src, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);
        unset($src);

        $this->drawOfficialWatermark($canvas, $dstW, $dstH, $meta);

        ob_start();
        imagejpeg($canvas, null, max(50, min(92, $jpegQuality)));
        $jpegBinary = ob_get_clean();
        unset($canvas);

        if (! is_string($jpegBinary) || $jpegBinary === '') {
            return null;
        }

        return 'data:image/jpeg;base64,'.base64_encode($jpegBinary);
    }

    /**
     * Putar gambar otomatis jika berkas JPEG kamera membawa orientasi EXIF.
     */
    private function applyExifOrientation(\GdImage $image, string $path): \GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path);
        $orientation = (int) ($exif['Orientation'] ?? 1);

        $rotated = match ($orientation) {
            3 => @imagerotate($image, 180, 0),
            6 => @imagerotate($image, -90, 0),
            8 => @imagerotate($image, 90, 0),
            default => false,
        };

        if ($rotated instanceof \GdImage) {
            unset($image);

            return $rotated;
        }

        return $image;
    }

    /**
     * Gambar banner watermark resmi InJourney WHMS serta stempel metadata ke atas kanvas GD.
     *
     * @param  array{no_laporan?: string, tanggal_jam?: string, lokasi?: string, unit?: string, gps?: ?string}  $meta
     */
    private function drawOfficialWatermark(\GdImage $canvas, int $width, int $height, array $meta): void
    {
        $noLaporan = trim((string) ($meta['no_laporan'] ?? 'BA-WHMS'));
        $tanggalJam = trim((string) ($meta['tanggal_jam'] ?? '-'));
        $lokasi = trim((string) ($meta['lokasi'] ?? '-'));
        $unit = trim((string) ($meta['unit'] ?? 'AMC'));
        $gps = trim((string) ($meta['gps'] ?? ''));

        $bannerHeight = max(48, min(68, (int) round($height * 0.145)));
        $bannerTop = max(0, $height - $bannerHeight);

        // Stempel diagonal samar di tengah foto agar foto bukti tidak mudah disalahgunakan
        if ($height >= 240) {
            $stampColor = imagecolorallocatealpha($canvas, 255, 255, 255, 96);
            $boldFont = $this->resolveFontPath(true);
            if ($boldFont !== null && function_exists('imagettftext')) {
                $stampSize = max(12, min(22, (int) round($width * 0.028)));
                @imagettftext(
                    $canvas,
                    $stampSize,
                    18,
                    (int) round($width * 0.18),
                    (int) round($height * 0.56),
                    $stampColor,
                    $boldFont,
                    'INJOURNEY WHMS • VERIFIED EVIDENCE'
                );
            }
        }

        // Latar strip watermark gelap semi-transparan di bagian bawah foto
        $stripBg = imagecolorallocatealpha($canvas, 0, 38, 56, 22);
        imagefilledrectangle($canvas, 0, $bannerTop, $width, $height, $stripBg);

        // Garis aksen cyan khas InJourney Airports
        $accentCyan = imagecolorallocate($canvas, 0, 169, 193);
        imagefilledrectangle($canvas, 0, $bannerTop, $width, min($height, $bannerTop + 3), $accentCyan);

        $line1Left = 'INJOURNEY AIRPORTS • WHMS JUANDA';
        $line1Right = $noLaporan;

        $metaParts = array_filter([
            $tanggalJam,
            $lokasi,
            'Unit '.$unit,
            $gps !== '' ? 'GPS: '.$gps : null,
        ]);
        $line2Text = implode('  |  ', $metaParts);

        $boldFont = $this->resolveFontPath(true);
        $regularFont = $this->resolveFontPath(false);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        $cyanText = imagecolorallocate($canvas, 125, 232, 245);
        $mutedLight = imagecolorallocate($canvas, 226, 238, 244);

        if ($boldFont !== null && $regularFont !== null && function_exists('imagettftext')) {
            $padX = max(12, (int) round($width * 0.022));
            $primarySize = max(9, min(12, (int) round($width * 0.0155)));
            $secondarySize = max(8, min(10.5, (int) round($width * 0.013)));

            $line1Y = $bannerTop + (int) round($bannerHeight * 0.44);
            $line2Y = $bannerTop + (int) round($bannerHeight * 0.80);

            @imagettftext($canvas, $primarySize, 0, $padX, $line1Y, $white, $boldFont, $line1Left);

            // Posisikan nomor BA rata kanan pada baris pertama
            $rightBox = @imagettfbbox($primarySize, 0, $boldFont, $line1Right);
            $rightW = is_array($rightBox) ? abs($rightBox[2] - $rightBox[0]) : 140;
            $rightX = max($padX + 180, $width - $padX - $rightW);
            @imagettftext($canvas, $primarySize, 0, $rightX, $line1Y, $cyanText, $boldFont, $line1Right);

            // Potong aman baris kedua jika sangat panjang untuk resolusi foto sempit
            $maxLine2Chars = max(48, (int) floor(($width - ($padX * 2)) / ($secondarySize * 0.56)));
            $safeLine2 = Str::limit($line2Text, $maxLine2Chars, '...');
            @imagettftext($canvas, $secondarySize, 0, $padX, $line2Y, $mutedLight, $regularFont, $safeLine2);

            return;
        }

        // Fallback bitmap font jika ekstensi FreeType tidak tersedia
        imagestring($canvas, 3, 10, $bannerTop + 8, $line1Left.' | '.$line1Right, $white);
        imagestring($canvas, 2, 10, $bannerTop + 26, Str::limit($line2Text, 90, '...'), $mutedLight);
    }

    private function resolveFontPath(bool $bold): ?string
    {
        $filename = $bold ? 'DejaVuSans-Bold.ttf' : 'DejaVuSans.ttf';
        $vendorFont = base_path('vendor/dompdf/dompdf/lib/fonts/'.$filename);
        if (is_file($vendorFont) && is_readable($vendorFont)) {
            return $vendorFont;
        }

        $storageFont = storage_path('fonts/'.$filename);
        if (is_file($storageFont) && is_readable($storageFont)) {
            return $storageFont;
        }

        return null;
    }
}
