<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Simpan sekaligus pengaturan semua isian form inspeksi (label, petunjuk, wajib, aktif, urutan, pilihan).
 */
class UpdateFormInspeksiRequest extends FormRequest
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
            'fields' => ['required', 'array'],
            'fields.*.label' => ['required', 'string', 'max:100'],
            'fields.*.placeholder' => ['nullable', 'string', 'max:255'],
            'fields.*.urutan' => ['required', 'integer', 'min:1', 'max:999'],
            'fields.*.wajib' => ['nullable', 'boolean'],
            'fields.*.aktif' => ['nullable', 'boolean'],
            'fields.*.opsi' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'fields.*.label.required' => 'Label setiap isian wajib diisi.',
        ];
    }
}
