<?php

namespace App\Livewire\Character;

use App\Livewire\BaseReviews;
use App\Models\Character;
use Illuminate\Database\Eloquent\Model;

class Reviews extends BaseReviews
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
    protected string $view = 'livewire.character.reviews';

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
     * The character the reviews belong to.
     *
     * @return Model
     */
    protected function reviewable(): Model
    {
        return $this->character;
    }
}
