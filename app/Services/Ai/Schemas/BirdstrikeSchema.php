<?php

namespace App\Services\Ai\Schemas;

class BirdstrikeSchema
{
    /**
     * @return array<string, mixed>
     */
    public static function get(): array
    {
        return [
            'type' => 'object',
            'required' => [
                'probability_score',
                'hazard_level',
                'critical_time_window',
                'critical_zones',
                'target_species',
                'contributing_factors',
                'confidence_score',
                'executive_summary',
                'actionable_recommendations',
            ],
            'properties' => [
                'probability_score' => [
                    'type' => 'number',
                    'minimum' => 0,
                    'maximum' => 1,
                    'description' => 'Nilai probabilitas birdstrike 0.0 sampai 1.0',
                ],
                'hazard_level' => [
                    'type' => 'string',
                    'enum' => ['rendah', 'sedang', 'tinggi', 'kritis'],
                ],
                'critical_time_window' => [
                    'type' => 'string',
                    'description' => "Rentang jam rawan format 'HH:mm - HH:mm WIB'",
                ],
                'critical_zones' => [
                    'type' => 'array',
                    'maxItems' => 12,
                    'items' => [
                        'type' => 'string',
                        'description' => "Kode grid contoh 'K-8', 'D-9'",
                    ],
                ],
                'target_species' => [
                    'type' => 'array',
                    'maxItems' => 5,
                    'items' => [
                        'type' => 'object',
                        'required' => ['nama_lokal', 'nama_latin', 'risiko_ancaman'],
                        'properties' => [
                            'nama_lokal' => ['type' => 'string'],
                            'nama_latin' => ['type' => 'string'],
                            'risiko_ancaman' => ['type' => 'string'],
                        ],
                    ],
                ],
                'contributing_factors' => [
                    'type' => 'array',
                    'maxItems' => 8,
                    'items' => [
                        'type' => 'string',
                    ],
                ],
                'confidence_score' => [
                    'type' => 'number',
                    'minimum' => 0,
                    'maximum' => 1,
                ],
                'executive_summary' => [
                    'type' => 'string',
                ],
                'actionable_recommendations' => [
                    'type' => 'array',
                    'minItems' => 1,
                    'maxItems' => 6,
                    'items' => [
                        'type' => 'string',
                    ],
                ],
            ],
        ];
    }
}
