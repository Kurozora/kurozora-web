<?php

namespace App;

class Studio extends KModel
{
    // Table name
    const TABLE_NAME = 'studios';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the anime that belongs to the studio
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function anime()
    {
        return $this->hasMany(Anime::class);
    }
}
