<?php

use App\Http\Controllers\SertifikatController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::middleware(['web', 'auth'])->prefix('sertifikat')->name('sertifikat.')->group(function () {
    Route::get('/cetak/{santri}', [SertifikatController::class, 'printSingle'])->name('cetak_single');
    Route::get('/cetak-kolektif', [SertifikatController::class, 'printCollective'])->name('cetak_kolektif');
    Route::get('/preview/{setting}', [SertifikatController::class, 'previewSetting'])->name('preview_setting');
});
