<?php

use App\Http\Controllers\Admin\BackupLogController;
use Illuminate\Support\Facades\Route;
use Webkul\Core\Http\Middleware\NoCacheMiddleware;

Route::group(['middleware' => ['web', 'admin', NoCacheMiddleware::class], 'prefix' => config('app.admin_url')], function () {
    Route::get('settings/backups', [BackupLogController::class, 'index'])->name('admin.settings.backups.index');
    Route::post('settings/backups/run', [BackupLogController::class, 'run'])->name('admin.settings.backups.run');
});
