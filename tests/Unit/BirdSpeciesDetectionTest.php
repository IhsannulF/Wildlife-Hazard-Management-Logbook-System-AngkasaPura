<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\StatistikController;
use PHPUnit\Framework\TestCase;

class BirdSpeciesDetectionTest extends TestCase
{
    public function test_is_bird_species_detection(): void
    {
        $this->assertTrue(StatistikController::isBirdSpecies('Burung Blekok Sawah'));
        $this->assertTrue(StatistikController::isBirdSpecies('Kuntul Kerbau'));
        $this->assertTrue(StatistikController::isBirdSpecies('Burung Cangak Abu'));
        $this->assertTrue(StatistikController::isBirdSpecies('Burung Layang-layang Api'));
        $this->assertTrue(StatistikController::isBirdSpecies('Burung Elang Tikus'));
        $this->assertTrue(StatistikController::isBirdSpecies('Burung Gereja'));
        $this->assertTrue(StatistikController::isBirdSpecies('Burung Walet Sapi'));
        $this->assertTrue(StatistikController::isBirdSpecies('Burung Alap-alap Sapi'));

        $this->assertFalse(StatistikController::isBirdSpecies('Biawak Air'));
        $this->assertFalse(StatistikController::isBirdSpecies('Kera Ekor Panjang'));
        $this->assertFalse(StatistikController::isBirdSpecies('Ular Sanca Kembang'));
        $this->assertFalse(StatistikController::isBirdSpecies('Anjing Liar'));
        $this->assertFalse(StatistikController::isBirdSpecies('Kucing Liar'));
        $this->assertFalse(StatistikController::isBirdSpecies(''));
        $this->assertFalse(StatistikController::isBirdSpecies(null));
    }
}
