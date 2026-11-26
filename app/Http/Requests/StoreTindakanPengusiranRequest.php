<?php

namespace App\Http\Requests;

use App\Models\TindakanPengusiran;
use App\Support\GridCode;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTindakanPengusiranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('grid')) {
            $this->merge(['grid' => GridCode::normalize((string) $this->input('grid'))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'alat_id' => ['required', 'integer', Rule::exists('master_alat_dispersal', 'id')->where('aktif', true)],
            // Waktu lapangan disimpan sebagai WIB tanpa zona, jadi dibandingkan dengan "sekarang" versi WIB.
            'waktu' => ['required', 'date', 'before_or_equal:'.now(config('whms_risk.zona_waktu'))->format('Y-m-d H:i:s')],
            'grid' => [
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! GridCode::isValid((string) $value)) {
                        $fail('Kode grid tidak valid. Gunakan format huruf A–L dan angka 1–29, misalnya K-10.');
                    }
                },
            ],
            'jumlah_digunakan' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'hasil' => ['nullable', Rule::in(array_keys(TindakanPengusiran::HASIL))],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'alat_id' => 'alat pengusiran',
            'waktu' => 'waktu pengusiran',
            'jumlah_digunakan' => 'jumlah pemakaian',
        ];
    }
}
