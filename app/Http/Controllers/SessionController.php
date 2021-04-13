<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Helpers\KuroAuthToken;
use App\Http\Requests\CreateSessionRequest;
use App\Http\Requests\UpdateSessionRequest;
use App\Http\Resources\SessionResource;
use App\Http\Resources\UserResource;
use App\Models\LoginAttempt;
use App\Models\Session;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Laravel\Nova\Exceptions\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class SessionController extends Controller
{
    /**
     * Creates a new session for a user.
     *
     * @param CreateSessionRequest $request
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws TooManyRequestsHttpException
     */
    public function create(CreateSessionRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Check if the request IP is not banned from logging in
        if (!LoginAttempt::isIPAllowedToLogin($request->ip()))
            throw new TooManyRequestsHttpException(300, 'You have failed to login too many times. Please grab yourself a snack and try again in a bit.');

        // Find the user
        /** @var User $user */
        $user = User::where('email', $data['email'])->first();

        // Compare the passwords
        if (!User::checkPassHash($data['password'], $user->password)) {
            // Register the login attempt
            LoginAttempt::registerFailedLoginAttempt($request->ip());

            // Throw authorization error message
            throw new AuthenticationException('Your Kurozora ID or password was incorrect.');
        }

        // Check if email is confirmed
        if (!$user->hasConfirmedEmail())
            throw new AuthenticationException('You have not confirmed your email address yet. Please check your email inbox or spam folder.');

        // Create a new session
        $session = $user->createSession([
            'platform'          => $data['platform'],
            'platform_version'  => $data['platform_version'],
            'device_vendor'     => $data['device_vendor'],
            'device_model'      => $data['device_model'],
        ]);

        return JSONResult::success([
            'data'                  => [
                UserResource::make($user)->includingSession($session)
            ],
            'authenticationToken'   => KuroAuthToken::generate($user->id, $session->secret)
        ]);
    }

    /**
     * Updates a session's information.
     *
     * @param UpdateSessionRequest $request
     * @param Session $session
     * @return JsonResponse
     */
    function update(UpdateSessionRequest $request, Session $session): JsonResponse
    {
        $data = $request->validated();

        // Track if anything changed
        $changedFields = [];

        // Update APN device token
        if ($request->has('apn_device_token')) {
            $session->apn_device_token = $data['apn_device_token'];
            $changedFields[] = 'APN device token';
        }

        // Successful response
        $displayMessage = 'Session update successful. ';

        if (count($changedFields)) {
            $displayMessage .= 'You have updated: ' . join(', ', $changedFields) . '.';
            $session->save();
        }
        else $displayMessage .= 'No information was updated.';

        return JSONResult::success([
            'message' => $displayMessage
        ]);
    }

    /**
     * Deletes a session
     *
     * @param Session $session
     * @return JsonResponse
     * @throws Exception
     */
    public function delete(Session $session): JsonResponse
    {
        // Delete the session
        $session->delete();

        return JSONResult::success();
    }

    /**
     * Displays session information
     *
     * @param Session $session
     * @return JsonResponse
     */
    public function details(Session $session): JsonResponse
    {
        return JSONResult::success([
            'data' => SessionResource::collection([$session])
        ]);
    }
}
