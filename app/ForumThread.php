<?php

namespace App;

use App\Traits\KuroSearchTrait;
use Carbon\Carbon;
use Cog\Contracts\Love\Reactable\Models\Reactable as ReactableContract;
use Cog\Laravel\Love\Reactable\Models\Traits\Reactable;

class ForumThread extends KModel implements ReactableContract
{
    use KuroSearchTrait,
        Reactable;

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
    const TABLE_NAME = 'forum_threads';
    protected $table = self::TABLE_NAME;

    // A user can post a thread once every {?} seconds
    const COOLDOWN_POST_THREAD = 60;

    // Amount of results to display per thread page
    const REPLIES_PER_PAGE = 25;

    /**
     * Get the section the thread was posted in.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function forum_section() {
        return $this->belongsTo(ForumSection::class, 'section_id', 'id');
    }

    /**
     * Get the user associated with the thread
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
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
