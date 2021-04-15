<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Helpers\KuroAuthToken;
use App\Http\Requests\GetAnimeFavoritesRequest;
use App\Http\Requests\GetFeedMessagesRequest;
use App\Http\Requests\GetFollowersRequest;
use App\Http\Requests\GetFollowingRequest;
use App\Http\Requests\GetSessionsRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\AnimeResourceBasic;
use App\Http\Resources\FeedMessageResource;
use App\Http\Resources\SessionResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserResourceBasic;
use App\Models\Session;
use App\Models\User;
use Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        $sessionID = (int) $request->input('session_id');
        $session = Session::find($sessionID);

        return JSONResult::success([
            'data'                  => [
                UserResource::make($session->user)->includingSession($session)
            ],
            'authenticationToken'   => KuroAuthToken::generate($session->user->id, $session->secret)
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
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $data = $request->validated();

        /** @var User $user */
        $user = Auth::user();

        // Track if anything changed
        $changedFields = [];

        // Update username
        if ($request->has('username')) {
            if (!$user->username_change_available)
                throw new AuthorizationException('The request wasn’t accepted due to not being allowed to change the username.');

            $user->username = $data['username'];
            $changedFields[] = 'username';
        }

        // Update biography
        if ($request->has('biography')) {
            $user->biography = $data['biography'];
            $changedFields[] = 'biography';
        }

        // Update profile image
        if ($request->has('profileImage')) {
            // Remove previous profile image
            $user->clearMediaCollection(User::MEDIA_PROFILE_IMAGE);

            if ($data['profileImage'] != null) {
                // Upload a new profile image, if one was uploaded
                if ($request->hasFile('profileImage') && $request->file('profileImage')->isValid()) {
                    $user->addMediaFromRequest('profileImage')->toMediaCollection(User::MEDIA_PROFILE_IMAGE);
                }
            }

            $changedFields[] = 'profile';
        }

        // Update banner
        if ($request->has('bannerImage')) {
            // Remove previous banner
            $user->clearMediaCollection(User::MEDIA_BANNER_IMAGE);

            if ($data['bannerImage'] != null) {
                // Save the uploaded banner, if one was uploaded
                if ($request->hasFile('bannerImage') && $request->file('bannerImage')->isValid()) {
                    $user->addMediaFromRequest('bannerImage')->toMediaCollection(User::MEDIA_BANNER_IMAGE);
                }
            }

            $changedFields[] = 'banner image';
        }

        // Successful response
        $displayMessage = 'Your settings were saved. ';

        if (count($changedFields)) {
            $displayMessage .= 'You have updated your ' . join(', ', $changedFields) . '.';
            $user->save();
        }
        else $displayMessage .= 'No information was updated.';

        return JSONResult::success([
            'data'      => [
                'biography'         => $user->biography,
                'profileImageURL'   => $user->profile_image_url,
                'bannerImageURL'    => $user->banner_image_url
            ],
            'message'   => $displayMessage,
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
     * Returns a list of the user's feed messages.
     *
     * @param GetFeedMessagesRequest $request
     * @return JsonResponse
     */
    function getFeedMessages(GetFeedMessagesRequest $request): JsonResponse
    {
        $data = $request->validated();

        /** @var User $user */
        $user = Auth::user();

        // Get the feed messages
        $feedMessages = $user->feedMessages()
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
        /** @var User $user */
        $user = Auth::user();

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

        /** @var User $user */
        $user = Auth::user();

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
