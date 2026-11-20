<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrediksiBirdstrikeAi extends Model
{
    use HasFactory;

    protected $table = 'prediksi_birdstrike_ai';

    protected $fillable = [
        'tanggal_prediksi',
        'waktu_analisis',
        'user_id',
        'trigger',
        'status_eksekusi',
        'error_message',
        'skor_probabilitas',
        'level_bahaya',
        'jam_rawan_mulai',
        'jam_rawan_selesai',
        'grid_kritis',
        'spesies_target',
        'faktor_penyebab',
        'ringkasan_eksekutif',
        'rekomendasi_taktis',
        'confidence_score',
        'model_version',
        'input_payload',
        'raw_response',
        'durasi_ms',
        'status_advisory',
        'diakui_oleh',
        'diakui_at',
        'insiden_terjadi',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_prediksi' => 'date',
            'waktu_analisis' => 'datetime',
            'skor_probabilitas' => 'decimal:2',
            'confidence_score' => 'decimal:2',
            'grid_kritis' => 'array',
            'spesies_target' => 'array',
            'faktor_penyebab' => 'array',
            'rekomendasi_taktis' => 'array',
            'input_payload' => 'array',
            'raw_response' => 'array',
            'durasi_ms' => 'integer',
            'diakui_at' => 'datetime',
            'insiden_terjadi' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diakui_oleh');
    }

    public function scopeLatestSuccess(Builder $query): Builder
    {
        return $query->where('status_eksekusi', 'sukses')->latest('waktu_analisis');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status_advisory', 'aktif');
    }
}
