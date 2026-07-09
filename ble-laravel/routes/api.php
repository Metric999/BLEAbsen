<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AbsensiController;
use App\Http\Controllers\API\DosenController;
use App\Http\Controllers\API\RuanganController;
use Illuminate\Support\Facades\Route;

// ─── Public ───────────────────────────────────────────────────────────
Route::post('/auth/login',  [AuthController::class, 'login']);
Route::get('/ruangan',      [RuanganController::class, 'index']);
Route::get('/ruangan/{id}', [RuanganController::class, 'show']);

// ─── Authenticated ────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/auth/logout',          [AuthController::class, 'logout']);
    Route::get('/auth/me',               [AuthController::class, 'me']);
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);

    // ── Mahasiswa ──────────────────────────────────────────────────────
    Route::middleware('role:mahasiswa')->prefix('mahasiswa')->group(function () {
        Route::get('/profile',             fn(\Illuminate\Http\Request $r) => response()->json($r->user()));
        Route::post('/absensi/check',      [AbsensiController::class, 'checkJadwal']);
        Route::post('/absensi/store',      [AbsensiController::class, 'store']);
        Route::get('/absensi/riwayat',     [AbsensiController::class, 'riwayat']);
    });

    // ── Dosen ──────────────────────────────────────────────────────────
    Route::middleware('role:dosen')->prefix('dosen')->group(function () {
        Route::get('/profile',                             fn(\Illuminate\Http\Request $r) => response()->json($r->user()));
        Route::get('/jadwal',                              [DosenController::class, 'jadwal']);
        Route::get('/absensi/{id_jadwal}',                 [DosenController::class, 'daftarHadir']);
        Route::put('/absensi/{id_absensi}',                [DosenController::class, 'updateAbsensi']);
        Route::get('/rekap/{id_jadwal}',                   [DosenController::class, 'rekap']);
    });
});