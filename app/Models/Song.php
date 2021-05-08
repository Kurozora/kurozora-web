<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    use HasFactory;

    // Table name
    const TABLE_NAME = 'songs';
    protected $table = self::TABLE_NAME;
}
