# Wildlife Hazard Management Logbook System
### PT Angkasa Pura (Persero)

[![Laravel 12](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![Tailwind CSS v4](https://img.shields.io/badge/Tailwind_CSS-v4.0-38B2AC?style=for-the-badge&logo=tailwind-css)](https://tailwindcss.com)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)

Sistem pencatatan, pemantauan, analisis, dan pelaporan terpadu berbasis digital untuk mitigasi risiko bahaya satwa liar (*wildlife hazard*) di area sisi udara bandara (*airside operations*). Dikembangkan untuk memenuhi standar kepatuhan regulasi keselamatan penerbangan nasional dan internasional (**ICAO Annex 14** dan **ICAO Doc 9137**).

---

## 📋 Fitur Utama Sistem

1. **Autentikasi & Otorisasi Dinas Berbasis Peran**
   - Hak akses role-based: `admin` (Safety Manager / Airport Operation) dan `pegawai` (AMC, ARFF, Avsec, SMS & OHS).
   - Antarmuka login modern dengan brand identity resmi.

2. **Formulir Pelaporan Lapangan Interaktif (5 Bagian)**
   - **Bagian 1**: Data kondisi pemantauan (cuaca, unit kerja, waktu, area inspeksi).
   - **Bagian 2**: Inventarisasi satwa multi-select + input multi-titik sebaran (ekor & grid).
   - **Bagian 3**: Denah visual spasial **Airside Gridmap**.
   - **Bagian 4**: Detail morfologi, kondisi, aktivitas, dan tindakan pengusiran (*dispersal action*).
   - **Bagian 5**: Tanda tangan digital basah berbasis Canvas HTML5 (`SignaturePad`).

3. **Dashboard Operasional & Validasi Cepat**
   - Rekap metrik statistik status penanganan secara *real-time*.
   - Filter cepat berdasarkan status penanganan dan pencarian teks.
   - Modal pratinjau detail 2 halaman & validasi cepat satu klik.

4. **Visualisasi Statistik & Analisis Spasial**
   - Grafik batang bertumpuk (*Stacked Bar Chart*) tren kemunculan satwa bulanan.
   - Analisis Top 5 spesies satwa terbanyak per tahun.
   - Filter interaktif berbasis kode zona grid koordinat bandara.

5. **Manajemen Master Data & Dynamic Form Builder**
   - Pengaturan field formulir dinamis tanpa koding.
   - Manajemen katalog master satwa (nama, foto referensi).
   - Manajemen pengguna operasional.

6. **Pelaporan & Ekspor Berkas Formal**
   - Cetak Berita Acara resmi format A4 (dilengkapi kop dinas, rincian temuan, dan TTD digital) via browser print / PDF.
   - Ekspor rekapitulasi data ke format Spreadsheet Microsoft Excel (.xls).

---

## 🛠️ Tech Stack

- **Backend**: Laravel 12.x (PHP 8.2+)
- **Frontend**: Blade Templating, Tailwind CSS v4, Vite 8.x
- **Basis Data**: MySQL / MariaDB (InnoDB engine, foreign keys)
- **Komponen Interaktif**: Chart.js, SignaturePad.js

---

## 🚀 Panduan Instalasi & Menjalankan Lokal

### 1. Prasyarat
- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL Server

### 2. Kloning & Pengaturan Dependensi
```bash
# Clone repository
git clone https://github.com/IhsannulF/Wildlife-Hazard-Management-Logbook-System-AngkasaPura.git
cd Wildlife-Hazard-Management-Logbook-System-AngkasaPura

# Install dependensi PHP & JavaScript
composer install
npm install
```

### 3. Konfigurasi Lingkungan (.env)
```bash
# Salin file environment
cp .env.example .env

# Generate Application Key
php artisan key:generate
```
Sesuaikan konfigurasi database di file `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=logbook_project_baru
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Migrasi & Seeding Basis Data
```bash
# Jalankan migrasi dan isi data awal seeder
php artisan migrate --seed
```

### 5. Kompilasi Aset Frontend & Jalankan Server
```bash
# Build aset Tailwind CSS
npm run build

# Jalankan server lokal Laravel
php artisan serve
```
Buka browser di: `http://127.0.0.1:8000`

---

## 👥 Akun Default Operasional

| Username | Role | Unit Kerja | Password Default |
| :--- | :--- | :--- | :--- |
| `admin.utama` | Admin | Kantor Cabang / Safety Unit | `juanda123` |
| `amc.divisi` | Pegawai | Apron Movement Control (AMC) | `juanda123` |
| `arff.divisi` | Pegawai | Rescue & Fire Fighting (ARFF) | `juanda123` |
| `security.divisi` | Pegawai | Aviation Security (Avsec) | `juanda123` |
| `smsohs.divisi` | Pegawai | SMS & OHS Safety Division | `juanda123` |

---

## 📖 Dokumentasi Lengkap (PRD)

Dokumentasi spesifikasi teknis dan fungsional lengkap dapat dilihat di berkas **[prd.md](prd.md)**.
