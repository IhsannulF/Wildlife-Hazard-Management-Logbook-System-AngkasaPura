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
        Schema::create('histori_birdstrike', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_kejadian')->index();
            $table->time('jam_kejadian')->nullable();
            $table->string('kode_runway', 5);
            $table->string('titik_lokasi', 20);
            $table->decimal('koordinat_lat', 10, 7)->nullable();
            $table->decimal('koordinat_lng', 10, 7)->nullable();
            $table->string('jenis_pesawat', 50);
            $table->enum('fase_penerbangan', ['take_off', 'landing', 'approach', 'climb', 'taxi']);
            $table->string('bagian_pesawat', 50);
            $table->string('spesies_satwa', 100);
            $table->unsignedInteger('jumlah_satwa')->default(1);
            $table->enum('tingkat_kerusakan', ['nihil', 'minor', 'substansial', 'fatal'])->default('nihil');
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('histori_birdstrike');
    }
};
