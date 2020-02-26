<?php

namespace App;

class Session extends KModel
{
    // Table name
    const TABLE_NAME = 'sessions';
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
}
