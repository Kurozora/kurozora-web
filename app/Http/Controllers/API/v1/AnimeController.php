<?php

namespace App\Http\Controllers\API\v1;

use App\Events\AnimeViewed;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetAnimeCastRequest;
use App\Http\Requests\GetAnimeCharactersRequest;
use App\Http\Requests\GetAnimeMoreByStudioRequest;
use App\Http\Requests\GetAnimeRelatedShowsRequest;
use App\Http\Requests\GetAnimeSeasonsRequest;
use App\Http\Requests\GetAnimeSongsRequest;
use App\Http\Requests\GetAnimeStaffRequest;
use App\Http\Requests\GetAnimeStudiosRequest;
use App\Http\Requests\GetUpcomingAnimeRequest;
use App\Http\Requests\RateAnimeRequest;
use App\Http\Resources\AnimeCastResourceIdentity;
use App\Http\Resources\AnimeRelatedShowsResource;
use App\Http\Resources\AnimeResource;
use App\Http\Resources\AnimeResourceIdentity;
use App\Http\Resources\AnimeSongResource;
use App\Http\Resources\AnimeStaffResource;
use App\Http\Resources\CharacterResourceIdentity;
use App\Http\Resources\SeasonResourceIdentity;
use App\Http\Resources\StudioResource;
use App\Models\Anime;
use App\Models\MediaRating;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class AnimeController extends Controller
{
    /**
     * Returns detailed information of an Anime.
     *
     * @param Anime $anime
     * @return JsonResponse
     */
    public function view(Anime $anime): JsonResponse
    {
        // Call the AnimeViewed event
        AnimeViewed::dispatch($anime);

        // Show the Anime details response
        return JSONResult::success([
            'data' => AnimeResource::collection([$anime])
        ]);
    }

    /**
     * Returns character information of an Anime.
     *
     * @param GetAnimeCharactersRequest $request
     * @param Anime $anime
     * @return JsonResponse
     */
    public function characters(GetAnimeCharactersRequest $request, Anime $anime): JsonResponse
    {
        $data = $request->validated();

        // Get the characters
        $characters = $anime->getCharacters($data['limit'] ?? 25, $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $characters->nextPageUrl());

        return JSONResult::success([
            'data' => CharacterResourceIdentity::collection($characters),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns the cast information of an Anime.
     *
     * @param GetAnimeCastRequest $request
     * @param Anime $anime
     * @return JsonResponse
     */
    public function cast(GetAnimeCastRequest $request, Anime $anime): JsonResponse
    {
        $data = $request->validated();

        // Get the anime cast
        $animeCast = $anime->getCast($data['limit'] ?? 25, $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $animeCast->nextPageUrl());

        return JSONResult::success([
            'data' => AnimeCastResourceIdentity::collection($animeCast),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns related-shows information of an Anime.
     *
     * @param GetAnimeRelatedShowsRequest $request
     * @param Anime $anime
     * @return JsonResponse
     */
    public function relatedShows(GetAnimeRelatedShowsRequest $request, Anime $anime): JsonResponse
    {
        $data = $request->validated();

        // Get the related shows
        $relatedShows = $anime->getAnimeRelations($data['limit'] ?? 25, $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $relatedShows->nextPageUrl());

        return JSONResult::success([
            'data' => AnimeRelatedShowsResource::collection($relatedShows),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns season information for an Anime
     *
     * @param GetAnimeSeasonsRequest $request
     * @param Anime $anime
     * @return JsonResponse
     */
    public function seasons(GetAnimeSeasonsRequest $request, Anime $anime): JsonResponse
    {
        $data = $request->validated();

        // Get the seasons
        $seasons = $anime->getSeasons($data['limit'] ?? 25, $data['page'] ?? 1, $data['reversed'] ?? false);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $seasons->nextPageUrl());

        return JSONResult::success([
            'data' => SeasonResourceIdentity::collection($seasons),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns song information for an Anime
     *
     * @param GetAnimeSongsRequest $request
     * @param Anime $anime
     * @return JsonResponse
     */
    public function songs(GetAnimeSongsRequest $request, Anime $anime): JsonResponse
    {
        $data = $request->validated();

        // Get the seasons
        $limit = ($data['limit'] ?? -1) == -1 ? 150 : $data['limit'];
        $animeSongs = $anime->getAnimeSongs($limit, $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $animeSongs->nextPageUrl());

        return JSONResult::success([
            'data' => AnimeSongResource::collection($animeSongs),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns staff information of an Anime.
     *
     * @param GetAnimeStaffRequest $request
     * @param Anime $anime
     * @return JsonResponse
     */
    public function staff(GetAnimeStaffRequest $request, Anime $anime): JsonResponse
    {
        $data = $request->validated();

        // Get the staff
        $staff = $anime->getStaff($data['limit'] ?? 25, $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $staff->nextPageUrl());

        return JSONResult::success([
            'data' => AnimeStaffResource::collection($staff),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns the studios information of an Anime.
     *
     * @param GetAnimeStudiosRequest $request
     * @param Anime $anime
     * @return JsonResponse
     */
    public function studios(GetAnimeStudiosRequest $request, Anime $anime): JsonResponse
    {
        $data = $request->validated();

        // Get the anime studios
        $mediaStudios = $anime->getStudios($data['limit'] ?? 25, $data['page'] ?? 1);

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
     * @param GetAnimeMoreByStudioRequest $request
     * @param Anime $anime
     * @return JsonResponse
     */
    public function moreByStudio(GetAnimeMoreByStudioRequest $request, Anime $anime): JsonResponse
    {
        $data = $request->validated();
        $studioAnimes = new LengthAwarePaginator([], 0, 1);

        // Get the anime studios
        if ($mediaStudio = $anime->studios()->firstWhere('is_studio', '=', true)) {
            $studioAnimes = $mediaStudio->getAnime($data['limit'] ?? 25, $data['page'] ?? 1);
        } elseif ($mediaStudio = $anime->studios()->first()) {
            $studioAnimes = $mediaStudio->getAnime($data['limit'] ?? 25, $data['page'] ?? 1);
        }

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $studioAnimes->nextPageUrl());

        return JSONResult::success([
            'data' => AnimeResourceIdentity::collection($studioAnimes),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Adds a rating for an Anime item
     *
     * @param RateAnimeRequest $request
     * @param Anime $anime
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Exception
     */
    public function rateAnime(RateAnimeRequest $request, Anime $anime): JsonResponse
    {
        $user = auth()->user();

        // Check if the user is already tracking the anime
        if ($user->hasNotTracked($anime)) {
            throw new AuthorizationException(__('Please add :x to your library first.', ['x' => $anime->title]));
        }

        // Validate the request
        $data = $request->validated();

        // Fetch the variables
        $givenRating = $data['rating'];

        // Try to modify the rating if it already exists
        /** @var MediaRating $foundRating */
        $foundRating = $user->animeRatings()
            ->where('model_id', '=', $anime->id)
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
                    'rating' => $givenRating,
                ]);
            }
        } else {
            // Only insert the rating if it's rated higher than 0
            if ($givenRating > 0) {
                $user->episode_ratings()->create([
                    'user_id'       => $user->id,
                    'model_type'    => Anime::class,
                    'model_id'      => $anime->id,
                    'rating'        => $givenRating
                ]);
            }
        }

        return JSONResult::success();
    }

    /**
     * Retrieves upcoming Anime results
     *
     * @param GetUpcomingAnimeRequest $request
     * @return JsonResponse
     */
    public function upcoming(GetUpcomingAnimeRequest $request): JsonResponse
    {
        $data = $request->validated();

        $anime = Anime::upcomingShows(-1)
            ->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $anime->nextPageUrl());

        return JSONResult::success([
            'data' => AnimeResourceIdentity::collection($anime),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
