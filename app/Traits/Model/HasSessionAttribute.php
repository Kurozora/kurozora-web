<?php

namespace App\Traits\Model;

use App\Models\Comment;
use App\Models\Session;
use App\Models\SessionAttribute;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
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
                if (!$model->forceDeleting) {
                    return;
                }
            }

            $model->session_attribute()->delete();
        });
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
