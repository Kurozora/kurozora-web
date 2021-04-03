<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends KModel
{
    // Table name
    const TABLE_NAME = 'badges';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the associated users with this badge
     *
     * @return BelongsToMany
     */
    function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, UserBadge::TABLE_NAME, 'badge_id', 'user_id');
    }
}
