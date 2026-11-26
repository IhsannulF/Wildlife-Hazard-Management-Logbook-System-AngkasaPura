<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Pengaturan isian form inspeksi yang dikelola admin (halaman Form Inspeksi).
 *
 * @property string $nama_field
 * @property string $tipe
 * @property string $label
 * @property string|null $placeholder
 * @property bool $wajib
 * @property bool $aktif
 * @property int $urutan
 */
class FormField extends Model
{
    use HasFactory;

    /**
     * Field bawaan yang tampilannya sudah tersedia di form inspeksi (tidak bisa dihapus, hanya diatur).
     *
     * @var list<string>
     */
    public const STANDARD_FIELDS = [
        'nama_petugas', 'tanggal', 'kondisi_cuaca', 'unit_kerja', 'area_inspeksi',
        'grid_lokasi', 'tanda_tangan', 'ciri_ukuran', 'kondisi_apron', 'aktivitas_satwa',
        'tindak_lanjut', 'detail_pengusiran',
    ];

    /**
     * Field inti yang selalu aktif & wajib: dipakai Berita Acara, nomor laporan, dan prediksi per grid.
     *
     * @var list<string>
     */
    public const LOCKED_FIELDS = ['tanggal', 'grid_lokasi', 'tanda_tangan'];

    /**
     * Tipe isian yang bisa dipilih untuk field tambahan.
     *
     * @var array<string, string>
     */
    public const CUSTOM_TYPES = [
        'text' => 'Teks pendek',
        'textarea' => 'Teks panjang',
        'dropdown' => 'Pilihan (dropdown)',
        'date' => 'Tanggal',
        'number' => 'Angka',
    ];

    protected $table = 'form_fields';

    public const UPDATED_AT = null;

    protected $fillable = [
        'nama_field',
        'tipe',
        'label',
        'placeholder',
        'wajib',
        'aktif',
        'urutan',
        'keterangan',
        'gridmap_path',
    ];

    protected function casts(): array
    {
        return [
            'wajib' => 'boolean',
            'aktif' => 'boolean',
            'urutan' => 'integer',
        ];
    }

    public function options(): HasMany
    {
        return $this->hasMany(FormFieldOption::class, 'field_id')->orderBy('urutan', 'asc');
    }

    public function isLocked(): bool
    {
        return in_array($this->nama_field, self::LOCKED_FIELDS, true);
    }

    public function isStandard(): bool
    {
        return in_array($this->nama_field, self::STANDARD_FIELDS, true);
    }

    /**
     * Ganti daftar pilihan dropdown dari teks satu-pilihan-per-baris; tidak berubah bila isinya sama.
     */
    public function syncOptionsFromText(?string $text): void
    {
        $values = array_values(array_unique(array_filter(array_map('trim', preg_split('/\R/', (string) $text)))));

        if ($values === $this->options->pluck('nilai')->all()) {
            return;
        }

        $this->options()->delete();
        foreach ($values as $index => $value) {
            $this->options()->create(['nilai' => $value, 'urutan' => $index + 1]);
        }
        $this->unsetRelation('options');
    }
}
