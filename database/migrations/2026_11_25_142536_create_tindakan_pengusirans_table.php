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
        Schema::create('tindakan_pengusiran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_id')->nullable()->constrained('laporan')->nullOnDelete();
            $table->foreignId('alat_id')->constrained('master_alat_dispersal');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('waktu');
            $table->string('grid', 20);
            $table->unsignedInteger('jumlah_digunakan')->default(1);
            $table->enum('hasil', ['berhasil', 'sebagian', 'tidak_berhasil'])->nullable();
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();

            $table->index(['grid', 'waktu']);
            $table->index('waktu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tindakan_pengusiran');
    }
};
