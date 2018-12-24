<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AnimeEpisode extends Model
{
    // Table name
    const TABLE_NAME = 'anime_episode';
    protected $table = self::TABLE_NAME;

    // Fillable columns
    protected $fillable = [
        'season_id',
        'number',
        'name',
        'first_aired',
        'overview'
    ];

    /**
     * Formats the necessary data of this episode
     *
     * @return array
     */
    public function formatEpisodeData() {
        $firstAiredUnix = (new Carbon($this->first_aired))->timestamp;
        $formattedFirstAired = date('j M, Y', $firstAiredUnix);

        return [
            'id'            => $this->id,
            'number'        => $this->number,
            'name'          => $this->name,
            'first_aired'   => $formattedFirstAired,
            'overview'      => $this->overview,
            'verified'      => (bool) $this->verified
        ];
    }
}
