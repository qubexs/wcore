<?php
// modules/filehosting/routes/web.php

use Illuminate\Support\Facades\Route;
use App\Modules\FileHosting\Controllers\FileHostingController;




Route::prefix('filehosting')->name('filehosting.')->group(function () {
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
    Route::get('/all', [FileHostingController::class, 'all'])
        ->name('all')
        ->middleware('permission:filehosting.view');

    // Optional: delete file
    Route::delete('/{file}', [FileHostingController::class, 'delete'])
        ->name('delete')
        ->middleware('permission:filehosting.delete');

    // Temporary bypassing middleware    
    //Route::get('/filehosting/all', [FileHostingController::class, 'all'])
    // ->withoutMiddleware(\Illuminate\Auth\Middleware\Authorize::class);
    

});
