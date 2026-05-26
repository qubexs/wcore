<?php
// modules/filehosting/routes/web.php

use Illuminate\Support\Facades\Route;
use App\Modules\FileHosting\Controllers\FileHostingController;

Route::prefix('filehosting')
    ->name('filehosting.')
    ->middleware(['web', 'auth']) // ensure web + auth
    ->group(function () {

        // Main index page
        Route::get('/', [FileHostingController::class, 'index'])
            ->name('index')
            ->middleware('permission:filehosting.view');

        // Show upload form
        Route::get('/upload', [FileHostingController::class, 'showUploadForm'])
            ->name('upload')
            ->middleware('permission:filehosting.upload');

        // Handle file upload
        Route::post('/upload', [FileHostingController::class, 'upload'])
            ->name('upload.post')
            ->middleware('permission:filehosting.upload');

        // List all files
        Route::get('/all', [FileHostingController::class, 'index'])
            ->name('all')
            ->middleware('permission:filehosting.view');

        // 🔑 VIEW / OPEN FILE (THIS FIXES 404)
        Route::get('/view/{file}', [FileHostingController::class, 'view'])
            ->name('view')
            ->middleware('permission:filehosting.view');
        
        // Download
        Route::get('/download/{file}', [FileHostingController::class, 'download'])
            ->name('download')
            ->middleware('permission:filehosting.download');

        // Delete file
        Route::delete('/{file}', [FileHostingController::class, 'delete'])
            ->name('delete')
            ->middleware('permission:filehosting.delete');
    });
