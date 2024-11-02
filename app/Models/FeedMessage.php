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
    const string TABLE_NAME = 'feed_messages';
    protected $table = self::TABLE_NAME;

    // Text limit on body
    const int MAX_CONTENT_LENGTH = 280;
    const int MAX_CONTENT_LENGTH_PRO = 500;
    const int MAX_CONTENT_LENGTH_PLUS = 1000;

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'is_nsfw' => 'bool',
            'is_pinned' => 'bool',
            'is_reply' => 'bool',
            'is_reshare' => 'bool',
            'is_spoiler' => 'bool',
        ];
    }

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (FeedMessage $feedMessage) {
            // Pre-populate id, otherwise MentionParser can't
            // associate the FeedMessage model to a mentioned
            // model. We could of course move the parsing logic
            // to the `saved` static method, but then the parsed
            // content won't be available by the time we use the
            // created model. Also, one less query this way.
            if (empty($feedMessage->id)) {
                $id = FeedMessage::max('id');
                $feedMessage->id = (int)$id + 1;
            }

            // Strip HTML tags
            $feedMessage->content = strip_tags(trim(Markdown::parse(nl2br($feedMessage->content))));

            // Parse user mentions
            $parser = new MentionParser($feedMessage);
            $feedMessage->content_markdown = $parser->parse($feedMessage->content);

            // Parse user mentions
            $feedMessage->content_html = trim(Markdown::parse(nl2br($feedMessage->content_markdown)));
        });
    }

    /**
     * Returns the allowed max content length of a feed message.
     *
     * @return int
     */
    static function maxContentLength(): int
    {
        return auth()->user()?->is_subscribed
            ? FeedMessage::MAX_CONTENT_LENGTH_PLUS
            : (auth()->user()?->is_pro ? FeedMessage::MAX_CONTENT_LENGTH_PRO : FeedMessage::MAX_CONTENT_LENGTH);
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
