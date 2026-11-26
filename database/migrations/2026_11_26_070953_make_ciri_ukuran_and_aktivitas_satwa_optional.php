<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const FIELDS = ['ciri_ukuran', 'aktivitas_satwa'];

    /**
     * "Ciri-ciri & Ukuran Satwa Liar" dan "Aktivitas Satwa Liar di Lapangan" menjadi isian opsional
     * di form inspeksi (perilaku kawanan sudah tercatat terstruktur lewat FR-0.2).
     */
    public function up(): void
    {
        DB::table('form_fields')->whereIn('nama_field', self::FIELDS)->update(['wajib' => false, 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('form_fields')->whereIn('nama_field', self::FIELDS)->update(['wajib' => true, 'updated_at' => now()]);
    }
};
