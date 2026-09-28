<?php

namespace App\Livewire;

use App\Enums\SearchSource;
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
    public function mount(): void
    {
        //
    }

    /**
     * The object containing the collection of explore category data.
     *
     * @return array|Collection
     */
    public function getExploreCategoriesProperty(): array|Collection
    {
        return ExploreCategory::orderBy('position')
            ->get();
    }

    /**
     * The models shown in each explore category.
     *
     * @return Collection
     */
    public function getExploreCategoryItemsProperty(): Collection
    {
        return ExploreCategory::itemsFor($this->exploreCategories);
    }

    /**
     * Get the list of users.
     *
     * @return User[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getUsersProperty(): array|Collection
    {
        $userIDs = [385, 363, 765];
        $users = User::whereIn('id', $userIDs)
            ->get();
        $orderedUsers = [];
        foreach ($userIDs as $key) {
            if ($user = $users->firstWhere('id', $key)) {
                $orderedUsers[$key] = $user;
            }
        }

        return $orderedUsers;
    }

    /**
     * The Schema.org JSON-LD payload for this page.
     */
    public function getSchemaProperty(): array
    {
        return [
            '@type' => 'WebSite',
            'url' => config('app.url'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => route('search.index') . '?q={search_term_string}&src=' . SearchSource::Google,
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
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
