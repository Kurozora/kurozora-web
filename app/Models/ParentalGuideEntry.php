<?php

namespace App\Models;

use App\Enums\ParentalGuideCategory;
use App\Enums\ParentalGuideDepiction;
use App\Enums\ParentalGuideFrequency;
use App\Enums\ParentalGuideRating;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ParentalGuideEntry extends KModel
{
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
     * Returns the user to which the receipt belongs.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Returns the model in the media theme.
     *
     * @return MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Eloquent builder scope that limits the query to the models with the specified type.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_hidden', false);
    }
}
