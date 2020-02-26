<?php

namespace App;

class Actor extends KModel
{
    // Table name
    const TABLE_NAME = 'actors';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the Anime the actor belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function anime() {
        return $this->belongsTo(Anime::class);
    }
}
