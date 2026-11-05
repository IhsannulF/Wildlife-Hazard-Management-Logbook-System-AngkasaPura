<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ManajemenController as AdminManajemenController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\StatistikController as AdminStatistikController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LaporanController;
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

// Rute Laporan (Pegawai & Admin)
Route::middleware(['auth', 'role:pegawai,admin'])->group(function () {
    Route::get('/laporan/create', [LaporanController::class, 'create'])->name('laporan.create');
    Route::post('/laporan', [LaporanController::class, 'store'])->name('laporan.store');
    Route::get('/laporan/{id}/detail', [LaporanController::class, 'detail'])->name('laporan.detail.user');
    Route::get('/laporan/{id}/cetak', [AdminReportController::class, 'cetak'])->name('laporan.cetak');
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
    Route::get('/export-excel', [AdminReportController::class, 'index'])->name('export.excel');
    Route::match(['get', 'post'], '/export-excel/download', [AdminReportController::class, 'downloadExcel'])->name('export.excel.download');
    Route::get('/export-excel/count', [AdminReportController::class, 'countMatching'])->name('export.excel.count');
});
