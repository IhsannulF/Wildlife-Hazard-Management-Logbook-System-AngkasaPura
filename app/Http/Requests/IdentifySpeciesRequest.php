<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IdentifySpeciesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPegawai() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'foto' => ['required', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'foto.required' => 'Harap unggah foto bukti satwa untuk diidentifikasi.',
            'foto.image' => 'Berkas harus berupa gambar valid (JPEG, PNG, atau WEBP).',
            'foto.mimes' => 'Format gambar harus JPEG, JPG, PNG, atau WEBP.',
            'foto.max' => 'Ukuran foto maksimal adalah 5 MB.',
        ];
    }
}
