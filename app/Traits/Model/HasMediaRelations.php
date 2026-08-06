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
                if ($model->forceDeleting) {
                    $model->mediaRelations()->forceDelete();
                    return;
                }
            }

            $model->mediaRelations()->delete();
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restoring(function (Model $model) {
                $model->mediaRelations()->restore();
            });
        }
    }

    /**
     * Get the model's media relations.
     *
     * @return MorphMany
     */
    public function mediaRelations(): MorphMany
    {
        return $this->morphMany(MediaRelation::class, 'model')
            ->select(MediaRelation::TABLE_NAME . '.*');
    }

    /**
     * The related anime of this model.
     *
     * @return morphMany
     */
    public function animeRelations(): MorphMany
    {
        return $this->mediaRelations()
            ->where('related_type', '=', Anime::class)
            ->whereHasMorph('related', [Anime::class], function ($query) {
                $this->viewableViaParent($query);
            });
    }

    /**
     * The related manga of this model.
     *
     * @return MorphMany
     */
    public function mangaRelations(): MorphMany
    {
        return $this->mediaRelations()
            ->where('related_type', '=', Manga::class)
            ->whereHasMorph('related', [Manga::class], function ($query) {
                $this->viewableViaParent($query);
            });
    }

    /**
     * The related game of this model.
     *
     * @return MorphMany
     */
    public function gameRelations(): MorphMany
    {
        return $this->mediaRelations()
            ->where('related_type', '=', Game::class)
            ->whereHasMorph('related', [Game::class], function ($query) {
                $this->viewableViaParent($query);
            });
    }
}
