<?php

namespace App\Models;

use App\Scopes\ExploreCategoryIsEnabledScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExploreCategoryItem extends KModel
{
    use HasFactory,
        SoftDeletes;

    // Table name
    const TABLE_NAME = 'explore_category_items';
    protected $table = self::TABLE_NAME;

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'model'
    ];

    /**
     * Returns the explore category which the item belongs to.
     *
     * @return BelongsTo
     */
    function explore_category(): BelongsTo
    {
        return $this->belongsTo(ExploreCategory::class)
            ->withoutGlobalScope(new ExploreCategoryIsEnabledScope);
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
