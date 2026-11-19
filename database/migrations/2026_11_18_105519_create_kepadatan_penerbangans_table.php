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
        Schema::create('kepadatan_penerbangan', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->unsignedTinyInteger('jam'); // 0-23
            $table->unsignedSmallInteger('jumlah_pergerakan');
            $table->timestamps();

            $table->unique(['tanggal', 'jam']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kepadatan_penerbangan');
    }
};
