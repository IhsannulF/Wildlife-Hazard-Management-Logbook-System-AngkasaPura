<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Atribut sel gridmap A–L × 1–29 terhadap runway 10/28 (PRD v2.1 FR-1.1).
 *
 * @property string $grid
 * @property string $zona
 * @property int $jarak_runway_m
 * @property bool $di_approach
 * @property string $runway_terdekat
 * @property bool $terkalibrasi
 */
class GridCell extends Model
{
    protected $table = 'grid_cell';

    protected $fillable = [
        'grid',
        'baris',
        'kolom',
        'zona',
        'jarak_runway_m',
        'di_approach',
        'runway_terdekat',
        'terkalibrasi',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kolom' => 'integer',
            'jarak_runway_m' => 'integer',
            'di_approach' => 'boolean',
            'terkalibrasi' => 'boolean',
        ];
    }
}
