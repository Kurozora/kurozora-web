<?php

namespace App\Traits\Model;

use App\Models\Anime;
use App\Models\MediaTag;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasMediaTags
{
    /**
     * Bootstrap the model with Tags.
     *
     * @return void
     */
    public static function bootHasMediaTags(): void
    {
        static::deleting(function (Model $model) {
            $model->mediaTags()->delete();
        });
    }

    /**
     * Get the model's tags.
     *
     * @return MorphMany
     */
    public function mediaTags(): MorphMany
    {
        return $this->morphMany(MediaTag::class, 'taggable');
    }

    /**
     * Get the model's tags.
     *
     * @return HasManyThrough
     */
    public function tags(): HasManyThrough
    {
        return $this->hasManyThrough(Tag::class, MediaTag::class, 'taggable_id', 'id', 'id', 'tag_id')
            ->where('taggable_type', '=', Anime::class);
    }
}
