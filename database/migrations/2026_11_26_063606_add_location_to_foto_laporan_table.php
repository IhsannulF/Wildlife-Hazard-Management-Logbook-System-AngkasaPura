<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lokasi GPS perangkat & waktu saat foto bukti inspeksi diambil/diunggah (tercetak juga di watermark foto).
     */
    public function up(): void
    {
        Schema::table('foto_laporan', function (Blueprint $table) {
            $table->decimal('gps_lat', 10, 7)->nullable()->after('foto_path');
            $table->decimal('gps_lng', 10, 7)->nullable()->after('gps_lat');
            $table->unsignedInteger('gps_akurasi_m')->nullable()->after('gps_lng');
            $table->dateTime('diambil_at')->nullable()->after('gps_akurasi_m');
            $table->string('sumber', 20)->nullable()->after('diambil_at');
        });
    }

    public function down(): void
    {
        Schema::table('foto_laporan', function (Blueprint $table) {
            $table->dropColumn(['gps_lat', 'gps_lng', 'gps_akurasi_m', 'diambil_at', 'sumber']);
        });
    }
};
