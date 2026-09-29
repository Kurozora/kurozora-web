<?php

namespace App\Traits\Model;

use App\Enums\LanguageSupportType;
use App\Models\Language;
use App\Models\MediaLanguage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

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
     * Get every language the model supports.
     *
     * @return MorphToMany
     */
    public function supportedLanguages(): MorphToMany
    {
        return $this->morphToMany(Language::class, 'model', MediaLanguage::class)
            ->distinct()
            ->orderBy('languages.name');
    }

    /**
     * Get the languages the model is voiced in.
     *
     * @return MorphToMany
     */
    public function audioLanguages(): MorphToMany
    {
        return $this->languagesSupporting(LanguageSupportType::Audio());
    }

    /**
     * Get the languages the model is subtitled in.
     *
     * @return MorphToMany
     */
    public function subtitleLanguages(): MorphToMany
    {
        return $this->languagesSupporting(LanguageSupportType::Subtitles());
    }

    /**
     * Get the languages the model's interface is translated into.
     *
     * @return MorphToMany
     */
    public function interfaceLanguages(): MorphToMany
    {
        return $this->languagesSupporting(LanguageSupportType::Interface());
    }

    /**
     * Get the languages the model's text is published in.
     *
     * @return MorphToMany
     */
    public function textLanguages(): MorphToMany
    {
        return $this->languagesSupporting(LanguageSupportType::Text());
    }

    /**
     * Get the language the model was originally made in.
     *
     * @return string|null
     */
    public function originLanguage(): ?string
    {
        return origin_language($this->country_id);
    }

    /**
     * Get the language to lead with.
     *
     * @return Language|null
     */
    public function primaryLanguage(): ?Language
    {
        $languages = $this->mediaLanguages
            ->pluck('language')
            ->filter()
            ->unique('id')
            ->sortBy('name');

        return $languages->firstWhere('code', '=', icu_locale())
            ?? $languages->firstWhere('code', '=', $this->originLanguage())
            ?? $languages->first();
    }

    /**
     * Get the support types of the model's primary language.
     *
     * @return Collection
     */
    public function primaryLanguageTypes(): Collection
    {
        $primary = $this->primaryLanguage();

        if ($primary === null) {
            return collect();
        }

        return $this->mediaLanguages
            ->where('language_id', '=', $primary->id)
            ->pluck('type')
            ->map(fn (LanguageSupportType $type) => $type->value)
            ->unique()
            ->sort()
            ->values();
    }


    /**
     * Get the languages the model supports for the given support type.
     *
     * @param LanguageSupportType $type
     * @return MorphToMany
     */
    protected function languagesSupporting(LanguageSupportType $type): MorphToMany
    {
        return $this->morphToMany(Language::class, 'model', MediaLanguage::class)
            ->wherePivot('type', '=', $type->value)
            ->withPivot('type')
            ->withTimestamps()
            ->orderBy('languages.name');
    }
}
