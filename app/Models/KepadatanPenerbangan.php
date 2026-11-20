<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KepadatanPenerbangan extends Model
{
    use HasFactory;

    protected $table = 'kepadatan_penerbangan';

    protected $fillable = [
        'tanggal',
        'jam',
        'jumlah_pergerakan',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jam' => 'integer',
            'jumlah_pergerakan' => 'integer',
        ];
    }
}
