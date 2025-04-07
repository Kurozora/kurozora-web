<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\MediaCollection;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetLibraryRequest;
use App\Http\Requests\GetPaginatedRequest;
use App\Http\Requests\GetUpNextEpisodesRequest;
use App\Http\Requests\GetUserFavoritesRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\EpisodeResourceIdentity;
use App\Http\Resources\UserResource;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class MeController extends Controller
{
    /**
     * Returns the profile details for the authenticated user.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        // Get authenticated session
        $personalAccessToken = auth()->user()
            ->currentAccessToken()
            ->load(['session_attribute']);

        $user = $request->user()
            ->load([
                'badges' => function ($query) {
                    $query->with(['media']);
                },
                'media',
                'sessions' => function ($query) {
                    $query
                        ->orderBy('last_activity', 'desc')
                        ->limit(1);
                },
            ])
            ->loadCount(['followers', 'followedModels as following_count', 'mediaRatings'])
            // Since we already have the latest access token, we
            // simply set the relation here instead of loading
            // the same relation on the user again.
            ->setRelation('tokens', collect([$personalAccessToken]));

        return JSONResult::success([
            'data' => [
                UserResource::make($user)
                    ->includingAccessToken($personalAccessToken)
            ]
        ]);
    }

    /**
     * Update profile information.
     *
     * @param UpdateProfileRequest $request
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @throws FileCannotBeAdded
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = auth()->user();

        // Track if anything changed
        $changedFields = [];

        // Update username
        if ($request->has('username')) {
            if (empty($user->username) || $user->can_change_username || $user->is_subscribed) {
                $user->slug = $data['username'];
                $user->can_change_username = $user->is_subscribed;
                $changedFields[] = 'username';
            }
        }

        // Update nickname
        if ($request->has('nickname')) {
            $user->username = $data['nickname'];
            $changedFields[] = 'nickname';
        }

        // Update biography
        if ($request->has('biography')) {
            $user->biography = $data['biography'];
            $changedFields[] = 'biography';
        }

        // Update profile image
        if ($request->has('profileImage')) {
            // Remove previous profile image
            $user->clearMediaCollection(MediaCollection::Profile);

            if ($data['profileImage'] != null) {
                // Upload a new profile image, if one was uploaded
                if ($request->hasFile('profileImage') && $request->file('profileImage')->isValid()) {
                    $user->updateImageMedia(MediaCollection::Profile(), $request->file('profileImage'));
                }
            }

            $changedFields[] = 'profile';
        }

        // Update banner
        if ($request->has('bannerImage')) {
            // Remove previous banner
            $user->clearMediaCollection(MediaCollection::Banner);

            if ($data['bannerImage'] != null) {
                // Save the uploaded banner, if one was uploaded
                if ($request->hasFile('bannerImage') && $request->file('bannerImage')->isValid()) {
                    $user->updateImageMedia(MediaCollection::Banner(), $request->file('bannerImage'));
                }
            }

            $changedFields[] = 'banner image';
        }

        // Update language
        if ($request->has('preferredLanguage')) {
            $user->language_id = $data['preferredLanguage'];
            $changedFields[] = 'language';
        }

        // Update tv rating
        if ($request->has('preferredTVRating')) {
            $user->tv_rating = $data['preferredTVRating'];
            $changedFields[] = 'TV rating';
        }

        // Update timezone
        if ($request->has('preferredTimezone')) {
            $user->timezone = $data['preferredTimezone'];
            $changedFields[] = 'timezone';
        }

        // Successful response
        $displayMessage = 'Your settings were saved. ';

        if (count($changedFields)) {
            $displayMessage .= 'You have updated your ' . join(', ', $changedFields) . '.';
            $user->save();
        } else {
            $displayMessage .= 'No information was updated.';
        }

        return JSONResult::success([
            'data' => [
                'username' => $user->slug,
                'nickname' => $user->username,
                'biography' => $user->biography,
                'profileImageURL' => $user->getFirstMediaFullUrl(MediaCollection::Profile()),
                'bannerImageURL' => $user->getFirstMediaFullUrl(MediaCollection::Banner()),
                'preferredLanguage' => $user->language_id,
                'preferredTVRating' => (int) $user->tv_rating,
                'preferredTimezone' => $user->timezone,
            ],
            'message' => $displayMessage,
        ]);
    }

    /**
     * Returns a list of the authenticated user's library.
     *
     * @param GetLibraryRequest $request
     *
     * @return JsonResponse
     * @throws InvalidEnumKeyException
     * @throws InvalidEnumMemberException
     */
    function getLibrary(GetLibraryRequest $request): JsonResponse
    {
        // Get the authenticated user
        $user = auth()->user();

        return (new LibraryController())
            ->index($request, $user);
    }

    /**
     * Returns a list of the authenticated user's achievements.
     *
     * @param GetPaginatedRequest $request
     *
     * @return JsonResponse
     */
    function getAchievements(GetPaginatedRequest $request): JsonResponse
    {
        // Get the authenticated user
        $user = auth()->user();

        return (new AchievementController())
            ->index($request, $user);
    }

    /**
     * Returns a list of the authenticated user's favorites.
     *
     * @param GetUserFavoritesRequest $request
     *
     * @return JsonResponse
     */
    function getFavorites(GetUserFavoritesRequest $request): JsonResponse
    {
        // Get the authenticated user
        $user = auth()->user();

        return (new UserFavoriteController())
            ->index($request, $user);
    }

    /**
     * Returns a list of the user's feed messages.
     *
     * @param GetPaginatedRequest $request
     *
     * @return JsonResponse
     */
    function getFeedMessages(GetPaginatedRequest $request): JsonResponse
    {
        return (new UserController())
            ->getFeedMessages($request, auth()->user());
    }

    /**
     * Returns a list of the user's followers.
     *
     * @param GetPaginatedRequest $request
     *
     * @return JsonResponse
     */
    function getFollowers(GetPaginatedRequest $request): JsonResponse
    {
        return (new FollowingController())
            ->getFollowers($request, auth()->user());
    }

    /**
     * Returns a list of the user's following.
     *
     * @param GetPaginatedRequest $request
     *
     * @return JsonResponse
     */
    function getFollowing(GetPaginatedRequest $request): JsonResponse
    {
        return (new FollowingController())
            ->getFollowing($request, auth()->user());
    }

    /**
     * Returns a list of the user's ratings.
     *
     * @param GetPaginatedRequest $request
     *
     * @return JsonResponse
     */
    function getRatings(GetPaginatedRequest $request): JsonResponse
    {
        return (new UserController())
            ->getRatings($request, auth()->user());
    }

    /**
     * Returns a list of the user's ratings.
     *
     * @param GetUpNextEpisodesRequest $request
     *
     * @return JsonResponse
     */
    function upNextEpisodes(GetUpNextEpisodesRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the feed messages
        $mediaRatings = auth()->user()->up_next_episodes($data['model_id'] ?? null)
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $mediaRatings->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => EpisodeResourceIdentity::collection($mediaRatings),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
