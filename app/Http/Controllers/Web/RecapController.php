<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\RecapPeriod;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class RecapController extends Controller
{
    /**
     * Show the recap of the selected period.
     *
     * @param Request $request
     *
     * @return Application|Factory|View
     */
    public function index(Request $request): Application|Factory|View
    {
        $year = $request->filled('year') && ctype_digit($request->string('year')->value())
            ? $request->integer('year')
            : now()->year;
        $month = $request->filled('month') && ctype_digit($request->string('month')->value())
            ? $request->integer('month')
            : null;

        if ($month !== null && ($month < 0 || $month > 12)) {
            $month = null;
        }

        return view('recap.index', [
            'period' => new RecapPeriod($request->user(), $year, $month),
        ]);
    }
}
