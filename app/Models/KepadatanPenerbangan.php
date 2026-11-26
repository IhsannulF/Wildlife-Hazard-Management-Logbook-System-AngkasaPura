<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Pergerakan pesawat per jam. `jumlah_pergerakan` adalah total; bila take-off/landing diisi,
 * total otomatis disamakan dengan jumlah keduanya (PRD v2.1 FR-0.6).
 */
class KepadatanPenerbangan extends Model
{
    use HasFactory;

    protected $table = 'kepadatan_penerbangan';

    protected $fillable = [
        'tanggal',
        'jam',
        'jumlah_takeoff',
        'jumlah_landing',
        'jumlah_pergerakan',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jam' => 'integer',
            'jumlah_takeoff' => 'integer',
            'jumlah_landing' => 'integer',
            'jumlah_pergerakan' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (KepadatanPenerbangan $model): void {
            if ($model->isDirty(['jumlah_takeoff', 'jumlah_landing'])) {
                $model->jumlah_pergerakan = (int) $model->jumlah_takeoff + (int) $model->jumlah_landing;
            }
        });
    }

    /**
     * Baris lama yang hanya mencatat total, tanpa rincian take-off/landing.
     */
    public function isSplit(): bool
    {
        return ($this->jumlah_takeoff + $this->jumlah_landing) > 0 || $this->jumlah_pergerakan === 0;
    }
}
