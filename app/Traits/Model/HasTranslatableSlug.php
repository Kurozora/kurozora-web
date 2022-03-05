<?php

namespace App\Traits\Model;

use App\Exceptions\AttributeIsNotTranslatable;
use App\Exceptions\TranslationNotFound as TranslationNotFoundAlias;
use Spatie\Sluggable\Exceptions\InvalidOption;
use Spatie\Sluggable\HasSlug;

trait HasTranslatableSlug
{
    use HasSlug;

    /**
     * In which local to search for the translatable attribute.
     *
     * @return string
     */
    public function slugLocale(): string
    {
        return 'en';
    }

    /**
     * @throws AttributeIsNotTranslatable
     * @throws TranslationNotFoundAlias
     */
    protected function ensureValidSlugData()
    {
        $fieldNames = $this->slugOptions->generateSlugFrom;

        // Checks whether the chosen field is in the translatable array.
        foreach ($fieldNames as $fieldName) {
            $this->guardAgainstUntranslatableAttribute($fieldName);
        }

        // Checks whether the chosen locale is present in the translations of the model.
        $this->guardAgainstNonExistentTranslation($this->slugLocale());
    }

    /**
     * @throws AttributeIsNotTranslatable
     * @throws InvalidOption
     * @throws TranslationNotFoundAlias
     */
    protected function addSlug(): void
    {
        $this->ensureValidSlugOptions();
        $this->ensureValidSlugData();

        $slug = $this->generateNonUniqueSlug();

        if ($this->slugOptions->generateUniqueSlugs) {
            $slug = $this->makeSlugUnique($slug);
        }

        $slugField = $this->slugOptions->slugField;

        $this->$slugField = $slug;
    }

    /**
     * @return string
     */
    protected function generateNonUniqueSlug(): string
    {
        $slugField = $this->slugOptions->slugField;

        if ($this->hasCustomSlugBeenUsed() && ! empty($this->$slugField)) {
            return $this->$slugField;
        }

        return str($this->getSlugSourceString())->slug($this->slugOptions->slugSeparator, $this->slugOptions->slugLanguage);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function isTranslatableAttribute(string $key) : bool
    {
        return in_array($key, $this->getTranslatableAttributes());
    }

    /**
     * @return array
     */
    public function getTranslatableAttributes() : array
    {
        return is_array($this->translatedAttributes)
            ? $this->translatedAttributes
            : [];
    }

    /**
     * @throws TranslationNotFoundAlias
     */
    protected function guardAgainstNonExistentTranslation(string $locale)
    {
        if (!$this->translate($locale)) {
            throw TranslationNotFoundAlias::make($locale, $this);
        }
    }

    /**
     * @param string $key
     * @throws AttributeIsNotTranslatable
     */
    protected function guardAgainstUntranslatableAttribute(string $key)
    {
        if (!$this->isTranslatableAttribute($key)) {
            throw AttributeIsNotTranslatable::make($key, $this);
        }
    }
}
