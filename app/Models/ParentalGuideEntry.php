<?php

namespace App\Models;

use App\Enums\ParentalGuideCategory;
use App\Enums\ParentalGuideDepiction;
use App\Enums\ParentalGuideFrequency;
use App\Enums\ParentalGuideRating;
use App\Enums\ParentalGuideReaction;
use Cog\Contracts\Love\Reactable\Models\Reactable as ReactableContract;
use Cog\Laravel\Love\Reactable\Models\Traits\Reactable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ParentalGuideEntry extends KModel implements ReactableContract
{
    use Reactable;

    // Table name
    const string TABLE_NAME = 'parental_guide_entries';
    protected $table = self::TABLE_NAME;

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'category' => ParentalGuideCategory::class,
            'rating' => ParentalGuideRating::class,
            'frequency' => ParentalGuideFrequency::class,
            'depiction' => ParentalGuideDepiction::class,
            'is_spoiler' => 'boolean',
            'is_hidden' => 'boolean',
        ];
    }

    /**
     * Returns the user to which the entry belongs.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Returns the media model the entry references.
     *
     * @return MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Returns the reports filed against this entry.
     *
     * @return MorphMany
     */
    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
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
     * Eloquent builder scope that limits the query to the visible entries.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_hidden', false);
    }

    /**
     * Eloquent builder scope that limits the query to entries that have a non-empty reason.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeWithReason(Builder $query): Builder
    {
        return $query->whereNotNull('reason')
            ->where('reason', '!=', '');
    }

    /**
     * The eager-loads required to render an entry with reaction state.
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
                    $hasMany->with(['type'])->where('reacter_id', '=', $reacterId);
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
     * Returns the count for the given reaction, falling back to zero when the reactant is not yet materialized.
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
}
