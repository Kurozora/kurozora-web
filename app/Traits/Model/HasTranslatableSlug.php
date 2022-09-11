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
     * Make sure the slug is valid.
     *
     * @return void
     * @throws AttributeIsNotTranslatable
     * @throws TranslationNotFoundAlias
     */
    protected function ensureValidSlugData(): void
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
     * Add slug to the model.
     *
     * @return void
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

        $this->{$slugField} = $slug;
    }

    /**
     * Generate a non-unique slug.
     *
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
     * Check whether the given key is a translatable attribute.
     *
     * @param string $key
     * @return bool
     */
    public function isTranslatableAttribute(string $key): bool
    {
        return in_array($key, $this->getTranslatableAttributes());
    }

    /**
     * Get all translatable attributes.
     *
     * @return array
     */
    public function getTranslatableAttributes(): array
    {
        return is_array($this->translatedAttributes)
            ? $this->translatedAttributes
            : [];
    }

    /**
     * Throw an error if the given local is invalid.
     *
     * @param string $locale
     * @return void
     * @throws TranslationNotFoundAlias
     */
    protected function guardAgainstNonExistentTranslation(string $locale): void
    {
        if (!$this->translate($locale)) {
            throw TranslationNotFoundAlias::make($locale, $this);
        }
    }

    /**
     * Throw an error if the given attribute is not translatable.
     *
     * @param string $key
     * @return void
     * @throws AttributeIsNotTranslatable
     */
    protected function guardAgainstUntranslatableAttribute(string $key): void
    {
        if (!$this->isTranslatableAttribute($key)) {
            throw AttributeIsNotTranslatable::make($key, $this);
        }
    }
}
