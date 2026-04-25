<?php

namespace App\Facades;

use App\Services\AppStoreService;
use Illuminate\Support\Facades\Facade;

class AppStore extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AppStoreService::class;
    }
}

