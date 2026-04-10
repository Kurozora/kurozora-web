<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaTypeCategory extends KModel
{
    // Table name
    const string TABLE_NAME = 'media_type_categories';
    protected $table = self::TABLE_NAME;

    protected $fillable = [
        'model_type',
        'rating_category_id',
        'display_order',
    ];

    // -----------------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------------

    /**
     * The rating category this mapping points to.
     *
     * @return BelongsTo
     */
    public function ratingCategory(): BelongsTo
    {
        return $this->belongsTo(RatingCategory::class);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Return the ordered list of RatingCategory models for a given morph type.
     *
     * Usage:
     *   MediaTypeCategory::categoriesFor(Anime::class)
     *   MediaTypeCategory::categoriesFor('App\Models\Anime')
     *
     * @param  string|object  $modelType  Class name or FQCN string
     * @return \Illuminate\Support\Collection
     */
    public static function categoriesFor(string|object $modelType): \Illuminate\Support\Collection
    {
        $type = is_object($modelType) ? get_class($modelType) : $modelType;

        return static::query()
            ->where('model_type', $type)
            ->orderBy('display_order')
            ->with('ratingCategory')
            ->get()
            ->pluck('ratingCategory');
    }
}