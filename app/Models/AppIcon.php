<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class AppIcon
{
    /**
     * The public directory holding the icon categories.
     *
     * @var string
     */
    const string DIRECTORY = 'images/icons';

    /**
     * The categories in display order.
     *
     * @var array
     */
    const array CATEGORY_ORDER = ['Default', 'Events'];

    /**
     * The icons in display order.
     *
     * @var array
     */
    const array ICON_ORDER = ['Kurozora', 'Kuro-chan', '6 Colors', '6 Colors Inverted', 'Kurozora Connect', 'Kurozora Support', 'Kurozora Support Inverted', 'iOS 6', 'Winter', 'Spring', 'Summer', 'Fall'];

    public string $category;
    public string $name;
    public ?string $light;
    public ?string $dark;
    public ?string $tinted;

    public function __construct(string $category, string $name, ?string $light, ?string $dark, ?string $tinted)
    {
        $this->category = $category;
        $this->name = $name;
        $this->light = $light;
        $this->dark = $dark;
        $this->tinted = $tinted;
    }

    /**
     * All app icons keyed by name and grouped by category.
     *
     * @return Collection
     */
    public static function all(): Collection
    {
        return collect(File::directories(public_path(self::DIRECTORY)))
            ->sortBy(fn (string $categoryPath) => self::position(basename($categoryPath), self::CATEGORY_ORDER))
            ->mapWithKeys(function (string $categoryPath) {
                $category = basename($categoryPath);

                return [
                    $category => collect(File::directories($categoryPath))
                        ->sortBy(fn (string $iconPath) => self::position(basename($iconPath), self::ICON_ORDER))
                        ->mapWithKeys(fn (string $iconPath) => [basename($iconPath) => self::fromDirectory($category, $iconPath)]),
                ];
            });
    }

    /**
     * The app icon with the given name.
     *
     * @param string $name
     *
     * @return AppIcon|null
     */
    public static function find(string $name): ?AppIcon
    {
        return self::all()
            ->flatten()
            ->first(fn (AppIcon $appIcon) => strcasecmp($appIcon->name, $name) === 0);
    }

    /**
     * Whether the icon is reserved for subscribers.
     *
     * @return bool
     */
    public function isPremium(): bool
    {
        return strtolower($this->category) !== 'default';
    }

    /**
     * The app icon described by the variants inside a directory.
     *
     * @param string $category
     * @param string $iconPath
     *
     * @return AppIcon
     */
    protected static function fromDirectory(string $category, string $iconPath): AppIcon
    {
        $name = basename($iconPath);
        $light = $dark = $tinted = null;

        foreach (File::files($iconPath) as $file) {
            $fileName = $file->getFilename();
            $url = '/' . self::DIRECTORY . '/' . $category . '/' . $name . '/' . $fileName;

            if (preg_match('/^(.+?)~dark\.webp$/', $fileName)) {
                $dark = $url;
            } elseif (preg_match('/^(.+?)~tinted\.webp$/', $fileName)) {
                $tinted = $url;
            } elseif (preg_match('/^(.+?)\.webp$/', $fileName)) {
                $light = $url;
            }
        }

        return new AppIcon($category, $name, $light ?? $dark ?? $tinted, $dark, $tinted);
    }

    /**
     * The sort position of a name inside an ordered list.
     *
     * @param string $name
     * @param array  $order
     *
     * @return int
     */
    protected static function position(string $name, array $order): int
    {
        $index = array_search($name, $order, true);

        return $index === false ? PHP_INT_MAX : $index;
    }

    /**
     * Get the best icon variant based on conditions.
     */
    public function getImage(?string $variant = null): ?string
    {
        if (!$variant) {
            $variant = $this->determineVariant();
        }

        return $this->{$variant} ?? $this->light;
    }

    /**
     * Determine which variant to use by default.
     */
    protected function determineVariant(): string
    {
        // - TODO: Update based on system/user preference
        return 'light'; // light/dark/tinted
    }
}
