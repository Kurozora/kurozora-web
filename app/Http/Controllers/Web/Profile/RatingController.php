<?php

namespace App\Http\Controllers\Web\Profile;

use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaSong;
use App\Models\Person;
use App\Models\Studio;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RatingController extends Controller
{
    /**
     * Show a user's ratings and reviews.
     *
     * @param User $user
     *
     * @return Application|Factory|View
     */
    public function index(User $user): Application|Factory|View
    {
        return view('profile.ratings', [
            'user' => $user,
            'mediaRatings' => $user->mediaRatings()
                ->with([
                    'model' => function (MorphTo $morphTo) {
                        $morphTo->constrain([
                            Anime::class => function (Builder $query) {
                                $query->with(['media', 'translation']);
                            },
                            Character::class => function (Builder $query) {
                                $query->with(['media']);
                            },
                            Episode::class => function (Builder $query) {
                                $query->with(['media', 'translation']);
                            },
                            Game::class => function (Builder $query) {
                                $query->with(['media', 'translation']);
                            },
                            Manga::class => function (Builder $query) {
                                $query->with(['media', 'translation']);
                            },
                            Person::class => function (Builder $query) {
                                $query->with(['media']);
                            },
                            Studio::class => function (Builder $query) {
                                $query->with(['media']);
                            },
                            MediaSong::class => function (Builder $query) {
                                $query->with([
                                    'song' => function ($query) {
                                        $query->with(['media']);
                                    },
                                ]);
                            },
                        ]);
                    },
                ])
                ->orderBy('created_at', 'desc')
                ->paginate(25),
        ]);
    }
}
