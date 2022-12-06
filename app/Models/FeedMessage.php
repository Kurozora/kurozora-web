<?php

namespace App\Models;

use App\Parsers\MentionParser;
use Cog\Contracts\Love\Reactable\Models\Reactable as ReactableContract;
use Cog\Laravel\Love\Reactable\Models\Traits\Reactable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Markdown;
use Xetaio\Mentions\Models\Traits\HasMentionsTrait;

class FeedMessage extends KModel implements ReactableContract
{
    use HasFactory,
        HasMentionsTrait,
        Reactable;

    // Table name
    const TABLE_NAME = 'feed_messages';
    protected $table = self::TABLE_NAME;

    // Text limit on body
    const MAX_CONTENT_LENGTH = 240;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_nsfw' => 'bool',
        'is_spoiler' => 'bool',
        'is_reshare' => 'bool',
        'is_reply' => 'bool',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'user',
        'loveReactant.reactions.reacter.reacterable',
        'loveReactant.reactions.type',
        'loveReactant.reactionCounters',
        'loveReactant.reactionTotal',
    ];

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (FeedMessage $feedMessage) {
            // Strip HTML tags
            $feedMessage->content = trim(strip_tags(Markdown::parse(nl2br($feedMessage->content))));

            // Parse user mentions
            $parser = new MentionParser($feedMessage);
            $feedMessage->content_markdown = $parser->parse($feedMessage->content);

            // Parse user mentions
            $feedMessage->content_html = Markdown::parse(nl2br($feedMessage->content_markdown));
        });
    }

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
    function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Returns the parent message to which the current message belongs.
     *
     * @return HasOne
     */
    function parentMessage(): HasOne
    {
        return $this->hasOne(FeedMessage::class, 'id', 'parent_feed_message_id');
    }

    /**
     * Returns all the feed messages that reply to this one.
     *
     * @return HasMany
     */
    function replies(): HasMany
    {
        return $this->hasMany(FeedMessage::class, 'parent_feed_message_id')
            ->where('is_reply', '=', true);
    }

    /**
     * Returns the parent feed message that this one re-shared.
     *
     * @return HasMany
     */
    function reShares(): HasMany
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
