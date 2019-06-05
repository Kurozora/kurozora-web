<?php

namespace App;


/**
 * @property false|string last_validated
 * @property mixed id
 * @property string ip
 * @property string city
 * @property string region
 * @property string country
 * @property float latitude
 * @property float longitude
 * @property mixed device
 * @property mixed expiration_date
 */
class Session extends KModel
{
    // Table name
    const TABLE_NAME = 'user_session';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the user that owns the session.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function user() {
        return $this->belongsTo(User::class);
    }

    // Checks if the session is expired
    public function isExpired() {
        return (strtotime($this->expiration_date) < time());
    }

    // Formats the last validated data properly
    public function formatLastValidated() {
        if($this->last_validated == null)
            return 'Unknown date';

        $lastValUnix = strtotime($this->last_validated);
        $lastValDate = date('j M, Y', $lastValUnix) . ' at ';
        $lastValDate .= date('H:i', $lastValUnix);

        return $lastValDate;
    }

    /**
     * Formats the session for display in the API's list of sessions
     *
     * @return array
     */
    public function formatForSessionList() {
        return [
            'id'                => $this->id,
            'device'            => $this->device,
            'ip'                => $this->ip,
            'last_validated'    => $this->formatLastValidated(),
            'location'          => [
                'city'      => $this->city,
                'region'    => $this->region,
                'country'   => $this->country,
                'latitude'  => $this->latitude,
                'longitude' => $this->longitude,
            ]
        ];
    }

    /**
     * Formats the session for display in the details API
     *
     * @return array
     */
    public function formatForSessionDetails() {
        return [
            'id'                => $this->id,
            'device'            => $this->device,
            'ip'                => $this->ip,
            'last_validated'    => $this->formatLastValidated(),
            'location'          => [
                'city'      => $this->city,
                'region'    => $this->region,
                'country'   => $this->country,
                'latitude'  => $this->latitude,
                'longitude' => $this->longitude,
            ]
        ];
    }
}
