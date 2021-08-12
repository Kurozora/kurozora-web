<?php

namespace App\Http\Controllers;

use App\Events\AnimeViewed;
use App\Helpers\JSONResult;
use App\Http\Requests\GetAnimeCastRequest;
use App\Http\Requests\GetAnimeCharactersRequest;
use App\Http\Requests\GetAnimeRelatedShowsRequest;
use App\Http\Requests\GetAnimeSeasonsRequest;
use App\Http\Requests\GetAnimeStaffRequest;
use App\Http\Requests\GetAnimeStudiosRequest;
use App\Http\Requests\RateAnimeRequest;
use App\Http\Requests\SearchAnimeRequest;
use App\Http\Resources\AnimeCastResource;
use App\Http\Resources\AnimeRelatedShowsResource;
use App\Http\Resources\AnimeResource;
use App\Http\Resources\AnimeResourceBasic;
use App\Http\Resources\AnimeStaffResource;
use App\Http\Resources\CharacterResourceBasic;
use App\Http\Resources\SeasonResource;
use App\Http\Resources\StudioResource;
use App\Models\Anime;
use App\Models\AnimeRating;
use Auth;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

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
            'data' => CharacterResourceBasic::collection($characters),
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
            'data' => AnimeCastResource::collection($animeCast),
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
        $seasons = $anime->getSeasons($data['limit'] ?? 25, $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $seasons->nextPageUrl());

        return JSONResult::success([
            'data' => SeasonResource::collection($seasons),
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
        $animeStudios = $anime->getStudios($data['limit'] ?? 25, $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $animeStudios->nextPageUrl());

        return JSONResult::success([
            'data' => StudioResource::collection($animeStudios),
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
        if (!Auth::user()->isTracking($anime))
            throw new AuthorizationException('Please add ' . $anime->title . ' to your library first.');

        // Fetch the variables
        $givenRating = $request->input('rating');

        // Try to modify the rating if it already exists
        /** @var AnimeRating $foundRating */
        $foundRating = AnimeRating::where([
            ['anime_id', '=', $anime->id],
            ['user_id', '=', Auth::id()]
        ])->first();

        // The rating exists
        if ($foundRating) {
            // If the given rating is 0, delete the rating
            if ($givenRating <= 0)
                $foundRating->delete();
            // Update the current rating
            else {
                $foundRating->rating = $givenRating;
                $foundRating->save();
            }
        }
        // Rating needs to be inserted
        else {
            // Only insert the rating if it's rated higher than 0
            if ($givenRating > 0) {
                AnimeRating::create([
                    'anime_id'  => $anime->id,
                    'user_id'   => Auth::id(),
                    'rating'    => $givenRating
                ]);
            }
        }

        return JSONResult::success();
    }

    /**
     * Retrieves Anime search results
     *
     * @param SearchAnimeRequest $request
     * @return JsonResponse
     */
    public function search(SearchAnimeRequest $request): JsonResponse
    {
        $searchQuery = $request->input('query');

        // Search for the anime
        $anime = Anime::kSearch($searchQuery)->paginate(Anime::MAX_SEARCH_RESULTS)
            ->appends('query', $searchQuery);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $anime->nextPageUrl());

        return JSONResult::success([
            'data' => AnimeResourceBasic::collection($anime),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
