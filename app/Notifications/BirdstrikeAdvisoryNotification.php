<?php

namespace App\Notifications;

use App\Models\PrediksiBirdstrikeAi;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BirdstrikeAdvisoryNotification extends Notification
{
    use Queueable;

    public function __construct(
        public PrediksiBirdstrikeAi $prediksi
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $jamRawan = ($this->prediksi->jam_rawan_mulai && $this->prediksi->jam_rawan_selesai)
            ? "{$this->prediksi->jam_rawan_mulai} - {$this->prediksi->jam_rawan_selesai} WIB"
            : 'N/A';

        return [
            'prediksi_id' => $this->prediksi->id,
            'tanggal' => $this->prediksi->tanggal_prediksi?->toDateString(),
            'level_bahaya' => $this->prediksi->level_bahaya,
            'skor_probabilitas' => $this->prediksi->skor_probabilitas,
            'jam_rawan' => $jamRawan,
            'ringkasan' => $this->prediksi->ringkasan_eksekutif,
            'url' => route('admin.dashboard'),
            'type' => 'birdstrike_advisory',
            'created_at' => now()->toIso8601String(),
        ];
    }
}
