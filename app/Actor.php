<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Actor extends Model
{
    // Table name
    const TABLE_NAME = 'anime_actor';
    protected $table = self::TABLE_NAME;

    // Remove column guards
    protected $guarded = [];

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
