<?php

namespace App\Http\Controllers\Web;

use App\Events\ModelViewed;
use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Traits\Controller\PaginatesTitles;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CharacterController extends Controller
{
    use PaginatesTitles;

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

    /**
     * Show the anime a character appears in.
     *
     * @param Character $character
     *
     * @return Application|Factory|View
     */
    public function anime(Character $character): Application|Factory|View
    {
        return view('character.anime', [
            'character' => $character->load(['media']),
            'titles' => $this->paginateTitles($character->anime()),
        ]);
    }

    /**
     * Show the manga a character appears in.
     *
     * @param Character $character
     *
     * @return Application|Factory|View
     */
    public function manga(Character $character): Application|Factory|View
    {
        return view('character.manga', [
            'character' => $character->load(['media']),
            'titles' => $this->paginateTitles($character->manga()),
        ]);
    }

    /**
     * Show the games a character appears in.
     *
     * @param Character $character
     *
     * @return Application|Factory|View
     */
    public function games(Character $character): Application|Factory|View
    {
        return view('character.games', [
            'character' => $character->load(['media']),
            'titles' => $this->paginateTitles($character->games()),
        ]);
    }

    /**
     * Show the people who voiced a character.
     *
     * @param Character $character
     *
     * @return Application|Factory|View
     */
    public function people(Character $character): Application|Factory|View
    {
        return view('character.people', [
            'character' => $character->load(['media']),
            'people' => $character->people()
                ->with(['media'])
                ->paginate(25),
        ]);
    }
}
