<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Requests\CreateSessionAttributeRequest;
use App\Http\Requests\UpdateSessionAttributeRequest;
use App\Http\Resources\AccessTokenResource;
use App\Http\Resources\UserResource;
use App\Models\LoginAttempt;
use App\Models\PersonalAccessToken;
use App\Models\User;
use Auth;
use Hash;
use Illuminate\Http\JsonResponse;
use Laravel\Nova\Exceptions\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class AccessTokenController
{
    /**
     * Displays token information
     *
     * @param PersonalAccessToken $accessToken
     * @return JsonResponse
     */
    public function details(PersonalAccessToken $accessToken): JsonResponse
    {
        return JSONResult::success([
            'data' => AccessTokenResource::collection([$accessToken])
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
            throw new TooManyRequestsHttpException(300, 'You have failed to login too many times. Please grab yourself a snack and try again in a bit.');
        }

        // Find the user
        $user = User::where('email', $data['email'])->first();

        // Compare the passwords
        if (!$user || !Hash::check($data['password'], $user->password)) {
            // Register the login attempt
            LoginAttempt::registerFailedLoginAttempt($request->ip());

            // Throw authorization error message
            throw new AuthenticationException('Your Kurozora ID or password was incorrect.');
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
        ]);

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
     * @return JsonResponse
     */
    function update(UpdateSessionAttributeRequest $request): JsonResponse
    {
        $data = $request->validated();

        /** @var PersonalAccessToken $personalAccessToken */
        $personalAccessToken = Auth::user()->tokens()->firstWhere('token', hash('sha256', $request->bearerToken()));

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
     * @param PersonalAccessToken $accessToken
     * @return JsonResponse
     */
    public function delete(PersonalAccessToken $accessToken): JsonResponse
    {
        // Delete the token
        $accessToken->delete();

        return JSONResult::success();
    }
}
