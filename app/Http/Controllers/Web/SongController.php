<?php

namespace App\Http\Controllers\Web;

use App\Events\ModelViewed;
use App\Http\Controllers\Controller;
use App\Models\Song;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SongController extends Controller
{
    /**
     * Show a song's page.
     *
     * @param Request $request
     * @param Song    $song
     *
     * @return Application|Factory|View
     */
    public function show(Request $request, Song $song): Application|Factory|View
    {
        ModelViewed::dispatch($song, $request->ip());

        $user = $request->user();
        $locales = array_values(array_unique(array_filter([
            'ja',
            app()->getLocale(),
            config('app.fallback_locale'),
        ])));

        $song->load(['media', 'translations' => function ($query) use ($locales) {
            $query->with('language')
                ->whereIn('locale', $locales);
        }])
            ->when($user, function ($query, $user) use ($song) {
                return $song->loadMissing(['mediaRatings' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            });

        if ($user === null) {
            $song->setRelation('mediaRatings', collect());
        }

        return view('song.details', [
            'song' => $song,
            'userRating' => $song->mediaRatings,
            'reviewBoxID' => str()->random(20),
            'musicLinks' => [
                'amazon' => $song->amazon_id ? config('services.amazon.music.albums') . $song->amazon_id : null,
                'deezer' => $song->deezer_id ? config('services.deezer.track') . $song->deezer_id : null,
                'spotify' => $song->spotify_id ? config('services.spotify.track') . $song->spotify_id : null,
                'youtube' => $song->youtube_id ? config('services.youtube.music.watch') . $song->youtube_id : null,
            ],
        ]);
    }
}
