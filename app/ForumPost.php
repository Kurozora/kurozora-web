<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ForumPost extends Model
{
    // Table name
    const TABLE_NAME = 'forum_post';
    protected $table = self::TABLE_NAME;

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
