<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;

class FeedMessage extends KModel
{
    // Table name
    const TABLE_NAME = 'feed_messages';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the user that posted the feed message.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Returns all the feed messages that reply to this one.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    function replies()
    {
        return $this->hasMany(FeedMessage::class, 'parent_feed_message_id');
    }

    /**
     * Filters out the feed messages that are replies.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeNoReplies($query)
    {
        return $query->whereNull('parent_feed_message_id');
    }

    /**
     * Filters out the feed messages that are not replies.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeRepliesOnly($query)
    {
        return $query->whereNotNull('parent_feed_message_id');
    }
}
