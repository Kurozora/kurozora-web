<?php

namespace App\Models;

use App\Events\UserLibraryCreatedEvent;
use App\Events\UserLibraryUpdatedEvent;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserLibrary extends Pivot
{
    // Table name
    const TABLE_NAME = 'user_libraries';
    protected $table = self::TABLE_NAME;

    /**
     * @inheritdoc
     */
    static function boot()
    {
        parent::boot();

        static::created(function (UserLibrary $userLibrary) {
            event(new UserLibraryCreatedEvent($userLibrary));
        });

        static::updated(function (UserLibrary $userLibrary) {
            event(new UserLibraryUpdatedEvent($userLibrary));
        });
    }
}
