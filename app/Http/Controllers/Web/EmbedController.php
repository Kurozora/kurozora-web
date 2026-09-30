<?php

namespace App\Http\Controllers\Web;

use App\Enums\VideoSource;
use App\Http\Controllers\Controller;
use App\Models\Episode;
use App\Models\Song;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Http\Request;

class EmbedController extends Controller
{
    /**
     * Show the embeddable player of an episode.
     *
     * @param Request $request
     * @param Episode $episode
     *
     * @return Application|Factory|View
     */
    public function episode(Request $request, Episode $episode): Application|Factory|View
    {
        $episode->loadMissing([
            'anime' => function (HasOneThrough $hasOneThrough) {
                $hasOneThrough->with([
                    'countryOfOrigin',
                    'genres',
                    'studios',
                    'translation',
                    'tvRating',
                    'orderedVideos',
                    'videos',
                ]);
            },
            'media',
            'mediaStat',
            'translation',
            'tvRating',
            'videos',
        ]);

        $anime = $episode->anime;
        $video = $episode->videos->firstWhere('source', '=', VideoSource::Default()->value)
            ?? $episode->videos->first()
            ?? $anime?->orderedVideos->first();

        $schema = $episode->toSchemaOrg();
        $schema['@graph'][0]['url'] = route('embed.episodes', $episode);

        return view('embed.episode', [
            'episode' => $episode,
            'anime' => $anime,
            'video' => $video,
            'timestamp' => $request->integer('t'),
            'schema' => $schema,
        ]);
    }

    /**
     * Show the embeddable player of a song.
     *
     * @param Song $song
     *
     * @return Application|Factory|View
     */
    public function song(Song $song): Application|Factory|View
    {
        $song->load(['media']);

        return view('embed.song', [
            'song' => $song,
        ]);
    }
}
