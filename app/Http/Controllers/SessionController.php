<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Requests\CreateSessionRequest;
use App\Http\Requests\UpdateSessionRequest;
use App\Http\Resources\SessionResource;
use App\Http\Resources\UserResourceSmall;
use App\Jobs\FetchSessionLocation;
use App\LoginAttempt;
use App\Notifications\NewSession;
use App\Session;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use KuroAuthToken;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    /**
     * Creates a new session for a user.
     *
     * @param CreateSessionRequest $request
     * @return JsonResponse
     */
    public function create(CreateSessionRequest $request)
    {
        // Check if the request IP is not banned from logging in
        if(!LoginAttempt::isIPAllowedToLogin($request->ip()))
            return JSONResult::error('Oops. You have failed to login too many times. Please grab yourself a snack and try again in a bit.');

        // Fetch the variables and sanitize them
        $email          = $request->input('email');
        $rawPassword    = $request->input('password');
        $device         = $request->input('device');
        $apnToken       = $request->input('apn_device_token');

        // Find the user
        /** @var User $user */
        $user = User::where('email', $email)->first();

        // Compare the passwords
        if(!User::checkPassHash($rawPassword, $user->password)) {
            // Register the login attempt
            LoginAttempt::registerFailedLoginAttempt($request->ip());

            // Show error message
            return JSONResult::error('The entered password does not match.');
        }

        // Check if email is confirmed
        if(!$user->hasConfirmedEmail())
            return JSONResult::error('You have not confirmed your email address yet. Please check your email inbox or spam folder.');

        // Create a new session
        $ip = $request->ip();

        $session = Session::create([
            'user_id'           => $user->id,
            'device'            => $device,
            'secret'            => Str::random(128),
            'expiration_date'   => date('Y-m-d H:i:s', strtotime('90 days')),
            'ip'                => $ip,
            'apn_device_token'  => $apnToken
        ]);

        // Dispatch job to retrieve location
        dispatch(new FetchSessionLocation($session));

        // Send notification
        $user->notify(new NewSession($ip, $session));

        // Show a successful response
        return JSONResult::success([
            'user' => [
                'kuro_auth_token'   => KuroAuthToken::generate($user->id, $session->secret),
                'user'              => UserResourceSmall::make($user),
                'session'           => SessionResource::make($session)
            ]
        ]);
    }

    /**
     * Updates a session's information.
     *
     * @param UpdateSessionRequest $request
     * @param Session $session
     * @return JsonResponse
     */
    function update(UpdateSessionRequest $request, Session $session)
    {
        $data = $request->validated();

        // Track if anything changed
        $changedFields = [];

        // Update APN device token
        if($request->has('apn_device_token')) {
            $session->apn_device_token = $data['apn_device_token'];
            $changedFields[] = 'APN device token';
        }

        // Successful response
        $displayMessage = 'Session update successful. ';

        if(count($changedFields)) {
            $displayMessage .= 'You have updated: ' . join(', ', $changedFields) . '.';
            $session->save();
        }
        else $displayMessage .= 'No information was updated.';

        return JSONResult::success([
            'message' => $displayMessage
        ]);
    }

    /**
     * Checks whether or not a session_secret/user_id combination is valid
     *
     * @param Session $session
     * @return JsonResponse
     * @throws \Exception
     */
    public function validateSession(Session $session) {
        // Check if the session is not expired
        if($session->isExpired()) {
            $session->delete();

            return JSONResult::error('Session is expired.');
        }
        // Session is perfectly valid
        else {
            $session->last_validated = date('Y-m-d H:i:s', time());
            $session->save();

            return JSONResult::success();
        }
    }

    /**
     * Deletes a session
     *
     * @param Request $request
     * @param Session $session
     * @return JsonResponse
     * @throws \Exception
     */
    public function delete(Request $request, Session $session)
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
    public function details(Session $session)
    {
        return JSONResult::success([
            'session' => SessionResource::make($session)
        ]);
    }
}
