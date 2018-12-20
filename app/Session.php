<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    // Table name
    const TABLE_NAME = 'user_session';
    protected $table = self::TABLE_NAME;

    // Fillable columns
    protected $fillable = [
        'user_id',
        'device',
        'secret',
        'expiration_date',
        'last_validated',
        'ip'
    ];

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
            'last_validated'    => $this->formatLastValidated()
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
            'last_validated'    => $this->formatLastValidated()
        ];
    }
}
