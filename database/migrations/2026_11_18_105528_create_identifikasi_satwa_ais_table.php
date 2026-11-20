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
        Schema::create('identifikasi_satwa_ai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('laporan_id')->nullable()->constrained('laporan')->nullOnDelete();
            $table->string('foto_path', 255);
            $table->string('model_version', 60)->nullable();
            $table->json('hasil')->nullable();
            $table->string('nama_lokal', 100)->nullable();
            $table->string('nama_latin', 150)->nullable();
            $table->decimal('confidence', 3, 2)->nullable();
            $table->boolean('diterima')->nullable();
            $table->string('nama_final', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('identifikasi_satwa_ai');
    }
};
