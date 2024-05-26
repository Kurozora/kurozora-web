<?php

namespace App\Livewire\Platform;

use App\Events\ModelViewed;
use App\Models\Platform;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Details extends Component
{
    /**
     * The object containing the platform data.
     *
     * @var Platform $platform
     */
    public Platform $platform;

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Prepare the component.
     *
     * @param Platform $platform
     *
     * @return void
     */
    public function mount(Platform $platform): void
    {
        // Call the ModelViewed event
        ModelViewed::dispatch($platform, request()->ip());

        $this->platform = $platform->load(['media']);
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
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.platform.details');
    }
}
