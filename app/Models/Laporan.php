<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Laporan extends Model
{
    use HasFactory;

    protected $table = 'laporan';

    protected $fillable = [
        'user_id',
        'nama_petugas',
        'tanggal',
        'kondisi_cuaca',
        'unit_kerja',
        'area_inspeksi',
        'tanda_tangan',
        'grid_lokasi',
        'ciri_ukuran',
        'kondisi_apron',
        'aktivitas_satwa',
        'tindak_lanjut',
        'detail_pengusiran',
        'status',
        'extra_data',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'extra_data' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detailSatwa(): HasMany
    {
        return $this->hasMany(DetailSatwa::class, 'laporan_id');
    }

    public function fotoLaporan(): HasMany
    {
        return $this->hasMany(FotoLaporan::class, 'laporan_id');
    }

    public function tanggapan(): HasMany
    {
        return $this->hasMany(Tanggapan::class, 'laporan_id');
    }

    public function getFotoExtraAttribute(): array
    {
        return $this->fotoLaporan()
            ->where('tipe', 'extra')
            ->pluck('nama_file')
            ->map(fn($file) => 'storage/uploads/extra/' . $file)
            ->toArray();
    }
}
