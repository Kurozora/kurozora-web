<?php

namespace Laravel\Nova\Http\Controllers;

use Illuminate\Routing\Controller;
use Laravel\Nova\GlobalSearch;
use Laravel\Nova\Http\Requests\GlobalSearchRequest;
use Laravel\Nova\Nova;

class SearchController extends Controller
{
    /**
     * Get the global search results for the given query.
     *
     * @param  \Laravel\Nova\Http\Requests\GlobalSearchRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function index(GlobalSearchRequest $request)
    {
        return (new GlobalSearch(
            $request, Nova::globallySearchableResources($request)
        ))->get();
    }
}
