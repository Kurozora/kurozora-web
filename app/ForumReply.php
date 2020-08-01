<?php

namespace App;

use Carbon\Carbon;
use Cog\Contracts\Love\Reactable\Models\Reactable as ReactableContract;
use Cog\Laravel\Love\Reactable\Models\Traits\Reactable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ForumReply extends KModel implements ReactableContract
{
    use Reactable;

    // Table name
    const TABLE_NAME = 'forum_replies';
    protected $table = self::TABLE_NAME;

    // A user can post a forum reply once every {?} seconds
    const COOLDOWN_POST_REPLY = 10;

    /**
     * Returns the thread the reply was posted in.
     *
     * @return BelongsTo
     */
    function forum_thread(): BelongsTo
    {
        return $this->belongsTo(ForumThread::class, 'thread_id', 'id');
    }

    /**
     * Get the user associated with the reply
     *
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * Checks whether or not the user passes the cooldown check
     *
     * Returns true when the user has posted within the cooldown
     * Returns false when the user is allowed to post
     *
     * @param $userID
     * @return bool
     */
    public static function testPostCooldown($userID): bool
    {
        $secondsCooldown = self::COOLDOWN_POST_REPLY;

        $checkQuery = ForumReply::where('user_id', '=', $userID)
            ->where('created_at', '>', Carbon::now()->subSeconds($secondsCooldown));

        return $checkQuery->exists();
    }
}
