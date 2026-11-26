<?php

namespace App\Http\Requests;

use App\Models\MasterAlatDispersal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAlatDispersalRequest extends FormRequest
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
            'nama_alat' => [
                'required',
                'string',
                'max:150',
                Rule::unique('master_alat_dispersal', 'nama_alat')->ignore($this->route('id')),
            ],
            'kategori' => ['required', Rule::in(MasterAlatDispersal::KATEGORI)],
            'satuan' => ['required', 'string', 'max:50'],
            'stok_tersedia' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nama_alat' => 'nama alat',
            'stok_tersedia' => 'stok tersedia',
        ];
    }
}
