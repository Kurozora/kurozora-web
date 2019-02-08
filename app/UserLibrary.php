<?php

namespace App;

class UserLibrary extends KModel
{
    // Status of user library
    const STATUS_UNKNOWN    = 0;
    const STATUS_WATCHING   = 1;
    const STATUS_DROPPED    = 2;
    const STATUS_PLANNING   = 3;
    const STATUS_COMPLETED  = 4;
    const STATUS_ON_HOLD    = 5;

    // Map library status to string
    const STATUS_MAPPING = [
        self::STATUS_WATCHING   => 'watching',
        self::STATUS_DROPPED    => 'dropped',
        self::STATUS_PLANNING   => 'planning',
        self::STATUS_COMPLETED  => 'completed',
        self::STATUS_ON_HOLD    => 'on-hold'
    ];

    // Table name
    const TABLE_NAME = 'user_library';
    protected $table = self::TABLE_NAME;

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
        foreach(self::STATUS_MAPPING as $status => $string) {
            if($statusStr == $string)
                return $status;
        }

        return null;
    }

    /**
     * Returns the string of the status, or null if not found
     *
     * @param $statusInt
     * @return string|null
     */
    public static function getStringFromStatus($statusInt) {
        // Find the status
        foreach(self::STATUS_MAPPING as $status => $string) {
            if($statusInt == $status)
                return $string;
        }

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
