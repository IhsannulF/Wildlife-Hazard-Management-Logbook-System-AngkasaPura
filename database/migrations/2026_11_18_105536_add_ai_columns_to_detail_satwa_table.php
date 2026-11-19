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
        Schema::table('detail_satwa', function (Blueprint $table) {
            $table->string('nama_latin', 150)->nullable()->after('nama_satwa');
            $table->enum('sumber_identifikasi', ['manual', 'ai'])->default('manual')->after('foto_path');
            $table->index(['laporan_id', 'nama_satwa']);
        });

        Schema::table('laporan', function (Blueprint $table) {
            $table->index(['tanggal', 'grid_lokasi']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan', function (Blueprint $table) {
            $table->dropIndex(['tanggal', 'grid_lokasi']);
        });

        Schema::table('detail_satwa', function (Blueprint $table) {
            $table->dropIndex(['laporan_id', 'nama_satwa']);
            $table->dropColumn(['nama_latin', 'sumber_identifikasi']);
        });
    }
};
