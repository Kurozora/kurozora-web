<?php

namespace App\Models;

use App\Scopes\ExploreCategoryIsEnabledScope;
use App\Traits\Model\MorphTvRated;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class ExploreCategoryItem extends KModel implements Sortable
{
    use MorphTvRated,
        SoftDeletes,
        SortableTrait;

    // Table name
    const string TABLE_NAME = 'explore_category_items';
    protected $table = self::TABLE_NAME;

    /**
     * The sortable configurations.
     *
     * @var array
     */
    public array $sortable = [
        'order_column_name' => 'position',
        'sort_when_creating' => true,
        'sort_on_has_many' => true,
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
