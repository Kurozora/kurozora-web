<?php

namespace App\Traits\Model;

use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaRelation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasMediaRelations
{
    /**
     * Bootstrap the model with MediaRelation.
     *
     * @return void
     */
    public static function bootHasMediaRelations(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if (!$model->forceDeleting) {
                    return;
                }
            }

            $model->mediaRelations()->delete();
        });
    }

    /**
     * Get the model's media relations.
     *
     * @return MorphMany
     */
    public function mediaRelations(): MorphMany
    {
        return $this->morphMany(MediaRelation::class, 'model');
    }

    /**
     * The related anime of this model.
     *
     * @return morphMany
     */
    public function animeRelations(): morphMany
    {
        return $this->mediaRelations()
            ->where('related_type', '=', Anime::class)
            ->join(Anime::TABLE_NAME, function ($join) {
                $join->on(Anime::TABLE_NAME . '.id', '=', MediaRelation::TABLE_NAME . '.related_id');

                if (auth()->check()) {
                    $tvRating = auth()->user()->tv_rating;
                    if ($tvRating >= 0) {
                        $join->where('tv_rating_id', '<=', $tvRating);
                    }
                } else {
                    $join->where('tv_rating_id', '<=', 4);
                }
            });
    }

    /**
     * The related manga of this model.
     *
     * @return morphMany
     */
    public function mangaRelations(): morphMany
    {
        return $this->mediaRelations()
            ->where('related_type', '=', Manga::class)
            ->join(Manga::TABLE_NAME, function ($join) {
                $join->on(Manga::TABLE_NAME . '.id', '=', MediaRelation::TABLE_NAME . '.related_id');

                if (auth()->check()) {
                    $tvRating = auth()->user()->tv_rating;
                    if ($tvRating >= 0) {
                        $join->where('tv_rating_id', '<=', $tvRating);
                    }
                } else {
                    $join->where('tv_rating_id', '<=', 4);
                }
            });
    }

    /**
     * The related game of this model.
     *
     * @return morphMany
     */
    public function gameRelations(): morphMany
    {
        return $this->mediaRelations()
            ->where('related_type', '=', Game::class)
            ->join(Game::TABLE_NAME, function ($join) {
                $join->on(Game::TABLE_NAME . '.id', '=', MediaRelation::TABLE_NAME . '.related_id');

                if (auth()->check()) {
                    $tvRating = auth()->user()->tv_rating;
                    if ($tvRating >= 0) {
                        $join->where('tv_rating_id', '<=', $tvRating);
                    }
                } else {
                    $join->where('tv_rating_id', '<=', 4);
                }
            });
    }
}
