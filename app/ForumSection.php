<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ForumSection extends Model
{
    // Table name
    protected $table = 'forum_section';

    // Fillable columns
    protected $fillable = ['name', 'icon', 'locked'];
}
