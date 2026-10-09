<?php

namespace App\Models;

use App\Enums\ParentalGuideReaction;
use App\Enums\ReviewRecommendation;
use App\Traits\Model\MorphTvRated;
use Cog\Contracts\Love\Reactable\Models\Reactable as ReactableContract;
use Cog\Laravel\Love\Reactable\Models\Traits\Reactable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use InvalidArgumentException;

class MediaRating extends KModel implements ReactableContract
{
    use MorphTvRated,
        Reactable;

    // Rating boundaries
    const float MIN_RATING_VALUE = 0.00;
    const float MAX_RATING_VALUE = 5.00;

    // Table name
    const string TABLE_NAME = 'media_ratings';
    protected $table = self::TABLE_NAME;

    /**
     * The attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'description_written_at' => 'datetime',
            'is_spoiler' => 'boolean',
            'recommendation' => ReviewRecommendation::class,
            'is_low_effort' => 'boolean',
            'is_elevated' => 'boolean',
            'elevated_at' => 'datetime',
        ];
    }

    /**
     * Returns the model related to the media rating.
     *
     * @return MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo()
            ->withoutGlobalScopes();
    }

    /**
     * Shapes the query for a public review list.
     *
     * Every column ordered on is non-nullable, so the cursor stays stable across pages.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return void
     */
    public function scopeForReading(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->withCount('revisions')
            ->orderByDesc('is_elevated')
            ->orderBy('is_low_effort')
            ->orderBy('created_at');
    }

    /**
     * Adds the related episode's public ID as a select column for episode ratings.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return void
     */
    public function scopeAddEpisodePublicIdSelect(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->addSelect([
            'episode_public_id' => Episode::withoutGlobalScopes()
                ->select('public_id')
                ->whereColumn(Episode::TABLE_NAME . '.id', self::TABLE_NAME . '.model_id')
                ->where(self::TABLE_NAME . '.model_type', Episode::class),
        ]);
    }

    /**
     * Orders the ratings by the title of the rated model.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $direction
     *
     * @return void
     */
    public function scopeOrderByModelTitle(\Illuminate\Database\Eloquent\Builder $query, string $direction): void
    {
        if (!in_array($direction, ['asc', 'desc'], true)) {
            throw new InvalidArgumentException('Order direction must be "asc" or "desc".');
        }

        $translatedTitle = function (string $modelClass, string $column) {
            $translation = Relation::noConstraints(fn () => (new $modelClass)->translation());

            return $translation->getQuery()
                ->select($column)
                ->whereColumn($translation->getQualifiedForeignKeyName(), self::TABLE_NAME . '.model_id')
                ->where(self::TABLE_NAME . '.model_type', '=', $modelClass)
                ->limit(1)
                ->toBase();
        };
        $ownTitle = function (string $modelClass, string $column) {
            return $modelClass::withoutGlobalScopes()
                ->selectRaw($column)
                ->whereColumn($modelClass::TABLE_NAME . '.id', self::TABLE_NAME . '.model_id')
                ->where(self::TABLE_NAME . '.model_type', '=', $modelClass)
                ->toBase();
        };

        $titles = collect([
            $translatedTitle(Anime::class, 'title'),
            $translatedTitle(Manga::class, 'title'),
            $translatedTitle(Game::class, 'title'),
            $translatedTitle(Episode::class, 'title'),
            $translatedTitle(Song::class, 'title'),
            $translatedTitle(Character::class, 'name'),
            $ownTitle(Person::class, "concat_ws(', ', last_name, first_name)"),
            $ownTitle(Studio::class, 'name'),
        ]);

        $query->orderByRaw(
            'coalesce(' . $titles->map(fn ($title) => '(' . $title->toSql() . ')')->implode(', ') . ') ' . $direction,
            $titles->flatMap(fn ($title) => $title->getBindings())->all()
        );
    }

    /**
     * Returns the model related to the media rating.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Returns the staff member who elevated the review.
     *
     * @return BelongsTo
     */
    public function elevatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'elevated_by_user_id');
    }

    /**
     * Returns the per-category scores of the media rating.
     *
     * @return HasMany
     */
    public function categoryScores(): HasMany
    {
        return $this->hasMany(RatingCategoryScore::class, 'rating_id');
    }

    /**
     * Returns the superseded versions of the review, newest first.
     *
     * @return HasMany
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(MediaRatingRevision::class, 'rating_id')
            ->orderByDesc('written_at')
            ->orderByDesc('id');
    }

    /**
     * The number of users who reacted with `Helpful`.
     *
     * @return int
     */
    public function getHelpfulCountAttribute(): int
    {
        return $this->reactionCounterFor(ParentalGuideReaction::Helpful());
    }

    /**
     * The number of users who reacted with `Unhelpful`.
     *
     * @return int
     */
    public function getUnhelpfulCountAttribute(): int
    {
        return $this->reactionCounterFor(ParentalGuideReaction::Unhelpful());
    }

    /**
     * The eager-loads required to render a rating with reaction state.
     *
     * @param User|null $authUser
     *
     * @return array
     */
    public static function lockupEagerLoads(?User $authUser): array
    {
        $with = ['reactionCounters'];

        if ($authUser !== null) {
            $authUser->loadMissing('loveReacter');
            $reacter = $authUser->getLoveReacter();

            if ($reacter->isNotNull()) {
                $reacterId = $reacter->getId();

                $with['reactions'] = function (HasMany $hasMany) use ($reacterId) {
                    $hasMany->with(['type', 'reacter'])->where('reacter_id', '=', $reacterId);
                };
            }
        }

        return [
            'loveReactant' => function (BelongsTo $query) use ($with) {
                $query->with($with);
            },
        ];
    }

    /**
     * The orderable properties.
     *
     * @return array[]
     */
    public static function webSearchOrders(): array
    {
        return [
            'created_at' => [
                'title' => __('Date'),
                'options' => [
                    'Default' => null,
                    'Newest' => 'desc',
                    'Oldest' => 'asc',
                ],
                'selected' => null,
            ],
            'title' => [
                'title' => __('Title'),
                'options' => [
                    'Default' => null,
                    'A-Z' => 'asc',
                    'Z-A' => 'desc',
                ],
                'selected' => null,
            ],
            'rating' => [
                'title' => __('Rating'),
                'options' => [
                    'Default' => null,
                    'Highest' => 'desc',
                    'Lowest' => 'asc',
                ],
                'selected' => null,
            ],
        ];
    }

    /**
     * The filterable properties.
     *
     * @return array[]
     */
    public static function webSearchFilters(): array
    {
        return [
            'has_review' => [
                'title' => __('Content'),
                'type' => 'bool',
                'options' => [
                    __('With Review'),
                    __('Ratings Only'),
                ],
                'selected' => null,
            ],
            'rating' => [
                'title' => __('Rating'),
                'type' => 'rating',
                'selected' => null,
            ],
        ];
    }

    /**
     * Returns the count for the given reaction.
     *
     * @param ParentalGuideReaction $reaction
     *
     * @return int
     */
    private function reactionCounterFor(ParentalGuideReaction $reaction): int
    {
        $this->loadMissing('loveReactant.reactionCounters');

        $reactant = $this->getLoveReactant();

        if (!$reactant->isNotNull()) {
            return 0;
        }

        return $this->viaLoveReactant()->getReactionCounterOfType($reaction->description)->getCount();
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  Model|\Illuminate\Database\Eloquent\Relations\Relation  $query
     * @param  mixed  $value
     * @param  string|null  $field
     * @return Builder
     */
    public function resolveRouteBindingQuery($query, $value, $field = null): Builder
    {
        return $this->withoutGlobalScopesExceptSoftDeletes(parent::resolveRouteBindingQuery($query, $value, $field));
    }
}
