<?php

namespace App\Livewire\ThemeStore;

use App\Enums\KTheme;
use App\Models\AppTheme;
use App\Traits\Livewire\PresentsSubscriptionSheet;
use App\Traits\Livewire\WithThemeStoreSearch;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class Index extends Component
{
    use PresentsSubscriptionSheet;
    use WithThemeStoreSearch {
        getSearchResultsProperty as protected parentGetSearchResultsProperty;
    }

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount(): void
    {
    }

    /**
     * Sets the property to load the page.
     *
     * @return void
     */
    public function loadPage(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * The computed search results property.
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getSearchResultsProperty(): Collection|LengthAwarePaginator
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->parentGetSearchResultsProperty();
    }

    /**
     * Apply the given theme for the visitor.
     *
     * @param string $id
     *
     * @return void
     */
    #[On('theme-get')]
    public function getTheme(string $id): void
    {
        if (!is_numeric($id)) {
            $theme = KTheme::fromValue(strtolower($id));

            $this->dispatch('theme-download', theme: [
                'id' => $theme->value,
                'css' => $theme->toCSS(),
            ]);
            $this->dispatch('theme-changed', id: $theme->value);
            return;
        }

        $user = auth()->user();

        if ($user === null) {
            $this->redirectRoute('sign-in');
            return;
        }

        if (!($user->is_subscribed || $user->is_pro)) {
            $this->presentSubscriptionSheet(
                title: __('Dynamic Themes'),
                message: __('Choose from a range of themes to create a look that reflects your personality and style.'),
                tipJarEnabled: true
            );
            return;
        }

        $appTheme = AppTheme::findOrFail($id);

        $appTheme->update([
            'download_count' => $appTheme->download_count + 1
        ]);

        $this->dispatch('theme-download', theme: [
            'id' => $appTheme->id,
            'css' => $appTheme->toCSS(),
        ]);
        $this->dispatch('theme-changed', id: (string) $appTheme->id);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.theme-store.index');
    }
}
