<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MahasiswaController;
use App\Http\Controllers\Admin\DosenController;
use App\Http\Controllers\Admin\JadwalController;
use App\Http\Controllers\Admin\RuanganController;
use App\Http\Controllers\Admin\MataKuliahController;
use App\Http\Controllers\Dosen\DosenRekapController;
use Illuminate\Support\Facades\Route;

// ─── Auth (login / logout) ────────────────────────────────────────────
Route::get('/login',   [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login',  [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');

// ─── Admin Panel (guard: web / Admin model) ───────────────────────────
Route::middleware(['auth:web'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('mahasiswa', MahasiswaController::class)
         ->parameters(['mahasiswa' => 'mahasiswum']);

    Route::resource('dosen',   DosenController::class);
    Route::resource('jadwal',  JadwalController::class);
    Route::resource('ruangan', RuanganController::class);
    Route::post('ruangan/ajax-store', [RuanganController::class, 'ajaxStore'])->name('ruangan.ajax-store');
    Route::resource('matakuliah', MataKuliahController::class);
});

// ─── Dosen Panel (guard: dosen / Dosen model) ────────────────────────
Route::middleware(['auth:dosen'])->prefix('dosen')->name('dosen.')->group(function () {

    // Halaman rekap & daftar hadir live
    Route::get('/rekap',                      [DosenRekapController::class, 'index'])->name('rekap');

    // Load daftar mahasiswa saat jadwal dipilih (AJAX / form GET)
    Route::get('/rekap/mahasiswa',            [DosenRekapController::class, 'loadMahasiswa'])->name('rekap.mahasiswa');

    // Dosen ubah status absensi mahasiswa (form POST)
    Route::post('/absensi/{id_absensi}/ubah', [DosenRekapController::class, 'ubahStatus'])->name('absensi.ubah');
});

Route::redirect('/', '/login');