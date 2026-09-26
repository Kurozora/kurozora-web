<?php

namespace App\Livewire\Person;

use App\Livewire\BaseSearchIndex;
use App\Traits\Livewire\WithPersonSearch;

class Index extends BaseSearchIndex
{
    use WithPersonSearch;

    /**
     * The view to render.
     *
     * @var string $view
     */
    protected string $view = 'livewire.person.index';
}
