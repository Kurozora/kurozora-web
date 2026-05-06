<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;

/**
 * @method static ParentalGuideCategory SexAndNudity()
 * @method static ParentalGuideCategory ViolenceAndGore()
 * @method static ParentalGuideCategory Profanity()
 * @method static ParentalGuideCategory AlcoholDrugsAndSmoking()
 * @method static ParentalGuideCategory FrighteningAndIntenseScenes()
 *
 * @template TValue
 */
final class ParentalGuideCategory extends Enum
{
    const int SexAndNudity = 1;
    const int ViolenceAndGore = 2;
    const int Profanity = 3;
    const int AlcoholDrugsAndSmoking = 4;
    const int FrighteningAndIntenseScenes = 5;

    /**
     * The column name for the enum value.
     *
     * @var string $columnName
     */
    public string $columnName;

    /**
     * Construct an Enum instance.
     *
     * @param TValue $enumValue
     *
     * @throws InvalidEnumMemberException
     */
    public function __construct(mixed $enumValue)
    {
        parent::__construct($enumValue);

        $this->columnName = ParentalGuideCategory::getColumnName($enumValue);
    }

    /**
     * Get the localized description of a value.
     *
     * This works only if localization is enabled
     * for the enum and if the key exists in the lang file.
     *
     * @param TValue $value
     *
     * @return string|null
     */
    protected static function getLocalizedDescription(mixed $value): ?string
    {
        return match ($value) {
            self::SexAndNudity => __('Sex & Nudity'),
            self::ViolenceAndGore => __('Violence & Gore'),
            self::Profanity => __('Profanity'),
            self::AlcoholDrugsAndSmoking => __('Alcohol, Drugs & Smoking'),
            self::FrighteningAndIntenseScenes => __('Frightening & Intense Scenes'),
            default => ParentalGuideCategory::getLocalizedDescription($value),
        };
    }

    /**
     * Get the description for an enum value.
     *
     * @param TValue $value
     *
     * @return string
     * @throws InvalidEnumMemberException
     */
    public static function getColumnName(mixed $value): string
    {
        return match ($value) {
            self::SexAndNudity => 'sex_nudity',
            self::ViolenceAndGore => 'violence_gore',
            self::Profanity => 'profanity',
            self::AlcoholDrugsAndSmoking => 'alcohol_drugs_smoking',
            self::FrighteningAndIntenseScenes => 'frightening_intense',
            default => str(self::getFriendlyName(ParentalGuideCategory::getKey($value)))->slug('_'),
        };
    }

    /**
     * Whether the category accepts a depiction value.
     *
     * @return bool
     */
    public function supportsDepiction(): bool
    {
        return match ($this->value) {
            self::SexAndNudity,
            self::ViolenceAndGore,
            self::FrighteningAndIntenseScenes => true,
            default => false,
        };
    }

    /**
     * Resolve a category from its kebab-case key slug (e.g. `sex-and-nudity`).
     *
     * @param string $slug
     *
     * @return ParentalGuideCategory|null
     */
    public static function fromSlug(string $slug): ?ParentalGuideCategory
    {
        return array_find(
            self::getInstances(),
            fn($instance) => str($instance->key)->kebab()->value() === $slug
        );
    }

    /**
     * The slug for the defined categories.
     *
     * @return array<int, string>
     */
    public static function slugs(): array
    {
        static $slugs = null;

        if ($slugs === null) {
            $slugs = array_map(
                fn(ParentalGuideCategory $instance) => str($instance->key)->kebab()->value(),
                self::getInstances()
            );
        }

        return $slugs;
    }

    /**
     * The URL slug for this category.
     *
     * @return string
     */
    public function urlSlug(): string
    {
        return str($this->key)->kebab()->value();
    }
}
