<?php

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
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'tanda_tangan')) {
                $table->longText('tanda_tangan')->nullable()->after('jabatan');
            }
        });

        Schema::table('laporan', function (Blueprint $table): void {
            if (! Schema::hasColumn('laporan', 'validated_by')) {
                $table->foreignId('validated_by')
                    ->nullable()
                    ->after('status')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('laporan', 'validated_at')) {
                $table->timestamp('validated_at')->nullable()->after('validated_by');
            }

            if (! Schema::hasColumn('laporan', 'validator_signature')) {
                $table->longText('validator_signature')->nullable()->after('validated_at');
            }
        });

        // Selaraskan nomor registrasi historis ke format standar BA-WHMS/UNIT/BLN/THN/ID
        $legacyReports = DB::table('laporan')
            ->where('no_laporan', 'like', 'BA-WHMS/AP1/%')
            ->orderBy('id')
            ->get(['id', 'no_laporan']);

        foreach ($legacyReports as $report) {
            $normalizedNo = 'BA-WHMS/'.substr((string) $report->no_laporan, strlen('BA-WHMS/AP1/'));
            $exists = DB::table('laporan')
                ->where('no_laporan', $normalizedNo)
                ->where('id', '!=', $report->id)
                ->exists();

            if (! $exists) {
                DB::table('laporan')
                    ->where('id', $report->id)
                    ->update(['no_laporan' => $normalizedNo]);
            }
        }

        // Isi metadata validasi historis bagi laporan berstatus 'sudah' bila tercatat penutupan resmi
        $resolvedReports = DB::table('laporan')
            ->where('status', 'sudah')
            ->whereNull('validated_at')
            ->get(['id', 'updated_at', 'created_at']);

        foreach ($resolvedReports as $report) {
            $closingResponse = DB::table('tanggapan')
                ->where('laporan_id', $report->id)
                ->where('kategori_tanggapan', 'penutupan')
                ->orderByDesc('id')
                ->first(['user_id', 'created_at']);

            DB::table('laporan')
                ->where('id', $report->id)
                ->update([
                    'validated_by' => $closingResponse?->user_id,
                    'validated_at' => $closingResponse?->created_at ?? $report->updated_at ?? $report->created_at,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan', function (Blueprint $table): void {
            if (Schema::hasColumn('laporan', 'validated_by')) {
                $table->dropForeign(['validated_by']);
                $table->dropColumn('validated_by');
            }

            if (Schema::hasColumn('laporan', 'validated_at')) {
                $table->dropColumn('validated_at');
            }

            if (Schema::hasColumn('laporan', 'validator_signature')) {
                $table->dropColumn('validator_signature');
            }
        });

        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'tanda_tangan')) {
                $table->dropColumn('tanda_tangan');
            }
        });
    }
};
