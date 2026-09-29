<?php

use App\Http\Controllers\Web\KurozoraPlusController;

Route::get('/kurozora-plus', [KurozoraPlusController::class, 'index'])
    ->name('kurozora-plus');
