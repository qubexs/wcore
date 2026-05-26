<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Infographic\Controllers\InfographicController;

Route::prefix('infographic')
    ->name('infographic.')
    ->middleware(['web', 'auth'])
    ->group(function () {

        // Main index
        Route::get('/', [InfographicController::class, 'index'])
            ->name('index')
            ->middleware('permission:infographic.view');

        // Create form
        Route::get('/create', [InfographicController::class, 'create'])
            ->name('create')
            ->middleware('permission:infographic.create');

        // Store
        Route::post('/', [InfographicController::class, 'store'])
            ->name('store')
            ->middleware('permission:infographic.create');

        // View single infographic
        Route::get('/view/{infographic}', [InfographicController::class, 'view'])
            ->name('view')
            ->middleware('permission:infographic.view');

        // Edit
        Route::get('/edit/{infographic}', [InfographicController::class, 'edit'])
            ->name('edit')
            ->middleware('permission:infographic.edit');

        // Update
        Route::put('/{infographic}', [InfographicController::class, 'update'])
            ->name('update')
            ->middleware('permission:infographic.edit');

        // Delete
        Route::delete('/{infographic}', [InfographicController::class, 'destroy'])
            ->name('delete')
            ->middleware('permission:infographic.delete');
    });