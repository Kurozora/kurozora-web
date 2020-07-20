<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnimeImages extends Model
{
    // Table name
    const TABLE_NAME = 'anime_images';
    protected $table = self::TABLE_NAME;

    public function anime() {
        return $this->belongsTo(Anime::class);
    }
}
