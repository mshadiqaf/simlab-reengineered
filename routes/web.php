<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    
    // Mahasiswa
    Route::inertia('pengajuan', 'pengajuan/Index')->name('pengajuan.index');
    Route::inertia('pengajuan/baru', 'pengajuan/Create')->name('pengajuan.create');
    Route::inertia('pengajuan/{id}', 'pengajuan/Show')->name('pengajuan.show');
    Route::inertia('ketersediaan', 'ketersediaan/Index')->name('ketersediaan.index');

    // Kepala Lab
    Route::inertia('kepala-lab', 'kepala-lab/Index')->name('kepala-lab.index');
    Route::inertia('kepala-lab/{id}', 'kepala-lab/Show')->name('kepala-lab.show');

    // Laboran
    Route::inertia('laboran', 'laboran/Index')->name('laboran.index');
    Route::inertia('laboran/{id}', 'laboran/Show')->name('laboran.show');

    // Master Data
    Route::inertia('master-data/ruangan', 'master-data/ruangan/Index')->name('master-data.ruangan.index');
    Route::inertia('master-data/alat', 'master-data/alat/Index')->name('master-data.alat.index');
    Route::inertia('master-data/pengujian', 'master-data/pengujian/Index')->name('master-data.pengujian.index');
});

use App\Http\Controllers\Api\MasterDataController;
use App\Http\Controllers\Api\AdminMasterDataController;
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

    // ── Kepala Laboratorium (Verifikasi + Admin Master Data) ───────
    Route::middleware(['auth', 'role:Kepala Laboratorium'])->prefix('kepala-lab')->group(function () {
        // Verifikasi Pengajuan
        Route::get('/pengajuan',                   [KepalaLabController::class, 'index']);
        Route::get('/pengajuan/{id}',              [KepalaLabController::class, 'show']);
        Route::patch('/pengajuan/{id}/verifikasi', [KepalaLabController::class, 'verifikasi']);

        // Admin CRUD Master Data
        Route::post('/ruangan',             [AdminMasterDataController::class, 'storeRuangan']);
        Route::put('/ruangan/{id}',         [AdminMasterDataController::class, 'updateRuangan']);
        Route::delete('/ruangan/{id}',      [AdminMasterDataController::class, 'destroyRuangan']);

        Route::post('/alat',                [AdminMasterDataController::class, 'storeAlat']);
        Route::put('/alat/{id}',            [AdminMasterDataController::class, 'updateAlat']);
        Route::delete('/alat/{id}',         [AdminMasterDataController::class, 'destroyAlat']);

        Route::post('/jenis-pengujian',         [AdminMasterDataController::class, 'storeJenisPengujian']);
        Route::put('/jenis-pengujian/{id}',     [AdminMasterDataController::class, 'updateJenisPengujian']);
        Route::delete('/jenis-pengujian/{id}',  [AdminMasterDataController::class, 'destroyJenisPengujian']);
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
