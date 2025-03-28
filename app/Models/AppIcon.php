<?php

namespace App\Models;

class AppIcon
{
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
