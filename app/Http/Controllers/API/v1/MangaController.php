<?php

namespace App\Http\Controllers\API\v1;

use App\Events\MangaViewed;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetMangaCastRequest;
use App\Http\Requests\GetMangaCharactersRequest;
use App\Http\Requests\GetMangaMoreByStudioRequest;
use App\Http\Requests\GetMediaRelatedMangasRequest;
use App\Http\Requests\GetMediaRelatedShowsRequest;
use App\Http\Requests\GetMediaStaffRequest;
use App\Http\Requests\GetMangaStudiosRequest;
use App\Http\Requests\GetUpcomingMangaRequest;
use App\Http\Requests\RateMangaRequest;
use App\Http\Resources\MangaCastResourceIdentity;
use App\Http\Resources\MediaRelatedMangaResource;
use App\Http\Resources\MediaRelatedShowResource;
use App\Http\Resources\MangaResource;
use App\Http\Resources\MangaResourceIdentity;
use App\Http\Resources\MediaStaffResource;
use App\Http\Resources\CharacterResourceIdentity;
use App\Http\Resources\StudioResource;
use App\Models\Manga;
use App\Models\MediaRating;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class MangaController extends Controller
{
    /**
     * Returns detailed information of a Manga.
     *
     * @param Manga $manga
     * @return JsonResponse
     */
    public function view(Manga $manga): JsonResponse
    {
        // Call the MangaViewed event
        MangaViewed::dispatch($manga);

        // Show the Manga details response
        return JSONResult::success([
            'data' => MangaResource::collection([$manga])
        ]);
    }

    /**
     * Returns character information of a Manga.
     *
     * @param GetMangaCharactersRequest $request
     * @param Manga $manga
     * @return JsonResponse
     */
    public function characters(GetMangaCharactersRequest $request, Manga $manga): JsonResponse
    {
        $data = $request->validated();

        // Get the characters
        $characters = $manga->getCharacters($data['limit'] ?? 25, $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $characters->nextPageUrl());

        return JSONResult::success([
            'data' => CharacterResourceIdentity::collection($characters),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns the cast information of a Manga.
     *
     * @param GetMangaCastRequest $request
     * @param Manga $manga
     * @return JsonResponse
     */
    public function cast(GetMangaCastRequest $request, Manga $manga): JsonResponse
    {
        $data = $request->validated();

        // Get the manga cast
        $mangaCast = $manga->getCast($data['limit'] ?? 25, $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $mangaCast->nextPageUrl());

        return JSONResult::success([
            'data' => MangaCastResourceIdentity::collection($mangaCast),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns related-shows information of a Manga.
     *
     * @param GetMediaRelatedShowsRequest $request
     * @param Manga $manga
     * @return JsonResponse
     */
    public function relatedShows(GetMediaRelatedShowsRequest $request, Manga $manga): JsonResponse
    {
        $data = $request->validated();

        // Get the related shows
        $relatedShows = $manga->getMangaRelations($data['limit'] ?? 25, $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $relatedShows->nextPageUrl());

        return JSONResult::success([
            'data' => MediaRelatedShowResource::collection($relatedShows),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns related-mangas information of a Manga.
     *
     * @param GetMediaRelatedMangasRequest $request
     * @param Manga $manga
     * @return JsonResponse
     */
    public function relatedMangas(GetMediaRelatedMangasRequest $request, Manga $manga): JsonResponse
    {
        $data = $request->validated();

        // Get the related mangas
        $relatedMangas = $manga->getMangaRelations($data['limit'] ?? 25, $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $relatedMangas->nextPageUrl());

        return JSONResult::success([
            'data' => MediaRelatedMangaResource::collection($relatedMangas),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns staff information of a Manga.
     *
     * @param GetMediaStaffRequest $request
     * @param Manga $manga
     * @return JsonResponse
     */
    public function staff(GetMediaStaffRequest $request, Manga $manga): JsonResponse
    {
        $data = $request->validated();

        // Get the staff
        $staff = $manga->getMediaStaff($data['limit'] ?? 25, $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $staff->nextPageUrl());

        return JSONResult::success([
            'data' => MediaStaffResource::collection($staff),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns the studios information of a Manga.
     *
     * @param GetMangaStudiosRequest $request
     * @param Manga $manga
     * @return JsonResponse
     */
    public function studios(GetMangaStudiosRequest $request, Manga $manga): JsonResponse
    {
        $data = $request->validated();

        // Get the manga studios
        $mangaStudios = $manga->getStudios($data['limit'] ?? 25, $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $mangaStudios->nextPageUrl());

        return JSONResult::success([
            'data' => StudioResource::collection($mangaStudios),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns the more manga made by the same studio.
     *
     * @param GetMangaMoreByStudioRequest $request
     * @param Manga $manga
     * @return JsonResponse
     */
    public function moreByStudio(GetMangaMoreByStudioRequest $request, Manga $manga): JsonResponse
    {
        $data = $request->validated();
        $studioMangas = new LengthAwarePaginator([], 0, 1);

        // Get the manga studios
        if ($mangaStudio = $manga->studios()->firstWhere('is_studio', '=', true)) {
            $studioMangas = $mangaStudio->getManga($data['limit'] ?? 25, $data['page'] ?? 1);
        } elseif ($mangaStudio = $manga->studios()->first()) {
            $studioMangas = $mangaStudio->getManga($data['limit'] ?? 25, $data['page'] ?? 1);
        }

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $studioMangas->nextPageUrl());

        return JSONResult::success([
            'data' => MangaResourceIdentity::collection($studioMangas),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Adds a rating for a Manga item
     *
     * @param RateMangaRequest $request
     * @param Manga $manga
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Exception
     */
    public function rateManga(RateMangaRequest $request, Manga $manga): JsonResponse
    {
        $user = auth()->user();

        // Check if the user is already tracking the manga
        if ($user->hasNotTracked($manga)) {
            throw new AuthorizationException(__('Please add :x to your library first.', ['x' => $manga->title]));
        }

        // Validate the request
        $data = $request->validated();

        // Fetch the variables
        $givenRating = $data['rating'];

        // Try to modify the rating if it already exists
        /** @var MediaRating $foundRating */
        $foundRating = $user->mangaRatings()
            ->where('model_id', '=', $manga->id)
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
                    'model_type'    => $manga->getMorphClass(),
                    'model_id'      => $manga->id,
                    'rating'        => $givenRating
                ]);
            }
        }

        return JSONResult::success();
    }

    /**
     * Retrieves upcoming Manga results
     *
     * @param GetUpcomingMangaRequest $request
     * @return JsonResponse
     */
    public function upcoming(GetUpcomingMangaRequest $request): JsonResponse
    {
        $data = $request->validated();

        $manga = Manga::upcomingMangas(-1)
            ->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $manga->nextPageUrl());

        return JSONResult::success([
            'data' => MangaResourceIdentity::collection($manga),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}