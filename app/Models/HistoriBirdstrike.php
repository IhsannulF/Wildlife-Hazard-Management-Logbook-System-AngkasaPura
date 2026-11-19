<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoriBirdstrike extends Model
{
    use HasFactory;

    protected $table = 'histori_birdstrike';

    protected $fillable = [
        'tanggal_kejadian',
        'jam_kejadian',
        'kode_runway',
        'titik_lokasi',
        'koordinat_lat',
        'koordinat_lng',
        'jenis_pesawat',
        'fase_penerbangan',
        'bagian_pesawat',
        'spesies_satwa',
        'jumlah_satwa',
        'tingkat_kerusakan',
        'keterangan',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_kejadian' => 'date',
            'koordinat_lat' => 'decimal:7',
            'koordinat_lng' => 'decimal:7',
            'jumlah_satwa' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (HistoriBirdstrike $model): void {
            if ($model->tanggal_kejadian) {
                PrediksiBirdstrikeAi::query()
                    ->whereDate('tanggal_prediksi', $model->tanggal_kejadian)
                    ->update(['insiden_terjadi' => true]);
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
