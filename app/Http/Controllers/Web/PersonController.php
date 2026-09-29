<?php

namespace App\Http\Controllers\Web;

use App\Events\ModelViewed;
use App\Http\Controllers\Controller;
use App\Models\Person;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PersonController extends Controller
{
    /**
     * Show a person's page.
     *
     * @param Request $request
     * @param Person  $person
     *
     * @return Application|Factory|View
     */
    public function show(Request $request, Person $person): Application|Factory|View
    {
        ModelViewed::dispatch($person, $request->ip());

        $user = $request->user();

        $person->load(['media'])
            ->when($user, function ($query, $user) use ($person) {
                return $person->loadMissing(['mediaRatings' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            });

        if ($user === null) {
            $person->setRelation('mediaRatings', collect());
        }

        return view('person.details', [
            'person' => $person,
            'userRating' => $person->mediaRatings,
            'reviewBoxID' => str()->random(20),
        ]);
    }
}
