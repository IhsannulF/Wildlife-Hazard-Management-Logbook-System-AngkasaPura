<?php

use App\Support\AirportGridGeometry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Atribut per sel gridmap (PRD v2.1 FR-1.1). Nilai awal diperkirakan dari gridmap
     * dan ditandai belum terkalibrasi sampai dicocokkan dengan aerodrome chart.
     */
    public function up(): void
    {
        Schema::create('grid_cell', function (Blueprint $table) {
            $table->id();
            $table->string('grid', 20)->unique();
            $table->char('baris', 1);
            $table->unsignedTinyInteger('kolom');
            $table->enum('zona', ['runway', 'taxiway', 'apron', 'perimeter']);
            $table->unsignedInteger('jarak_runway_m');
            $table->boolean('di_approach')->default(false);
            $table->string('runway_terdekat', 5);
            $table->boolean('terkalibrasi')->default(false);
            $table->timestamps();
        });

        $now = now();
        $rows = array_map(
            fn (array $cell): array => [...$cell, 'created_at' => $now, 'updated_at' => $now],
            AirportGridGeometry::cells()
        );

        DB::table('grid_cell')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('grid_cell');
    }
};
