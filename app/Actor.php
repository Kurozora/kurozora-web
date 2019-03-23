<?php

namespace App;

/**
 * @property mixed name
 * @property mixed role
 * @property mixed image
 */
class Actor extends KModel
{
    // Table name
    const TABLE_NAME = 'anime_actor';
    protected $table = self::TABLE_NAME;

    /**
     * Formats the actor with the minimal data needed for a response
     *
     * @return array
     */
    public function formatForResponse() {
        return [
            'name'  => $this->name,
            'role'  => $this->role,
            'image' => $this->image
        ];
    }
}
