<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DetailSatwa extends Model
{
    use HasFactory;

    protected $table = 'detail_satwa';

    protected $fillable = [
        'laporan_id',
        'nama_satwa',
        'jumlah',
        'grid',
        'foto_path',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
        ];
    }

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(Laporan::class, 'laporan_id');
    }

    public function fotoLaporan(): HasMany
    {
        return $this->hasMany(FotoLaporan::class, 'detail_satwa_id');
    }
}
