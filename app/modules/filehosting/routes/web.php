<?php

use Illuminate\Support\Facades\Route;
use App\Modules\FileHosting\Controllers\FileHostingController;

Route::middleware(['auth'])->prefix('filehosting')->group(function () {
    Route::get('/', [FileHostingController::class, 'index'])->name('filehosting.index');
    Route::post('/upload', [FileHostingController::class, 'upload'])->name('filehosting.upload');
    Route::delete('/delete/{file}', [FileHostingController::class, 'delete'])->name('filehosting.delete');
});
