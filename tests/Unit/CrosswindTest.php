<?php

namespace Tests\Unit;

use App\Services\Weather\WeatherSnapshotService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CrosswindTest extends TestCase
{
    /**
     * @return array<string, array{float, int, float}>
     */
    public static function windCases(): array
    {
        return [
            'searah heading RWY 10' => [15.0, 100, 0.0],
            'searah heading RWY 28' => [15.0, 280, 0.0],
            'tegak lurus dari selatan' => [15.0, 190, 15.0],
            'tegak lurus dari utara' => [15.0, 10, 15.0],
            'miring 135 derajat' => [1.9, 235, 1.3],
        ];
    }

    #[DataProvider('windCases')]
    public function test_crosswind_component_against_runway_10_28(float $speedKnots, int $directionDegrees, float $expectedKnots): void
    {
        $this->assertSame($expectedKnots, WeatherSnapshotService::crosswindKnots($speedKnots, $directionDegrees, 100));
    }

    public function test_crosswind_is_null_when_wind_data_is_missing(): void
    {
        $this->assertNull(WeatherSnapshotService::crosswindKnots(null, 190, 100));
        $this->assertNull(WeatherSnapshotService::crosswindKnots(10.0, null, 100));
    }
}
