<?php

namespace Laravel\Nova\Tests\Fixtures;

use Laravel\Nova\Http\Requests\NovaRequest;

class UserWithRedirectResource extends UserResource
{
    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    public static function uriKey()
    {
        return 'users-with-redirects';
    }

    public static function redirectAfterCreate(NovaRequest $request, $newResource)
    {
        return 'https://yahoo.com';
    }

    public static function redirectAfterUpdate(NovaRequest $request, $newResource)
    {
        return 'https://google.com';
    }

    public static function redirectAfterDelete(NovaRequest $request)
    {
        return 'https://laravel.com';
    }
}
