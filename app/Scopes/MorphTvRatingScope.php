<?php

namespace App\Scopes;

use App\Models\MediaSong;
use App\Traits\Model\TvRated;
use File;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;

class MorphTvRatingScope extends TvRatingScope
{
    /**
     * @inheritDoc
     */
    public function apply(Builder $builder, Model $model): void
    {
        $preferredTvRating = config('app.tv_rating');

        // Basically if Tv Rating exists
        if ($preferredTvRating > 0) {
            $builder->whereMorphRelation($this->getMorphTvRatingRelation(), $this->getMorphTvRatingTypes($model), $model->getQualifiedTvRatingColumn(), '<=', $preferredTvRating)
            ->orWhereHasMorph($this->getMorphTvRatingRelation(), [MediaSong::class], function (Builder $builder) use ($model, $preferredTvRating) {
                $builder->whereMorphRelation($this->getMorphTvRatingRelation(), $this->getMorphTvRatingTypes($model), $model->getQualifiedTvRatingColumn(), '<=', $preferredTvRating);
            });
        }
    }

    /**
     * The name of the morph relation.
     *
     * @return String
     */
    function getMorphTvRatingRelation(): string
    {
        return 'model';
    }

    /**
     * The possible types of the morph relation.
     *
     * @return String[]
     */
    function getMorphTvRatingTypes(Model $model): array
    {
        $models = [];
        $modelsPath = app_path('Models');

        foreach (File::allFiles($modelsPath) as $file) {
            $className = 'App\\Models\\' . $file->getBasename('.php');

            if (class_exists($className) && $className !== $model::class) {
                $reflection = new ReflectionClass($className);

                if ($reflection->isSubclassOf(Model::class)) {
                    if (in_array(TvRated::class, $reflection->getTraitNames())) {
                        $models[] = $className;
                    }
                }
            }
        }

        return $models;
    }

    /**
     * The possible types of the morph relation.
     *
     * @return String[]
     */
    function getMorphMorphTvRatingTypes(Model $model): array
    {
        $models = [];
//        $modelsPath = app_path('Models');
//
//        foreach (File::allFiles($modelsPath) as $file) {
//            $className = 'App\\Models\\' . $file->getBasename('.php');
//
//            if (class_exists($className) && $className !== $model::class) {
//                $reflection = new ReflectionClass($className);
//
//                if ($reflection->isSubclassOf(Model::class)) {
//                    if (in_array(MorphTvRated::class, $reflection->getTraitNames())) {
//                        $models[] = $className;
//                    }
//                }
//            }
//        }

        return $models;
    }
}
