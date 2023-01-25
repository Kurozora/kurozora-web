<?php

namespace App\Http\Livewire\Person;

use App\Models\Person;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class Anime extends Component
{
    use WithPagination;

    /**
     * The object containing the person data.
     *
     * @var Person $person
     */
    public Person $person;

    /**
     * Prepare the component.
     *
     * @param Person $person
     *
     * @return void
     */
    public function mount(Person $person): void
    {
        $this->person = $person;
    }

    /**
     * Get the anime property.
     *
     * @return LengthAwarePaginator
     */
    public function getAnimeProperty(): LengthAwarePaginator
    {
        return $this->person->anime()->paginate(25);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.person.anime');
    }
}
