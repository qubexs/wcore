<?php
// modules/FileHosting/routes/web.php

use Illuminate\Support\Facades\Route;
use App\Modules\FileHosting\Http\Controllers\DashboardController;
use App\Modules\FileHosting\Http\Controllers\FolderController;
use App\Modules\FileHosting\Http\Controllers\FileController;
use App\Modules\FileHosting\Http\Controllers\SettingController;
use App\Modules\FileHosting\Http\Controllers\PreviewController;

/*
|--------------------------------------------------------------------------
| Preview routes (must be before file routes)
|--------------------------------------------------------------------------
*/
Route::prefix('filehosting')
    ->name('filehosting.')
    ->middleware(['web', 'auth'])
    ->group(function () {
        Route::get('/preview/word/{id}', [PreviewController::class, 'previewWord'])->name('preview.word');
        Route::get('/preview/excel/{id}', [PreviewController::class, 'previewExcel'])->name('preview.excel');
    });

/*
|--------------------------------------------------------------------------
| Settings routes — MUST be declared BEFORE the main filehosting prefix
| to prevent the wildcard folder/file routes from swallowing them.
|--------------------------------------------------------------------------
*/
Route::prefix('filehosting/settings')
    ->name('filehosting.settings.')
    ->middleware(['web', 'auth'])
    ->group(function () {
        Route::get('/',              [SettingController::class, 'index'])             ->name('index');
        Route::get('/data',          [SettingController::class, 'show'])              ->name('show');
        Route::patch('/',            [SettingController::class, 'update'])            ->name('update');
        Route::post('/',             [SettingController::class, 'updateRoles'])       ->name('updateRoles');
        Route::post('/flush',        [SettingController::class, 'flushCache'])        ->name('flush');
        Route::get('/permissions',   [SettingController::class, 'permissions'])       ->name('permissions');
        Route::post('/permissions',  [SettingController::class, 'updatePermissions']) ->name('permissions.update');
    });

/*
|--------------------------------------------------------------------------
| Main FileHosting routes
|--------------------------------------------------------------------------
*/
Route::prefix('filehosting')
    ->name('filehosting.')
    ->middleware(['web', 'auth'])
    ->group(function () {

        /*
        |--------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------
        */
        Route::get('/', [DashboardController::class, 'index'])->name('index');

        /*
        |--------------------------------------------------------------
        | Folders
        |--------------------------------------------------------------
        */
        Route::prefix('folders')
            ->name('folders.')
            ->group(function () {

                // List / tree
                Route::get('/tree', [FolderController::class, 'tree'])->name('tree');

                // Create
                Route::post('/', [FolderController::class, 'store'])->name('store');

                // Browse a folder
                Route::get('/{id}', [FolderController::class, 'show'])->name('show');

                // Rename
                Route::patch('/{id}/rename', [FolderController::class, 'rename'])->name('rename');

                // Move to another parent
                Route::patch('/{id}/move', [FolderController::class, 'move'])->name('move');

                // Delete
                Route::delete('/{id}', [FolderController::class, 'destroy'])->name('destroy');

                // Permissions
                Route::get('/{id}/permissions',  [FolderController::class, 'permissions'])    ->name('permissions');
                Route::post('/{id}/permissions', [FolderController::class, 'grantPermission'])->name('grant');
            });

        /*
        |--------------------------------------------------------------
        | Files
        |--------------------------------------------------------------
        */
        Route::prefix('files')
            ->name('files.')
            ->group(function () {

                // Upload form (GET) — shows Upload / Files / Folders tabs
                Route::get('/upload', [FileController::class, 'uploadView'])->name('upload');

                // All files page (GET) — the all.blade.php view
                Route::get('/all', [FileController::class, 'all'])->name('all');

                // Search
                Route::get('/search', [FileController::class, 'search'])->name('search');

                // Store uploaded file
                Route::post('/', [FileController::class, 'store'])->name('store');

                // ── Specific ID routes BEFORE the catch-all /{id} ──

                // Download
                Route::get('/{id}/download', [FileController::class, 'download'])->name('download');

                // Versions list
                Route::get('/{id}/versions', [FileController::class, 'versions'])->name('versions');

                // Replace file content (new version)
                Route::post('/{id}/replace', [FileController::class, 'replace'])->name('replace');

                // Move file to another folder
                Route::patch('/{id}/move', [FileController::class, 'move'])->name('move');

                // Restore a specific version
                Route::post('/{fileId}/versions/{versionId}/restore', [FileController::class, 'restoreVersion'])
                    ->name('versions.restore');

                // Delete a specific version
                Route::delete('/{fileId}/versions/{versionId}', [FileController::class, 'deleteVersion'])
                    ->name('versions.destroy');

                // Delete file
                Route::delete('/{id}', [FileController::class, 'destroy'])->name('destroy');

                // Update file metadata
                Route::patch('/{id}', [FileController::class, 'update'])->name('update');

                // Report file
                Route::post('/{id}/report', [FileController::class, 'report'])->name('report');

                // Favorite file
                Route::post('/{id}/favorite', [FileController::class, 'favorite'])->name('favorite');

                // Share file (create share link)
                Route::post('/{id}/share', [FileController::class, 'share'])->name('share');

                // View shared file by token
                Route::get('/shared/{token}', [FileController::class, 'viewShared'])->name('shared');

                // View / preview file — catch-all LAST
                Route::get('/{id}', [FileController::class, 'show'])->name('show');
            });
    });