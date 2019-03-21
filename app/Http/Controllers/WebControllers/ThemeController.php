<?php

namespace App\Http\Controllers\WebControllers;

use App\AppTheme;
use App\Http\Controllers\Controller;

class ThemeController extends Controller
{
    /**
     * Index page of themes.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    function index() {
        $themes = AppTheme::simplePaginate(8);

        return view('website.themes.index',
            ['themes' => $themes]
        );
    }

    /**
     * Page to create theme.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    function create() {
        return view('website.themes.create', ['title' => 'Create']);
    }

}
