<?php

use Illuminate\Support\Facades\Route;
use LagMedical\Eblast\Http\Controllers\Admin\BrevoConnectionController;

Route::middleware(['web', 'admin'])
    ->prefix(config('app.admin_url'))
    ->group(function () {
        Route::post('eblast/connection/test', [BrevoConnectionController::class, 'test'])
            ->name('admin.eblast.connection.test');
    });
