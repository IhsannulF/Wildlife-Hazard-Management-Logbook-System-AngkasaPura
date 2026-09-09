<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nama_petugas', 100);
            $table->date('tanggal');
            $table->string('kondisi_cuaca', 50);
            $table->string('unit_kerja', 100);
            $table->string('area_inspeksi', 100);
            $table->longText('tanda_tangan')->nullable();
            $table->string('grid_lokasi', 50)->nullable();
            $table->text('ciri_ukuran')->nullable();
            $table->text('kondisi_apron')->nullable();
            $table->text('aktivitas_satwa')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->text('detail_pengusiran')->nullable();
            $table->enum('status', ['belum', 'sudah'])->default('belum');
            $table->json('extra_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan');
    }
};
