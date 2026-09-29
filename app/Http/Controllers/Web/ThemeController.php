<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ExploreCategory;
use App\Models\Theme;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class ThemeController extends Controller
{
    /**
     * Show the list of themes.
     *
     * @return Application|Factory|View
     */
    public function index(): Application|Factory|View
    {
        return view('theme.index', [
            'themes' => Theme::orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Show a theme's page.
     *
     * @param Theme $theme
     *
     * @return Application|Factory|View
     */
    public function show(Theme $theme): Application|Factory|View
    {
        $theme->load(['media']);

        $exploreCategories = ExploreCategory::where('is_global', true)
            ->orderBy('position')
            ->get();

        return view('theme.details', [
            'theme' => $theme,
            'exploreCategories' => $exploreCategories,
            'exploreCategoryItems' => ExploreCategory::itemsFor($exploreCategories, $theme),
        ]);
    }
}
