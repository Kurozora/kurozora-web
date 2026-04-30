<?php

namespace App\Models;

use App\Notifications\NewFeedMessageReply;
use App\Notifications\NewFeedMessageReShare;
use App\Parsers\MentionParser;
use Cog\Contracts\Love\Reactable\Models\Reactable as ReactableContract;
use Cog\Laravel\Love\Reactable\Models\Traits\Reactable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
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
     * Creates a feed message for the given user.
     *
     * @param User  $user
     * @param array $attributes
     *
     * @return FeedMessage
     * @throws AuthorizationException
     */
    static function createFor(User $user, array $attributes): FeedMessage
    {
        $parentID = $attributes['parent_id'] ?? null;
        $content = $attributes['content'] ?? null;
        $isReply = (bool) ($attributes['is_reply'] ?? false);
        $isReShare = (bool) ($attributes['is_reshare'] ?? false);
        $isSimpleReShare = $isReShare && trim((string) $content) === '';
        $parentMessage = null;

        if ($parentID !== null && ($isReply || $isReShare)) {
            $parentMessage = FeedMessage::with('user')->find($parentID);

            if ($parentMessage !== null && !$user->canInteractWith($parentMessage->user)) {
                throw new AuthorizationException(__('You are not allowed to engage with this user.'));
            }
        }

        if ($isSimpleReShare && $parentMessage !== null) {
            $alreadyReShared = $parentMessage->simpleReShares()
                ->where('user_id', '=', $user->id)
                ->exists();

            if ($alreadyReShared) {
                throw new AuthorizationException(__('You are not allowed to re-share a message more than once.'));
            }
        }

        $feedMessage = $user->feed_messages()->create([
            'parent_feed_message_id' => $parentID,
            'content' => $isSimpleReShare ? '' : $content,
            'is_nsfw' => (bool) ($attributes['is_nsfw'] ?? false),
            'is_pinned' => false,
            'is_reply' => $isReply,
            'is_reshare' => $isReShare,
            'is_spoiler' => (bool) ($attributes['is_spoiler'] ?? false),
        ]);

        if ($parentMessage !== null && $parentMessage->user->id !== $user->id) {
            if ($isReply) {
                $parentMessage->user->notify(new NewFeedMessageReply($feedMessage));
            } else if ($isReShare) {
                $parentMessage->user->notify(new NewFeedMessageReShare($feedMessage));
            }
        }

        return $feedMessage;
    }

    /**
     * Returns the eager-load tree used by the message lockup template.
     *
     * @param ?User $authUser
     *
     * @return array
     */
    static function lockupEagerLoads(?User $authUser): array
    {
        $loveReactantLoader = function (BelongsTo $query) {
            $query->with([
                'reactionCounters',
                'reactions' => function (HasMany $hasMany) {
                    $hasMany->with(['reacter', 'type']);
                },
            ]);
        };

        $userLoader = function (BelongsTo $belongsTo) {
            $belongsTo->with(['media']);
        };

        return [
            'user' => $userLoader,
            'loveReactant' => $loveReactantLoader,
            'linkPreview',
            'parentMessage' => function ($query) use ($userLoader, $loveReactantLoader, $authUser) {
                $query->with([
                    'user' => $userLoader,
                    'loveReactant' => $loveReactantLoader,
                    'linkPreview',
                ])
                    ->withCount(['replies', 'reShares']);

                if ($authUser !== null) {
                    $query->withExists(['simpleReShares as isReShared' => function ($query) use ($authUser) {
                        $query->where('user_id', '=', $authUser->id);
                    }]);
                }
            },
        ];
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
     * Returns the simple re-shares of this feed message.
     *
     * @return HasMany
     */
    function simpleReShares(): HasMany
    {
        return $this->hasMany(FeedMessage::class, 'parent_feed_message_id')
            ->where('is_reshare', '=', true)
            ->where(function ($query) {
                $query->whereNull('content')
                    ->orWhere('content', '=', '');
            });
    }

    /**
     * Returns the quote re-shares of this feed message.
     *
     * @return HasMany
     */
    function quoteReShares(): HasMany
    {
        return $this->hasMany(FeedMessage::class, 'parent_feed_message_id')
            ->where('is_reshare', '=', true)
            ->whereNotNull('content')
            ->where('content', '!=', '');
    }

    /**
     * Returns the feed message hashtags the current feed message has.
     *
     * @return HasMany
     */
    public function feedMessageHashtags(): HasMany
    {
        return $this->hasMany(FeedMessageHashtag::class, 'feed_message_id');
    }

    /**
     * Returns the link preview the current feed message has.
     *
     * @return HasOne
     */
    public function linkPreview(): HasOne
    {
        return $this->hasOne(LinkPreview::class, 'url', 'last_link');
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

    /**
     * Filters out feed messages whose author is mutually invisible to the given user.
     *
     * @param Builder $query
     * @param User|null $user
     * @return Builder
     */
    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if ($user === null) {
            return $query;
        }

        return $query->whereHas('user', function (Builder $query) use ($user): Builder {
            return $query->visibleTo($user);
        });
    }
}
