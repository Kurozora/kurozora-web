<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserLibrary extends Model
{
    // Status of user library
    const STATUS_UNKNOWN    = 0;
    const STATUS_WATCHING   = 1;
    const STATUS_DROPPED    = 2;

    // Table name
    protected $table = 'user_library';

    // Fillable columns
    protected $fillable = ['user_id', 'anime_id', 'status'];

    /**
     * Returns the integer of the status, or null if not found
     *
     * @param $statusStr
     * @return int|null
     */
    public static function getStatusFromString($statusStr) {
        if($statusStr == 'watching')
            return UserLibrary::STATUS_WATCHING;
        else if($statusStr == 'dropped')
            return UserLibrary::STATUS_DROPPED;
        else return null;
    }

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
