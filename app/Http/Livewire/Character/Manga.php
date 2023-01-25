<?php

namespace App\Http\Livewire\Character;

use App\Models\Character;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class Manga extends Component
{
    use WithPagination;

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
        $this->character = $character;
    }

    /**
     * Get the manga property.
     *
     * @return LengthAwarePaginator
     */
    public function getMangaProperty(): LengthAwarePaginator
    {
        return $this->character->manga()->paginate(25);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.character.manga');
    }
}
