<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoriBirdstrike extends Model
{
    use HasFactory;

    /**
     * @var array<string, string>
     */
    public const JENIS_KEJADIAN = [
        'strike' => 'Bird strike',
        'near_miss' => 'Near-miss',
    ];

    protected $table = 'histori_birdstrike';

    protected $fillable = [
        'tanggal_kejadian',
        'jam_kejadian',
        'jenis_kejadian',
        'kode_runway',
        'titik_lokasi',
        'grid',
        'koordinat_lat',
        'koordinat_lng',
        'jenis_pesawat',
        'fase_penerbangan',
        'ketinggian_ft',
        'jarak_centerline_m',
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
            'ketinggian_ft' => 'integer',
            'jarak_centerline_m' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (HistoriBirdstrike $model): void {
            if ($model->tanggal_kejadian && $model->jenis_kejadian !== 'near_miss') {
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
