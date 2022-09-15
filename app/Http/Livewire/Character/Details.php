<?php

namespace App\Http\Livewire\Character;

use App\Events\CharacterViewed;
use App\Models\Character;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Details extends Component
{
    /**
     * The object containing the character data.
     *
     * @var Character $character
     */
    public Character $character;

    /**
     * Prepare the component.
     *
     * @param Character $character
     *
     * @return void
     */
    public function mount(Character $character): void
    {
        // Call the CharacterViewed event
        CharacterViewed::dispatch($character);

        $character->generateSlug();
        $character->save();
        $this->character = $character;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.character.details', [
            'characterAnime' => $this->character->getAnime(Character::MAXIMUM_RELATIONSHIPS_LIMIT),
            'characterPeople' => $this->character->getPeople(Character::MAXIMUM_RELATIONSHIPS_LIMIT),
        ]);
    }
}
