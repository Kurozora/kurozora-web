<?php

namespace App\Traits\Livewire;

use App\Models\Character;
use Carbon\Month;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Laravel\Scout\Builder as ScoutBuilder;

trait WithCharacterSearch
{
    use WithSearch;

    /**
     * The model used for searching.
     *
     * @var string $searchModel
     */
    public static string $searchModel = Character::class;

    /**
     * The column used for the letter index query.
     *
     * @return string
     */
    protected function letterIndexColumn(): string
    {
        return 'name';
    }

    /**
     * The column used for the letter index query.
     *
     * @return string
     */
    protected function typeColumn(): string
    {
        return 'birth_month';
    }

    /**
     * Redirect the user to a random character.
     *
     * @return void
     */
    public function randomCharacter(): void
    {
        $character = Character::inRandomOrder()->first();
        $this->redirectRoute('characters.details', $character);
    }

    /**
     * Build a 'search index' query for the given resource.
     *
     * @param EloquentBuilder $query
     * @return EloquentBuilder
     */
    public function searchIndexQuery(EloquentBuilder $query): EloquentBuilder
    {
        return $query
            ->with(['media', 'translation']);
    }

    /**
     * Build a 'search' query for the given resource.
     *
     * @param ScoutBuilder $query
     * @return ScoutBuilder
     */
    public function searchQuery(ScoutBuilder $query): ScoutBuilder
    {
        return $query->query(function (EloquentBuilder $query) {
            $query->with(['media', 'translation']);
        });
    }

    /**
     * Set the orderable attributes of the model.
     *
     * @return array
     */
    public function setOrderableAttributes(): array
    {
        return Character::webSearchOrders();
    }

    /**
     * Set the filterable attributes of the model.
     *
     * @return array
     */
    public function setFilterableAttributes(): array
    {
        return Character::webSearchFilters();
    }

    /**
     * Set the search types of the model.
     *
     * @return array
     */
    public function setSearchTypes(): array
    {
        // Since array keys start from 0, even when casting the numbers to a string,
        // we prepend a temp value to the array and then unset it, preserving the
        // correct keys.
        $months = collect(Month::cases())->flatMap(function ($month) {
            return [
                $month->value => $month->name
            ];
        })
            ->unshift('temp')
            ->prepend(__('All'), 'all');
        $months->offsetUnset(0);
        return $months->toArray();
    }
}
