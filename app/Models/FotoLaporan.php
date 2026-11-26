<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Foto bukti inspeksi. Lokasi GPS perangkat & waktu ambil juga tercetak di watermark foto.
 *
 * @property float|null $gps_lat
 * @property float|null $gps_lng
 * @property int|null $gps_akurasi_m
 * @property Carbon|null $diambil_at
 * @property string|null $sumber
 */
class FotoLaporan extends Model
{
    use HasFactory;

    protected $table = 'foto_laporan';

    protected $fillable = [
        'laporan_id',
        'detail_satwa_id',
        'nama_file',
        'tipe',
        'foto_path',
        'gps_lat',
        'gps_lng',
        'gps_akurasi_m',
        'diambil_at',
        'sumber',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gps_lat' => 'float',
            'gps_lng' => 'float',
            'gps_akurasi_m' => 'integer',
            'diambil_at' => 'datetime',
        ];
    }

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(Laporan::class, 'laporan_id');
    }

    public function detailSatwa(): BelongsTo
    {
        return $this->belongsTo(DetailSatwa::class, 'detail_satwa_id');
    }

    public function getUrlAttribute(): string
    {
        if ($this->tipe === 'extra') {
            return asset('storage/uploads/extra/'.$this->nama_file);
        }

        return asset('storage/uploads/satwa/'.$this->nama_file);
    }
}
