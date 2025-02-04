<?php

namespace App\Livewire\ThemeStore;

use App\Models\AppTheme;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class GetButton extends Component
{
    /**
     * The id of the theme.
     *
     * @var int $themeID
     */
    public int $themeID;

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
     * Prepare the component.
     *
     * @param int    $themeId
     * @param string $name
     *
     * @return void
     */
    public function mount(int $themeId, string $name): void
    {
        $this->themeID = $themeId;
        $this->name = $name;
    }

    public function getTheme(): void
    {
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
//        $appTheme->update([
//            'download_count' => $appTheme->download_count + 1
//        ]);

        $this->currentThemeID = $appTheme->id;
        $this->dispatch('theme-download', theme: [
            'id' => $appTheme->id,
            'css' => $appTheme->toCSS(),
        ]);
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
