# Rencana Migrasi Project ke Struktur Laravel (MVC)

Dokumen ini menjelaskan langkah-langkah migrasi sistem **Portal Satwa Liar (InJourney Airports)** dari arsitektur PHP Native prosedural menjadi arsitektur modern berbasis **Laravel Framework (v11/v12)** dengan pola Model-View-Controller (MVC).

---

## 1. Analisis Kebutuhan & Arsitektur Baru

### Perbandingan Struktur:
| Aspek | PHP Native (Saat Ini) | Laravel (Target) |
| :--- | :--- | :--- |
| **Routing** | File terpisah langsung diakses via URL (`form_pengaduan.php`, dll) | `routes/web.php` (RESTful & named routes) |
| **Database** | Raw PDO di `koneksi.php` + SQL dump | Eloquent ORM + Migrations & Seeders |
| **Autentikasi** | Session manual + lockout logic di `login.php` | Laravel Auth / Fortify / Custom Session Auth + Role Middleware |
| **Business Logic**| Bercampur dalam satu file bersama markup HTML | Controller (`app/Http/Controllers/`) |
| **Tampilan / UI** | File `.php` campur CSS & JS inline | Blade Templates (`resources/views/`) dengan Layouting & Components |
| **Upload Berkas** | Folder `uploads/` langsung | `Storage::disk('public')` + `php artisan storage:link` |

---

## 2. User Review Required

> [!IMPORTANT]
> - **Penyimpanan File Lama**: Semua file native lama (`*.php` dan data gambar) akan kita amankan terlebih dahulu ke dalam folder cadangan (misalnya `legacy_native/`) sebelum instalasi kerangka kerja Laravel dimulai, sehingga tidak ada kode atau aset yang hilang.
> - **Kompatibilitas Versi**: Lingkungan sistem memiliki **PHP 8.5.5**, **Composer 2.9.7**, dan **Node.js v24.14.1**, yang sangat kompatibel dengan rilis Laravel terbaru (Laravel 11/12).
> - **Database MySQL**: Konfigurasi database di `.env` akan diselaraskan dengan database `logbook_project_baru` di MySQL lokal.

---

## 3. Rencana Komponen yang Akan Dibuat

### A. Pengamanan Berkas & Inisialisasi Kerangka Laravel
1. Pindahkan seluruh file native yang ada saat ini (`admin_*.php`, `user_*.php`, `form_*.php`, `koneksi.php`, dll) ke direktori cadangan `legacy_backup/` agar direktori siap diinisialisasi.
2. Inisialisasi proyek Laravel bersih menggunakan `composer create-project laravel/laravel . --prefer-dist`.
3. Konfigurasi file `.env` (koneksi DB `logbook_project_baru`, `APP_NAME="Portal Satwa Liar"`, `APP_URL`).

### B. Database: Migrations, Seeders & Models
1. **Migrations**:
   - `create_users_table`: kolom `username`, `password`, `namalengkap`, `jabatan`, `role` (enum: `admin`, `pegawai`), `aktif`.
   - `create_form_fields_table`: kolom `nama_field`, `tipe`, `label`, `urutan`, `gridmap_path`, `placeholder`, `wajib`, `aktif`, `keterangan`.
   - `create_form_field_options_table`: relasi ke `form_fields` (`field_id`, `nilai`, `urutan`).
   - `create_jenis_satwa_table`: kolom `nama`, `foto_path`.
   - `create_laporan_table`: relasi `user_id`, data dasar (petugas, tanggal, cuaca, unit, area), canvas `tanda_tangan` (base64/file), `grid_lokasi`, ciri ukuran, apron, aktivitas, tindak lanjut, pengusiran, `status`, dan `extra_data` (JSON).
   - `create_detail_satwa_table`: relasi `laporan_id`, `nama_satwa`, `jumlah`, `grid`, `foto_path`.
   - `create_foto_laporan_table`: relasi `laporan_id`, `detail_satwa_id`, `nama_file`, `tipe` (satwa/extra).
   - `create_tanggapan_table`: relasi `laporan_id`, `user_id`, `isi`.
2. **Seeders**:
   - `DatabaseSeeder` yang memasukkan data bawaan (5 user default, 8 jenis satwa dengan gambar, konfigurasi form fields & options sesuai data di `logbook_project_baru.sql`).
3. **Eloquent Models**:
   - `User.php`
   - `Laporan.php` (relasi `user()`, `detailSatwa()`, `fotoLaporan()`, `tanggapan()`, casts `extra_data => array`)
   - `DetailSatwa.php` (relasi `laporan()`, `foto()`)
   - `FotoLaporan.php`
   - `JenisSatwa.php`
   - `FormField.php` (relasi `options()`)
   - `FormFieldOption.php`
   - `Tanggapan.php`

### C. Middleware & Autentikasi
1. `RoleMiddleware.php`: Membatasi rute khusus `admin` dan `pegawai`.
2. `AuthController.php`:
   - Login (dilengkapi CSRF protection bawaan Laravel, throttling/rate limiting 5x coba per menit).
   - Logout.
   - Redirect otomatis sesuai peran (`admin` ke dashboard admin, `pegawai` ke dashboard user).

### D. Controllers
1. **User / Pegawai**:
   - `UserDashboardController.php`: Halaman beranda pegawai.
   - `LaporanController.php`:
     - `create`: Menampilkan `form_pengaduan` dengan field dinamis, daftar satwa, gridmap interaktif, dan auto-detect divisi user.
     - `store`: Validasi input, pemrosesan tanda tangan base64, penyimpanan foto satwa (`storage/uploads`), entri data `laporan` dan `detail_satwa`, serta JSON `extra_data`.
2. **Administrator**:
   - `AdminDashboardController.php`: Tabel semua laporan, filter & pencarian, stat card, modal validasi status ('belum' -> 'sudah'), dan aksi hapus laporan.
   - `DetailLaporanController.php`: Endpoint JSON untuk modal detail 2 halaman.
   - `AdminStatistikController.php`: Visualisasi grafik bulanan per jenis satwa (Stacked Bar chart) dan analisis grid zona temuan satwa.
   - `AdminManajemenController.php`:
     - Tab Manajemen User (CRUD akun, toggle aktif, reset password).
     - Tab Form Builder (tambah/edit field dinamis, ubah urutan, upload peta gridmap baru).
     - Tab Jenis Satwa (CRUD master satwa dan upload foto referensi).
   - `ReportExportController.php`:
     - Format cetak laporan PDF/Print view (`cetak_laporan.blade.php`).
     - Ekspor rekapan data ke Excel / CSV spreadsheet (`export_excel.blade.php` atau exporter).

### E. Views (Blade Templates) & Assets
1. **Layouting**:
   - `resources/views/layouts/app.blade.php` (layout umum).
   - `resources/views/layouts/admin.blade.php` (dengan sidebar komponen `partials.sidebar`).
2. **Halaman**:
   - `auth/login.blade.php` (desain visual modern sesuai tampilan login asli).
   - `user/dashboard.blade.php`.
   - `user/form-pengaduan.blade.php` (lengkap dengan JavaScript Signature Pad & Interactive Gridmap).
   - `admin/dashboard.blade.php`.
   - `admin/statistik.blade.php`.
   - `admin/manajemen.blade.php`.
   - `reports/cetak.blade.php`.
3. **Aset Gambar**:
   - Gambar master satwa, `gridmap_injourney.jpeg`, `watermark_injourney.jpeg`, `bg_login.jpeg`, `logo_login.png` dipindahkan ke `public/images/`.

---

## 4. Verification Plan

### Verifikasi Otomatis
- `php artisan test` atau validasi sintaks PHP `php -l`.
- `php artisan migrate:fresh --seed` untuk memastikan seluruh tabel dan data awal terisi dengan sempurna tanpa error relasi.

### Verifikasi Manual & Fungsional
1. **Pengujian Login**: Coba login dengan `admin.utama` dan `arff.divisi` / `amc.divisi`.
2. **Pengujian Form Pengaduan Pegawai**: Buka form pengaduan, klik koordinat peta gridmap, lakukan tanda tangan digital, pilih jenis satwa dan upload foto bukti, lalu submit.
3. **Pengujian Dashboard Admin**: Pastikan laporan baru muncul di tabel admin, periksa modal detail laporan, dan ubah status menjadi 'sudah ditangani'.
4. **Pengujian Statistik**: Buka menu statistik dan pastikan grafik rekapitulasi satwa bulanan terisi data.
5. **Pengujian Cetak & Export**: Cek halaman cetak laporan dan fitur ekspor data.
