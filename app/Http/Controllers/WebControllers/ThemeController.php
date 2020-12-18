<?php

namespace App\Http\Controllers\WebControllers;

use App\Models\AppTheme;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

class ThemeController extends Controller
{
    /**
     * Index page of themes.
     *
     * @return Application|Factory|View
     */
    function index() {
        $themes = AppTheme::paginate(6);

        return view('website.themes.index',
            ['themes' => $themes]
        );
    }

    /**
     * Page to create theme.
     *
     * @return Application|Factory|View
     */
    function create() {
        return view('website.themes.create', ['title' => 'Create']);
    }
}
