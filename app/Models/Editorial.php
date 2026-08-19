<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;

class Editorial extends KModel
{
    // Table name
    const string TABLE_NAME = 'editorials';
    protected $table = self::TABLE_NAME;

    /**
     * The attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    /**
     * Returns the model the editorial endorses.
     *
     * @return MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo()
            ->withoutGlobalScopes();
    }

    /**
     * Limits the query to editorials that have gone live.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return void
     */
    public function scopePublished(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
