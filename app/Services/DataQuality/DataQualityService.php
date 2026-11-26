<?php

namespace App\Services\DataQuality;

use App\Models\CuacaSnapshot;
use App\Models\Laporan;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Mendeteksi celah data cuaca dan observasi yang dipakai model risiko (PRD v2.1 FR-0.7).
 * Celah = lebih dari `maks_interval_kosong` interval berturut-turut tanpa data. Semua waktu WIB.
 */
class DataQualityService
{
    public const SOURCE_WEATHER = 'cuaca';

    public const SOURCE_OBSERVATION = 'observasi';

    /**
     * @return list<array{sumber: string, mulai: CarbonImmutable, selesai: CarbonImmutable, interval_kosong: int, interval_menit: int, pesan: string}>
     */
    public function warnings(?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now(config('whms_risk.zona_waktu'));
        $from = $now->subHours((int) config('whms_risk.kualitas_data.jendela_jam'));

        return [
            ...$this->weatherGaps($from, $now),
            ...$this->observationGaps($from, $now),
        ];
    }

    /**
     * @return list<array{sumber: string, mulai: CarbonImmutable, selesai: CarbonImmutable, interval_kosong: int, interval_menit: int, pesan: string}>
     */
    private function weatherGaps(CarbonImmutable $from, CarbonImmutable $now): array
    {
        $minutes = (int) config('whms_risk.kualitas_data.cuaca_interval_menit');

        $filled = CuacaSnapshot::query()
            ->whereBetween('waktu', [$from->toDateTimeString(), $now->toDateTimeString()])
            ->pluck('waktu')
            ->map(fn ($waktu): string => $this->slotKey(CarbonImmutable::parse($waktu->format('Y-m-d H:i:s'), $now->timezone), $minutes));

        return $this->findGaps(self::SOURCE_WEATHER, $this->slots($from, $now, $minutes), $filled, $minutes);
    }

    /**
     * @return list<array{sumber: string, mulai: CarbonImmutable, selesai: CarbonImmutable, interval_kosong: int, interval_menit: int, pesan: string}>
     */
    private function observationGaps(CarbonImmutable $from, CarbonImmutable $now): array
    {
        $minutes = (int) config('whms_risk.kualitas_data.observasi_interval_menit');
        $patrolStart = (int) config('whms_risk.kualitas_data.observasi_jam_mulai');
        $patrolEnd = (int) config('whms_risk.kualitas_data.observasi_jam_selesai');

        $filled = Laporan::query()
            ->whereBetween('tanggal', [$from->toDateString(), $now->toDateString()])
            ->whereNotNull('jam')
            ->get(['tanggal', 'jam'])
            ->map(fn ($laporan): CarbonImmutable => CarbonImmutable::parse($laporan->tanggal->toDateString().' '.$laporan->jam, $now->timezone))
            ->filter(fn (CarbonImmutable $waktu): bool => $waktu->betweenIncluded($from, $now))
            ->map(fn (CarbonImmutable $waktu): string => $this->slotKey($waktu, $minutes));

        // Slot di luar jam patroli dibuang sehingga malam hari memutus rangkaian, bukan ikut dihitung kosong.
        $runs = $this->splitByPatrolHours($this->slots($from, $now, $minutes), $patrolStart, $patrolEnd);

        $gaps = [];
        foreach ($runs as $run) {
            array_push($gaps, ...$this->findGaps(self::SOURCE_OBSERVATION, $run, $filled, $minutes));
        }

        return $gaps;
    }

    /**
     * Slot yang sudah selesai di dalam jendela; slot yang sedang berjalan belum dinilai.
     *
     * @return list<CarbonImmutable>
     */
    private function slots(CarbonImmutable $from, CarbonImmutable $now, int $minutes): array
    {
        $slots = [];
        $slot = $this->floorToSlot($from, $minutes);
        if ($slot->lt($from)) {
            $slot = $slot->addMinutes($minutes);
        }

        while ($slot->addMinutes($minutes)->lte($now)) {
            $slots[] = $slot;
            $slot = $slot->addMinutes($minutes);
        }

        return $slots;
    }

    /**
     * @param  list<CarbonImmutable>  $slots
     * @return list<list<CarbonImmutable>>
     */
    private function splitByPatrolHours(array $slots, int $startHour, int $endHour): array
    {
        $runs = [];
        $current = [];

        foreach ($slots as $slot) {
            if ($slot->hour >= $startHour && $slot->hour < $endHour) {
                $current[] = $slot;

                continue;
            }

            if ($current !== []) {
                $runs[] = $current;
                $current = [];
            }
        }

        if ($current !== []) {
            $runs[] = $current;
        }

        return $runs;
    }

    /**
     * @param  list<CarbonImmutable>  $slots
     * @param  Collection<int, string>  $filled
     * @return list<array{sumber: string, mulai: CarbonImmutable, selesai: CarbonImmutable, interval_kosong: int, interval_menit: int, pesan: string}>
     */
    private function findGaps(string $source, array $slots, Collection $filled, int $minutes): array
    {
        $filledKeys = $filled->flip();
        $maxEmpty = (int) config('whms_risk.kualitas_data.maks_interval_kosong');
        $gaps = [];
        $run = [];

        foreach ([...$slots, null] as $slot) {
            if ($slot !== null && ! $filledKeys->has($this->slotKey($slot, $minutes))) {
                $run[] = $slot;

                continue;
            }

            if (count($run) > $maxEmpty) {
                $gaps[] = $this->gap($source, $run[0], end($run)->addMinutes($minutes), count($run), $minutes);
            }
            $run = [];
        }

        return $gaps;
    }

    /**
     * @return array{sumber: string, mulai: CarbonImmutable, selesai: CarbonImmutable, interval_kosong: int, interval_menit: int, pesan: string}
     */
    private function gap(string $source, CarbonImmutable $start, CarbonImmutable $end, int $emptyCount, int $minutes): array
    {
        $label = $source === self::SOURCE_WEATHER ? 'Snapshot cuaca' : 'Laporan observasi';

        return [
            'sumber' => $source,
            'mulai' => $start,
            'selesai' => $end,
            'interval_kosong' => $emptyCount,
            'interval_menit' => $minutes,
            'pesan' => sprintf(
                '%s kosong %s–%s WIB (%d interval %d menit berturut-turut).',
                $label,
                $start->format('d/m H:i'),
                $end->format($start->isSameDay($end) ? 'H:i' : 'd/m H:i'),
                $emptyCount,
                $minutes
            ),
        ];
    }

    private function floorToSlot(CarbonImmutable $time, int $minutes): CarbonImmutable
    {
        return $time->setTime($time->hour, intdiv($time->minute, $minutes) * $minutes);
    }

    private function slotKey(CarbonImmutable $time, int $minutes): string
    {
        return $this->floorToSlot($time, $minutes)->format('Y-m-d H:i');
    }
}
