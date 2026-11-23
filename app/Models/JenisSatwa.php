<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisSatwa extends Model
{
    use HasFactory;

    protected $table = 'jenis_satwa';

    public const UPDATED_AT = null;

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
        if ($this->foto_path && file_exists(public_path('images/'.$this->foto_path))) {
            return asset('images/'.$this->foto_path);
        }
        if ($this->foto_path && file_exists(public_path('storage/'.$this->foto_path))) {
            return asset('storage/'.$this->foto_path);
        }
        if ($this->foto_path && str_starts_with($this->foto_path, 'uploads/')) {
            return asset('storage/'.$this->foto_path);
        }

        return asset('images/'.($this->foto_path ?: 'biawak.jpeg'));
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

    public static function resolveNamaIlmiah(string $nama, string $fallback = '-'): string
    {
        $name = strtolower(trim($nama));

        return match (true) {
            str_contains($name, 'biawak') => 'Varanus salvator',
            str_contains($name, 'blekok') => 'Ardeola speciosa',
            str_contains($name, 'cangak abu') => 'Ardea cinerea',
            str_contains($name, 'cangak merah') => 'Ardea purpurea',
            str_contains($name, 'kuntul kecil') => 'Egretta garzetta',
            str_contains($name, 'kuntul kerbau'), str_contains($name, 'kuntul') => 'Bubulcus ibis',
            str_contains($name, 'pecuk') => 'Microcarbo niger',
            str_contains($name, 'kera'), str_contains($name, 'monyet') => 'Macaca fascicularis',
            str_contains($name, 'kucing') => 'Felis catus',
            str_contains($name, 'anjing') => 'Canis lupus familiaris',
            str_contains($name, 'sanca'), str_contains($name, 'ular') => 'Malayopython reticulatus',
            str_contains($name, 'elang') => 'Elanus caeruleus',
            str_contains($name, 'layang'), str_contains($name, 'walet') => 'Hirundo rustica',
            str_contains($name, 'gereja') => 'Passer montanus',
            str_contains($name, 'dara'), str_contains($name, 'merpati') => 'Columba livia',
            default => $fallback,
        };
    }

    public function getNamaIlmiahAttribute(): string
    {
        return static::resolveNamaIlmiah((string) $this->nama, 'Spesies liar');
    }

    public function getTaksonPillAttribute(): string
    {
        $name = strtolower($this->nama);
        if (str_contains($name, 'biawak') || str_contains($name, 'ular')) {
            return 'Reptil';
        }
        if (str_contains($name, 'kera') || str_contains($name, 'anjing') || str_contains($name, 'kucing')) {
            return 'Mamalia';
        }

        return 'Avian';
    }

    public function getTagRisikoAttribute(): array
    {
        $name = strtolower($this->nama);
        if (str_contains($name, 'biawak')) {
            return ['label' => 'Tinggi Risiko Runway', 'class' => 'bg-rose-50 text-rose-600 border border-rose-200/80'];
        }
        if (str_contains($name, 'blekok')) {
            return ['label' => 'Risiko Sedang', 'class' => 'bg-amber-50 text-amber-700 border border-amber-200/80'];
        }
        if (str_contains($name, 'kuntul kerbau') || str_contains($name, 'kuntul kecil')) {
            return ['label' => 'Risiko Flocking', 'class' => 'bg-amber-50 text-amber-700 border border-amber-200/80'];
        }
        if (str_contains($name, 'kera')) {
            return ['label' => 'Zona Perimeter', 'class' => 'bg-slate-50 text-slate-600 border border-slate-200'];
        }
        if (str_contains($name, 'anjing') || str_contains($name, 'kucing')) {
            return ['label' => 'Tinggi Bahaya', 'class' => 'bg-rose-50 text-rose-600 border border-rose-200/80'];
        }
        if (str_contains($name, 'ular')) {
            return ['label' => 'Risiko Saluran', 'class' => 'bg-amber-50 text-amber-700 border border-amber-200/80'];
        }

        return ['label' => 'Risiko Sedang', 'class' => 'bg-amber-50 text-amber-700 border border-amber-200/80'];
    }

    public function getKodeSpesiesAttribute(): string
    {
        $name = strtolower($this->nama);
        if (str_contains($name, 'biawak')) {
            return '#SAT-094';
        }
        if (str_contains($name, 'blekok')) {
            return '#SAT-021';
        }
        if (str_contains($name, 'kuntul kerbau')) {
            return '#SAT-015';
        }
        if (str_contains($name, 'kera')) {
            return '#SAT-052';
        }
        if (str_contains($name, 'anjing') || str_contains($name, 'kucing')) {
            return '#SAT-063';
        }
        if (str_contains($name, 'ular')) {
            return '#SAT-088';
        }

        return '#SAT-'.str_pad((string) $this->id, 3, '0', STR_PAD_LEFT);
    }
}
