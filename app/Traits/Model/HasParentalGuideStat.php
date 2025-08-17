<?php

namespace App\Traits\Model;

use App\Models\ParentalGuideStat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasParentalGuideStat
{
    /**
     * Bootstrap the model with Stat.
     *
     * @return void
     */
    public static function bootHasParentalGuideStat(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if ($model->forceDeleting) {
                    $model->parental_guide_stat()->forceDelete();
                    return;
                }
            }

            $model->parental_guide_stat()->delete();
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restoring(function (Model $model) {
                $model->parental_guide_stat()->restore();
            });
        }

        static::created(function (Model $model) {
            $model->parental_guide_stat()->create();
        });
    }

    /**
     * Get the model's parental guide stats.
     *
     * @return MorphOne
     */
    public function parental_guide_stat(): MorphOne
    {
        return $this->morphOne(ParentalGuideStat::class, 'model');
    }
}
