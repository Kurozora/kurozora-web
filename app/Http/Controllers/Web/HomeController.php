<?php

namespace App\Http\Controllers\Web;

use App\Enums\SearchSource;
use App\Http\Controllers\Controller;
use App\Models\ExploreCategory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * Show the home page.
     *
     * @return Application|Factory|View
     */
    public function index(): Application|Factory|View
    {
        $exploreCategories = ExploreCategory::orderBy('position')
            ->get();

        return view('home', [
            'exploreCategories' => $exploreCategories,
            'exploreCategoryItems' => ExploreCategory::itemsFor($exploreCategories),
            'schema' => $this->schema(),
        ]);
    }

    /**
     * The Schema.org JSON-LD payload for the home page.
     *
     * @return array
     */
    protected function schema(): array
    {
        return [
            '@type' => 'WebSite',
            'url' => config('app.url'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => route('search.index') . '?q={search_term_string}&src=' . SearchSource::Google,
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }
}
