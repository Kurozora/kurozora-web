<?php

namespace App\Http\Controllers\Web;

use App\Events\ModelViewed;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetSearchIndexRequest;
use App\Models\Person;
use App\Support\SearchCriteria;
use App\Support\SearchIndex;
use App\Traits\Controller\PaginatesTitles;
use Carbon\Month;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PersonController extends Controller
{
    use PaginatesTitles;

    /**
     * Show the people index.
     *
     * @param GetSearchIndexRequest $request
     *
     * @return Application|Factory|View
     */
    public function index(GetSearchIndexRequest $request): Application|Factory|View
    {
        $criteria = SearchCriteria::fromRequest(
            $request,
            filters: Person::webSearchFilters(),
            orders: Person::webSearchOrders(),
            searchTypes: ['all' => __('All')] + collect(Month::cases())->mapWithKeys(fn (Month $month) => [$month->value => $month->name])->all(),
        );

        $people = (new SearchIndex(Person::class, $criteria))
            ->hydrate(fn (Builder $query) => $query->with(['media']))
            ->letter('first_name')
            ->type('birth_month', fn (Builder $query, int|string $month) => $query->whereMonth('birthdate', '=', $month))
            ->paginate()
            ->withQueryString();

        return view('person.index', [
            'criteria' => $criteria,
            'people' => $people,
        ]);
    }

    /**
     * Send the visitor to a random person.
     *
     * @return RedirectResponse
     */
    public function random(): RedirectResponse
    {
        return to_route('people.details', Person::randomFirst());
    }

    /**
     * Show a person's page.
     *
     * @param Request $request
     * @param Person  $person
     *
     * @return Application|Factory|View
     */
    public function show(Request $request, Person $person): Application|Factory|View
    {
        ModelViewed::dispatch($person, $request->ip());

        $user = $request->user();

        $person->load(['media'])
            ->when($user, function ($query, $user) use ($person) {
                return $person->loadMissing(['mediaRatings' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            });

        if ($user === null) {
            $person->setRelation('mediaRatings', collect());
        }

        return view('person.details', [
            'person' => $person,
            'userRating' => $person->mediaRatings,
            'reviewBoxID' => str()->random(20),
        ]);
    }

    /**
     * Show the anime a person worked on.
     *
     * @param Person $person
     *
     * @return Application|Factory|View
     */
    public function anime(Person $person): Application|Factory|View
    {
        return view('person.anime', [
            'person' => $person->load(['media']),
            'titles' => $this->paginateTitles($person->anime()),
        ]);
    }

    /**
     * Show the manga a person worked on.
     *
     * @param Person $person
     *
     * @return Application|Factory|View
     */
    public function manga(Person $person): Application|Factory|View
    {
        return view('person.manga', [
            'person' => $person->load(['media']),
            'titles' => $this->paginateTitles($person->manga()),
        ]);
    }

    /**
     * Show the games a person worked on.
     *
     * @param Person $person
     *
     * @return Application|Factory|View
     */
    public function games(Person $person): Application|Factory|View
    {
        return view('person.games', [
            'person' => $person->load(['media']),
            'titles' => $this->paginateTitles($person->games()),
        ]);
    }

    /**
     * Show the characters a person voiced.
     *
     * @param Person $person
     *
     * @return Application|Factory|View
     */
    public function characters(Person $person): Application|Factory|View
    {
        return view('person.characters', [
            'person' => $person->load(['media']),
            'characters' => $person->characters()
                ->with(['media', 'translation'])
                ->paginate(25),
        ]);
    }
}
