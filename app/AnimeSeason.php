<?php

namespace App;

class AnimeSeason extends KModel
{
    // Table name
    const TABLE_NAME = 'anime_season';
    protected $table = self::TABLE_NAME;

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
     * Formats the seasons for a response
     *
     * @return array
     */
    public function formatForResponse() {
        return [
            'id'        => $this->id,
            'title'     => $this->getTitle(),
            'number'    => $this->number
        ];
    }

    /**
     * Formats the season for the "season info" response
     *
     * @return array
     */
    public function formatForInfoResponse() {
        return [
            'id'            => $this->id,
            'anime_id'      => $this->anime_id,
            'title'         => $this->getTitle(),
            'episode_count' => $this->getEpisodeCount()
        ];
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
