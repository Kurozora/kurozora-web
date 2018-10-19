<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ForumPost extends Model
{
    // Table name
    protected $table = 'forum_post';

    /**
     * Formats the post for a response
     *
     * @return array
     */
    public function formatForResponse() {
        return [
            'id'            => $this->id,
            'user_id'       => $this->user_id,
            'parent_post'   => $this->parent_post,
            'title'         => $this->title,
            'content'       => $this->content,
        ];
    }
}
