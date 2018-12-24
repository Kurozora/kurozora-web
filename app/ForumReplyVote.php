<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ForumReplyVote extends Model
{
    // Table name
    const TABLE_NAME = 'forum_reply_vote';
    protected $table = self::TABLE_NAME;

    // Fillable columns
    protected $fillable = ['reply_id', 'user_id', 'positive'];
}
