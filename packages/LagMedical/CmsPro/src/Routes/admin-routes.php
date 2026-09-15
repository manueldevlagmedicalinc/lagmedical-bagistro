<?php

use Illuminate\Support\Facades\Route;
use LagMedical\CmsPro\Http\Controllers\Admin\EditorController;
use LagMedical\CmsPro\Http\Controllers\Admin\MediaController;
use Webkul\Core\Http\Middleware\NoCacheMiddleware;

Route::group([
    'middleware' => ['web', 'admin', NoCacheMiddleware::class],
    'prefix' => config('app.admin_url').'/cms-pro',
], function () {
    Route::get('pages/{pageId}/edit', [EditorController::class, 'edit'])->name('admin.cms.pro.edit');
    Route::put('pages/{pageId}', [EditorController::class, 'update'])->name('admin.cms.pro.update');
    Route::put('pages/{pageId}/publish', [EditorController::class, 'publish'])->name('admin.cms.pro.publish');
    Route::post('pages/{pageId}/preview', [EditorController::class, 'preview'])->name('admin.cms.pro.preview');
    Route::post('pages/{pageId}/revisions/{revision}/restore', [EditorController::class, 'restore'])->name('admin.cms.pro.revisions.restore');

    Route::get('media', [MediaController::class, 'index'])->name('admin.cms.pro.media.index');
    Route::post('media', [MediaController::class, 'store'])->name('admin.cms.pro.media.store');
    Route::post('media/import', [MediaController::class, 'import'])->name('admin.cms.pro.media.import');
    Route::delete('media/{media}', [MediaController::class, 'delete'])->name('admin.cms.pro.media.delete');
});
