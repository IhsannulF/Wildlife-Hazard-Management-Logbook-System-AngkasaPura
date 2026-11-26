<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Ambang kelas skor zonasi (PRD v2.1 FR-1.4); harus naik berurutan agar keempat kelas tetap terpisah.
 */
class UpdateAmbangZonasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sedang' => ['required', 'numeric', 'min:0.1', 'max:1000'],
            'tinggi' => ['required', 'numeric', 'gt:sedang', 'max:1000'],
            'sangat_tinggi' => ['required', 'numeric', 'gt:tinggi', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tinggi.gt' => 'Ambang Tinggi harus lebih besar dari ambang Sedang.',
            'sangat_tinggi.gt' => 'Ambang Sangat Tinggi harus lebih besar dari ambang Tinggi.',
        ];
    }
}
