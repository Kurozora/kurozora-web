<?php

namespace App\Models;

use App\Casts\AsArrayObject;

class AppStoreNotification extends KModel
{
    // Table name
    const string TABLE_NAME = 'app_store_notifications';
    protected $table = self::TABLE_NAME;

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'payload' => AsArrayObject::class,
        ];
    }
}
