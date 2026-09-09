<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('foto_laporan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_id')->constrained('laporan')->onDelete('cascade');
            $table->foreignId('detail_satwa_id')->nullable()->constrained('detail_satwa')->onDelete('set null');
            $table->string('nama_file', 255)->default('');
            $table->enum('tipe', ['satwa', 'extra'])->default('satwa');
            $table->string('foto_path', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foto_laporan');
    }
};
