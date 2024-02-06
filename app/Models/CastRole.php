<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CastRole extends KModel
{
    use HasFactory,
        SoftDeletes;

    // Table name
    const string TABLE_NAME = 'cast_roles';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the cast that belong to the role.
     *
     * @return HasMany
     */
    public function cast(): HasMany
    {
        return $this->hasMany(AnimeCast::class);
    }
}
