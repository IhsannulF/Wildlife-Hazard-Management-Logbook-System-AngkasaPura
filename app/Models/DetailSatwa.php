<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DetailSatwa extends Model
{
    use HasFactory;

    /**
     * @var array<string, string>
     */
    public const PERILAKU = [
        'melintas' => 'Terbang melintas',
        'mencari_makan' => 'Mencari makan',
        'bertengger' => 'Bertengger / diam',
        'lainnya' => 'Lainnya',
    ];

    /**
     * @var array<string, string>
     */
    public const ARAH_TERBANG = [
        'U' => 'Utara',
        'TL' => 'Timur Laut',
        'T' => 'Timur',
        'TG' => 'Tenggara',
        'S' => 'Selatan',
        'BD' => 'Barat Daya',
        'B' => 'Barat',
        'BL' => 'Barat Laut',
    ];

    /**
     * @var array<string, string>
     */
    public const KETINGGIAN = [
        '<50ft' => '< 50 ft (di darat / sangat rendah)',
        '50-200ft' => '50–200 ft',
        '>200ft' => '> 200 ft',
    ];

    protected $table = 'detail_satwa';

    protected $fillable = [
        'laporan_id',
        'nama_satwa',
        'nama_latin',
        'jumlah',
        'grid',
        'perilaku',
        'arah_terbang',
        'ketinggian',
        'foto_path',
        'sumber_identifikasi',
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

    /**
     * Ringkasan perilaku kawanan (FR-0.2) untuk modal admin, berita acara, dan ekspor.
     * Null bila petugas tidak mengisi satu pun kolomnya.
     */
    public function flockBehaviorSummary(): ?string
    {
        $parts = array_filter([
            self::PERILAKU[$this->perilaku] ?? null,
            isset(self::ARAH_TERBANG[$this->arah_terbang]) ? 'arah '.self::ARAH_TERBANG[$this->arah_terbang] : null,
            isset(self::KETINGGIAN[$this->ketinggian]) ? 'ketinggian '.strtok(self::KETINGGIAN[$this->ketinggian], '(') : null,
        ]);

        return $parts === [] ? null : trim(implode(' · ', array_map('trim', $parts)));
    }
}
