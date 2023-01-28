<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\MediaCollection;
use App\Enums\UserLibraryType;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetAccessTokensRequest;
use App\Http\Requests\GetFeedMessagesRequest;
use App\Http\Requests\GetFollowersRequest;
use App\Http\Requests\GetFollowingRequest;
use App\Http\Requests\GetUserFavoritesRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\AccessTokenResource;
use App\Http\Resources\AnimeResourceBasic;
use App\Http\Resources\FeedMessageResource;
use App\Http\Resources\GameResourceBasic;
use App\Http\Resources\MangaResourceBasic;
use App\Http\Resources\SessionResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserResourceBasic;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\PersonalAccessToken;
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
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        // Get authenticated session
        $bearerToken = $request->bearerToken();
        $personalAccessToken = PersonalAccessToken::findToken($bearerToken);

        return JSONResult::success([
            'data' => [
                UserResource::make($personalAccessToken->user)->includingAccessToken($personalAccessToken)
            ]
        ]);
    }

    /**
     * Update profile information.
     *
     * @param UpdateProfileRequest $request
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
            if (empty($user->username) || $user->is_subscribed) {
                $user->slug = $data['username'];
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

        // Successful response
        $displayMessage = 'Your settings were saved. ';

        if (count($changedFields)) {
            $displayMessage .= 'You have updated your ' . join(', ', $changedFields) . '.';
            $user->save();
        } else {
            $displayMessage .= 'No information was updated.';
        }

        return JSONResult::success([
            'data'      => [
                'username'          => $user->slug,
                'nickname'          => $user->username,
                'biography'         => $user->biography,
                'profileImageURL'   => $user->getFirstMediaFullUrl(MediaCollection::Profile()),
                'bannerImageURL'    => $user->getFirstMediaFullUrl(MediaCollection::Banner())
            ],
            'message'   => $displayMessage,
        ]);
    }

    /**
     * Returns the current active access tokens for a user
     *
     * @param GetAccessTokensRequest $request
     * @return JsonResponse
     */
    public function getAccessTokens(GetAccessTokensRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the authenticated user
        $user = auth()->user();

        // Get paginated sessions except current session
        $tokens = $user->tokens()->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $tokens->nextPageUrl());

        return JSONResult::success([
            'data' => AccessTokenResource::collection($tokens),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns a list of the authenticated user's favorite anime.
     *
     * @param GetUserFavoritesRequest $request
     * @return JsonResponse
     */
    function getFavorites(GetUserFavoritesRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the authenticated user
        $user = auth()->user();

        // Get morph class
        $morphClass = match ((int) ($data['library'] ?? UserLibraryType::Anime)) {
            UserLibraryType::Manga => Manga::class,
            UserLibraryType::Game => Game::class,
            default => Anime::class,
        };

        // Paginate the user favorites
        $userFavorites = $user->whereFavorited($morphClass)
            ->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $userFavorites->nextPageUrl());

        // Get data collection
        $data = match ((int) ($data['library'] ?? UserLibraryType::Anime)) {
            UserLibraryType::Manga => MangaResourceBasic::collection($userFavorites),
            UserLibraryType::Game => GameResourceBasic::collection($userFavorites),
            default => AnimeResourceBasic::collection($userFavorites),
        };

        // Show successful response
        return JSONResult::success([
            'data' => $data,
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns a list of the user's feed messages.
     *
     * @param GetFeedMessagesRequest $request
     * @return JsonResponse
     */
    function getFeedMessages(GetFeedMessagesRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the authenticated user
        $user = auth()->user();

        // Get the feed messages
        $feedMessages = $user->feed_messages()
            ->orderByDesc('created_at')
            ->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $feedMessages->nextPageUrl());

        return JSONResult::success([
            'data' => FeedMessageResource::collection($feedMessages),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns a list of the user's followers.
     *
     * @param GetFollowersRequest $request
     * @return JsonResponse
     */
    function getFollowers(GetFollowersRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the authenticated user
        $user = auth()->user();

        // Get the followers
        $followers = $user->followers()->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $followers->nextPageUrl());

        return JSONResult::success([
            'data' => UserResourceBasic::collection($followers),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns a list of the user's following.
     *
     * @param GetFollowingRequest $request
     * @return JsonResponse
     */
    function getFollowing(GetFollowingRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the authenticated user
        $user = auth()->user();

        // Get the following
        $following = $user->following()->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $following->nextPageUrl());

        return JSONResult::success([
            'data' => UserResourceBasic::collection($following),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns the current active sessions for a user
     *
     * @param GetAccessTokensRequest $request
     * @return JsonResponse
     */
    public function getSessions(GetAccessTokensRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the authenticated user
        $user = auth()->user();

        // Get paginated sessions except current session
        $sessions = $user->sessions()->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $sessions->nextPageUrl());

        return JSONResult::success([
            'data' => SessionResource::collection($sessions),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
