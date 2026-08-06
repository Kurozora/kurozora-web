<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RatingCategory extends KModel
{
    // Table name
    const string TABLE_NAME = 'rating_categories';
    protected $table = self::TABLE_NAME;

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'weight' => 'double',
        ];
    }

    /**
     * Returns the per-category scores of the rating category.
     *
     * @return HasMany
     */
    public function categoryScores(): HasMany
    {
        return $this->hasMany(RatingCategoryScore::class);
    }

    /**
     * Eloquent builder scope that limits the query to the given model type, in display order.
     *
     * @param Builder $query
     * @param string  $modelType
     *
     * @return Builder
     */
    public function scopeForModelType(Builder $query, string $modelType): Builder
    {
        return $query->where('model_type', '=', $modelType)
            ->orderBy('display_order');
    }
}
