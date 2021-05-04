<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Relation extends Model
{
    use HasFactory;

    // Table name
    const TABLE_NAME = 'relations';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the anime that the relation has.
     *
     * @return HasMany
     */
    public function anime(): HasMany
    {
        return $this->hasMany(MediaRelation::class, 'relation_id')->where('media_type', 'anime');
    }
}
