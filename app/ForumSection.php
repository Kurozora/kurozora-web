<?php

namespace App;

class ForumSection extends KModel
{
    // Table name
    const TABLE_NAME = 'forum_sections';
    protected $table = self::TABLE_NAME;

    // Amount of threads to display per page
    const THREADS_PER_PAGE = 10;

    /**
     * Retrieve the threads for the section
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function forum_threads() {
        return $this->hasMany(ForumThread::class, 'section_id', 'id');
    }

    /**
     * Get the amount of threads in this section
     *
     * @return integer
     */
    public function getThreadCount() {
        return ForumThread::where('section_id', $this->id)->count();
    }
}
