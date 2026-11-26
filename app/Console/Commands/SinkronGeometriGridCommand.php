<?php

namespace App\Console\Commands;

use App\Models\GridCell;
use App\Support\AirportGridGeometry;
use Illuminate\Console\Command;

class SinkronGeometriGridCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grid:sinkron-geometri';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menghitung ulang atribut grid_cell (zona, jarak runway, approach) dari config; sel terkalibrasi tidak diubah';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $updated = 0;
        $skipped = 0;

        foreach (AirportGridGeometry::cells() as $attributes) {
            $cell = GridCell::firstOrNew(['grid' => $attributes['grid']]);

            if ($cell->exists && $cell->terkalibrasi) {
                $skipped++;

                continue;
            }

            $cell->fill($attributes)->save();
            $updated++;
        }

        $this->info("{$updated} sel diperbarui, {$skipped} sel terkalibrasi dilewati.");

        return self::SUCCESS;
    }
}
