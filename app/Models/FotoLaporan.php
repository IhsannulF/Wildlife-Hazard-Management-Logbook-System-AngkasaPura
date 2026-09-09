<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

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
            return asset('storage/uploads/extra/' . $this->nama_file);
        }
        return asset('storage/uploads/satwa/' . $this->nama_file);
    }
}
