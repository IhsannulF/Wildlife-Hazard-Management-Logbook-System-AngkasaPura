<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * `jumlah_pergerakan` tetap dipakai sebagai total (take-off + landing). Baris lama yang hanya punya
     * total dibiarkan dengan take-off/landing 0, artinya "belum dipisah".
     */
    public function up(): void
    {
        Schema::table('kepadatan_penerbangan', function (Blueprint $table) {
            $table->unsignedSmallInteger('jumlah_takeoff')->default(0)->after('jam');
            $table->unsignedSmallInteger('jumlah_landing')->default(0)->after('jumlah_takeoff');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kepadatan_penerbangan', function (Blueprint $table) {
            $table->dropColumn(['jumlah_takeoff', 'jumlah_landing']);
        });
    }
};
