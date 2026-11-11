<?php

namespace App\Models;

use Carbon\Carbon;
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
        'no_laporan',
        'nama_petugas',
        'tanggal',
        'jam',
        'kondisi_cuaca',
        'unit_kerja',
        'area_inspeksi',
        'tanda_tangan',
        'grid_lokasi',
        'gps_lat',
        'gps_lng',
        'risk_score',
        'risk_level',
        'ciri_ukuran',
        'kondisi_apron',
        'aktivitas_satwa',
        'tindak_lanjut',
        'detail_pengusiran',
        'status',
        'extra_data',
        'offline_sync_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'risk_score' => 'integer',
            'gps_lat' => 'float',
            'gps_lng' => 'float',
            'extra_data' => 'array',
        ];
    }

    public function getKodeGridAttribute(): ?string
    {
        return $this->grid_lokasi;
    }

    protected static function booted(): void
    {
        static::creating(function (Laporan $laporan) {
            if (empty($laporan->no_laporan)) {
                $tanggalStr = null;
                if (! empty($laporan->tanggal)) {
                    $tanggalStr = is_string($laporan->tanggal) ? $laporan->tanggal : $laporan->tanggal->format('Y-m-d');
                }
                $laporan->no_laporan = static::generateNoLaporan(
                    $laporan->unit_kerja ?? 'AMC',
                    $tanggalStr
                );
            }
        });
    }

    public static function generateNoLaporan(string $unitKerja, ?string $tanggal = null, ?int $id = null): string
    {
        $unit = strtoupper(trim($unitKerja ?: 'AMC'));
        $unitCode = 'AMC';
        if (str_contains($unit, 'RESCUE') || str_contains($unit, 'ARFF') || str_contains($unit, 'PK')) {
            $unitCode = 'ARFF';
        } elseif (str_contains($unit, 'SEC') || str_contains($unit, 'AVSEC')) {
            $unitCode = 'AVSEC';
        } elseif (str_contains($unit, 'SMS') || str_contains($unit, 'SAFETY')) {
            $unitCode = 'SMS';
        } elseif (str_contains($unit, 'APRON') || str_contains($unit, 'AMC')) {
            $unitCode = 'AMC';
        }

        $date = ! empty($tanggal) ? Carbon::parse($tanggal) : Carbon::now();
        $bulan = $date->format('m');
        $tahun = $date->format('Y');

        if (! $id) {
            $lastId = (int) (static::max('id') ?? 0) + 1;
        } else {
            $lastId = $id;
        }

        return sprintf('BA-WHMS/AP1/%s/%s/%s/%04d', $unitCode, $bulan, $tahun, $lastId);
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
        return $this->hasMany(Tanggapan::class, 'laporan_id')->latest();
    }

    public function getFotoExtraAttribute(): array
    {
        return $this->fotoLaporan()
            ->where('tipe', 'extra')
            ->pluck('nama_file')
            ->map(fn ($file) => 'storage/uploads/extra/'.$file)
            ->toArray();
    }
}
