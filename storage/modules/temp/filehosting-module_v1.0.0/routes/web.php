<?php
// modules/FileHosting/routes/web.php

use Illuminate\Support\Facades\Route;
use App\Modules\FileHosting\Http\Controllers\DashboardController;
use App\Modules\FileHosting\Http\Controllers\FolderController;
use App\Modules\FileHosting\Http\Controllers\FileController;
use App\Modules\FileHosting\Http\Controllers\SettingController;

Route::prefix('filehosting')
    ->name('filehosting.')
    ->middleware(['web', 'auth'])
    ->group(function () {

        // ----------------------------------------------------------------
        // Dashboard
        // ----------------------------------------------------------------
        Route::get('/', [DashboardController::class, 'index'])->name('index');

        // ----------------------------------------------------------------
        // Folders (optional if you use them)
        // ----------------------------------------------------------------
        Route::prefix('folders')->name('folders.')->group(function () {
            Route::get('/tree',              [FolderController::class, 'tree'])->name('tree');
            Route::get('/{id}',              [FolderController::class, 'show'])->name('show');
            Route::post('/',                 [FolderController::class, 'store'])->name('store');
            Route::patch('/{id}/rename',     [FolderController::class, 'rename'])->name('rename');
            Route::patch('/{id}/move',       [FolderController::class, 'move'])->name('move');
            Route::delete('/{id}',           [FolderController::class, 'destroy'])->name('destroy');

            // Permissions sub-resource
            Route::get('/{id}/permissions',  [FolderController::class, 'permissions'])->name('permissions');
            Route::post('/{id}/permissions', [FolderController::class, 'grantPermission'])->name('grant');
        });

        // ----------------------------------------------------------------
        // Files
        // ----------------------------------------------------------------
        Route::name('files.')->group(function () {
            Route::get('/upload',             fn () => view('filehosting::upload'))->name('upload');
            Route::post('/',                  [FileController::class, 'store'])->name('store');
            Route::get('/all',                [FileController::class, 'all'])->name('all');
            Route::get('/search',             [FileController::class, 'search'])->name('search');
            Route::get('/{id}',               [FileController::class, 'show'])->name('show');
            Route::get('/{id}/download',      [FileController::class, 'download'])->name('download');
            Route::patch('/{id}',             [FileController::class, 'update'])->name('update');
            Route::post('/{id}/replace',      [FileController::class, 'replace'])->name('replace');
            Route::patch('/{id}/move',        [FileController::class, 'move'])->name('move');
            Route::delete('/{id}',            [FileController::class, 'destroy'])->name('destroy');

            // Versions sub-resource
            Route::get('/{id}/versions',                             [FileController::class, 'versions'])->name('versions');
            Route::post('/{fileId}/versions/{versionId}/restore',   [FileController::class, 'restoreVersion'])->name('versions.restore');
            Route::delete('/{fileId}/versions/{versionId}',         [FileController::class, 'deleteVersion'])->name('versions.destroy');
        });

        // ----------------------------------------------------------------
        // Settings (admin only)
        // ----------------------------------------------------------------
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/',       [SettingController::class, 'index'])->name('index');
            Route::get('/data',   [SettingController::class, 'show'])->name('show');
            Route::patch('/',     [SettingController::class, 'update'])->name('update');
            Route::post('/flush', [SettingController::class, 'flushCache'])->name('flush');
        });
    });