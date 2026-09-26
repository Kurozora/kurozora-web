<?php

namespace App\Livewire\Platform;

use App\Livewire\BaseSearchIndex;
use App\Traits\Livewire\WithPlatformSearch;

class Index extends BaseSearchIndex
{
    use WithPlatformSearch;

    /**
     * The view to render.
     *
     * @var string $view
     */
    protected string $view = 'livewire.platform.index';
}
