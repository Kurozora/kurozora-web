<?php

namespace App;

class AnimeSeason extends KModel
{
    // Table name
    const TABLE_NAME = 'anime_seasons';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the episodes associated with the season
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    function episodes() {
        return $this->hasMany(AnimeEpisode::class, 'season_id');
    }

    /**
     * Returns the Anime that owns the season
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function anime() {
        return $this->belongsTo(Anime::class);
    }

    /**
     * Returns the title of the Season
     *
     * @return string
     */
    public function getTitle() {
        if($this->number == 0)
            return 'Specials';

        if($this->title != null)
            return $this->title;

        return 'Season ' . $this->number;
    }

    /**
     * Gets the first aired date of the first aired episode in this season.
     *
     * @return string|null
     */
    public function getFirstAired()
    {
        $firstEpisode = $this->episodes->firstWhere('number', 1);

        if($firstEpisode == null)
            return null;

        return $firstEpisode->first_aired->format('Y-m-d');
    }

    /**
     * Gets the count of the amount of episodes in this season
     *
     * @return int
     */
    public function getEpisodeCount() {
        return AnimeEpisode::where('season_id', $this->id)->count();
    }

    /**
     * Gets the episodes for this season
     *
     * @return array
     */
    public function getEpisodes() {
        return AnimeEpisode::where('season_id', $this->id)->get();
    }
}
