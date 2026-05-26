<?php

use Illuminate\Support\Facades\Route;
use App\Modules\WebsiteMonitor\Controllers\WebsiteMonitorController;

Route::prefix('websitemonitor')
    ->name('websitemonitor.')
    ->middleware(['web', 'auth'])
    ->group(function () {

        Route::get('/', [WebsiteMonitorController::class, 'index'])
            ->name('index')
            ->middleware('permission:websitemonitor.view');

        Route::get('/create', [WebsiteMonitorController::class, 'create'])
            ->name('create')
            ->middleware('permission:websitemonitor.create');

        Route::post('/', [WebsiteMonitorController::class, 'store'])
            ->name('store')
            ->middleware('permission:websitemonitor.create');

        Route::get('/{id}/edit', [WebsiteMonitorController::class, 'edit'])
            ->name('edit')
            ->middleware('permission:websitemonitor.edit');

        Route::put('/{id}', [WebsiteMonitorController::class, 'update'])
            ->name('update')
            ->middleware('permission:websitemonitor.edit');

        Route::delete('/{id}', [WebsiteMonitorController::class, 'destroy'])
            ->name('destroy')
            ->middleware('permission:websitemonitor.delete');

        Route::post('/{id}/check', [WebsiteMonitorController::class, 'checkNow'])
            ->name('check')
            ->middleware('permission:websitemonitor.check');

        Route::get('/{id}/logs', [WebsiteMonitorController::class, 'logs'])
            ->name('logs')
            ->middleware('permission:websitemonitor.view');

        // Settings
        Route::get('/settings', [WebsiteMonitorController::class, 'settings'])
            ->name('settings')
            ->middleware('permission:websitemonitor.manage_settings');

        Route::post('/settings', [WebsiteMonitorController::class, 'updateSettings'])
            ->name('settings.update')
            ->middleware('permission:websitemonitor.manage_settings');

        // Widget API
        Route::get('/widget/data', [WebsiteMonitorController::class, 'widgetData'])
            ->name('widget.data');
    });
