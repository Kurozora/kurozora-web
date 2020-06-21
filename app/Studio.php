<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Studio extends Model
{
    // Table name
    const TABLE_NAME = 'studios';
    protected $table = self::TABLE_NAME;
}
