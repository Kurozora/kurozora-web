<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Requests\CreateSessionAttributeRequest;
use App\Http\Requests\GetPaginatedRequest;
use App\Http\Requests\UpdateSessionAttributeRequest;
use App\Http\Resources\AccessTokenResource;
use App\Http\Resources\UserResource;
use App\Models\LoginAttempt;
use App\Models\PersonalAccessToken;
use App\Models\User;
use Hash;
use Illuminate\Http\JsonResponse;
use Laravel\Nova\Exceptions\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class AccessTokenController
{
    /**
     * Returns the current active access tokens for a user
     *
     * @param GetPaginatedRequest $request
     * @return JsonResponse
     */
    public function index(GetPaginatedRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the authenticated user
        $user = auth()->user();

        // Get paginated sessions except current session
        $tokens = $user->tokens()
            ->with(['session_attribute'])
            ->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $tokens->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => AccessTokenResource::collection($tokens),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Displays token information
     *
     * @param PersonalAccessToken $personalAccessToken
     * @return JsonResponse
     */
    public function details(PersonalAccessToken $personalAccessToken): JsonResponse
    {
        $personalAccessToken->load(['session_attribute']);

        return JSONResult::success([
            'data' => AccessTokenResource::collection([$personalAccessToken])
        ]);
    }

    /**
     * Creates a new session for a user.
     *
     * @param CreateSessionAttributeRequest $request
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws TooManyRequestsHttpException
     */
    public function create(CreateSessionAttributeRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Check if the request IP is not banned from logging in
        if (!LoginAttempt::isIPAllowedToLogin($request->ip())) {
            throw new TooManyRequestsHttpException(300, 'You have failed to sign in too many times. Please grab yourself a snack and try again in a bit.');
        }

        // Find the user
        $user = User::where('email', $data['email'])
            ->with([
                'badges' => function ($query) {
                    $query->with(['media']);
                },
                'media',
                'tokens' => function ($query) {
                    $query->orderBy('last_used_at', 'desc')
                        ->limit(1);
                },
                'sessions' => function ($query) {
                    $query->orderBy('last_activity', 'desc')
                        ->limit(1);
                },
            ])
            ->withCount(['followers', 'followedModels as following_count', 'mediaRatings'])
            ->first();

        // Compare the passwords
        if (!$user || !Hash::check($data['password'], $user->password)) {
            // Register the sign in attempt
            LoginAttempt::registerFailedLoginAttempt($request->ip());

            // Throw authorization error message
            throw new AuthenticationException('Your Kurozora Account or password was incorrect.');
        }

        // Check if email is confirmed
        if (!$user->hasVerifiedEmail()) {
            throw new AuthenticationException('You have not confirmed your email address yet. Please check your email inbox or spam folder.');
        }

        // Create new token
        $newToken = $user->createToken($user->username . '’s ' . $data['device_model']);
        /** @var PersonalAccessToken $personalAccessToken */
        $personalAccessToken = $newToken->accessToken;

        // Create a new session attribute
        $user->createSessionAttributes($personalAccessToken, [
            'platform'          => $data['platform'],
            'platform_version'  => $data['platform_version'],
            'device_vendor'     => $data['device_vendor'],
            'device_model'      => $data['device_model'],
        ], true);

        return JSONResult::success([
            'data'                  => [
                UserResource::make($user)->includingAccessToken($personalAccessToken)
            ],
            'authenticationToken'   => $newToken->plainTextToken
        ]);
    }

    /**
     * Updates a session's information.
     *
     * @param UpdateSessionAttributeRequest $request
     * @param PersonalAccessToken $personalAccessToken
     * @return JsonResponse
     */
    function update(UpdateSessionAttributeRequest $request, PersonalAccessToken $personalAccessToken): JsonResponse
    {
        $data = $request->validated();

        // Track if anything changed
        $changedFields = [];

        // Update APN device token
        if ($request->has('apn_device_token')) {
            $personalAccessToken->session_attribute->apn_device_token = $data['apn_device_token'];
            $changedFields[] = 'APN device token';
        }

        // Successful response
        $displayMessage = 'Token update successful. ';

        if (count($changedFields)) {
            $displayMessage .= 'You have updated: ' . join(', ', $changedFields) . '.';
            $personalAccessToken->session_attribute->save();
        } else {
            $displayMessage .= 'No information was updated.';
        }

        return JSONResult::success([
            'message' => $displayMessage
        ]);
    }

    /**
     * Deletes a session
     *
     * @param PersonalAccessToken $personalAccessToken
     * @return JsonResponse
     */
    public function delete(PersonalAccessToken $personalAccessToken): JsonResponse
    {
        // Delete the token
        $personalAccessToken->delete();

        return JSONResult::success();
    }
}
