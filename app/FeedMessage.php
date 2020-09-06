<?php

namespace App;

use Cog\Contracts\Love\Reactable\Models\Reactable as ReactableContract;
use Cog\Laravel\Love\Reactable\Models\Traits\Reactable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FeedMessage extends KModel implements ReactableContract
{
    use Reactable;

    // Table name
    const TABLE_NAME = 'feed_messages';
    protected $table = self::TABLE_NAME;

    // Text limit on body
    const MAX_BODY_LENGTH = 240;

    /**
     * Returns whether the feed message is a reply.
     *
     * @return bool
     */
    function isReply(): bool
    {
        return $this->parent_feed_message_id !== null && $this->is_reply;
    }

    /**
     * Returns the user that posted the feed message.
     *
     * @return BelongsTo
     */
    function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Returns the parent message to which the current message belongs.
     *
     * @return HasOne
     */
    function parentMessage()
    {
        return $this->hasOne(FeedMessage::class, 'id', 'parent_feed_message_id');
    }

    /**
     * Returns all the feed messages that reply to this one.
     *
     * @return HasMany
     */
    function replies()
    {
        return $this->hasMany(FeedMessage::class, 'parent_feed_message_id')
            ->where('is_reply', '=', true);
    }

    /**
     * Returns the parent feed message that this one re-shared.
     *
     * @return HasMany
     */
    function reShares()
    {
        return $this->hasMany(FeedMessage::class, 'parent_feed_message_id')
            ->where('is_reshare', '=', true);
    }

    /**
     * Filters out the feed messages that are replies.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeNoReplies(Builder $query): Builder
    {
        return $query->where('is_reply', '=', false);
    }

    /**
     * Filters out the feed messages that are not replies.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeRepliesOnly(Builder $query): Builder
    {
        return $query->where('is_reply', '=', true);
    }
}
