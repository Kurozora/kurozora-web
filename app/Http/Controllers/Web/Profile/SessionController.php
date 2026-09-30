<?php

namespace App\Http\Controllers\Web\Profile;

use App\Http\Controllers\Controller;
use App\Traits\InteractsWithSessions;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class SessionController extends Controller
{
    use InteractsWithSessions;

    /**
     * Show the sessions the signed-in user is active on.
     *
     * @return Application|Factory|View
     */
    public function index(): Application|Factory|View
    {
        $currentSession = $this->currentSessionView();
        $otherSessions = $this->otherSessionViews();

        $coordinates = collect([$currentSession])
            ->concat($otherSessions)
            ->filter()
            ->filter(fn ($session) => $session->latitude !== null && $session->longitude !== null && !($session->latitude == 0 && $session->longitude == 0))
            ->map(fn ($session) => [
                'latitude' => (float) $session->latitude,
                'longitude' => (float) $session->longitude,
                'title' => $session->full_platform,
                'subtitle' => $session->full_location,
            ])
            ->values();

        return view('profile.sessions.index', [
            'currentSession' => $currentSession,
            'otherSessions' => $otherSessions,
            'mapToken' => config('services.apple.maps.token'),
            'coordinates' => $coordinates,
        ]);
    }
}
