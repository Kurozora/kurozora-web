<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class UpNextController extends Controller
{
    /**
     * Show the episodes to watch next.
     *
     * @return Application|Factory|View
     */
    public function episodes(): Application|Factory|View
    {
        return view('up-next.episodes');
    }
}
