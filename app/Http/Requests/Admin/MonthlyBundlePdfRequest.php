<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MonthlyBundlePdfRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna berwenang meminta bundel logbook bulanan.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Aturan validasi parameter bulan, tahun, dan filter opsional bundel PDF.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'between:2020,2100'],
            'status' => ['nullable', 'string', 'in:all,sudah,belum'],
            'unit_kerja' => ['nullable', 'string', 'max:100'],
            'inline' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'bulan.required' => 'Bulan arsip logbook wajib dipilih.',
            'bulan.integer' => 'Nilai bulan tidak valid.',
            'bulan.between' => 'Bulan arsip harus berada di antara 1 sampai 12.',
            'tahun.required' => 'Tahun arsip logbook wajib dipilih.',
            'tahun.integer' => 'Nilai tahun tidak valid.',
            'tahun.between' => 'Tahun arsip harus berada di antara 2020 sampai 2100.',
            'status.in' => 'Filter status penanganan harus bernilai semua, sudah, atau belum.',
        ];
    }

    public function bulan(): int
    {
        return (int) $this->validated('bulan');
    }

    public function tahun(): int
    {
        return (int) $this->validated('tahun');
    }

    public function statusFilter(): string
    {
        $status = $this->validated('status');

        return is_string($status) && $status !== '' ? $status : 'all';
    }

    public function unitKerjaFilter(): string
    {
        $unit = $this->validated('unit_kerja');

        return is_string($unit) && trim($unit) !== '' ? trim($unit) : 'all';
    }

    public function isInline(): bool
    {
        return $this->boolean('inline');
    }
}
