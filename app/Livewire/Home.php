<?php

namespace App\Livewire;

use App\Models\ExploreCategory;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Home extends Component
{
    /**
     * Determines whether to load the page.
     *
     * @var bool $readyToLoad
     */
    public $readyToLoad = false;

    /**
     * Prepare the component.
     *
     * @return void
     */
    function mount(): void
    {
        //
    }

    /**
     * Sets the property to load the page.
     *
     * @return void
     */
    public function loadPage(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * The object containing the collection of explore category data.
     *
     * @return array|Collection
     */
    function getExploreCategoriesProperty(): array|Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return ExploreCategory::orderBy('position')
            ->get();
    }

    /**
     * Get the list of users.
     *
     * @return User[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getUsersProperty(): array|Collection
    {
        return User::whereIn('id', [363, 765])->get();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.home');
    }
}
