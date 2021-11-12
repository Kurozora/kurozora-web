<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ExploreCategoryItem extends KModel
{
    use HasFactory;

    const TABLE_NAME = 'explore_category_items';

    /**
     * Returns the explore category which the item belongs to.
     *
     * @return BelongsTo
     */
    function explore_category(): BelongsTo
    {
        return $this->belongsTo(ExploreCategory::class);
    }

    /**
     * Returns the model in the category item.
     *
     * @return MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
