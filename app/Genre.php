<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    // Table name
    const TABLE_NAME = 'genre';
    protected $table = self::TABLE_NAME;

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
