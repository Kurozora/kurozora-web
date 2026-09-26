<?php

namespace App\Livewire\Character;

use App\Livewire\BaseSearchIndex;
use App\Traits\Livewire\WithCharacterSearch;

class Index extends BaseSearchIndex
{
    use WithCharacterSearch;

    /**
     * The view to render.
     *
     * @var string $view
     */
    protected string $view = 'livewire.character.index';
}
