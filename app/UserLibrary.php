<?php

namespace App;

class UserLibrary extends KModel
{
    // Table name
    const TABLE_NAME = 'user_library';
    protected $table = self::TABLE_NAME;

    /**
     * Formats this library item for a response
     *
     * @return array
     */
    public function formatForResponse() {
        return [
            'id'        => $this->id,
            'anime_id'  => $this->anime_id
        ];
    }
}
