<?php

namespace App\Models;

use Database\Factories\TindakanPengusiranFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Log tindakan pengusiran satwa (scare action) per waktu dan grid.
 * Menjadi variabel x_pengusiran pada prediksi bird strike (PRD v2.1 FR-0.3).
 *
 * @property Carbon $waktu
 */
class TindakanPengusiran extends Model
{
    /** @use HasFactory<TindakanPengusiranFactory> */
    use HasFactory;

    /**
     * @var array<string, string>
     */
    public const HASIL = [
        'berhasil' => 'Berhasil (satwa menjauh)',
        'sebagian' => 'Sebagian (satwa kembali / berpindah dekat)',
        'tidak_berhasil' => 'Tidak berhasil',
    ];

    protected $table = 'tindakan_pengusiran';

    protected $fillable = [
        'laporan_id',
        'alat_id',
        'user_id',
        'waktu',
        'grid',
        'jumlah_digunakan',
        'hasil',
        'keterangan',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'waktu' => 'datetime',
            'jumlah_digunakan' => 'integer',
        ];
    }

    public function alat(): BelongsTo
    {
        return $this->belongsTo(MasterAlatDispersal::class, 'alat_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(Laporan::class);
    }
}
