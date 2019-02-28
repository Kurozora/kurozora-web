<?php

namespace App;

use App\Traits\KuroSearchTrait;
use Carbon\Carbon;
use Cog\Contracts\Love\Likeable\Models\Likeable as LikeableContract;
use Cog\Laravel\Love\Likeable\Models\Traits\Likeable;

/**
 * @property int locked
 */
class ForumThread extends KModel implements LikeableContract
{
    use KuroSearchTrait, Likeable;

    /**
     * Searchable rules.
     *
     * @var array
     */
    protected $searchable = [
        'columns' => [
            'title' => 10
        ]
    ];

    // Maximum amount of returned search results
    const MAX_SEARCH_RESULTS = 10;

    // Table name
    const TABLE_NAME = 'forum_thread';
    protected $table = self::TABLE_NAME;

    // A user can post a thread once every {?} seconds
    const COOLDOWN_POST_THREAD = 60;

    // Amount of results to display per thread page
    const REPLIES_PER_PAGE = 10;

    /**
     * Get the user associated with the thread
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user() {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * Retrieve the replies for the thread
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies() {
        return $this->hasMany(ForumReply::class, 'thread_id', 'id');
    }

    /**
     * Formats the thread for a details response
     *
     * @return array
     */
    public function formatForDetailsResponse() {
        return [
            'id'            => $this->id,
            'content'       => $this->content,
            'reply_pages'   => $this->getPageCount()
        ];
    }

    /**
     * Get the amount of pages the thread has
     *
     * @return integer
     */
    public function getPageCount() {
        return ceil($this->replies->count() / self::REPLIES_PER_PAGE);
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
