<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    // Table name
    const TABLE_NAME = 'languages';
    protected $table = self::TABLE_NAME;
}