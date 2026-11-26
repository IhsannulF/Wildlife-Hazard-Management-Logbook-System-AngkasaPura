<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Snapshot cuaca bandara per 15 menit (PRD v2.1 FR-0.5).
 * `waktu` adalah waktu lokal bandara (WIB) tanpa zona, sama seperti jam laporan.
 *
 * @property Carbon $waktu
 */
class CuacaSnapshot extends Model
{
    protected $table = 'cuaca_snapshot';

    protected $fillable = [
        'waktu',
        'kode_cuaca',
        'hujan_mm',
        'jarak_pandang_km',
        'arah_angin_derajat',
        'kecepatan_angin_kt',
        'hembusan_kt',
        'angin_silang_kt',
        'suhu_c',
        'kelembapan_persen',
        'tutupan_awan_persen',
        'sumber',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'waktu' => 'datetime',
            'kode_cuaca' => 'integer',
            'hujan_mm' => 'float',
            'jarak_pandang_km' => 'float',
            'arah_angin_derajat' => 'integer',
            'kecepatan_angin_kt' => 'float',
            'hembusan_kt' => 'float',
            'angin_silang_kt' => 'float',
            'suhu_c' => 'float',
            'kelembapan_persen' => 'integer',
            'tutupan_awan_persen' => 'integer',
        ];
    }
}
