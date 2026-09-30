<?php

namespace App\Http\Controllers\Web;

use App\Events\ModelViewed;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetSearchIndexRequest;
use App\Models\Platform;
use App\Support\SearchCriteria;
use App\Support\SearchIndex;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PlatformController extends Controller
{
    /**
     * Show the platforms index.
     *
     * @param GetSearchIndexRequest $request
     *
     * @return Application|Factory|View
     */
    public function index(GetSearchIndexRequest $request): Application|Factory|View
    {
        $criteria = SearchCriteria::fromRequest(
            $request,
            filters: Platform::webSearchFilters(),
            orders: Platform::webSearchOrders(),
        );

        $platforms = (new SearchIndex(Platform::class, $criteria))
            ->hydrate(fn (Builder $query) => $query->with(['media', 'translation']))
            ->letter('original_name')
            ->paginate()
            ->withQueryString();

        return view('platform.index', [
            'criteria' => $criteria,
            'platforms' => $platforms,
        ]);
    }

    /**
     * Send the visitor to a random platform.
     *
     * @return RedirectResponse
     */
    public function random(): RedirectResponse
    {
        return to_route('platforms.details', Platform::randomFirst());
    }

    /**
     * Show a platform's page.
     *
     * @param Request  $request
     * @param Platform $platform
     *
     * @return Application|Factory|View
     */
    public function show(Request $request, Platform $platform): Application|Factory|View
    {
        ModelViewed::dispatch($platform, $request->ip());

        return view('platform.details', [
            'platform' => $platform->load(['media']),
        ]);
    }
}
