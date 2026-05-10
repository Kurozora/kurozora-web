<?php

namespace App\Traits\Model;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait HasTranslations
{
    use Translatable;

    /**
     * The model's translation relationship.
     *
     * @return HasOne
     */
    public function translation(): HasOne
    {
        $locale = $this->locale();
        $column = $this->getTranslationsTable() . '.' . $this->getLocaleKey();

        if (!$this->useFallback()) {
            return $this->hasOne($this->getTranslationModelName(), $this->getTranslationRelationKey())
                ->where($column, $locale);
        }

        $locales = array_values(array_unique(array_filter([
            $locale,
            $this->getFallbackLocale($locale),
            $this->getFallbackLocale(),
        ])));

        $cases = collect($locales)
            ->map(fn($l, $index) => "WHEN ? THEN $index")
            ->implode(' ');

        return $this->hasOne($this->getTranslationModelName(), $this->getTranslationRelationKey())
            ->whereIn($column, $locales)
            ->orderByRaw("CASE $column $cases END", $locales);
    }

    /**
     * Get the translation by locale key.
     *
     * @param string $key
     *
     * @return Model|null
     */
    protected function getTranslationByLocaleKey(string $key): ?Model
    {
        if ($this->relationLoaded('translation')) {
            if ($this->translation && $this->translation->getAttribute($this->getLocaleKey()) == $key) {
                return $this->translation;
            }

            if (!$this->relationLoaded('translations')) {
                return null;
            }
        }

        return $this->translations->firstWhere($this->getLocaleKey(), $key);
    }
}
