<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Language extends KModel
{
    use HasFactory,
        Searchable,
        SoftDeletes;

    // Table name
    const string TABLE_NAME = 'languages';
    protected $table = self::TABLE_NAME;

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
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'iso_639_3' => $this->iso_639_3,
        ];
    }
}
