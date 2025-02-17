<?php declare(strict_types=1);

namespace App\Enums;

use App\Models\AppTheme;
use BenSampo\Enum\Enum;
use File;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Throwable;

/**
 * @method static KTheme KUROZORA()
 * @method static KTheme DAY()
 * @method static KTheme NIGHT()
 * @method static KTheme GRASS()
 * @method static KTheme SKY()
 * @method static KTheme SAKURA()
 * @method static KTheme OTHER()
 */
final class KTheme extends Enum
{
    const string KUROZORA = 'kurozora';
    const string DAY = 'day';
    const string NIGHT = 'night';
    const string GRASS = 'grass';
    const string SKY = 'sky';
    const string SAKURA = 'sakura';
    const string OTHER = 'other';

    /**
     * Get the default themes.
     *
     * @return KTheme[]
     */
    public static function defaultCases(): array
    {
        return [
            self::KUROZORA(),
            self::DAY(),
            self::NIGHT(),
            self::GRASS(),
            self::SKY(),
            self::SAKURA(),
        ];
    }

    /**
     * Get the human-readable name of the theme.
     *
     * @return string
     */
    public function stringValue(): string
    {
        return match ($this->value) {
            self::KUROZORA => 'Kurozora',
            self::DAY => 'Day',
            self::NIGHT => 'Night',
            self::GRASS => 'Grass',
            self::SKY => 'Sky',
            self::SAKURA => 'Sakura',
            default => '',
        };
    }

    /**
     * Get the theme's description.
     *
     * @param null|AppTheme $theme
     *
     * @return string
     */
    public function descriptionValue(?AppTheme $theme = null): string
    {
        return match ($this->value) {
            self::KUROZORA => __('The official Kurozora theme.'),
            self::DAY => __('Rise and shine.'),
            self::NIGHT => __('Easy on the eyes.'),
            self::GRASS => __('Get off my lawn!'),
            self::SKY => __('Cloudless.'),
            self::SAKURA => __('In full bloom.'),
            self::OTHER => $theme ? $this->formatDownloadCount($theme->download_count) : '',
        };
    }

    /**
     * Get the CSS color of the theme.
     *
     * @return string
     */
    public function colorValue(): string
    {
        return match ($this->value) {
            self::KUROZORA => '#353A50',
            self::DAY => '#FFFFFF',
            self::NIGHT => '#202020',
            self::GRASS => '#E5F0AC',
            self::SKY => '#CAF0FF',
            self::SAKURA => '#FFDCD2',
            self::OTHER => 'transparent',
        };
    }

    /**
     * Get the images for the theme.
     *
     * @return string[]
     */
    public function imageValues(): array
    {
        return match ($this->value) {
            self::KUROZORA => [
                asset('images/themes/default/Screenshot_1.webp'),
                asset('images/themes/default/Screenshot_2.webp'),
                asset('images/themes/default/Screenshot_3.webp'),
            ],
            self::DAY => [
                asset('images/themes/day/Screenshot_1.webp'),
                asset('images/themes/day/Screenshot_2.webp'),
                asset('images/themes/day/Screenshot_3.webp'),
            ],
            self::NIGHT => [
                asset('images/themes/night/Screenshot_1.webp'),
                asset('images/themes/night/Screenshot_2.webp'),
                asset('images/themes/night/Screenshot_3.webp'),
            ],
            self::GRASS => [
                asset('images/themes/grass/Screenshot_1.webp'),
                asset('images/themes/grass/Screenshot_2.webp'),
                asset('images/themes/grass/Screenshot_3.webp'),
            ],
            self::SKY => [
                asset('images/themes/sky/Screenshot_1.webp'),
                asset('images/themes/sky/Screenshot_2.webp'),
                asset('images/themes/sky/Screenshot_3.webp'),
            ],
            self::SAKURA => [
                asset('images/themes/sakura/Screenshot_1.webp'),
                asset('images/themes/sakura/Screenshot_2.webp'),
                asset('images/themes/sakura/Screenshot_3.webp'),
            ],
            self::OTHER => [],
        };
    }

    /**
     * Format download count for custom themes.
     *
     * @param int $count
     *
     * @return string
     */
    private function formatDownloadCount(int $count): string
    {
        return match ($count) {
            0 => __('New'),
            default => trans_choice('{1} :x Download|:x Downloads', $count, ['x' => $count])
        };
    }

    /**
     * Generates the CSS string for the theme.
     *
     * @return string
     * @throws Throwable
     */
    public function toCSS(): string
    {
        $view = view('themes.css', [
            'theme' => $this->json(),
        ]);

        return $view->render();
    }

    /**
     * Get the theme as a json object.
     *
     * @return object
     * @throws FileNotFoundException
     */
    private function json(): object
    {
        $filePath = match ($this->value) {
            self::DAY => resource_path('themes/day.json'),
            self::NIGHT => resource_path('themes/black.json'),
            self::GRASS => resource_path('themes/grass.json'),
            self::SKY => resource_path('themes/sky.json'),
            self::SAKURA => resource_path('themes/sakura.json'),
            default => resource_path('themes/default.json'),
        };

        return json_decode(File::get($filePath));
    }

    /**
     * Check if the given theme name matches this theme.
     *
     * @param string $theme
     *
     * @return bool
     */
    public function isEqual(string $theme): bool
    {
        return match ($this->value) {
            self::NIGHT => in_array($theme, [$this->stringValue(), 'Black']),
            default => $theme === $this->stringValue(),
        };
    }
}
