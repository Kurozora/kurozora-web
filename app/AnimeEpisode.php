<?php

namespace App;

class AnimeEpisode extends KModel
{
    // Table name
    const TABLE_NAME = 'anime_episode';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the season this episode belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function season() {
        return $this->belongsTo(AnimeSeason::class);
    }
}
