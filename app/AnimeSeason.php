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
}
