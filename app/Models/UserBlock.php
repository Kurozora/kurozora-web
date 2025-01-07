<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBlock extends KModel
{
    // Table name
    const string TABLE_NAME = 'user_blocks';
    protected $table = self::TABLE_NAME;

    /**
     * Returns who has blocked a user.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Returns who a user has blocked.
     *
     * @return BelongsTo
     */
    public function blocked(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_user_id');
    }
}
