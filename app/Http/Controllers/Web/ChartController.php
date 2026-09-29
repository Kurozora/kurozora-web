<?php

namespace App\Http\Controllers\Web;

use App\Enums\ChartKind;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class ChartController extends Controller
{
    /**
     * Show the top charts page.
     *
     * @return Application|Factory|View
     */
    public function index(): Application|Factory|View
    {
        return view('chart.index', [
            'chartKinds' => ChartKind::getValues(),
        ]);
    }
}
