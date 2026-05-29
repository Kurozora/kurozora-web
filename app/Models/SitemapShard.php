<?php

namespace App\Models;

class SitemapShard extends KModel
{
    // Table name
    const string TABLE_NAME = 'sitemap_shards';
    protected $table = self::TABLE_NAME;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'max_updated_at' => 'datetime',
        'generated_at'   => 'datetime',
    ];
}
