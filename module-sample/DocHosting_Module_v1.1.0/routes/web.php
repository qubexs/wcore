<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileHosting\FolderController;
use App\Http\Controllers\FileHosting\FileController;
use App\Http\Controllers\FileHosting\SettingController;
use App\Http\Controllers\FileHosting\DashboardController;

/*
|--------------------------------------------------------------------------
| FileHosting Module Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->prefix('filehosting')->name('filehosting.')->group(function () {
    
    // Dashboard / Index
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/folder/{folder?}', [DashboardController::class, 'browse'])->name('browse');
    
    // Folder Management
    Route::prefix('folders')->name('folders.')->group(function () {
        Route::post('/', [FolderController::class, 'store'])->name('store');
        Route::put('/{folder}', [FolderController::class, 'update'])->name('update');
        Route::delete('/{folder}', [FolderController::class, 'destroy'])->name('destroy');
        Route::post('/{folder}/move', [FolderController::class, 'move'])->name('move');
        Route::post('/{folder}/permissions', [FolderController::class, 'permissions'])->name('permissions');
    });
    
    // File Management
    Route::prefix('files')->name('files.')->group(function () {
        Route::get('/upload', [FileController::class, 'create'])->name('create');
        Route::post('/upload', [FileController::class, 'store'])->name('store');
        Route::get('/{file}', [FileController::class, 'show'])->name('show');
        Route::get('/{file}/download', [FileController::class, 'download'])->name('download');
        Route::put('/{file}', [FileController::class, 'update'])->name('update');
        Route::delete('/{file}', [FileController::class, 'destroy'])->name('destroy');
        Route::post('/{file}/replace', [FileController::class, 'replace'])->name('replace');
        Route::post('/{file}/move', [FileController::class, 'move'])->name('move');
        Route::get('/{file}/versions', [FileController::class, 'versions'])->name('versions');
        Route::post('/{file}/restore/{version}', [FileController::class, 'restoreVersion'])->name('restore');
    });
    
    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    
    // Search & Filters
    Route::get('/search', [DashboardController::class, 'search'])->name('search');
    Route::get('/recent', [DashboardController::class, 'recent'])->name('recent');
    Route::get('/shared', [DashboardController::class, 'shared'])->name('shared');
    Route::get('/trash', [DashboardController::class, 'trash'])->name('trash');
    Route::post('/trash/restore/{id}', [DashboardController::class, 'restore'])->name('trash.restore');
    Route::delete('/trash/permanent/{id}', [DashboardController::class, 'forceDelete'])->name('trash.forceDelete');
});