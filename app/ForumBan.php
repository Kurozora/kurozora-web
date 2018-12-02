<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ForumBan extends Model
{
    // Table name
    const TABLE_NAME = 'forum_ban';
    protected $table = self::TABLE_NAME;
}
