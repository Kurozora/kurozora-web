<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use phpDocumentor\Reflection\Types\Boolean;

class ForumPost extends Model
{
    // Table name
    const TABLE_NAME = 'forum_post';
    protected $table = self::TABLE_NAME;

    // Fillable columns
    protected $fillable = ['section_id', 'user_id', 'ip', 'title', 'content'];

    // Minimum lengths
    const MIN_TITLE_LENGTH = 5;
    const MIN_CONTENT_LENGTH = 5;

    // Cooldowns in seconds
    const COOLDOWN_POST_THREAD = 500;
    const COOLDOWN_POST_REPLY = 60;

    /**
     * Formats the post for a response
     *
     * @return array
     */
    public function formatForResponse() {
        return [
            'id'            => $this->id,
            'user_id'       => $this->user_id,
            'parent_post'   => $this->parent_post,
            'title'         => $this->title,
            'content'       => $this->content,
        ];
    }

    /**
     * Checks whether or not the user passes the cooldown check
     *
     * Returns true when the user has posted within the cooldown
     * Returns false when the user is allowed to post
     *
     * @param Bool $thread
     * @param $userID
     * @return mixed
     */
    public static function testCooldown(Bool $thread, $userID) {
        $secondsCooldown = ($thread) ? self::COOLDOWN_POST_THREAD : self::COOLDOWN_POST_REPLY;

        $checkQuery = ForumPost::where('user_id', '=', $userID)
            ->where('created_at', '>', Carbon::now()->subSeconds($secondsCooldown));

        if($thread)
            $checkQuery->where('parent_post', null);
        else
            $checkQuery->where('parent_post', '!=', null);

        return $checkQuery->exists();
    }
}
