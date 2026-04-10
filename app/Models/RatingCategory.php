<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class RatingCategory extends KModel
{
    // Table name
    const string TABLE_NAME = 'rating_categories';
    protected $table = self::TABLE_NAME;

    protected $fillable = [
        'slug',
        'name',
        'description',
    ];

    // -----------------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------------

    /**
     * The media types that use this category.
     *
     * @return HasMany
     */
    public function mediaTypeCategories(): HasMany
    {
        return $this->hasMany(MediaTypeCategory::class);
    }

    /**
     * The individual category scores tied to this category.
     *
     * @return HasMany
     */
    public function categoryScores(): HasMany
    {
        return $this->hasMany(RatingCategoryScore::class);
    }
}