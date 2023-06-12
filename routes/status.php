<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect(config('app.status_url'));
})
    ->name('status');
