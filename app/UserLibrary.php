<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserLibrary extends Model
{
    // Status of user library
    const STATUS_UNKNOWN    = 0;
    const STATUS_WATCHING   = 1;
    const STATUS_DROPPED    = 2;
    const STATUS_PLANNING   = 3;
    const STATUS_COMPLETED  = 4;
    const STATUS_ON_HOLD    = 5;

    // Table name
    const TABLE_NAME = 'user_library';
    protected $table = self::TABLE_NAME;

    // Fillable columns
    protected $fillable = ['user_id', 'anime_id', 'status'];

    /**
     * Returns the integer of the status, or null if not found
     *
     * @param $statusStr
     * @return int|null
     */
    public static function getStatusFromString($statusStr) {
        // Make the status lowercase (case insensitive)
        $statusStr = strtolower($statusStr);

        // Find the status
        if($statusStr == 'watching')
            return UserLibrary::STATUS_WATCHING;
        else if($statusStr == 'dropped')
            return UserLibrary::STATUS_DROPPED;
        else if($statusStr == 'planning')
            return UserLibrary::STATUS_PLANNING;
        else if($statusStr == 'completed')
            return UserLibrary::STATUS_COMPLETED;
        else if($statusStr == 'on-hold')
            return UserLibrary::STATUS_ON_HOLD;
        else
            return null;
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
