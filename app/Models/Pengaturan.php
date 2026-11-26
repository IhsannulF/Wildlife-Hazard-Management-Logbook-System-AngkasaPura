<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Pengaturan kunci–nilai yang dapat diubah admin tanpa deploy (PRD v2.1 FR-1.4).
 */
class Pengaturan extends Model
{
    public const AMBANG_ZONASI = 'zonasi.ambang';

    protected $table = 'pengaturan';

    protected $fillable = [
        'kunci',
        'nilai',
        'diubah_oleh',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nilai' => 'array',
        ];
    }

    /**
     * Nilai tersimpan, atau `$default` bila belum pernah diubah admin.
     */
    public static function value(string $key, mixed $default = null): mixed
    {
        return static::query()->where('kunci', $key)->first()?->nilai ?? $default;
    }
}
