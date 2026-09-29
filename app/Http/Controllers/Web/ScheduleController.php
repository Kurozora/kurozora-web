<?php

namespace App\Http\Controllers\Web;

use App\Enums\ScheduleKind;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Show the schedule page.
     *
     * @param Request $request
     *
     * @return Application|Factory|View
     */
    public function index(Request $request): Application|Factory|View
    {
        $type = strtolower($request->string('type', ScheduleKind::Anime()->key)->toString());
        $date = $request->string('date', today()->toDateString())->toString();

        return view('schedule.index', [
            'class' => ScheduleKind::modelClass($type),
            'dates' => $this->dates($date),
        ]);
    }

    /**
     * The week of dates surrounding the given date.
     *
     * @param string $date
     *
     * @return array
     */
    protected function dates(string $date): array
    {
        $date = Carbon::createFromFormat('Y-m-d', $date) ?? now();

        $dateCollection[] = $date->copy()->subDay();
        $dateCollection[] = $date;

        for ($i = 1; $i <= 5; $i++) {
            $dateCollection[] = $date->copy()->addDays($i);
        }

        return $dateCollection;
    }
}
