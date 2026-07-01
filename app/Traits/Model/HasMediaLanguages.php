<?php

namespace App\Traits\Model;

use App\Enums\LanguageSupportType;
use App\Models\Language;
use App\Models\MediaLanguage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasMediaLanguages
{
    /**
     * Bootstrap the model with Languages.
     *
     * @return void
     */
    public static function bootHasMediaLanguages(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if ($model->forceDeleting) {
                    $model->mediaLanguages()->forceDelete();
                    return;
                }
            }

            $model->mediaLanguages()->delete();
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restoring(function (Model $model) {
                $model->mediaLanguages()->restore();
            });
        }
    }

    /**
     * Get the model's media languages.
     *
     * @return MorphMany
     */
    public function mediaLanguages(): MorphMany
    {
        return $this->morphMany(MediaLanguage::class, 'model');
    }

    /**
     * Get the languages the model supports for the given support type.
     *
     * @param LanguageSupportType $type
     * @return MorphToMany
     */
    public function supportedLanguages(LanguageSupportType $type): MorphToMany
    {
        return $this->morphToMany(Language::class, 'model', MediaLanguage::class)
            ->wherePivot('type', '=', $type->value)
            ->withPivot('type')
            ->withTimestamps();
    }
}
