<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('laporan', function (Blueprint $table) {
            $table->string('no_laporan', 60)->nullable()->unique()->after('user_id');
            $table->time('jam')->nullable()->after('tanggal');
            $table->decimal('gps_lat', 10, 7)->nullable()->after('grid_lokasi');
            $table->decimal('gps_lng', 10, 7)->nullable()->after('gps_lat');
            $table->integer('risk_score')->default(1)->after('gps_lng');
            $table->enum('risk_level', ['rendah', 'sedang', 'tinggi', 'kritis'])->default('rendah')->after('risk_score');
            $table->string('offline_sync_id', 64)->nullable()->unique()->after('extra_data');
        });

        Schema::table('tanggapan', function (Blueprint $table) {
            $table->enum('kategori_tanggapan', ['instruksi', 'evaluasi', 'klarifikasi', 'penutupan'])
                ->default('instruksi')
                ->after('user_id');
        });

        // Backfill nomor laporan resmi untuk laporan historis eksisting
        $existingLaporans = DB::table('laporan')->whereNull('no_laporan')->get();
        foreach ($existingLaporans as $lap) {
            $unit = strtoupper(trim($lap->unit_kerja ?? 'AMC'));
            $unitCode = 'AMC';
            if (str_contains($unit, 'RESCUE') || str_contains($unit, 'ARFF') || str_contains($unit, 'PK')) {
                $unitCode = 'ARFF';
            } elseif (str_contains($unit, 'SEC') || str_contains($unit, 'AVSEC')) {
                $unitCode = 'AVSEC';
            } elseif (str_contains($unit, 'SMS') || str_contains($unit, 'SAFETY')) {
                $unitCode = 'SMS';
            } elseif (str_contains($unit, 'APRON') || str_contains($unit, 'AMC')) {
                $unitCode = 'AMC';
            }

            $date = ! empty($lap->tanggal) ? Carbon::parse($lap->tanggal) : Carbon::now();
            $bulan = $date->format('m');
            $tahun = $date->format('Y');
            $noLaporan = sprintf('BA-WHMS/AP1/%s/%s/%s/%04d', $unitCode, $bulan, $tahun, $lap->id);

            DB::table('laporan')->where('id', $lap->id)->update([
                'no_laporan' => $noLaporan,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tanggapan', function (Blueprint $table) {
            if (Schema::hasColumn('tanggapan', 'kategori_tanggapan')) {
                $table->dropColumn('kategori_tanggapan');
            }
        });

        Schema::table('laporan', function (Blueprint $table) {
            $columnsToDrop = [
                'no_laporan',
                'jam',
                'gps_lat',
                'gps_lng',
                'risk_score',
                'risk_level',
                'offline_sync_id',
            ];

            foreach ($columnsToDrop as $col) {
                if (Schema::hasColumn('laporan', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
