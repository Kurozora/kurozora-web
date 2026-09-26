<?php

namespace App\Livewire;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

abstract class BaseMediaIndex extends Component
{
    use WithPagination;

    /**
     * The view to render.
     *
     * @var string $view
     */
    protected string $view = '';

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
     * The query returning the titles listed on the page.
     *
     * @return Builder|Relation
     */
    abstract protected function query(): Builder|Relation;

    /**
     * Get the titles property.
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getTitlesProperty(): Collection|LengthAwarePaginator
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->query()
            ->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating'])
            ->when(auth()->user(), function ($query, $user) {
                $query->with(['library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            })
            ->paginate(25);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view($this->view);
    }
}
