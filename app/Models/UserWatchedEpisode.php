<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWatchedEpisode extends KModel
{
    // Table name
    const string TABLE_NAME = 'user_watched_episodes';
    protected $table = self::TABLE_NAME;

    /**
     * The user that the UserWatchedEpisode object belongs to.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The episode that the UserWatchedEpisode object belongs to.
     *
     * @return BelongsTo
     */
    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }
}
