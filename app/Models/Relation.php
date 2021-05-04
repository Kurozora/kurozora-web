<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Relation extends Model
{
    // Table name
    const TABLE_NAME = 'relations';
    protected $table = self::TABLE_NAME;
}
