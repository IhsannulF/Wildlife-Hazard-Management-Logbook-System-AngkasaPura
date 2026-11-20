<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SpeciesIdentificationValidator
{
    /**
     * Validate and normalize AI vision output.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    public function validate(array $data): array
    {
        $validator = Validator::make($data, [
            'terdeteksi_satwa' => ['required', 'boolean'],
            'kandidat' => ['present', 'array'],
            'kandidat.*.nama_lokal' => ['required_if:terdeteksi_satwa,true', 'string'],
            'kandidat.*.nama_latin' => ['required_if:terdeteksi_satwa,true', 'string'],
            'kandidat.*.confidence' => ['required_if:terdeteksi_satwa,true', 'numeric', 'min:0', 'max:1'],
            'rekomendasi_sop' => ['present', 'array'],
            'rekomendasi_sop.*.metode' => ['required', 'in:akustik,piroteknik,optik,fisik'],
            'rekomendasi_sop.*.langkah' => ['required', 'string'],
            'rekomendasi_sop.*.catatan_keselamatan' => ['nullable', 'string'],
            'catatan' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $terdeteksi = (bool) $data['terdeteksi_satwa'];
        if (! $terdeteksi) {
            return [
                'terdeteksi_satwa' => false,
                'kandidat' => [],
                'rekomendasi_sop' => [],
                'catatan' => $data['catatan'] ?? 'Tidak terdeteksi satwa liar pada gambar.',
            ];
        }

        $kandidat = array_slice($data['kandidat'] ?? [], 0, 3);
        $rekomendasi = array_slice($data['rekomendasi_sop'] ?? [], 0, 4);

        return [
            'terdeteksi_satwa' => true,
            'kandidat' => $kandidat,
            'rekomendasi_sop' => $rekomendasi,
            'catatan' => $data['catatan'] ?? null,
        ];
    }
}
