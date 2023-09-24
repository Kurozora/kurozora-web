<?php

namespace App\Traits\Model;

use App\Models\SessionAttribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasSessionAttribute
{
    /**
     * Bootstrap the model with Session Attribute.
     *
     * @return void
     */
    public static function bootHasSessionAttribute(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if ($model->forceDeleting) {
                    $model->session_attribute()->forceDelete();
                    return;
                }
            }

            $model->session_attribute()->delete();
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restoring(function (Model $model) {
                $model->session_attribute()->restore();
            });
        }
    }

    /**
     * The session attribute of the session.
     *
     * @return MorphOne
     */
    function session_attribute(): MorphOne
    {
        return $this->morphOne(SessionAttribute::class, 'model');
    }
}
