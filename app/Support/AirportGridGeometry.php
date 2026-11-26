<?php

namespace App\Support;

/**
 * Geometri sel gridmap A–L × 1–29 terhadap runway 10/28 (PRD v2.1 FR-1.1).
 * Koordinat dalam meter: x ke timur dari tepi kiri kolom 1, y ke selatan dari tepi atas baris A.
 */
class AirportGridGeometry
{
    public const ROWS = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];

    public const COLUMNS = 29;

    /**
     * Atribut semua 348 sel untuk mengisi tabel `grid_cell`.
     *
     * @return list<array{grid: string, baris: string, kolom: int, zona: string, jarak_runway_m: int, di_approach: bool, runway_terdekat: string}>
     */
    public static function cells(): array
    {
        $cells = [];

        foreach (self::ROWS as $row) {
            for ($column = 1; $column <= self::COLUMNS; $column++) {
                $cells[] = self::cell($row, $column);
            }
        }

        return $cells;
    }

    /**
     * @return array{grid: string, baris: string, kolom: int, zona: string, jarak_runway_m: int, di_approach: bool, runway_terdekat: string}
     */
    public static function cell(string $row, int $column): array
    {
        [$x, $y] = self::cellCenter($row, $column);
        [$runwayStartX, $runwayEndX, $centerlineY] = self::runwayLine();

        $alongOutside = max($runwayStartX - $x, 0, $x - $runwayEndX);
        $lateral = abs($y - $centerlineY);
        $approach = config('whms_risk.zonasi.approach');

        return [
            'grid' => $row.'-'.$column,
            'baris' => $row,
            'kolom' => $column,
            'zona' => self::zone($row, $column),
            'jarak_runway_m' => (int) round(hypot($alongOutside, $lateral)),
            'di_approach' => $alongOutside > 0 && $lateral <= $approach['setengah_lebar_m'] + $approach['divergensi'] * $alongOutside,
            // RWY 10 menghadap timur: ujung barat adalah threshold 10, ujung timur threshold 28.
            'runway_terdekat' => $x <= ($runwayStartX + $runwayEndX) / 2 ? '10' : '28',
        ];
    }

    /**
     * Zona mengikuti pembagian gridmap yang sudah ada: G–H di bentang runway, E–F taxiway, D dan J apron.
     */
    public static function zone(string $row, int $column): string
    {
        $isRunwaySpan = $column >= (int) config('whms_risk.runway.kolom_awal') && $column <= (int) config('whms_risk.runway.kolom_akhir');

        return match (true) {
            in_array($row, ['G', 'H'], true) && $isRunwaySpan => 'runway',
            in_array($row, ['E', 'F'], true) => 'taxiway',
            in_array($row, ['D', 'J'], true) => 'apron',
            default => 'perimeter',
        };
    }

    /**
     * @return array{0: float, 1: float}
     */
    private static function cellCenter(string $row, int $column): array
    {
        $rowIndex = array_search($row, self::ROWS, true);

        return [
            ($column - 0.5) * (float) config('whms_risk.grid.lebar_sel_m'),
            ($rowIndex + 0.5) * (float) config('whms_risk.grid.tinggi_sel_m'),
        ];
    }

    /**
     * @return array{0: float, 1: float, 2: float} x awal, x akhir, y garis tengah
     */
    private static function runwayLine(): array
    {
        $cellWidth = (float) config('whms_risk.grid.lebar_sel_m');
        $centerlineRow = array_search(config('whms_risk.runway.baris_garis_tengah'), self::ROWS, true);

        return [
            ((int) config('whms_risk.runway.kolom_awal') - 1) * $cellWidth,
            (int) config('whms_risk.runway.kolom_akhir') * $cellWidth,
            ($centerlineRow + 1) * (float) config('whms_risk.grid.tinggi_sel_m'),
        ];
    }
}
