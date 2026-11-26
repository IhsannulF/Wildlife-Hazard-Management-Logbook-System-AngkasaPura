<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterAlatDispersal extends Model
{
    /**
     * @var list<string>
     */
    public const KATEGORI = ['Akustik', 'Piroteknik', 'Optik', 'Fisik', 'Lainnya'];

    protected $table = 'master_alat_dispersal';

    protected $fillable = [
        'nama_alat',
        'kategori',
        'satuan',
        'stok_tersedia',
        'aktif',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stok_tersedia' => 'integer',
            'aktif' => 'boolean',
        ];
    }

    /**
     * @param  Builder<MasterAlatDispersal>  $query
     */
    public function scopeAktif(Builder $query): void
    {
        $query->where('aktif', true);
    }

    public function tindakanPengusiran(): HasMany
    {
        return $this->hasMany(TindakanPengusiran::class, 'alat_id');
    }
}
