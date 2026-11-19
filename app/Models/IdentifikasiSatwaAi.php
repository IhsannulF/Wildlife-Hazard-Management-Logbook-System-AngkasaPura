<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdentifikasiSatwaAi extends Model
{
    use HasFactory;

    protected $table = 'identifikasi_satwa_ai';

    protected $fillable = [
        'user_id',
        'laporan_id',
        'foto_path',
        'model_version',
        'hasil',
        'nama_lokal',
        'nama_latin',
        'confidence',
        'diterima',
        'nama_final',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hasil' => 'array',
            'confidence' => 'decimal:2',
            'diterima' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(Laporan::class, 'laporan_id');
    }
}
