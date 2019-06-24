<?php

namespace App;

use Carbon\Carbon;
use Cog\Contracts\Love\Likeable\Models\Likeable as LikeableContract;
use Cog\Laravel\Love\Likeable\Models\Traits\Likeable;

class ForumReply extends KModel implements LikeableContract
{
    use Likeable;

    // Table name
    const TABLE_NAME = 'forum_replies';
    protected $table = self::TABLE_NAME;

    // A user can post a forum reply once every {?} seconds
    const COOLDOWN_POST_REPLY = 10;

    /**
     * Returns the thread the reply was posted in.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function forum_thread() {
        return $this->belongsTo(ForumThread::class, 'thread_id', 'id');
    }

    /**
     * Get the user associated with the reply
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user() {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * Checks whether or not the user passes the cooldown check
     *
     * Returns true when the user has posted within the cooldown
     * Returns false when the user is allowed to post
     *
     * @param $userID
     * @return mixed
     */
    public static function testPostCooldown($userID) {
        $secondsCooldown = self::COOLDOWN_POST_REPLY;

        $checkQuery = ForumReply::where('user_id', '=', $userID)
            ->where('created_at', '>', Carbon::now()->subSeconds($secondsCooldown));

        return $checkQuery->exists();
    }
}
