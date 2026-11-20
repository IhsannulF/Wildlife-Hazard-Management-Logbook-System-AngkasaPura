<?php

namespace App\Services\Ai\Schemas;

class SpeciesSchema
{
    /**
     * @return array<string, mixed>
     */
    public static function get(): array
    {
        return [
            'type' => 'object',
            'required' => [
                'terdeteksi_satwa',
                'kandidat',
                'rekomendasi_sop',
            ],
            'properties' => [
                'terdeteksi_satwa' => [
                    'type' => 'boolean',
                ],
                'kandidat' => [
                    'type' => 'array',
                    'maxItems' => 3,
                    'items' => [
                        'type' => 'object',
                        'required' => ['nama_lokal', 'nama_latin', 'confidence'],
                        'properties' => [
                            'nama_lokal' => ['type' => 'string'],
                            'nama_latin' => ['type' => 'string'],
                            'taksonomi' => [
                                'type' => 'object',
                                'properties' => [
                                    'kelas' => ['type' => 'string'],
                                    'ordo' => ['type' => 'string'],
                                    'famili' => ['type' => 'string'],
                                ],
                            ],
                            'estimasi_bobot_kg' => [
                                'type' => 'object',
                                'properties' => [
                                    'min' => ['type' => 'number'],
                                    'max' => ['type' => 'number'],
                                ],
                            ],
                            'kategori_bobot_risiko' => [
                                'type' => 'integer',
                                'minimum' => 1,
                                'maximum' => 3,
                            ],
                            'perilaku_terlihat' => ['type' => 'string'],
                            'confidence' => [
                                'type' => 'number',
                                'minimum' => 0,
                                'maximum' => 1,
                            ],
                        ],
                    ],
                ],
                'rekomendasi_sop' => [
                    'type' => 'array',
                    'maxItems' => 4,
                    'items' => [
                        'type' => 'object',
                        'required' => ['metode', 'langkah'],
                        'properties' => [
                            'metode' => [
                                'type' => 'string',
                                'enum' => ['akustik', 'piroteknik', 'optik', 'fisik'],
                            ],
                            'langkah' => ['type' => 'string'],
                            'catatan_keselamatan' => ['type' => 'string'],
                        ],
                    ],
                ],
                'catatan' => [
                    'type' => 'string',
                ],
            ],
        ];
    }
}
