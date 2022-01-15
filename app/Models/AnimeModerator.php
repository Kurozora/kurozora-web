<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AnimeModerator extends Pivot
{
    // Table name
    const TABLE_NAME = 'anime_moderators';
    protected $table = self::TABLE_NAME;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
