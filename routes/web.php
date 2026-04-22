<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

use App\Http\Controllers\Api\MasterDataController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfilController;
use App\Http\Controllers\Api\KetersediaanController;
use App\Http\Controllers\Api\PengajuanController;
use App\Http\Controllers\Api\KepalaLabController;
use App\Http\Controllers\Api\LaboranController;

Route::prefix('api')->group(function() {

    // ── Master Data & Ketersediaan (Publik, tidak butuh login) ───────────
    Route::get('/ruangan',      [MasterDataController::class, 'getRuangan']);
    Route::get('/alat',         [MasterDataController::class, 'getAlat']);
    Route::get('/pengujian',    [MasterDataController::class, 'getJenisPengujian']);
    Route::get('/ketersediaan', [KetersediaanController::class, 'kalender']);

    // ── Semua User Terautentikasi ───────────────────────────────────────
    Route::middleware(['auth'])->group(function () {
        // Auth & Profil
        Route::get('/user',    [AuthController::class, 'user']);    // lama (tetap dipertahankan)
        Route::get('/profil',  [ProfilController::class, 'show']);
        Route::put('/profil',  [ProfilController::class, 'update']);

        // Transaksi Pengajuan
        Route::post('/pengajuan',       [PengajuanController::class, 'store']);
        Route::get('/pengajuan',        [PengajuanController::class, 'index']);
        Route::get('/pengajuan/{id}',   [PengajuanController::class, 'show']);
    });

    // ── Kepala Laboratorium ────────────────────────────────────
    Route::middleware(['auth', 'role:Kepala Laboratorium'])->prefix('kepala-lab')->group(function () {
        Route::get('/pengajuan',                   [KepalaLabController::class, 'index']);
        Route::get('/pengajuan/{id}',              [KepalaLabController::class, 'show']);
        Route::patch('/pengajuan/{id}/verifikasi', [KepalaLabController::class, 'verifikasi']);
    });

    // ── Petugas Laboran ──────────────────────────────────────
    Route::middleware(['auth', 'role:Petugas Laboran'])->prefix('laboran')->group(function () {
        Route::get('/pengajuan',                [LaboranController::class, 'index']);
        Route::get('/pengajuan/{id}',           [LaboranController::class, 'show']);
        Route::patch('/pengajuan/{id}/validasi',[LaboranController::class, 'validasi']);
        Route::patch('/pengajuan/{id}/selesai', [LaboranController::class, 'selesai']);
    });
});

require __DIR__.'/settings.php';
