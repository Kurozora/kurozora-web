<?php

namespace App\Http\Controllers\Web;

use App\Enums\MediaCollection;
use App\Events\ModelViewed;
use App\Http\Controllers\Controller;
use App\Models\Studio;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StudioController extends Controller
{
    /**
     * Show a studio's page.
     *
     * @param Request $request
     * @param Studio  $studio
     *
     * @return Application|Factory|View
     */
    public function show(Request $request, Studio $studio): Application|Factory|View
    {
        ModelViewed::dispatch($studio, $request->ip());

        $user = $request->user();

        $studio->load(['media', 'tvRating'])
            ->when($user, function ($query, $user) use ($studio) {
                return $studio->loadMissing(['mediaRatings' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            });

        if ($user === null) {
            $studio->setRelation('mediaRatings', collect());
        }

        return view('studio.details', [
            'studio' => $studio,
            'userRating' => $studio->mediaRatings,
            'reviewBoxID' => str()->random(20),
            'bannerUrl' => $studio->getFirstMediaFullUrl(MediaCollection::Banner()),
        ]);
    }
}
