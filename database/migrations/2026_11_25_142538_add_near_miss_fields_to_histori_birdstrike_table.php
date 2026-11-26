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
        Schema::table('histori_birdstrike', function (Blueprint $table) {
            $table->enum('jenis_kejadian', ['strike', 'near_miss'])->default('strike')->after('jam_kejadian');
            $table->string('grid', 20)->nullable()->after('titik_lokasi');
            $table->unsignedSmallInteger('ketinggian_ft')->nullable()->after('fase_penerbangan');
            $table->unsignedSmallInteger('jarak_centerline_m')->nullable()->after('ketinggian_ft');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('histori_birdstrike', function (Blueprint $table) {
            $table->dropColumn(['jenis_kejadian', 'grid', 'ketinggian_ft', 'jarak_centerline_m']);
        });
    }
};
