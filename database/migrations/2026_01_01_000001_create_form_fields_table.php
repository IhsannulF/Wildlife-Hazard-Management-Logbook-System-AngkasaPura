<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->string('nama_field', 100)->unique();
            $table->string('tipe', 50)->default('text'); // text, select, textarea, date, dropdown, number, file, grid
            $table->string('label', 100);
            $table->string('placeholder', 200)->nullable()->default('');
            $table->boolean('wajib')->default(true);
            $table->boolean('aktif')->default(true);
            $table->integer('urutan')->default(0);
            $table->string('keterangan', 300)->nullable()->default('');
            $table->string('gridmap_path', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
