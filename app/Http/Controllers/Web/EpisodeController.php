<?php

namespace App\Http\Controllers\Web;

use App\Events\ModelViewed;
use App\Http\Controllers\Controller;
use App\Models\Episode;
use App\Models\Video;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Http\Request;

class EpisodeController extends Controller
{
    /**
     * Show an episode's page.
     *
     * @param Request $request
     * @param Episode $episode
     *
     * @return Application|Factory|View
     */
    public function show(Request $request, Episode $episode): Application|Factory|View
    {
        ModelViewed::dispatch($episode, $request->ip());

        $user = $request->user();

        $episode->loadMissing([
            'previousEpisode' => function (BelongsTo $belongsTo) {
                $belongsTo->withoutGlobalScopes();
            },
            'nextEpisode' => function (BelongsTo $belongsTo) {
                $belongsTo->withoutGlobalScopes()
                    ->with(['translation']);
            },
            'media',
            'mediaStat',
            'anime' => function (HasOneThrough $hasOneThrough) {
                $hasOneThrough->withoutGlobalScopes()
                    ->with([
                        'countryOfOrigin',
                        'genres',
                        'media',
                        'studios',
                        'translation',
                        'tvRating',
                        'orderedVideos',
                        'videos',
                    ]);
            },
            'season' => function (BelongsTo $query) {
                $query->withoutGlobalScopes()
                    ->with([
                        'media',
                        'translation',
                    ]);
            },
            'translation',
            'tvRating',
            'videos',
        ]);

        if ($user !== null) {
            $episode->loadMissing([
                'mediaRatings' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                },
            ])
                ->loadExists([
                    'userWatchedEpisodes as isWatched' => function ($query) use ($user) {
                        $query->where('user_id', $user->id)
                            ->completed();
                    },
                ]);
        } else {
            $episode->setRelation('mediaRatings', collect());
        }

        $anime = $episode->anime;
        $episode->season->setRelation('anime', $anime);

        $videoable = $episode->withoutRelations()
            ->setRelation('media', $episode->media);
        $videos = $episode->videos
            ->mapWithKeys(fn (Video $video) => [$video->source->key => $video->setRelation('videoable', $videoable)]);
        $fallbackVideo = $anime->orderedVideos->first()
            ?->setRelation('videoable', $anime->withoutRelations()->setRelation('media', $anime->media));

        return view('episode.details', [
            'episode' => $episode,
            'anime' => $anime,
            'season' => $episode->season,
            'previousEpisode' => $episode->previousEpisode,
            'nextEpisode' => $episode->nextEpisode,
            'userRating' => $episode->mediaRatings,
            'isTracking' => $user !== null && $user->hasTracked($anime),
            'isReminded' => $user !== null && $user->hasReminded($anime),
            'videos' => $videos,
            'fallbackVideo' => $fallbackVideo,
            'defaultSource' => $videos->keys()->first(),
            'timestamp' => $request->integer('t'),
            'reviewBoxID' => str()->random(20),
            'schema' => $episode->toSchemaOrg(),
            'suggestedRefreshUrl' => route('episodes.section', [$episode, 'suggested-episodes'], false),
        ]);
    }
}
