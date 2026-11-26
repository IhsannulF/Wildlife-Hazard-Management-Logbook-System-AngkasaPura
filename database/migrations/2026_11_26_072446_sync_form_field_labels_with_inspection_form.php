<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Label & placeholder field bawaan disamakan dengan teks yang selama ini tampil di form inspeksi,
     * karena form kini membaca pengaturan dari tabel ini. Field inti (tanggal, gridmap, tanda tangan)
     * dipastikan aktif dan wajib.
     *
     * @var array<string, array{label: string, placeholder?: string}>
     */
    private const LABELS = [
        'nama_petugas' => ['label' => 'Nama Petugas'],
        'tanggal' => ['label' => 'Tanggal Pemantauan'],
        'kondisi_cuaca' => ['label' => 'Kondisi Cuaca'],
        'unit_kerja' => ['label' => 'Unit Kerja'],
        'area_inspeksi' => ['label' => 'Area Inspeksi'],
        'ciri_ukuran' => ['label' => 'Ciri-ciri & Ukuran Satwa Liar', 'placeholder' => 'Deskripsikan morfologi dan ukuran fisik satwa...'],
        'kondisi_apron' => ['label' => 'Kondisi Ditemukan di Area Sisi Udara'],
        'aktivitas_satwa' => ['label' => 'Aktivitas Satwa Liar di Lapangan', 'placeholder' => 'cth: Melintas taxiway, mencari makan'],
        'tindak_lanjut' => ['label' => 'Tindak Lanjut Penanganan'],
        'detail_pengusiran' => ['label' => 'Detail Metode Pengusiran yang Dilakukan', 'placeholder' => "Sebutkan alat atau tindakan pengusiran yang diterapkan (isi '-' jika tidak ada)"],
    ];

    public function up(): void
    {
        foreach (self::LABELS as $field => $values) {
            DB::table('form_fields')->where('nama_field', $field)->update($values);
        }

        DB::table('form_fields')
            ->whereIn('nama_field', ['tanggal', 'grid_lokasi', 'tanda_tangan'])
            ->update(['aktif' => true, 'wajib' => true]);
    }

    public function down(): void
    {
        // Label lama tidak dipulihkan: hanya teks tampilan, tidak memengaruhi data laporan.
    }
};
