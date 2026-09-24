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
        'kategori',
        'tingkat_risiko',
        'grid_hotspot',
        'deskripsi',
        'sop_pengusiran',
        'jam_puncak',
        'bobot_rata_rata',
        'foto_path',
    ];

    public function getFotoUrlAttribute(): string
    {
        if ($this->foto_path && (str_starts_with($this->foto_path, 'http://') || str_starts_with($this->foto_path, 'https://'))) {
            return $this->foto_path;
        }
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

    public function getKategoriLabelAttribute(): string
    {
        return match (strtolower($this->kategori ?? '')) {
            'reptil' => 'Reptil',
            'mamalia' => 'Mamalia',
            default => 'Avian / Burung',
        };
    }

    public function getRisikoLabelAttribute(): string
    {
        return match (strtolower($this->tingkat_risiko ?? '')) {
            'kritis' => 'Risiko Kritis (Kategori 4)',
            'rendah' => 'Risiko Rendah (Kategori 2)',
            default => 'Risiko Sedang (Kategori 3)',
        };
    }
}
