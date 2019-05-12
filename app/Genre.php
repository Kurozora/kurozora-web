<?php

namespace App;

/**
 * @property mixed id
 * @property mixed name
 * @property mixed nsfw
 * @property mixed description
 */
class Genre extends KModel
{
    // Table name
    const TABLE_NAME = 'genre';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the Anime with the genre
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function animes() {
        return $this->belongsToMany(Anime::class, AnimeGenre::TABLE_NAME, 'genre_id', 'anime_id');
    }

    /**
     * Formats the genre for the Anime response
     *
     * @return array
     */
    public function formatForAnimeResponse() {
        return [
            'id'    => $this->id,
            'name'  => $this->name
        ];
    }

    /**
     * Formats the genre for the overview response
     *
     * @return array
     */
    public function formatForOverviewResponse() {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'nsfw'  => (bool) $this->nsfw
        ];
    }

    /**
     * Formats the genre for the details response
     *
     * @return array
     */
    public function formatForDetailsResponse() {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'description'   => $this->description,
            'nsfw'          => (bool) $this->nsfw
        ];
    }
}
