<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ForumThread extends Model
{
    // Table name
    const TABLE_NAME = 'forum_thread';
    protected $table = self::TABLE_NAME;

    // Fillable columns
    protected $fillable = ['section_id', 'user_id', 'ip', 'title', 'content'];

    // Minimum lengths
    const MIN_TITLE_LENGTH = 5;
    const MIN_CONTENT_LENGTH = 5;

    // A user can post a thread once every {?} seconds
    const COOLDOWN_POST_THREAD = 60;

    /**
     * Returns the amount of replies in this thread
     *
     * @return int
     */
    public function getReplyCount() {
        return (int) ForumReply::where('thread_id', $this->id)->count();
    }

    /**
     * Formats the post for a response
     *
     * @return array
     */
    public function formatForResponse() {
        return [
            'id'            => $this->id,
            'user_id'       => $this->user_id,
            'title'         => $this->title,
            'content'       => $this->content,
            'reply_count'   => $this->getReplyCount()
        ];
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
        $secondsCooldown = self::COOLDOWN_POST_THREAD;

        $checkQuery = ForumThread::where('user_id', '=', $userID)
            ->where('created_at', '>', Carbon::now()->subSeconds($secondsCooldown));

        return $checkQuery->exists();
    }
}
