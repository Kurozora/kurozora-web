<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = ['username', 'email', 'password', 'email_confirmation_id'];

    /**
        Checks if this user has confirmed their email address
    **/
    public function hasConfirmedEmail() {
        return ($this->email_confirmation_id == null);
    }
}
