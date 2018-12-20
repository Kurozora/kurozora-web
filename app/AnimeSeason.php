<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnimeSeason extends Model
{
    // Table name
    const TABLE_NAME = 'anime_season';
    protected $table = self::TABLE_NAME;

    // Fillable columns
    protected $fillable = ['anime_id', 'number', 'title'];

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
        return AnimeEpisode::where([
            ['season',      '=', $this->number],
            ['anime_id',    '=', $this->anime_id]
        ])->count();
    }
}
