<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Relation extends KModel
{
    use HasFactory,
        SoftDeletes;

    // Table name
    const string TABLE_NAME = 'relations';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the anime that the relation has.
     *
     * @return HasMany
     */
    public function anime(): HasMany
    {
        return $this->hasMany(MediaRelation::class, 'relation_id')->where('model_type', Anime::class);
    }
}
