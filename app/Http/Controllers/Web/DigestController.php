<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DigestController extends Controller
{
    /**
     * Show the weekly digest.
     *
     * @param Request $request
     *
     * @return Application|Factory|View
     */
    public function index(Request $request): Application|Factory|View
    {
        $reference = $request->query('reference');
        $referenceDate = rescue(fn () => $reference ? Carbon::parse($reference) : null, null, false) ?? Carbon::now();
        $windowEnd = $referenceDate->copy()->startOfWeek(Carbon::MONDAY);
        $windowStart = $windowEnd->copy()->subWeek();

        return view('digest.index', [
            'reference' => is_string($reference) ? $reference : null,
            'windowLabel' => __(':start – :end', [
                'start' => $windowStart->isoFormat('MMM D'),
                'end' => $windowEnd->copy()->subDay()->isoFormat('MMM D'),
            ]),
        ]);
    }
}
