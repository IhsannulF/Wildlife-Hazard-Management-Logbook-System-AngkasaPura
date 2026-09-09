<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormFieldOption extends Model
{
    use HasFactory;

    protected $table = 'form_field_options';

    protected $fillable = [
        'field_id',
        'nilai',
        'urutan',
    ];

    public function formField(): BelongsTo
    {
        return $this->belongsTo(FormField::class, 'field_id');
    }
}
