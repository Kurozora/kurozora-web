<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ReflectionClass;

class UserNotification extends KModel
{
    // Types of notification
    const TYPE_UNKNOWN              = 0;
    const TYPE_NEW_FOLLOWER         = 1;
    const TYPE_NEW_SESSION          = 2;
    const TYPE_ANIME_IMPORT_UPDATE  = 3;

    // Table name
    const TABLE_NAME = 'user_notifications';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the user the notification belongs to.
     *
     * @return BelongsTo
     */
    function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Returns the string/text for this notification
     *
     * @return string
     */
    public function getString(): string
    {
        switch ($this->type) {
            // Someone started following the user
            case self::TYPE_NEW_FOLLOWER: {
                $followerName = $this->getDataVariable('follower_name');

                return (($followerName == null) ? 'An unknown user' : $followerName) . ' started following you';
            }
            // A new client/session was made for the user
            case self::TYPE_NEW_SESSION: {
                $sessionIPAddress = $this->getDataVariable('ip_address');

                return 'A new client has logged in to your account.' . (($sessionIPAddress != null) ? ' (IP Address: ' . $sessionIPAddress . ')' : null);
            }
            // Anime import update notification
            case self::TYPE_ANIME_IMPORT_UPDATE: {
                return 'Your "' . $this->getDataVariable('import_service') . '" anime import request has been processed. ' .
                    '(' . $this->getDataVariable('successful_count') . ' successful, ' .
                    $this->getDataVariable('failure_count') . ' failed imports)';
            }
            // Unknown type of notification
            default: return 'Error retrieving this notification';
        }
    }

    /**
     * Returns the type of the notification as a string
     *
     * @return string
     */
    public function getTypeString(): string
    {
        $reflection = new ReflectionClass(get_class($this));

        $constants_array = $reflection->getConstants();

        foreach ($constants_array as $constant_key => $constant_value) {
            if ($this->type == $constant_value)
                return $constant_key;
        }

        return 'TYPE_UNKNOWN';
    }

    /**
     * Returns the data for the notification
     *
     * @return array
     */
    public function getData(): array
    {
        $decoded =  json_decode($this->data);

        if ($decoded == null) {
            return [];
        }

        return (array) $decoded;
    }

    /**
     * Returns the value of a variable from the data field of this notification
     *
     * @param $varName
     * @return mixed
     */
    public function getDataVariable($varName): mixed
    {
        $data = $this->getData();

        if (isset($data[$varName])) {
            return $data[$varName];
        }

        return null;
    }
}
