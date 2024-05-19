<?php

namespace App\Livewire\ThemeStore;

use App\Models\AppTheme;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use JetBrains\PhpStorm\NoReturn;
use Livewire\Component;

class ThemeStoreRoller extends Component
{
    /**
     * The component's state.
     *
     * @var array $state
     */
    public array $state = [];

    /**
     * The element that is currently selected.
     *
     * @var ?string $selectedElement
     */
    public ?string $selectedElement = null;

    /**
     * A boolean indicating whether the color picker should be shown.
     *
     * @var bool $showColorPicker
     */
    public bool $showColorPicker = false;

    /**
     * Prepare the component.
     *
     * @return void
     */
    #[NoReturn]
    public function mount()
    {
        $appTheme = AppTheme::first()->getAttributes();
        $this->state = array_map(function() { return null; }, $appTheme);
    }

    /**
     * Toggles the statusbar style between light and dark.
     */
    public function switchStatusBarStyle()
    {
        if ($this->state['ui_status_bar_style'] === '#ffffff') {
            $this->state['ui_status_bar_style'] = '#000000';
        } else {
            $this->state['ui_status_bar_style'] = '#ffffff';
        }
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Factory|View|Application
    {
        return view('livewire.theme-store.theme-store-roller');
    }
}
