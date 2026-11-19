<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHistoriBirdstrikeRequest extends FormRequest
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
            'tanggal_kejadian' => ['required', 'date', 'before_or_equal:today'],
            'jam_kejadian' => ['nullable', 'date_format:H:i'],
            'kode_runway' => ['required', 'in:10,28,07L,25R'],
            'titik_lokasi' => ['required', 'in:touchdown,roll,threshold,grid'],
            'koordinat_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'koordinat_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'jenis_pesawat' => ['required', 'string', 'max:50'],
            'fase_penerbangan' => ['required', 'in:take_off,landing,approach,climb,taxi'],
            'bagian_pesawat' => ['required', 'string', 'max:50'],
            'spesies_satwa' => ['required', 'string', 'max:100'],
            'jumlah_satwa' => ['required', 'integer', 'min:1'],
            'tingkat_kerusakan' => ['required', 'in:nihil,minor,substansial,fatal'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
