<?php

namespace App\Traits\Model;

use App\Models\MediaTag;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if ($model->forceDeleting) {
                    $model->mediaTags()->forceDelete();
                    return;
                }
            }

            $model->mediaTags()->delete();
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restoring(function (Model $model) {
                $model->mediaTags()->restore();
            });
        }
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
     * @return BelongsToMany
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, MediaTag::class, 'taggable_id')
            ->where('taggable_type', '=', $this->getMorphClass())
            ->withTimestamps();
    }

    /**
     * Eloquent builder scope that limits the query to the given tag.
     *
     * @param Builder $query
     * @param string|int|Tag $tag
     * @return Builder
     */
    public function scopeWhereTag(Builder $query, string|int|Tag $tag): Builder
    {
        if (is_numeric($tag)) {
            $tagID = $tag;
        } else {
            $tagID = $tag->id;
        }

        return $query->leftJoin(MediaTag::TABLE_NAME, MediaTag::TABLE_NAME . '.taggable_id', '=', self::TABLE_NAME . '.' . $this->getKeyName())
            ->where(MediaTag::TABLE_NAME . '.taggable_type', '=', $this->getMorphClass())
            ->where(MediaTag::TABLE_NAME . '.tag_id', '=', $tagID)
            ->select(self::TABLE_NAME . '.*');
    }
}
