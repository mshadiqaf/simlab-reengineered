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

Route::prefix('api')->group(function() {
    Route::get('/ruangan', [MasterDataController::class, 'getRuangan']);
    Route::get('/alat', [MasterDataController::class, 'getAlat']);
    Route::get('/pengujian', [MasterDataController::class, 'getJenisPengujian']);
});

require __DIR__.'/settings.php';
