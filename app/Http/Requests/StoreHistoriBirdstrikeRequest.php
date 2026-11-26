<?php

namespace App\Http\Requests;

use App\Support\GridCode;
use Illuminate\Foundation\Http\FormRequest;

class StoreHistoriBirdstrikeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('grid')) {
            $this->merge(['grid' => GridCode::normalize((string) $this->input('grid'))]);
        }
    }

    /**
     * Data tervalidasi dengan jenis kejadian default "strike" bila tidak diisi.
     *
     * @return array<string, mixed>
     */
    public function historiData(): array
    {
        $data = $this->validated();
        $data['jenis_kejadian'] = $data['jenis_kejadian'] ?? 'strike';

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tanggal_kejadian' => ['required', 'date', 'before_or_equal:today'],
            'jam_kejadian' => ['nullable', 'date_format:H:i'],
            'jenis_kejadian' => ['nullable', 'in:strike,near_miss'],
            'kode_runway' => ['required', 'in:10,28'],
            'titik_lokasi' => ['required', 'in:touchdown,roll,threshold,grid'],
            'grid' => ['nullable', 'string', 'max:20', 'regex:'.GridCode::REGEX],
            'ketinggian_ft' => ['nullable', 'integer', 'min:0', 'max:20000'],
            'jarak_centerline_m' => ['nullable', 'integer', 'min:0', 'max:5000'],
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
