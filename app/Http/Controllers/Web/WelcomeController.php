<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class WelcomeController extends Controller
{
    /**
     * Show the welcome page.
     *
     * @return Application|Factory|View
     */
    public function index(): Application|Factory|View
    {
        return view('welcome');
    }
}
