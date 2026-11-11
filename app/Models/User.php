<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'username',
        'password',
        'namalengkap',
        'jabatan',
        'tanda_tangan',
        'role',
        'nama',
        'aktif',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPegawai(): bool
    {
        return $this->role === 'pegawai';
    }

    public function laporan(): HasMany
    {
        return $this->hasMany(Laporan::class, 'user_id');
    }

    public function tanggapan(): HasMany
    {
        return $this->hasMany(Tanggapan::class, 'user_id');
    }

    public function validatedLaporan(): HasMany
    {
        return $this->hasMany(Laporan::class, 'validated_by');
    }

    public function getValidatorDisplayTitleAttribute(): string
    {
        if (! empty($this->jabatan)) {
            return $this->jabatan;
        }

        return $this->isAdmin() ? 'Safety Manager' : 'Petugas Lapangan';
    }

    /**
     * Aturan validasi bersama untuk string data URI tanda tangan digital.
     *
     * @return array<int, mixed>
     */
    public static function signatureDataUriRules(): array
    {
        return [
            'nullable',
            'string',
            'max:700000',
            function (string $attribute, mixed $value, \Closure $fail): void {
                if (! is_string($value) || trim($value) === '') {
                    return;
                }

                if (! static::isValidSignatureDataUri($value)) {
                    $fail('Format tanda tangan digital tidak valid. Gunakan kanvas tanda tangan atau gambar PNG/JPG.');
                }
            },
        ];
    }

    public static function isValidSignatureDataUri(?string $value): bool
    {
        if (empty($value)) {
            return false;
        }

        if (! preg_match('/^data:image\/(png|jpe?g|webp);base64,([A-Za-z0-9+\/\r\n=]+)$/i', trim($value), $matches)) {
            return false;
        }

        $decoded = base64_decode($matches[2], true);

        return $decoded !== false && strlen($decoded) > 0;
    }

    /**
     * Mengonversi berkas gambar tanda tangan menjadi PNG data URI terkompresi.
     */
    public static function encodeUploadedSignature(UploadedFile $file): ?string
    {
        $realPath = $file->getRealPath();
        if (! $realPath || ! is_file($realPath)) {
            return null;
        }

        $raw = file_get_contents($realPath);
        if ($raw === false || $raw === '') {
            return null;
        }

        if (function_exists('imagecreatefromstring')) {
            $src = @imagecreatefromstring($raw);
            if ($src !== false) {
                $width = imagesx($src);
                $height = imagesy($src);
                $maxWidth = 600;
                $maxHeight = 240;

                if ($width > $maxWidth || $height > $maxHeight) {
                    $scale = min($maxWidth / max($width, 1), $maxHeight / max($height, 1));
                    $targetWidth = max(1, (int) round($width * $scale));
                    $targetHeight = max(1, (int) round($height * $scale));
                } else {
                    $targetWidth = $width;
                    $targetHeight = $height;
                }

                $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
                imagealphablending($canvas, false);
                imagesavealpha($canvas, true);
                $transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
                imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $transparent);
                imagecopyresampled($canvas, $src, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

                ob_start();
                imagepng($canvas, null, 6);
                $pngBinary = ob_get_clean();

                imagedestroy($canvas);
                imagedestroy($src);

                if (is_string($pngBinary) && $pngBinary !== '') {
                    return 'data:image/png;base64,'.base64_encode($pngBinary);
                }
            }
        }

        $mime = $file->getMimeType() ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode($raw);
    }
}
