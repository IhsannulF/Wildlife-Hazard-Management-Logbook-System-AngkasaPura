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
        Schema::create('master_alat_dispersal', function (Blueprint $table) {
            $table->id();
            $table->string('nama_alat', 150);
            $table->string('kategori', 100);
            $table->string('satuan', 50);
            $table->integer('stok_tersedia')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        $now = now();
        DB::table('master_alat_dispersal')->insert([
            ['nama_alat' => 'Sirene Kendaraan Patroli', 'kategori' => 'Akustik', 'satuan' => 'Kali', 'created_at' => $now, 'updated_at' => $now],
            ['nama_alat' => 'Bird Scaring Cartridge 12GA (Pyro)', 'kategori' => 'Piroteknik', 'satuan' => 'Butir', 'created_at' => $now, 'updated_at' => $now],
            ['nama_alat' => 'Gas Propane Cannon', 'kategori' => 'Akustik', 'satuan' => 'Dentuman', 'created_at' => $now, 'updated_at' => $now],
            ['nama_alat' => 'Handheld Green Laser', 'kategori' => 'Optik', 'satuan' => 'Menit', 'created_at' => $now, 'updated_at' => $now],
            ['nama_alat' => 'Patroli Kendaraan (Vehicle Sweep)', 'kategori' => 'Fisik', 'satuan' => 'Kali', 'created_at' => $now, 'updated_at' => $now],
            ['nama_alat' => 'Pengusiran Manual', 'kategori' => 'Fisik', 'satuan' => 'Kali', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_alat_dispersal');
    }
};
