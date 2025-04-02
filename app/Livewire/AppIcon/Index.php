<?php

namespace App\Livewire\AppIcon;

use App\Models\AppIcon;
use App\Traits\Livewire\PresentsAlert;
use File;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Index extends Component
{
    use PresentsAlert;

    /**
     * The currently used app icon's name.
     *
     * @var string
     */
    public string $currentAppIconName = 'Kurozora';

    /**
     * The icons directory path.
     *
     * @var string
     */
    protected string $iconPath = 'images/icons';

    /**
     * The order of the categories.
     *
     * @var string[]
     */
    protected array $categoryOrder = ['Default', 'Events'];

    /**
     * The order of the icons.
     *
     * @var string[]
     */
    protected array $iconOrder = ['Kurozora', 'Kuro-chan'];

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount(): void
    {
    }

    /**
     * The computed search results property.
     *
     * @return Collection
     */
    public function getSearchResultsProperty(): Collection
    {
        return $this->listAppIcons();
    }

    /**
     * Get a list of all app icons with their variants.
     *
     * @return Collection
     */
    public function listAppIcons(): Collection
    {
        $categories = collect(File::directories(public_path($this->iconPath)));

        return $categories->sortBy(function ($categoryPath) {
            $category = basename($categoryPath);
            $index = array_search($category, $this->categoryOrder);
            return $index === false ? PHP_INT_MAX : $index;
        })->mapWithKeys(function ($categoryPath) {
            $category = basename($categoryPath);
            $icons = collect(File::directories($categoryPath));

            $sortedIcons = $icons->sortBy(function ($iconPath) use ($category) {
                $iconName = basename($iconPath);
                $index = array_search($iconName, $this->iconOrder);
                return $index === false ? PHP_INT_MAX : $index;
            });

            return [
                $category => $sortedIcons->mapWithKeys(function ($iconPath) use ($category) {
                    $iconName = basename($iconPath);
                    $files = collect(File::files($iconPath));

                    $light = $dark = $tinted = null;

                    $files->each(function ($file) use ($category, $iconName, &$light, &$dark, &$tinted) {
                        $fileName = $file->getFilename();

                        if (preg_match('/^(.+?)~dark\.webp$/', $fileName)) {
                            $dark = "/$this->iconPath/$category/$iconName/$fileName";
                        } elseif (preg_match('/^(.+?)~tinted\.webp$/', $fileName)) {
                            $tinted = "/$this->iconPath/$category/$iconName/$fileName";
                        } elseif (preg_match('/^(.+?)\.webp$/', $fileName)) {
                            $light = "/$this->iconPath/$category/$iconName/$fileName";
                        }
                    });

                    $light ??= $dark ?? $tinted;

                    return [$iconName => new AppIcon($category, $iconName, $light, $dark, $tinted)];
                }),
            ];
        });
    }

    /**
     * Get a specific app icon model by name.
     *
     * @param string $name
     *
     * @return null|AppIcon
     */
    public function getAppIcon(string $name): ?AppIcon
    {
        $icons = $this->listAppIcons();

        return $icons[$name] ?? null;
    }

    /**
     * Set a specific app icon by name.
     *
     * @param string $name
     *
     * @return void
     */
    public function setAppIcon(string $name): void
    {
        /** @var AppIcon $appIcon */
        $appIcon = $this->listAppIcons()
            ->flatten()
            ->firstWhere('name', '=', $name);

        if (strtolower($appIcon->category) !== 'default') {
            // Get the auth user
            $user = auth()?->user();

            if ($user === null) {
                $this->redirectRoute('sign-in');
                return;
            }

            if (!($user->is_subscribed || $user->is_pro)) {
                $this->presentAlert(
                    title: __('That’s Unfortunate'),
                    message: __('Premium app icons are only available to pro and subscribed users 🧐'),
                );
                return;
            }
        }

        $this->currentAppIconName = $name;
        $this->dispatch('app-icon-changed', appIcon: [
            'name' => $name,
            'url' => $appIcon->getImage()
        ]);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.app-icon.index');
    }
}
