<?php

namespace Tests\Unit;

use App\Support\GridCode;
use PHPUnit\Framework\TestCase;

class GridCodeTest extends TestCase
{
    public function test_normalizes_grid_with_leading_zeros(): void
    {
        $this->assertSame('K-8', GridCode::normalize('K-08'));
        $this->assertSame('D-9', GridCode::normalize('d-09'));
        $this->assertSame('A-1', GridCode::normalize('a-01'));
        $this->assertSame('L-29', GridCode::normalize('L-29'));
        $this->assertSame('K-8', GridCode::normalize('k8'));
        $this->assertSame('B-12', GridCode::normalize(' b-12 '));
    }

    public function test_validates_grid_codes(): void
    {
        $this->assertTrue(GridCode::isValid('A-1'));
        $this->assertTrue(GridCode::isValid('K-8'));
        $this->assertTrue(GridCode::isValid('K-08')); // normalizes then validates
        $this->assertTrue(GridCode::isValid('L-29'));

        $this->assertFalse(GridCode::isValid('M-1')); // M is out of A-L
        $this->assertFalse(GridCode::isValid('K-0')); // 0 is invalid
        $this->assertFalse(GridCode::isValid('K-30')); // 30 is out of 1-29
        $this->assertFalse(GridCode::isValid('Z-99'));
        $this->assertFalse(GridCode::isValid('INVALID'));
        $this->assertFalse(GridCode::isValid(null));
    }

    public function test_converts_grid_to_cell_and_back(): void
    {
        $cellA1 = GridCode::toCell('A-1');
        $this->assertSame(['row' => 0, 'col' => 0], $cellA1);
        $this->assertSame('A-1', GridCode::fromCell(0, 0));

        $cellL29 = GridCode::toCell('L-29');
        $this->assertSame(['row' => 11, 'col' => 28], $cellL29);
        $this->assertSame('L-29', GridCode::fromCell(11, 28));

        $cellK8 = GridCode::toCell('K-08');
        $this->assertSame(['row' => 10, 'col' => 7], $cellK8);
        $this->assertSame('K-8', GridCode::fromCell(10, 7));

        $this->assertNull(GridCode::toCell('Z-99'));
        $this->assertNull(GridCode::fromCell(-1, 5));
        $this->assertNull(GridCode::fromCell(12, 5));
    }
}
