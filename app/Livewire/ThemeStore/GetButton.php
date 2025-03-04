<?php

namespace App\Livewire\ThemeStore;

use App\Enums\KTheme;
use App\Models\AppTheme;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Throwable;

class GetButton extends Component
{
    /**
     * The id of the theme.
     *
     * @var int|string $themeID
     */
    public int|string $themeID;

    /**
     * The id of the current theme.
     *
     * @var int|string $currentThemeID
     */
    public int|string $currentThemeID;

    /**
     * The name of the theme.
     *
     * @var string $name
     */
    public string $name;

    /**
     * The component's listeners.
     *
     * @var array
     */
    protected $listeners = [
        'theme-changed' => 'handleThemeChanged',
    ];

    /**
     * Prepare the component.
     *
     * @param int|string $themeId
     * @param string     $name
     *
     * @return void
     */
    public function mount(int|string $themeId, string $name): void
    {
        $this->themeID = $themeId;
        $this->name = $name;
    }

    /**
     * Handles the incoming `theme-changed` event.
     *
     * @param int|string $currentThemeID
     *
     * @return void
     */
    public function handleThemeChanged(int|string $currentThemeID): void
    {
        $this->currentThemeID = $currentThemeID;
    }

    /**
     * Download the theme if user is allowed to have it.
     *
     * @return void
     * @throws Throwable
     */
    public function getTheme(): void
    {
        if (!is_numeric($this->themeID)) {
            $theme = KTheme::fromValue(strtolower($this->themeID));
            $this->currentThemeID = $theme->value;
            $this->dispatch('theme-download', theme: [
                'id' => $theme->value,
                'css' => $theme->toCSS(),
            ]);
            $this->dispatch('theme-changed', currentThemeID: $this->currentThemeID)->to('theme-store.get-button');
            return;
        }

        // Get the auth user
        $user = auth()?->user();

        if ($user === null) {
            $this->redirectRoute('sign-in');
            return;
        }

        if (!($user->is_subscribed || $user->is_pro)) {
            throw new AuthorizationException(__('Premium platform themes are only available to pro and subscribed users.'));
        }

        $appTheme = AppTheme::find($this->themeID);
        // Increment the download count of the theme
        $appTheme->update([
            'download_count' => $appTheme->download_count + 1
        ]);

        $this->currentThemeID = $appTheme->id;
        $this->dispatch('theme-download', theme: [
            'id' => $appTheme->id,
            'css' => $appTheme->toCSS(),
        ]);
        $this->dispatch('theme-changed', currentThemeID: $this->currentThemeID)->to('theme-store.get-button');
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.theme-store.get-button');
    }
}
