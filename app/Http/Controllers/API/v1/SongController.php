<?php

namespace App\Http\Controllers\API\v1;

use App\Events\SongViewed;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetSongAnimesRequest;
use App\Http\Requests\GetSongGamesRequest;
use App\Http\Resources\AnimeResource;
use App\Http\Resources\GameResource;
use App\Http\Resources\SongResource;
use App\Models\Song;
use Illuminate\Http\JsonResponse;

class SongController extends Controller
{
    /**
     * Shows song details.
     *
     * @param Song $song
     * @return JsonResponse
     */
    public function details(Song $song): JsonResponse
    {
        // Call the SongViewed event
        SongViewed::dispatch($song);

        $song->load(['media', 'mediaStat']);

        return JSONResult::success([
            'data' => SongResource::collection([$song])
        ]);
    }

    /**
     * Returns anime information for a Song
     *
     * @param GetSongAnimesRequest $request
     * @param Song $song
     * @return JsonResponse
     */
    public function anime(GetSongAnimesRequest $request, Song $song): JsonResponse
    {
        $data = $request->validated();

        // Get the anime
        $animes = $song->anime()
            ->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating'])
            ->when(auth()->user(), function ($query, $user) {
                $query->with(['mediaRatings' => function ($query) use ($user) {
                    $query->where([
                        ['user_id', '=', $user->id]
                    ]);
                }, 'library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }])
                    ->withExists([
                        'favoriters as isFavorited' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        },
                        'reminderers as isReminded' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        },
                    ]);
            })
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $animes->nextPageUrl());

        return JSONResult::success([
            'data' => AnimeResource::collection($animes),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns anime information for a Song
     *
     * @param GetSongGamesRequest $request
     * @param Song $song
     * @return JsonResponse
     */
    public function games(GetSongGamesRequest $request, Song $song): JsonResponse
    {
        $data = $request->validated();

        // Get the games
        $games = $song->games()
            ->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating'])
            ->when(auth()->user(), function ($query, $user) {
                $query->with(['mediaRatings' => function ($query) use ($user) {
                    $query->where([
                        ['user_id', '=', $user->id]
                    ]);
                }, 'library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }])
                    ->withExists([
                        'favoriters as isFavorited' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        },
                        'reminderers as isReminded' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        },
                    ]);
            })
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $games->nextPageUrl());

        return JSONResult::success([
            'data' => GameResource::collection($games),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
