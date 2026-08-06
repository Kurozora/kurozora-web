<?php

use App\Livewire\TipJar\Index as TipJarIndex;

Route::get('/tip-jar', TipJarIndex::class)
    ->name('tip-jar');
