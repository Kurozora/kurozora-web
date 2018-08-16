<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected $fillable = ['user_id', 'device', 'secret', 'expiration_date'];

    // Checks if the session is expired
    public function isExpired() {
        return (strtotime($this->expiration_date) < time());
    }
}
