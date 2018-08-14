<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected $fillable = ['user_id', 'device', 'secret', 'expiration_date'];
}
