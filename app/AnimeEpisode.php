<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AnimeEpisode extends Model
{
    protected $fillable = [
        'anime_id',
        'season',
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
            'season'        => $this->season,
            'number'        => $this->number,
            'name'          => $this->name,
            'first_aired'   => $formattedFirstAired,
            'overview'      => $this->overview,
            'verified'      => (bool) $this->verified
        ];
    }
}
