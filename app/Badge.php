<?php

namespace App;

class Badge extends KModel
{
    // Table name
    const TABLE_NAME = 'badges';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the associated users with this badge
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function users() {
        return $this->belongsToMany(User::class, UserBadge::TABLE_NAME, 'badge_id', 'user_id');
    }
}
