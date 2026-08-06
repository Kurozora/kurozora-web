<?php

namespace App\Models;

use App\Enums\ParentalGuideReaction;
use App\Traits\Model\MorphTvRated;
use Cog\Contracts\Love\Reactable\Models\Reactable as ReactableContract;
use Cog\Laravel\Love\Reactable\Models\Traits\Reactable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaRating extends KModel implements ReactableContract
{
    use MorphTvRated,
        Reactable,
        SoftDeletes;

    // Rating boundaries
    const float MIN_RATING_VALUE = 0.00;
    const float MAX_RATING_VALUE = 5.00;

    // Table name
    const string TABLE_NAME = 'media_ratings';
    protected $table = self::TABLE_NAME;

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
     * Returns the model related to the media rating.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
        return parent::resolveRouteBindingQuery($query, $value, $field)
            ->withoutGlobalScopes();
    }
}
