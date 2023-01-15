<?php

namespace App\Http\Livewire;

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
     * Prepare the component.
     *
     * @return void
     */
    function mount(): void
    {
        //
    }

    /**
     * The object containing the collection of explore category data.
     *
     * @return array|Collection
     */
    function getExploreCategoriesProperty(): array|Collection
    {
        return ExploreCategory::orderBy('position')->get();
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
