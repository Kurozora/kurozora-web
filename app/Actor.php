<?php

namespace App;

class Actor extends KModel
{
    // Table name
    const TABLE_NAME = 'actors';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the full name of the actor.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return $this->last_name . ', ' . $this->first_name;
    }

    /**
     * Returns the anime the actor belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function anime() {
        return $this->belongsTo(Anime::class);
    }
}
