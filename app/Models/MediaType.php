<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaType extends Model
{
    use HasFactory;

    // Table name
    const TABLE_NAME = 'media_types';
    protected $table = self::TABLE_NAME;
}
