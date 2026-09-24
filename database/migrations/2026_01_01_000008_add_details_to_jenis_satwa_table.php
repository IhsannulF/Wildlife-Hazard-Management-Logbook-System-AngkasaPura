<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('jenis_satwa', function (Blueprint $table) {
            $table->string('kategori')->nullable()->after('nama');
            $table->string('tingkat_risiko')->nullable()->default('sedang')->after('kategori');
            $table->string('grid_hotspot')->nullable()->after('tingkat_risiko');
            $table->text('deskripsi')->nullable()->after('grid_hotspot');
            $table->text('sop_pengusiran')->nullable()->after('deskripsi');
            $table->string('jam_puncak')->nullable()->after('sop_pengusiran');
            $table->string('bobot_rata_rata')->nullable()->after('jam_puncak');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jenis_satwa', function (Blueprint $table) {
            $table->dropColumn([
                'kategori',
                'tingkat_risiko',
                'grid_hotspot',
                'deskripsi',
                'sop_pengusiran',
                'jam_puncak',
                'bobot_rata_rata',
            ]);
        });
    }
};
