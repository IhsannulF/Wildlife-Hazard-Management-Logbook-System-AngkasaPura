<?php

namespace App\Support;

class GridCode
{
    /**
     * Regular expression for a valid grid code (A-L, 1-29).
     */
    public const REGEX = '/^[A-L]-([1-9]|1[0-9]|2[0-9])$/';

    /**
     * Normalize grid code (e.g. 'k-08' -> 'K-8', 'k-8' -> 'K-8', ' D - 09 ' -> 'D-9').
     */
    public static function normalize(?string $grid): ?string
    {
        if ($grid === null) {
            return null;
        }

        $trimmed = strtoupper(trim($grid));

        // Match patterns like 'K-08', 'K8', 'K 8', 'K-8'
        if (preg_match('/^([A-L])\s*[-_ ]?\s*0*([1-9]|[12][0-9])$/', $trimmed, $matches)) {
            return $matches[1].'-'.(int) $matches[2];
        }

        return $trimmed;
    }

    /**
     * Check if grid code is valid.
     */
    public static function isValid(?string $grid): bool
    {
        if ($grid === null) {
            return false;
        }

        $normalized = self::normalize($grid);

        return (bool) preg_match(self::REGEX, $normalized);
    }

    /**
     * Convert grid code to 0-based cell coordinates (row: 0-11 for A-L, col: 0-28 for 1-29).
     *
     * @return array{row: int, col: int}|null
     */
    public static function toCell(string $grid): ?array
    {
        $normalized = self::normalize($grid);

        if (! self::isValid($normalized)) {
            return null;
        }

        [$rowChar, $colNum] = explode('-', $normalized);
        $row = ord($rowChar) - ord('A'); // 0 to 11
        $col = (int) $colNum - 1;        // 0 to 28

        return [
            'row' => $row,
            'col' => $col,
        ];
    }

    /**
     * Convert 0-based cell coordinates to grid code (row: 0-11, col: 0-28).
     */
    public static function fromCell(int $row, int $col): ?string
    {
        if ($row < 0 || $row > 11 || $col < 0 || $col > 28) {
            return null;
        }

        $rowChar = chr(ord('A') + $row);
        $colNum = $col + 1;

        return "{$rowChar}-{$colNum}";
    }
}
