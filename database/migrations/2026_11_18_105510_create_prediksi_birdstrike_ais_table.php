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
        Schema::create('prediksi_birdstrike_ai', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_prediksi');
            $table->timestamp('waktu_analisis');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('trigger', ['terjadwal', 'manual'])->default('terjadwal');
            $table->enum('status_eksekusi', ['sukses', 'gagal'])->default('sukses');
            $table->text('error_message')->nullable();
            $table->decimal('skor_probabilitas', 5, 2)->nullable();
            $table->enum('level_bahaya', ['rendah', 'sedang', 'tinggi', 'kritis'])->nullable();
            $table->time('jam_rawan_mulai')->nullable();
            $table->time('jam_rawan_selesai')->nullable();
            $table->json('grid_kritis')->nullable();
            $table->json('spesies_target')->nullable();
            $table->json('faktor_penyebab')->nullable();
            $table->text('ringkasan_eksekutif')->nullable();
            $table->json('rekomendasi_taktis')->nullable();
            $table->decimal('confidence_score', 3, 2)->nullable();
            $table->string('model_version', 60)->nullable();
            $table->json('input_payload')->nullable();
            $table->json('raw_response')->nullable();
            $table->unsignedInteger('durasi_ms')->nullable();
            $table->enum('status_advisory', ['aktif', 'diakui', 'kedaluwarsa'])->default('aktif');
            $table->foreignId('diakui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diakui_at')->nullable();
            $table->boolean('insiden_terjadi')->nullable();
            $table->timestamps();

            $table->index(['tanggal_prediksi', 'status_advisory']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prediksi_birdstrike_ai');
    }
};
