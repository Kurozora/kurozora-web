<?php

namespace App\Http\Controllers\Web;

use App\Events\ModelViewed;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetSearchIndexRequest;
use App\Models\Character;
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

class CharacterController extends Controller
{
    use PaginatesTitles;

    /**
     * Show the characters index.
     *
     * @param GetSearchIndexRequest $request
     *
     * @return Application|Factory|View
     */
    public function index(GetSearchIndexRequest $request): Application|Factory|View
    {
        $criteria = SearchCriteria::fromRequest(
            $request,
            filters: Character::webSearchFilters(),
            orders: Character::webSearchOrders(),
            searchTypes: ['all' => __('All')] + collect(Month::cases())->mapWithKeys(fn (Month $month) => [$month->value => $month->name])->all(),
        );

        $characters = (new SearchIndex(Character::class, $criteria))
            ->hydrate(fn (Builder $query) => $query->with(['media', 'translation']))
            ->letter('name', 'translations')
            ->type('birth_month')
            ->paginate()
            ->withQueryString();

        return view('character.index', [
            'criteria' => $criteria,
            'characters' => $characters,
        ]);
    }

    /**
     * Send the visitor to a random character.
     *
     * @return RedirectResponse
     */
    public function random(): RedirectResponse
    {
        return to_route('characters.details', Character::randomFirst());
    }

    /**
     * Show a character's page.
     *
     * @param Request   $request
     * @param Character $character
     *
     * @return Application|Factory|View
     */
    public function show(Request $request, Character $character): Application|Factory|View
    {
        ModelViewed::dispatch($character, $request->ip());

        $user = $request->user();

        $character->load(['media'])
            ->when($user, function ($query, $user) use ($character) {
                return $character->loadMissing(['mediaRatings' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            });

        if ($user === null) {
            $character->setRelation('mediaRatings', collect());
        }

        return view('character.details', [
            'character' => $character,
            'userRating' => $character->mediaRatings,
            'reviewBoxID' => str()->random(20),
        ]);
    }

    /**
     * Show the anime a character appears in.
     *
     * @param Character $character
     *
     * @return Application|Factory|View
     */
    public function anime(Character $character): Application|Factory|View
    {
        return view('character.anime', [
            'character' => $character->load(['media']),
            'titles' => $this->paginateTitles($character->anime()),
        ]);
    }

    /**
     * Show the manga a character appears in.
     *
     * @param Character $character
     *
     * @return Application|Factory|View
     */
    public function manga(Character $character): Application|Factory|View
    {
        return view('character.manga', [
            'character' => $character->load(['media']),
            'titles' => $this->paginateTitles($character->manga()),
        ]);
    }

    /**
     * Show the games a character appears in.
     *
     * @param Character $character
     *
     * @return Application|Factory|View
     */
    public function games(Character $character): Application|Factory|View
    {
        return view('character.games', [
            'character' => $character->load(['media']),
            'titles' => $this->paginateTitles($character->games()),
        ]);
    }

    /**
     * Show the people who voiced a character.
     *
     * @param Character $character
     *
     * @return Application|Factory|View
     */
    public function people(Character $character): Application|Factory|View
    {
        return view('character.people', [
            'character' => $character->load(['media']),
            'people' => $character->people()
                ->with(['media'])
                ->paginate(25),
        ]);
    }
}
