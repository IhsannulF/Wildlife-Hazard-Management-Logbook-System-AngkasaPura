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
        Schema::table('detail_satwa', function (Blueprint $table) {
            $table->enum('perilaku', ['melintas', 'mencari_makan', 'bertengger', 'lainnya'])->nullable()->after('grid');
            $table->enum('arah_terbang', ['U', 'TL', 'T', 'TG', 'S', 'BD', 'B', 'BL'])->nullable()->after('perilaku');
            $table->enum('ketinggian', ['<50ft', '50-200ft', '>200ft'])->nullable()->after('arah_terbang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_satwa', function (Blueprint $table) {
            $table->dropColumn(['perilaku', 'arah_terbang', 'ketinggian']);
        });
    }
};
