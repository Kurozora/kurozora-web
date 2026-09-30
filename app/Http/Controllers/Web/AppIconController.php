<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AppIcon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class AppIconController extends Controller
{
    /**
     * Show the app icons grouped by category.
     *
     * @return Application|Factory|View
     */
    public function index(): Application|Factory|View
    {
        return view('app-icon.index', [
            'appIcons' => AppIcon::all(),
        ]);
    }
}
