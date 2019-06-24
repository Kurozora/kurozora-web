<?php

namespace App;

class AnimeEpisode extends KModel
{
    // Table name
    const TABLE_NAME = 'anime_episodes';
    protected $table = self::TABLE_NAME;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'first_aired',
    ];

    /**
     * Returns the season this episode belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function season() {
        return $this->belongsTo(AnimeSeason::class);
    }
}
