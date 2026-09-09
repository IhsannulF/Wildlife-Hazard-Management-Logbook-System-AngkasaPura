<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormField extends Model
{
    use HasFactory;

    protected $table = 'form_fields';

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
}
