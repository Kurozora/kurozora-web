<?php

namespace App\Livewire\Studio;

use App\Livewire\BaseSearchIndex;
use App\Traits\Livewire\WithStudioSearch;

class Index extends BaseSearchIndex
{
    use WithStudioSearch;

    /**
     * The view to render.
     *
     * @var string $view
     */
    protected string $view = 'livewire.studio.index';
}
