<?php

namespace App\Http\Livewire\Person;

use App\Models\Person;
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
     * Get the manga property.
     *
     * @return LengthAwarePaginator
     */
    public function getMangaProperty(): LengthAwarePaginator
    {
        return $this->person->manga()->paginate(25);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.person.manga');
    }
}
