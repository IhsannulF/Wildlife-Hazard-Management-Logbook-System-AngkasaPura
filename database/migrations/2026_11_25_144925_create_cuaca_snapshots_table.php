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
        Schema::create('cuaca_snapshot', function (Blueprint $table) {
            $table->id();
            $table->dateTime('waktu')->unique();
            $table->unsignedSmallInteger('kode_cuaca')->nullable();
            $table->decimal('hujan_mm', 5, 2)->default(0);
            $table->decimal('jarak_pandang_km', 6, 2)->nullable();
            $table->unsignedSmallInteger('arah_angin_derajat')->nullable();
            $table->decimal('kecepatan_angin_kt', 5, 1)->nullable();
            $table->decimal('hembusan_kt', 5, 1)->nullable();
            $table->decimal('angin_silang_kt', 5, 1)->nullable();
            $table->decimal('suhu_c', 4, 1)->nullable();
            $table->unsignedTinyInteger('kelembapan_persen')->nullable();
            $table->unsignedTinyInteger('tutupan_awan_persen')->nullable();
            $table->string('sumber', 30);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuaca_snapshot');
    }
};
