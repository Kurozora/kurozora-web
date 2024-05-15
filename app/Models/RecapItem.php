<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class RecapItem extends KModel implements Sortable
{
    use SoftDeletes,
        SortableTrait;

    // Table name
    const string TABLE_NAME = 'recap_items';
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
     * The query used for sorting.
     *
     * @return Builder
     */
    public function buildSortQuery(): Builder
    {
        return static::query()
            ->withoutGlobalScopes()
            ->where([
                ['recap_id', '=', $this->recap_id],
                ['model_type', '=', $this->model_type]
            ]);
    }

    /**
     * Returns the recap which the item belongs to.
     *
     * @return BelongsTo
     */
    function recap(): BelongsTo
    {
        return $this->belongsTo(Recap::class);
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
