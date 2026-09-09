<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisSatwa extends Model
{
    use HasFactory;

    protected $table = 'jenis_satwa';

    protected $fillable = [
        'nama',
        'foto_path',
    ];

    public function getFotoUrlAttribute(): string
    {
        if ($this->foto_path && file_exists(public_path('images/' . $this->foto_path))) {
            return asset('images/' . $this->foto_path);
        }
        if ($this->foto_path && file_exists(public_path('storage/' . $this->foto_path))) {
            return asset('storage/' . $this->foto_path);
        }
        if ($this->foto_path && str_starts_with($this->foto_path, 'uploads/')) {
            return asset('storage/' . $this->foto_path);
        }
        return asset('images/' . ($this->foto_path ?: 'biawak.jpeg'));
    }
}
