<?php

namespace App\Http\Controllers\Web;

use App\Enums\MediaCollection;
use App\Enums\StudioType;
use App\Events\ModelViewed;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetSearchIndexRequest;
use App\Models\Studio;
use App\Support\SearchCriteria;
use App\Support\SearchIndex;
use App\Traits\Controller\PaginatesTitles;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StudioController extends Controller
{
    use PaginatesTitles;

    /**
     * Show the studios index.
     *
     * @param GetSearchIndexRequest $request
     *
     * @return Application|Factory|View
     */
    public function index(GetSearchIndexRequest $request): Application|Factory|View
    {
        $criteria = SearchCriteria::fromRequest(
            $request,
            filters: Studio::webSearchFilters(),
            orders: Studio::webSearchOrders(),
            searchTypes: ['all' => __('All')] + StudioType::asSelectArray(),
        );

        $studios = (new SearchIndex(Studio::class, $criteria))
            ->hydrate(fn (Builder $query) => $query->with(['media']))
            ->letter('name')
            ->type('type')
            ->paginate()
            ->withQueryString();

        return view('studio.index', [
            'criteria' => $criteria,
            'studios' => $studios,
        ]);
    }

    /**
     * Send the visitor to a random studio.
     *
     * @return RedirectResponse
     */
    public function random(): RedirectResponse
    {
        return to_route('studios.details', Studio::randomFirst());
    }

    /**
     * Show a studio's page.
     *
     * @param Request $request
     * @param Studio  $studio
     *
     * @return Application|Factory|View
     */
    public function show(Request $request, Studio $studio): Application|Factory|View
    {
        ModelViewed::dispatch($studio, $request->ip());

        $user = $request->user();

        $studio->load(['media', 'tvRating'])
            ->when($user, function ($query, $user) use ($studio) {
                return $studio->loadMissing(['mediaRatings' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            });

        if ($user === null) {
            $studio->setRelation('mediaRatings', collect());
        }

        return view('studio.details', [
            'studio' => $studio,
            'userRating' => $studio->mediaRatings,
            'reviewBoxID' => str()->random(20),
            'bannerUrl' => $studio->getFirstMediaFullUrl(MediaCollection::Banner()),
        ]);
    }

    /**
     * Show the anime of a studio.
     *
     * @param Studio $studio
     *
     * @return Application|Factory|View
     */
    public function anime(Studio $studio): Application|Factory|View
    {
        return view('studio.anime', [
            'studio' => $studio->load(['media']),
            'titles' => $this->paginateTitles($studio->anime()),
        ]);
    }

    /**
     * Show the manga of a studio.
     *
     * @param Studio $studio
     *
     * @return Application|Factory|View
     */
    public function manga(Studio $studio): Application|Factory|View
    {
        return view('studio.manga', [
            'studio' => $studio->load(['media']),
            'titles' => $this->paginateTitles($studio->manga()),
        ]);
    }

    /**
     * Show the games of a studio.
     *
     * @param Studio $studio
     *
     * @return Application|Factory|View
     */
    public function games(Studio $studio): Application|Factory|View
    {
        return view('studio.games', [
            'studio' => $studio->load(['media']),
            'titles' => $this->paginateTitles($studio->games()),
        ]);
    }
}
