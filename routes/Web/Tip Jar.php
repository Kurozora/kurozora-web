<?php

use App\Http\Controllers\Web\TipJarController;

Route::get('/tip-jar', [TipJarController::class, 'index'])
    ->name('tip-jar');
