<?php

use Illuminate\Support\Facades\Route;
use LagMedical\Eyewear\Http\Controllers\FramesController;

Route::middleware(['web', 'locale', 'theme', 'currency'])->group(function () {
    Route::get('campaign/frames/', [FramesController::class, 'index'])->name('campaign.frames.index');
    Route::post('campaign/frames/subscribe', [FramesController::class, 'subscribe'])->name('campaign.frames.subscribe');
});
