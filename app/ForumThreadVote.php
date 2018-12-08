<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ForumThreadVote extends Model
{
    // Table name
    const TABLE_NAME = 'forum_thread_vote';
    protected $table = self::TABLE_NAME;
}
