<?php

namespace App\Livewire\Leaderboards;

use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class Reputation extends Component
{
    use WithPagination;

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

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
     * The reputation leaderboard, paginated and ranked from highest to lowest reputation.
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getUsersProperty(): Collection|LengthAwarePaginator
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return User::query()
            ->with(['media'])
            ->withCount(['followers'])
            ->when(auth()->user(), function ($query, $user) {
                $query->withExists(['followers as isFollowed' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            })
            ->orderByDesc('reputation_count')
            ->orderBy('id')
            ->paginate(100);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.leaderboards.reputation');
    }
}
