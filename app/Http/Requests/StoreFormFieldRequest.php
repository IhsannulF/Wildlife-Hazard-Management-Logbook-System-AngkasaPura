<?php

namespace App\Http\Requests;

use App\Models\FormField;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Tambah isian baru di form inspeksi; nilainya tersimpan di extra_data laporan.
 */
class StoreFormFieldRequest extends FormRequest
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
            'label' => ['required', 'string', 'max:100'],
            'tipe' => ['required', Rule::in(array_keys(FormField::CUSTOM_TYPES))],
            'placeholder' => ['nullable', 'string', 'max:255'],
            'wajib' => ['nullable', 'boolean'],
            'opsi' => ['nullable', 'required_if:tipe,dropdown', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'opsi.required_if' => 'Isi minimal satu pilihan untuk tipe dropdown (satu pilihan per baris).',
        ];
    }
}
