<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Actor extends Model
{
    // Table name
    const TABLE_NAME = 'anime_actor';
    protected $table = self::TABLE_NAME;

    // Fillable columns
    protected $fillable = [
        'anime_id',
        'name',
        'role',
        'image'
    ];

    /**
     * Formats the actor with the minimal data needed for a response
     *
     * @return array
     */
    public static function formatForResponse($actor) {
        return [
            'name'  => $actor->name,
            'role'  => $actor->role,
            'image' => $actor->image
        ];
    }
}
