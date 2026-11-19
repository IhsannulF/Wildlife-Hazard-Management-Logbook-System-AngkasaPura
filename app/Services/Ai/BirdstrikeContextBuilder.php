<?php

namespace App\Services\Ai;

use App\Models\DetailSatwa;
use App\Models\HistoriBirdstrike;
use App\Models\KepadatanPenerbangan;
use App\Models\Laporan;
use App\Services\Weather\OpenMeteoProvider;
use App\Services\Weather\WeatherProvider;
use App\Support\GridCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BirdstrikeContextBuilder
{
    public function __construct(
        protected ?WeatherProvider $weatherProvider = null
    ) {
        $this->weatherProvider = $weatherProvider ?? new OpenMeteoProvider;
    }

    /**
     * Build aggregated context payload for Gemini API.
     *
     * @return array<string, mixed>
     */
    public function build(
        Carbon|string $targetDate,
        ?string $habitatNotes = null,
        int $windowDays = 30
    ): array {
        $target = $targetDate instanceof Carbon ? $targetDate->copy() : Carbon::parse($targetDate);
        $windowDays = min(max($windowDays, 7), config('whms_ai.max_logbook_window_days', 90));

        $payload = $this->compilePayload($target, $habitatNotes, $windowDays);

        // Check payload size (max 60 KB)
        $maxBytes = config('whms_ai.max_payload_kb', 60) * 1024;
        $jsonLength = strlen(json_encode($payload, JSON_UNESCAPED_UNICODE));

        if ($jsonLength > $maxBytes && $windowDays > 14) {
            // Narrow window to 14 days if payload is too large
            $payload = $this->compilePayload($target, $habitatNotes, 14);
        }

        return $payload;
    }

    /**
     * Compile payload data without any user personal information.
     *
     * @return array<string, mixed>
     */
    protected function compilePayload(Carbon $target, ?string $habitatNotes, int $windowDays): array
    {
        $startDate = $target->copy()->subDays($windowDays)->toDateString();
        $targetDateStr = $target->toDateString();

        // 1. Logbook aggregated data
        $logbookQuery = Laporan::query()
            ->whereBetween('tanggal', [$startDate, $targetDateStr]);

        $totalLaporan = (clone $logbookQuery)->count();
        $distinctDays = (clone $logbookQuery)->distinct('tanggal')->count('tanggal');
        $avgRiskScore = round((float) ((clone $logbookQuery)->avg('risk_score') ?? 1.0), 2);

        // Species dominant
        $spesiesDominan = DetailSatwa::query()
            ->join('laporan', 'detail_satwa.laporan_id', '=', 'laporan.id')
            ->whereBetween('laporan.tanggal', [$startDate, $targetDateStr])
            ->select('detail_satwa.nama_satwa', DB::raw('SUM(detail_satwa.jumlah) as total_ekor'), DB::raw('COUNT(detail_satwa.id) as frekuensi'))
            ->groupBy('detail_satwa.nama_satwa')
            ->orderByDesc('total_ekor')
            ->limit(5)
            ->get()
            ->toArray();

        // Total ekor
        $totalEkor = (int) (DetailSatwa::query()
            ->join('laporan', 'detail_satwa.laporan_id', '=', 'laporan.id')
            ->whereBetween('laporan.tanggal', [$startDate, $targetDateStr])
            ->sum('detail_satwa.jumlah'));

        // Top critical zones from logbook
        $gridFreq = Laporan::query()
            ->whereBetween('tanggal', [$startDate, $targetDateStr])
            ->whereNotNull('grid_lokasi')
            ->where('grid_lokasi', '!=', '')
            ->select('grid_lokasi', DB::raw('COUNT(*) as total_insiden'), DB::raw('AVG(risk_score) as avg_risk'))
            ->groupBy('grid_lokasi')
            ->orderByDesc('total_insiden')
            ->limit(6)
            ->get()
            ->map(function ($row) {
                return [
                    'grid' => GridCode::normalize($row->grid_lokasi),
                    'frekuensi' => (int) $row->total_insiden,
                    'rata_rata_risiko' => round((float) $row->avg_risk, 2),
                ];
            })
            ->toArray();

        // Hourly distribution
        $hourly = Laporan::query()
            ->whereBetween('tanggal', [$startDate, $targetDateStr])
            ->whereNotNull('jam')
            ->select(DB::raw('HOUR(jam) as jam_ke'), DB::raw('COUNT(*) as jumlah'))
            ->groupBy(DB::raw('HOUR(jam)'))
            ->orderBy('jam_ke')
            ->pluck('jumlah', 'jam_ke')
            ->toArray();

        // Trend last 7 days vs full window
        $last7DaysTotal = Laporan::query()
            ->whereBetween('tanggal', [$target->copy()->subDays(7)->toDateString(), $targetDateStr])
            ->count();
        $avgDaily7Days = round($last7DaysTotal / 7, 2);
        $avgDailyWindow = $distinctDays > 0 ? round($totalLaporan / $distinctDays, 2) : 0;

        // Animal activities
        $aktivitas = Laporan::query()
            ->whereBetween('tanggal', [$startDate, $targetDateStr])
            ->whereNotNull('aktivitas_satwa')
            ->where('aktivitas_satwa', '!=', '')
            ->select('aktivitas_satwa', DB::raw('COUNT(*) as total'))
            ->groupBy('aktivitas_satwa')
            ->orderByDesc('total')
            ->limit(5)
            ->pluck('total', 'aktivitas_satwa')
            ->toArray();

        // 2. Histori Birdstrike (maks 200 insiden terbaru + semua pada bulan yang sama)
        $targetMonth = $target->month;
        $historiQuery = HistoriBirdstrike::query()
            ->where(function ($q) use ($targetMonth) {
                $q->whereMonth('tanggal_kejadian', $targetMonth)
                    ->orWhereNotNull('id');
            })
            ->orderByDesc('tanggal_kejadian')
            ->limit(200);

        $historiData = $historiQuery->get()->map(function (HistoriBirdstrike $h) {
            return [
                'tanggal' => $h->tanggal_kejadian?->toDateString(),
                'jam' => $h->jam_kejadian ? substr($h->jam_kejadian, 0, 5) : null,
                'runway' => $h->kode_runway,
                'titik' => $h->titik_lokasi,
                'pesawat' => $h->jenis_pesawat,
                'fase' => $h->fase_penerbangan,
                'bagian' => $h->bagian_pesawat,
                'spesies' => $h->spesies_satwa,
                'jumlah' => $h->jumlah_satwa,
                'kerusakan' => $h->tingkat_kerusakan,
            ];
        })->toArray();

        // 3. Weather
        $lat = config('whms_ai.airport.lat', -7.3798);
        $lng = config('whms_ai.airport.lng', 112.7875);
        $weather = $this->weatherProvider->getWeather($lat, $lng, $targetDateStr);

        // 4. Traffic / Flight Density
        $trafficRows = KepadatanPenerbangan::query()
            ->where('tanggal', $targetDateStr)
            ->orderBy('jam')
            ->get();

        $trafficTersedia = $trafficRows->isNotEmpty();
        $trafficByHour = [];
        $peakHour = null;
        $maxMovements = 0;

        foreach ($trafficRows as $t) {
            $hourFormatted = sprintf('%02d:00', $t->jam);
            $trafficByHour[$hourFormatted] = $t->jumlah_pergerakan;
            if ($t->jumlah_pergerakan > $maxMovements) {
                $maxMovements = $t->jumlah_pergerakan;
                $peakHour = $hourFormatted;
            }
        }

        // 5. Habitat Notes (sanitized & truncated to 500 chars)
        $cleanHabitatNotes = null;
        if (! empty($habitatNotes)) {
            $cleanHabitatNotes = mb_substr(strip_tags(trim($habitatNotes)), 0, 500);
        }

        return [
            'periode' => [
                'tanggal_target' => $targetDateStr,
                'jendela_hari' => $windowDays,
                'mulai_jendela' => $startDate,
            ],
            'logbook' => [
                'total_laporan' => $totalLaporan,
                'total_ekor_terpantau' => $totalEkor,
                'rata_rata_skor_risiko' => $avgRiskScore,
                'spesies_dominan' => $spesiesDominan,
                'grid_frekuensi_tertinggi' => $gridFreq,
                'sebaran_per_jam' => $hourly,
                'tren_7_hari_vs_jendela' => [
                    'rata_rata_harian_7_hari' => $avgDaily7Days,
                    'rata_rata_harian_jendela' => $avgDailyWindow,
                ],
                'sebaran_aktivitas' => $aktivitas,
            ],
            'histori_birdstrike' => $historiData,
            'cuaca' => $weather,
            'lalu_lintas' => [
                'tersedia' => $trafficTersedia,
                'jam_sibuk_tertinggi' => $peakHour,
                'pergerakan_per_jam' => $trafficByHour,
            ],
            'habitat' => $cleanHabitatNotes,
            'meta' => [
                'logbook_tersedia_hari' => $distinctDays,
                'jumlah_histori' => count($historiData),
                'traffic_tersedia' => $trafficTersedia,
                'cuaca_sumber' => $weather['sumber'] ?? 'N/A',
            ],
        ];
    }
}
