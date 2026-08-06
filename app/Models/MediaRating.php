<?php

namespace App\Models;

use App\Traits\Model\MorphTvRated;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaRating extends KModel
{
    use MorphTvRated,
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
