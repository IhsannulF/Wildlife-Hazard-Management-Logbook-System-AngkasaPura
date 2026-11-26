<?php

namespace Tests\Unit;

use App\Services\Statistics\BirdStatisticsService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BirdStatisticsServiceTest extends TestCase
{
    public function test_diversity_indices_for_two_equally_abundant_species(): void
    {
        $indices = BirdStatisticsService::diversityIndices(['Kuntul' => 2, 'Cangak' => 2]);

        $this->assertSame([
            'richness' => 2,
            'total' => 4,
            'shannon' => 0.693,
            'simpson' => 0.667,
            'evenness' => 1.0,
        ], $indices);
    }

    public function test_diversity_indices_for_single_species_has_no_diversity_and_no_evenness(): void
    {
        $indices = BirdStatisticsService::diversityIndices(['Kuntul' => 5]);

        $this->assertSame([
            'richness' => 1,
            'total' => 5,
            'shannon' => 0.0,
            'simpson' => 0.0,
            'evenness' => null,
        ], $indices);
    }

    public function test_diversity_indices_ignore_species_with_zero_birds(): void
    {
        $indices = BirdStatisticsService::diversityIndices(['Kuntul' => 0, 'Cangak' => 0]);

        $this->assertSame([
            'richness' => 0,
            'total' => 0,
            'shannon' => 0.0,
            'simpson' => 0.0,
            'evenness' => null,
        ], $indices);
    }

    public function test_pearson_detects_perfect_positive_and_negative_correlation(): void
    {
        $this->assertSame(1.0, BirdStatisticsService::pearson([1, 2, 3], [2, 4, 6]));
        $this->assertSame(-1.0, BirdStatisticsService::pearson([1, 2, 3], [6, 4, 2]));
    }

    public function test_pearson_returns_null_for_too_few_samples_or_constant_values(): void
    {
        $this->assertNull(BirdStatisticsService::pearson([1, 2], [3, 4]));
        $this->assertNull(BirdStatisticsService::pearson([5, 5, 5], [1, 2, 3]));
    }

    /**
     * @return array<string, array{int, string}>
     */
    public static function activityBoundaries(): array
    {
        return [
            'zero birds' => [0, 'rendah'],
            'upper bound rendah' => [50, 'rendah'],
            'lower bound sedang' => [51, 'sedang'],
            'upper bound sedang' => [150, 'sedang'],
            'lower bound tinggi' => [151, 'tinggi'],
            'upper bound tinggi' => [300, 'tinggi'],
            'lower bound sangat tinggi' => [301, 'sangat_tinggi'],
        ];
    }

    #[DataProvider('activityBoundaries')]
    public function test_activity_class_follows_six_month_thresholds(int $totalBirds, string $expectedClass): void
    {
        $this->assertSame($expectedClass, BirdStatisticsService::activityClass($totalBirds));
    }
}
