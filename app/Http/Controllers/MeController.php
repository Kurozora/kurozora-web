<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Helpers\KuroAuthToken;
use App\Http\Requests\GetAnimeFavoritesRequest;
use App\Http\Requests\GetSessionsRequest;
use App\Http\Resources\AnimeResourceBasic;
use App\Http\Resources\SessionResource;
use App\Http\Resources\UserResource;
use App\Session;
use App\User;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    /**
     * Returns the profile details for the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        // Get authenticated session
        $sessionID = (int) $request->input('session_id');
        $session = Session::find($sessionID);

        return JSONResult::success([
            'data'      => [
                UserResource::make($session->user)->includingSession($session)
            ],
            'authToken' => KuroAuthToken::generate($session->user->id, $session->secret)
        ]);
    }

    /**
     * Returns a list of the authenticated user's favorite anime.
     *
     * @param GetAnimeFavoritesRequest $request
     * @return JsonResponse
     */
    function getFavorites(GetAnimeFavoritesRequest $request): JsonResponse
    {
        $data = $request->validated();

        /** @var User $user */
        $user = Auth::user();

        // Paginate the favorited anime
        $favoriteAnime = $user->favoriteAnime()->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $favoriteAnime->nextPageUrl());

        // Show successful response
        return JSONResult::success([
            'data' => AnimeResourceBasic::collection($favoriteAnime),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns the current active sessions for a user
     *
     * @param GetSessionsRequest $request
     * @return JsonResponse
     */
    public function getSessions(GetSessionsRequest $request): JsonResponse
    {
        $data = $request->validated();

        /** @var User $user */
        $user = Auth::user();

        // Get paginated sessions except current session
        $sessions = $user->sessions()->where([
            ['secret',  '!=',   $request['session_secret']]
        ])->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $sessions->nextPageUrl());

        return JSONResult::success([
            'data' => SessionResource::collection($sessions),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
