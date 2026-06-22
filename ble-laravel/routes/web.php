<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MahasiswaController;
use App\Http\Controllers\Admin\DosenController;
use App\Http\Controllers\Admin\JadwalController;
use App\Http\Controllers\Admin\RuanganController;
use App\Http\Controllers\Admin\MataKuliahController;
use Illuminate\Support\Facades\Route;

// ─── Auth Admin ───────────────────────────────────────────────────────
Route::get('/login',  [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store']);
Route::post('/logout',[\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');

// ─── Admin Panel ──────────────────────────────────────────────────────
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('mahasiswa', MahasiswaController::class)
         ->parameters(['mahasiswa' => 'mahasiswum']); // hindari reserved word

    Route::resource('dosen',   DosenController::class);
    Route::resource('jadwal',  JadwalController::class);
    Route::resource('ruangan', RuanganController::class);
    Route::resource('matakuliah', MataKuliahController::class);
});

Route::redirect('/', '/admin');