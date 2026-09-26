<?php

namespace App\Livewire\Character;

use App\Livewire\BaseMediaIndex;
use App\Models\Character;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class Manga extends BaseMediaIndex
{
    /**
     * The object containing the character data.
     *
     * @var Character $character
     */
    public Character $character;

    /**
     * The view to render.
     *
     * @var string $view
     */
    protected string $view = 'livewire.character.manga';

    /**
     * Prepare the component.
     *
     * @param Character $character
     *
     * @return void
     */
    public function mount(Character $character): void
    {
        $this->character = $character->load(['media']);
    }

    /**
     * The query returning the manga the character appears in.
     *
     * @return Builder|Relation
     */
    protected function query(): Builder|Relation
    {
        return $this->character->manga();
    }
}
