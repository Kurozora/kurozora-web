<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Tag extends KModel
{
    use HasUlids,
        Searchable,
        SoftDeletes;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool $incrementing
     */
    public $incrementing = false;

    // Table name
    const string TABLE_NAME = 'tags';
    protected $table = self::TABLE_NAME;

    /**
     * The media tag the tag belongs to.
     *
     * @return HasMany
     */
    public function mediaTags(): HasMany
    {
        return $this->hasMany(MediaTag::class);
    }

    /**
     * Dispatch the job to make the given models searchable.
     *
     * @param  Collection  $models
     * @return void
     */
    public function queueMakeSearchable($models)
    {
        // We just want the `toSearchableArray` method to be available,
        // hence we're using the `Searchable` trait. By keeping this
        // method empty, we avoid queueing created/updated models.
    }

    /**
     * Modify the query used to retrieve models when making all of the models searchable.
     *
     * @param Builder $query
     * @return Builder
     */
    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->withoutGlobalScopes();
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
