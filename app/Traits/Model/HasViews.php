<?php

namespace App\Traits\Model;

use App\Models\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasViews
{
    /**
     * Bootstrap the model with Views.
     *
     * @return void
     */
    public static function bootHasViews(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if ($model->forceDeleting) {
                    $model->views()->forceDelete();
                }
            }
        });
    }

    /**
     * Get the model's views.
     *
     * @return MorphMany
     */
    public function views(): MorphMany
    {
        return $this->morphMany(View::class, 'viewable');
    }
}
