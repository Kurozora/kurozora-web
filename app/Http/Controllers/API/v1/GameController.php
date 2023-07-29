<?php

namespace App\Http\Controllers\API\v1;

use App\Events\GameViewed;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetGameCharactersRequest;
use App\Http\Requests\GetGameMoreByStudioRequest;
use App\Http\Requests\GetGameReviewsRequest;
use App\Http\Requests\GetGameStudiosRequest;
use App\Http\Requests\GetMediaCastRequest;
use App\Http\Requests\GetMediaRelatedGamesRequest;
use App\Http\Requests\GetMediaRelatedLiteraturesRequest;
use App\Http\Requests\GetMediaRelatedShowsRequest;
use App\Http\Requests\GetMediaSongsRequest;
use App\Http\Requests\GetMediaStaffRequest;
use App\Http\Requests\GetUpcomingGameRequest;
use App\Http\Requests\RateGameRequest;
use App\Http\Resources\CharacterResourceIdentity;
use App\Http\Resources\GameResource;
use App\Http\Resources\GameResourceIdentity;
use App\Http\Resources\MediaRatingResource;
use App\Http\Resources\MediaRelatedResource;
use App\Http\Resources\MediaSongResource;
use App\Http\Resources\MediaStaffResource;
use App\Http\Resources\ShowCastResourceIdentity;
use App\Http\Resources\StudioResource;
use App\Models\Game;
use App\Models\MediaRating;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class GameController extends Controller
{
    /**
     * Returns detailed information of a game.
     *
     * @param game $game
     * @return JsonResponse
     */
    public function view(Game $game): JsonResponse
    {
        // Call the GameViewed event
        GameViewed::dispatch($game);

        // Show the game details response
        return JSONResult::success([
            'data' => GameResource::collection([$game])
        ]);
    }

    /**
     * Returns character information of a game.
     *
     * @param GetGameCharactersRequest $request
     * @param game $game
     * @return JsonResponse
     */
    public function characters(GetGameCharactersRequest $request, game $game): JsonResponse
    {
        $data = $request->validated();

        // Get the characters
        $characters = $game->getCharacters($data['limit'] ?? 25, $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $characters->nextPageUrl());

        return JSONResult::success([
            'data' => CharacterResourceIdentity::collection($characters),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns the cast information of a game.
     *
     * @param GetMediaCastRequest $request
     * @param game $game
     * @return JsonResponse
     */
    public function cast(GetMediaCastRequest $request, game $game): JsonResponse
    {
        $data = $request->validated();

        // Get the anime cast
        $game = $game->getCast($data['limit'] ?? 25, $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $game->nextPageUrl());

        return JSONResult::success([
            'data' => ShowCastResourceIdentity::collection($game),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns related-shows information of a game.
     *
     * @param GetMediaRelatedShowsRequest $request
     * @param game $game
     * @return JsonResponse
     */
    public function relatedShows(GetMediaRelatedShowsRequest $request, game $game): JsonResponse
    {
        $data = $request->validated();

        // Get the related shows
        $relatedShows = $game->getAnimeRelations($data['limit'] ?? 25, $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $relatedShows->nextPageUrl());

        return JSONResult::success([
            'data' => MediaRelatedResource::collection($relatedShows),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns related-literatures information of a game.
     *
     * @param GetMediaRelatedLiteraturesRequest $request
     * @param game $game
     * @return JsonResponse
     */
    public function relatedLiteratures(GetMediaRelatedLiteraturesRequest $request, game $game): JsonResponse
    {
        $data = $request->validated();

        // Get the related literatures
        $relatedLiterature = $game->getMangaRelations($data['limit'] ?? 25, $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $relatedLiterature->nextPageUrl());

        return JSONResult::success([
            'data' => MediaRelatedResource::collection($relatedLiterature),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns related-games information of a game.
     *
     * @param GetMediaRelatedGamesRequest $request
     * @param game $game
     * @return JsonResponse
     */
    public function relatedGames(GetMediaRelatedGamesRequest $request, game $game): JsonResponse
    {
        $data = $request->validated();

        // Get the related games
        $relatedGame = $game->getGameRelations($data['limit'] ?? 25, $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $relatedGame->nextPageUrl());

        return JSONResult::success([
            'data' => MediaRelatedResource::collection($relatedGame),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns song information for a game
     *
     * @param GetMediaSongsRequest $request
     * @param game $game
     * @return JsonResponse
     */
    public function songs(GetMediaSongsRequest $request, game $game): JsonResponse
    {
        $data = $request->validated();

        // Get the seasons
        $limit = ($data['limit'] ?? -1) == -1 ? 150 : $data['limit'];
        $mediaSongs = $game->getMediaSongs($limit, $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $mediaSongs->nextPageUrl());

        return JSONResult::success([
            'data' => MediaSongResource::collection($mediaSongs),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns staff information of a game.
     *
     * @param GetMediaStaffRequest $request
     * @param game $game
     * @return JsonResponse
     */
    public function staff(GetMediaStaffRequest $request, game $game): JsonResponse
    {
        $data = $request->validated();

        // Get the staff
        $staff = $game->getMediaStaff($data['limit'] ?? 25, $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $staff->nextPageUrl());

        return JSONResult::success([
            'data' => MediaStaffResource::collection($staff),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns the studios information of a game.
     *
     * @param GetGameStudiosRequest $request
     * @param game $game
     * @return JsonResponse
     */
    public function studios(GetGameStudiosRequest $request, game $game): JsonResponse
    {
        $data = $request->validated();

        // Get the anime studios
        $mediaStudios = $game->getStudios($data['limit'] ?? 25, $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $mediaStudios->nextPageUrl());

        return JSONResult::success([
            'data' => StudioResource::collection($mediaStudios),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns the more anime made by the same studio.
     *
     * @param GetGameMoreByStudioRequest $request
     * @param game $game
     * @return JsonResponse
     */
    public function moreByStudio(GetGameMoreByStudioRequest $request, game $game): JsonResponse
    {
        $data = $request->validated();
        $studioGames = new LengthAwarePaginator([], 0, 1);

        // Get the anime studios
        if ($mediaStudio = $game->studios()->firstWhere('is_studio', '=', true)) {
            $studioGames = $mediaStudio->getGame($data['limit'] ?? 25, $data['page'] ?? 1);
        } else if ($mediaStudio = $game->studios()->first()) {
            $studioGames = $mediaStudio->getGame($data['limit'] ?? 25, $data['page'] ?? 1);
        }

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $studioGames->nextPageUrl());

        return JSONResult::success([
            'data' => GameResourceIdentity::collection($studioGames),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Adds a rating for an game item
     *
     * @param RateGameRequest $request
     * @param game $game
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Exception
     */
    public function rate(RateGameRequest $request, game $game): JsonResponse
    {
        $user = auth()->user();

        // Check if the user is already tracking the anime
        if ($user->hasNotTracked($game)) {
            throw new AuthorizationException(__('Please add :x to your library first.', ['x' => $game->title]));
        }

        // Validate the request
        $data = $request->validated();

        // Fetch the variables
        $givenRating = $data['rating'];
        $description = $data['description'] ?? null;

        // Try to modify the rating if it already exists
        /** @var MediaRating $foundRating */
        $foundRating = $user->episodeRatings()
            ->where('model_id', '=', $game->id)
            ->first();

        // The rating exists
        if ($foundRating) {
            // If the given rating is 0
            if ($givenRating <= 0) {
                // Delete the rating
                $foundRating->delete();
            } else {
                // Update the current rating
                $foundRating->update([
                    'rating'        => $givenRating,
                    'description'   => $description
                ]);
            }
        } else {
            // Only insert the rating if it's rated higher than 0
            if ($givenRating > 0) {
                MediaRating::create([
                    'user_id'       => $user->id,
                    'model_id'      => $game->id,
                    'model_type'    => $game->getMorphClass(),
                    'rating'        => $givenRating,
                    'description'   => $description
                ]);
            }
        }

        return JSONResult::success();
    }

    /**
     * Retrieves upcoming games results
     *
     * @param GetUpcomingGameRequest $request
     * @return JsonResponse
     */
    public function upcoming(GetUpcomingGameRequest $request): JsonResponse
    {
        $data = $request->validated();

        $game = Game::upcomingGames(-1)
            ->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $game->nextPageUrl());

        return JSONResult::success([
            'data' => GameResourceIdentity::collection($game),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns the reviews of a Game.
     *
     * @param GetGameReviewsRequest $request
     * @param Game $game
     * @return JsonResponse
     */
    public function reviews(GetGameReviewsRequest $request, Game $game): JsonResponse
    {
        $reviews = $game->mediaRatings()
            ->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $reviews->nextPageUrl());

        return JSONResult::success([
            'data' => MediaRatingResource::collection($reviews),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
