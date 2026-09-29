<?php

namespace App\Http\Controllers\Web;

use App\Events\ModelViewed;
use App\Http\Controllers\Controller;
use App\Models\Character;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CharacterController extends Controller
{
    /**
     * Show a character's page.
     *
     * @param Request   $request
     * @param Character $character
     *
     * @return Application|Factory|View
     */
    public function show(Request $request, Character $character): Application|Factory|View
    {
        ModelViewed::dispatch($character, $request->ip());

        $user = $request->user();

        $character->load(['media'])
            ->when($user, function ($query, $user) use ($character) {
                return $character->loadMissing(['mediaRatings' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            });

        if ($user === null) {
            $character->setRelation('mediaRatings', collect());
        }

        return view('character.details', [
            'character' => $character,
            'userRating' => $character->mediaRatings,
            'reviewBoxID' => str()->random(20),
        ]);
    }
}
