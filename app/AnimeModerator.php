<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AnimeModerator extends Pivot
{
    // Table name
    const TABLE_NAME = 'anime_moderators';
    protected $table = self::TABLE_NAME;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;
}
