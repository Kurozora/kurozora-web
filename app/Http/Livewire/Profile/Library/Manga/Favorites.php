<?php

namespace App\Http\Livewire\Profile\Library\Manga;

use App\Models\Manga;
use App\Models\User;
use App\Traits\Livewire\WithMangaSearch;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;

class Favorites extends Component
{
    use WithMangaSearch;

    /**
     * The object containing the user data.
     *
     * @var User $user
     */
    public User $user;

    /**
     * Prepare the component.
     *
     * @param User $user
     * @return void
     */
    public function mount(User $user): void
    {
        $this->user = $user;
    }

    /**
     * Redirect the user to a random model.
     *
     * @return void
     */
    public function randomManga(): void
    {
        $manga = $this->user
            ->whereFavorited(Manga::class)
            ->inRandomOrder()
            ->first();
        $this->redirectRoute('manga.details', $manga);
    }

    /**
     * The computed search results property.
     *
     * @return ?LengthAwarePaginator
     */
    public function getSearchResultsProperty(): ?LengthAwarePaginator
    {
        // Order
        $orders = [];
        foreach ($this->order as $attribute => $order) {
            $attribute = str_replace(':', '.', $attribute);
            $selected = $order['selected'];

            if (!empty($selected)) {
                $orders[] = [
                    'column' => $attribute,
                    'direction' => $selected,
                ];
            }
        }

        // Filter
        $wheres = [];
        foreach ($this->filter as $attribute => $filter) {
            $attribute = str_replace(':', '.', $attribute);
            $selected = $filter['selected'];
            $type = $filter['type'];

            if ((is_numeric($selected) && $selected >= 0) || !empty($selected)) {
                $wheres[$attribute] = match ($type) {
                    'date' => Carbon::createFromFormat('Y-m-d', $selected)
                        ->setTime(0, 0)
                        ->timestamp,
                    'time' => $selected . ':00',
                    default => $selected,
                };
            }
        }

        // If no search was performed, return all manga
        if (empty($this->search) && empty($wheres) && empty($orders)) {
            $mangas = $this->user
                ->whereFavorited(Manga::class);
            return $mangas->paginate($this->perPage);
        }

        $mangaIDs = $this->user
            ->whereFavorited(Manga::class)
            ->limit(2000)
            ->pluck('favorable_id')
            ->toArray();
        $mangas = Manga::search($this->search);
        $mangas->whereIn('id', $mangaIDs);
        $mangas->wheres = $wheres;
        $mangas->orders = $orders;

        // Paginate
        return $mangas->paginate($this->perPage);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.library.manga.favorites');
    }
}