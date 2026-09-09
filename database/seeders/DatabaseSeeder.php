<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\FormField;
use App\Models\FormFieldOption;
use App\Models\JenisSatwa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Users
        $defaultPassword = Hash::make('juanda123'); // Password default: juanda123
        $users = [
            [
                'id' => 1,
                'username' => 'admin.utama',
                'password' => $defaultPassword,
                'namalengkap' => 'Admin Utama',
                'jabatan' => 'General Manager',
                'role' => 'admin',
                'aktif' => 1,
            ],
            [
                'id' => 2,
                'username' => 'amc.divisi',
                'password' => $defaultPassword,
                'namalengkap' => 'AMC Divisi',
                'jabatan' => 'Pegawai',
                'role' => 'pegawai',
                'aktif' => 1,
            ],
            [
                'id' => 3,
                'username' => 'arff.divisi',
                'password' => $defaultPassword,
                'namalengkap' => 'ARFF Divisi',
                'jabatan' => 'Pegawai',
                'role' => 'pegawai',
                'aktif' => 1,
            ],
            [
                'id' => 4,
                'username' => 'security.divisi',
                'password' => $defaultPassword,
                'namalengkap' => 'Security Divisi',
                'jabatan' => 'Pegawai',
                'role' => 'pegawai',
                'aktif' => 1,
            ],
            [
                'id' => 5,
                'username' => 'smsohs.divisi',
                'password' => $defaultPassword,
                'namalengkap' => 'SMSOHS Divisi',
                'jabatan' => 'Pegawai',
                'role' => 'pegawai',
                'aktif' => 1,
            ],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(['id' => $u['id']], $u);
        }

        // 2. Seed Jenis Satwa
        $satwaList = [
            ['id' => 1, 'nama' => 'Biawak', 'foto_path' => 'biawak.jpeg'],
            ['id' => 2, 'nama' => 'Burung Blekok Sawah', 'foto_path' => 'blekok_sawah.jpeg'],
            ['id' => 3, 'nama' => 'Burung Cangak Abu', 'foto_path' => 'cangak_abu.jpeg'],
            ['id' => 4, 'nama' => 'Burung Cangak Merah', 'foto_path' => 'cangak_merah.jpeg'],
            ['id' => 5, 'nama' => 'Burung Kuntul Kecil', 'foto_path' => 'kuntul_kecil.jpeg'],
            ['id' => 6, 'nama' => 'Burung Kuntul Kerbau', 'foto_path' => 'kuntul_kerbau.jpeg'],
            ['id' => 7, 'nama' => 'Burung Pecuk Padi Kecil', 'foto_path' => 'pecuk_padi_kecil.jpeg'],
            ['id' => 8, 'nama' => 'Ular', 'foto_path' => 'ular.jpeg'],
        ];

        foreach ($satwaList as $s) {
            JenisSatwa::updateOrCreate(['id' => $s['id']], $s);
        }

        // 3. Seed Form Fields
        $fields = [
            [
                'id' => 1,
                'nama_field' => 'nama_petugas',
                'tipe' => 'text',
                'label' => 'Nama petugas',
                'urutan' => 1,
                'placeholder' => 'Nama lengkap petugas',
                'wajib' => 1,
                'aktif' => 1,
                'keterangan' => '',
            ],
            [
                'id' => 2,
                'nama_field' => 'tanggal',
                'tipe' => 'date',
                'label' => 'Tanggal pemantauan',
                'urutan' => 2,
                'placeholder' => 'DD/MM/YY',
                'wajib' => 1,
                'aktif' => 1,
                'keterangan' => '',
            ],
            [
                'id' => 3,
                'nama_field' => 'kondisi_cuaca',
                'tipe' => 'dropdown',
                'label' => 'Kondisi cuaca',
                'urutan' => 3,
                'placeholder' => 'Pilih kondisi cuaca',
                'wajib' => 1,
                'aktif' => 1,
                'keterangan' => '',
            ],
            [
                'id' => 4,
                'nama_field' => 'unit_kerja',
                'tipe' => 'dropdown',
                'label' => 'Unit kerja',
                'urutan' => 4,
                'placeholder' => 'Unit atau divisi',
                'wajib' => 1,
                'aktif' => 1,
                'keterangan' => '',
            ],
            [
                'id' => 5,
                'nama_field' => 'area_inspeksi',
                'tipe' => 'dropdown',
                'label' => 'Area inspeksi',
                'urutan' => 5,
                'placeholder' => 'Pilih area inspeksi',
                'wajib' => 1,
                'aktif' => 1,
                'keterangan' => '',
            ],
            [
                'id' => 6,
                'nama_field' => 'grid_lokasi',
                'tipe' => 'grid',
                'label' => 'Gambar lokasi pengamatan (Gridmap)',
                'urutan' => 6,
                'placeholder' => 'Isi format X-Y (ex: 10-B)',
                'wajib' => 1,
                'aktif' => 1,
                'keterangan' => 'Klik pada peta atau isi manual',
                'gridmap_path' => 'gridmap_injourney.jpeg',
            ],
            [
                'id' => 7,
                'nama_field' => 'tanda_tangan',
                'tipe' => 'textarea',
                'label' => 'Tanda tangan',
                'urutan' => 7,
                'placeholder' => 'Tanda tangan / inisial',
                'wajib' => 1,
                'aktif' => 1,
                'keterangan' => '',
            ],
            [
                'id' => 8,
                'nama_field' => 'ciri_ukuran',
                'tipe' => 'textarea',
                'label' => 'Ciri-ciri ukuran satwa liar',
                'urutan' => 8,
                'placeholder' => 'Deskripsikan ukuran satwa',
                'wajib' => 1,
                'aktif' => 1,
                'keterangan' => '',
            ],
            [
                'id' => 9,
                'nama_field' => 'kondisi_apron',
                'tipe' => 'dropdown',
                'label' => 'Kondisi ditemukan di area lokasi',
                'urutan' => 9,
                'placeholder' => 'Hidup, mati, tidak ditemukan',
                'wajib' => 1,
                'aktif' => 1,
                'keterangan' => '',
            ],
            [
                'id' => 10,
                'nama_field' => 'aktivitas_satwa',
                'tipe' => 'text',
                'label' => 'Aktivitas satwa liar di area lokasi',
                'urutan' => 10,
                'placeholder' => 'Jelaskan aktivitas satwa',
                'wajib' => 1,
                'aktif' => 1,
                'keterangan' => '',
            ],
            [
                'id' => 11,
                'nama_field' => 'tindak_lanjut',
                'tipe' => 'dropdown',
                'label' => 'Tindak lanjut',
                'urutan' => 11,
                'placeholder' => 'Pilih tindak lanjut',
                'wajib' => 1,
                'aktif' => 1,
                'keterangan' => '',
            ],
            [
                'id' => 12,
                'nama_field' => 'detail_pengusiran',
                'tipe' => 'textarea',
                'label' => 'Detail Pengusiran yang Dilakukan',
                'urutan' => 12,
                'placeholder' => 'Metode pengusiran. Isi "-" jika belum dilakukan.',
                'wajib' => 1,
                'aktif' => 1,
                'keterangan' => 'Diisi setelah memilih tindak lanjut',
            ],
        ];

        foreach ($fields as $f) {
            FormField::updateOrCreate(['id' => $f['id']], $f);
        }

        // 4. Seed Form Field Options
        $options = [
            // kondisi_cuaca (field_id = 3)
            ['field_id' => 3, 'nilai' => 'Cerah', 'urutan' => 1],
            ['field_id' => 3, 'nilai' => 'Berawan', 'urutan' => 2],
            ['field_id' => 3, 'nilai' => 'Hujan Ringan', 'urutan' => 3],
            ['field_id' => 3, 'nilai' => 'Hujan Lebat', 'urutan' => 4],
            ['field_id' => 3, 'nilai' => 'Berkabut', 'urutan' => 5],

            // unit_kerja (field_id = 4)
            ['field_id' => 4, 'nilai' => 'Airport Rescue & Fire Fighting', 'urutan' => 1],
            ['field_id' => 4, 'nilai' => 'Apron Movement Control', 'urutan' => 2],
            ['field_id' => 4, 'nilai' => 'Airport Security', 'urutan' => 3],
            ['field_id' => 4, 'nilai' => 'SMS & OHS', 'urutan' => 4],

            // area_inspeksi (field_id = 5)
            ['field_id' => 5, 'nilai' => 'Apron', 'urutan' => 1],
            ['field_id' => 5, 'nilai' => 'Taxiway', 'urutan' => 2],
            ['field_id' => 5, 'nilai' => 'Runway', 'urutan' => 3],
            ['field_id' => 5, 'nilai' => 'Perimeter', 'urutan' => 4],
            ['field_id' => 5, 'nilai' => 'Terminal', 'urutan' => 5],
            ['field_id' => 5, 'nilai' => 'Gedung Operasional', 'urutan' => 6],
            ['field_id' => 5, 'nilai' => 'Area Parkir', 'urutan' => 7],

            // kondisi_apron (field_id = 9)
            ['field_id' => 9, 'nilai' => 'Hidup', 'urutan' => 1],
            ['field_id' => 9, 'nilai' => 'Mati', 'urutan' => 2],
            ['field_id' => 9, 'nilai' => 'Tidak Ditemukan', 'urutan' => 3],

            // tindak_lanjut (field_id = 11)
            ['field_id' => 11, 'nilai' => 'Belum ditangani', 'urutan' => 1],
            ['field_id' => 11, 'nilai' => 'Telah ditangani', 'urutan' => 2],
        ];

        foreach ($options as $opt) {
            FormFieldOption::firstOrCreate([
                'field_id' => $opt['field_id'],
                'nilai' => $opt['nilai']
            ], $opt);
        }
    }
}
