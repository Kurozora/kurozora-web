<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Language extends Model
{
    use HasFactory,
        SoftDeletes;

    // Table name
    const TABLE_NAME = 'languages';
    protected $table = self::TABLE_NAME;
}
