<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetSearchIndexRequest;
use App\Models\AppTheme;
use App\Support\SearchCriteria;
use App\Support\SearchIndex;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class ThemeStoreController extends Controller
{
    /**
     * Show the theme store.
     *
     * @param GetSearchIndexRequest $request
     *
     * @return Application|Factory|View
     */
    public function index(GetSearchIndexRequest $request): Application|Factory|View
    {
        $criteria = SearchCriteria::fromRequest(
            $request,
            filters: AppTheme::webSearchFilters(),
            orders: AppTheme::webSearchOrders(),
        );

        $themes = (new SearchIndex(AppTheme::class, $criteria))
            ->hydrate(function (Builder $query) {
                $query->with(['media' => function ($query) {
                    $query->orderBy('order_column');
                }]);
            })
            ->letter('name')
            ->paginate()
            ->withQueryString();

        return view('theme-store.index', [
            'criteria' => $criteria,
            'themes' => $themes,
        ]);
    }

    /**
     * Show the theme designer.
     *
     * @return Application|Factory|View
     */
    public function create(): Application|Factory|View
    {
        return view('theme-store.create');
    }
}
