<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumSection extends KModel
{
    // Table name
    const TABLE_NAME = 'forum_sections';
    protected $table = self::TABLE_NAME;

    // Amount of threads to display per page
    const THREADS_PER_PAGE = 25;

    /**
     * Retrieve the threads for the section
     *
     * @return HasMany
     */
    public function forum_threads(): HasMany
    {
        return $this->hasMany(ForumThread::class, 'section_id', 'id');
    }

    /**
     * Get the amount of threads in this section
     *
     * @return int
     */
    public function getThreadCount(): int
    {
        return ForumThread::where('section_id', $this->id)->count();
    }
}
