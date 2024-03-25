<?php

namespace App\Scopes;

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
            $builder->whereMorphRelation($this->getMorphTvRatingRelation(), $this->getMorphTvRatingTypes(), $model->getQualifiedTvRatingColumn(), '<=', $preferredTvRating);
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
    function getMorphTvRatingTypes(): array
    {
        $models = [];
        $modelsPath = app_path('Models');

        foreach (File::allFiles($modelsPath) as $file) {
            $className = 'App\\Models\\' . $file->getBasename('.php');

            if (class_exists($className)) {
                $reflection = new ReflectionClass($className);
                if ($reflection->isSubclassOf('Illuminate\Database\Eloquent\Model')) {
                    if (in_array(TvRated::class, $reflection->getTraitNames())) {
                        $models[] = $className;
                    }
                }
            }
        }

        return $models;
    }
}
