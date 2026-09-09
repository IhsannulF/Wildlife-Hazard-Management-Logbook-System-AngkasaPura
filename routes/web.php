<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\StatistikController as AdminStatistikController;
use App\Http\Controllers\Admin\ManajemenController as AdminManajemenController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;

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

// Rute Pegawai
Route::middleware(['auth', 'role:pegawai'])->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');
    Route::get('/laporan/create', [LaporanController::class, 'create'])->name('laporan.create');
    Route::post('/laporan', [LaporanController::class, 'store'])->name('laporan.store');
});

// Rute Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/laporan/{id}/detail', [AdminDashboardController::class, 'getDetail'])->name('laporan.detail');
    Route::post('/laporan/{id}/update', [AdminDashboardController::class, 'update'])->name('laporan.update');
    Route::post('/laporan/{id}/delete', [AdminDashboardController::class, 'destroy'])->name('laporan.delete');

    Route::get('/statistik', [AdminStatistikController::class, 'index'])->name('statistik');

    Route::get('/manajemen', [AdminManajemenController::class, 'index'])->name('manajemen');

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
    Route::get('/export-excel', [AdminReportController::class, 'exportExcel'])->name('export.excel');
});
