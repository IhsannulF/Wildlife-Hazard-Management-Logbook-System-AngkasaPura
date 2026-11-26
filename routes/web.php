<?php

use App\Http\Controllers\Admin\AiPrediksiController as AdminAiPrediksiController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\HistoriBirdstrikeController as AdminHistoriBirdstrikeController;
use App\Http\Controllers\Admin\KepadatanPenerbanganController as AdminKepadatanPenerbanganController;
use App\Http\Controllers\Admin\ManajemenController as AdminManajemenController;
use App\Http\Controllers\Admin\PengusiranController as AdminPengusiranController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\StatistikController as AdminStatistikController;
use App\Http\Controllers\AiIdentifikasiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\TanggapanController;
use App\Http\Controllers\TindakanPengusiranController;
use App\Http\Controllers\UserDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->role === 'admin'
            ? redirect()->route('admin.dashboard')
            : redirect()->route('user.dashboard');
    }

    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Rute Offline Fallback Page (PWA)
Route::view('/offline', 'errors.offline')->name('offline');

// Rute Input Pelaporan Khusus Pegawai (Admin tidak melakukan inspeksi lapangan)
Route::middleware(['auth', 'role:pegawai'])->group(function () {
    Route::get('/laporan/create', [LaporanController::class, 'create'])->name('laporan.create');
    Route::post('/laporan', [LaporanController::class, 'store'])->name('laporan.store');
    Route::post('/laporan/sync-offline', [LaporanController::class, 'syncOffline'])->name('laporan.syncOffline');

    // Pilar 4: AI Identifikasi Satwa & SOP (Gemini Vision)
    Route::post('/laporan/identifikasi-satwa', [AiIdentifikasiController::class, 'identify'])
        ->middleware('throttle:10,1')
        ->name('laporan.identifikasi');
    Route::post('/laporan/identifikasi-satwa/{id}/hasil', [AiIdentifikasiController::class, 'markResult'])
        ->name('laporan.identifikasi.hasil');
});

// Rute Detail & Cetak Laporan (Pegawai & Admin)
Route::middleware(['auth', 'role:pegawai,admin'])->group(function () {
    Route::get('/laporan/{id}/detail', [LaporanController::class, 'detail'])->name('laporan.detail.user');
    Route::get('/laporan/{id}/cetak', [AdminReportController::class, 'cetak'])->name('laporan.cetak');
    Route::get('/laporan/{id}/pdf', [AdminReportController::class, 'downloadPdf'])->name('laporan.pdf');
    Route::post('/laporan/{id}/tanggapan', [TanggapanController::class, 'store'])->name('laporan.tanggapan.store');

    // PRD v2.1 F0: Log tindakan pengusiran (scare action) per waktu & grid
    Route::post('/pengusiran', [TindakanPengusiranController::class, 'store'])->name('pengusiran.store');
});

// Rute Pegawai
Route::middleware(['auth', 'role:pegawai'])->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');
});

// Rute Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/verifikasi', [AdminDashboardController::class, 'verifikasiIndex'])->name('verifikasi');
    Route::get('/verifikasi/autocrat', [AdminDashboardController::class, 'verifikasiIndex'])->name('verifikasi.autocrat');
    Route::get('/verifikasi/patroli', [AdminDashboardController::class, 'verifikasiIndex'])->name('verifikasi.patroli');
    Route::get('/verifikasi/detail-temuan', [AdminDashboardController::class, 'verifikasiIndex'])->name('verifikasi.detail-temuan');
    Route::get('/verifikasi/backup-temuan', [AdminDashboardController::class, 'verifikasiIndex'])->name('verifikasi.backup-temuan');
    Route::get('/verifikasi/master-titik', [AdminDashboardController::class, 'verifikasiIndex'])->name('verifikasi.master-titik');
    Route::get('/verifikasi/master-jenis', [AdminDashboardController::class, 'verifikasiIndex'])->name('verifikasi.master-jenis');
    Route::get('/verifikasi/master-preview', [AdminDashboardController::class, 'verifikasiIndex'])->name('verifikasi.master-preview');
    Route::get('/laporan/{id}/detail', [AdminDashboardController::class, 'getDetail'])->name('laporan.detail');
    Route::post('/laporan/{id}/update', [AdminDashboardController::class, 'update'])->name('laporan.update');
    Route::post('/laporan/{id}/delete', [AdminDashboardController::class, 'destroy'])->name('laporan.delete');

    Route::get('/statistik', [AdminStatistikController::class, 'index'])->name('statistik');

    Route::get('/manajemen', [AdminManajemenController::class, 'index'])->name('manajemen');
    Route::get('/manajemen-user', [AdminManajemenController::class, 'userIndex'])->name('users');

    // Manajemen Fields
    Route::post('/manajemen/fields', [AdminManajemenController::class, 'storeField'])->name('manajemen.fields.store');
    Route::post('/manajemen/fields/{id}', [AdminManajemenController::class, 'updateField'])->name('manajemen.fields.update');
    Route::post('/manajemen/fields/{id}/toggle', [AdminManajemenController::class, 'toggleField'])->name('manajemen.fields.toggle');
    Route::post('/manajemen/fields/{id}/delete', [AdminManajemenController::class, 'destroyField'])->name('manajemen.fields.delete');

    // Manajemen Satwa
    Route::post('/manajemen/satwa', [AdminManajemenController::class, 'storeSatwa'])->name('manajemen.satwa.store');
    Route::post('/manajemen/satwa/{id}', [AdminManajemenController::class, 'updateSatwa'])->name('manajemen.satwa.update');
    Route::post('/manajemen/satwa/{id}/delete', [AdminManajemenController::class, 'destroySatwa'])->name('manajemen.satwa.delete');

    // Manajemen Users
    Route::post('/manajemen/users', [AdminManajemenController::class, 'storeUser'])->name('manajemen.users.store');
    Route::post('/manajemen/users/{id}', [AdminManajemenController::class, 'updateUser'])->name('manajemen.users.update');
    Route::post('/manajemen/users/{id}/toggle', [AdminManajemenController::class, 'toggleUser'])->name('manajemen.users.toggle');
    Route::post('/manajemen/users/{id}/delete', [AdminManajemenController::class, 'destroyUser'])->name('manajemen.users.delete');

    // Cetak & Export
    Route::get('/laporan/{id}/cetak', [AdminReportController::class, 'cetak'])->name('laporan.cetak');
    Route::get('/laporan/{id}/pdf', [AdminReportController::class, 'downloadPdf'])->name('laporan.pdf');
    Route::get('/export-excel', [AdminReportController::class, 'index'])->name('export.excel');
    Route::match(['get', 'post'], '/export-excel/download', [AdminReportController::class, 'downloadExcel'])->name('export.excel.download');
    Route::get('/export-excel/count', [AdminReportController::class, 'countMatching'])->name('export.excel.count');
    Route::match(['get', 'post'], '/export-bundle-pdf', [AdminReportController::class, 'downloadMonthlyBundlePdf'])->name('export.bundle.pdf');
    Route::get('/export-bundle-pdf/count', [AdminReportController::class, 'countMonthlyBundle'])->name('export.bundle.count');

    // Pilar 4: AI & Mitigasi Prediktif Birdstrike
    Route::prefix('ai')->name('ai.')->group(function () {
        // Prediksi Birdstrike
        Route::get('/prediksi/latest', [AdminAiPrediksiController::class, 'latest'])->name('prediksi.latest');
        Route::post('/prediksi/run', [AdminAiPrediksiController::class, 'run'])->name('prediksi.run')->middleware('throttle:3,10');
        Route::get('/prediksi', [AdminAiPrediksiController::class, 'index'])->name('prediksi.index');
        Route::post('/prediksi/{id}/akui', [AdminAiPrediksiController::class, 'acknowledge'])->name('prediksi.akui');

        // Histori Birdstrike
        Route::get('/histori', [AdminHistoriBirdstrikeController::class, 'index'])->name('histori.index');
        Route::post('/histori', [AdminHistoriBirdstrikeController::class, 'store'])->name('histori.store');
        Route::put('/histori/{id}', [AdminHistoriBirdstrikeController::class, 'update'])->name('histori.update');
        Route::delete('/histori/{id}', [AdminHistoriBirdstrikeController::class, 'destroy'])->name('histori.destroy');
        Route::get('/histori/template', [AdminHistoriBirdstrikeController::class, 'template'])->name('histori.template');
        Route::post('/histori/import', [AdminHistoriBirdstrikeController::class, 'import'])->name('histori.import');

        // Kepadatan Penerbangan
        Route::get('/lalu-lintas', [AdminKepadatanPenerbanganController::class, 'index'])->name('lalu-lintas.index');
        Route::post('/lalu-lintas', [AdminKepadatanPenerbanganController::class, 'store'])->name('lalu-lintas.store');
        Route::get('/lalu-lintas/template', [AdminKepadatanPenerbanganController::class, 'template'])->name('lalu-lintas.template');
        Route::post('/lalu-lintas/import', [AdminKepadatanPenerbanganController::class, 'import'])->name('lalu-lintas.import');

        // PRD v2.1 F0: Log Pengusiran & Master Alat Dispersal
        Route::get('/pengusiran', [AdminPengusiranController::class, 'index'])->name('pengusiran.index');
        Route::post('/pengusiran/alat', [AdminPengusiranController::class, 'storeAlat'])->name('pengusiran.alat.store');
        Route::put('/pengusiran/alat/{id}', [AdminPengusiranController::class, 'updateAlat'])->name('pengusiran.alat.update');
        Route::post('/pengusiran/alat/{id}/toggle', [AdminPengusiranController::class, 'toggleAlat'])->name('pengusiran.alat.toggle');
        Route::delete('/pengusiran/alat/{id}', [AdminPengusiranController::class, 'destroyAlat'])->name('pengusiran.alat.destroy');
    });
});
