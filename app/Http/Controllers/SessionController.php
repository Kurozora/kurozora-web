<?php

namespace App\Http\Controllers;

use App\Events\NewUserSessionEvent;
use App\Events\UserSessionKilledEvent;
use App\Helpers\JSONResult;
use App\LoginAttempt;
use App\Session;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use KuroAuthToken;
use Validator;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    /**
     * Creates a new session
     *
     * @param Request $request
     */
    public function create(Request $request) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'username'  => 'bail|required|exists:' . User::TABLE_NAME . ',username',
            'password'  => 'bail|required',
            'device'    => 'bail|required|max:50'
        ]);

        // Display an error if validation failed
        if($validator->fails())
            (new JSONResult())->setError($validator->errors()->first())->show();

        // Check if the request IP is not banned from logging in
        if(!LoginAttempt::isIPAllowedToLogin($request->ip()))
            (new JSONResult())->setError('Oops. You have failed to login too many times. Please grab yourself a snack and try again in a bit.')->show();

        // Fetch the variables and sanitize them
        $username       = $request->input('username');
        $rawPassword    = $request->input('password');
        $device         = $request->input('device');

        // Find the user
        $foundUser = User::where('username', $username)->first();

        // Compare the passwords
        if(!User::checkPassHash($rawPassword, $foundUser->password)) {
            // Register the login attempt
            LoginAttempt::registerFailedLoginAttempt($request->ip());

            // Show error message
            (new JSONResult())->setError('The entered password does not match.')->show();
        }

        // Check if email is confirmed
        if(!$foundUser->hasConfirmedEmail())
            (new JSONResult())->setError('You have not confirmed your email address yet. Please check your email inbox or spam folder.')->show();

        // Create a new session
        $loginIPAddress = $request->ip();

        $newSession = Session::create([
            'user_id'           => $foundUser->id,
            'device'            => $device,
            'secret'            => Str::random(128),
            'expiration_date'   => date('Y-m-d H:i:s', strtotime('90 days')),
            'ip'                => $loginIPAddress
        ]);

        // Fire event
        event(new NewUserSessionEvent($newSession));

        // Show a successful response
        (new JSONResult())->setData([
            'user' => [
                'id'                => $foundUser->id,
                'kuro_auth_token'   => KuroAuthToken::generate($foundUser->id, $newSession->secret),
                'session_id'        => $newSession->id,
                'role'              => $foundUser->role
            ]
        ])->show();
    }

    /**
     * Checks whether or not a session_secret/user_id combination is valid
     *
     * @param Session $session
     * @throws \Exception
     */
    public function validateSession(Session $session) {
        // Check if the session is not expired
        if($session->isExpired()) {
            (new JSONResult())->setError('Session is expired.')->show();
            $session->delete();
        }
        // Session is perfectly valid
        else {
            $session->last_validated = date('Y-m-d H:i:s', time());
            $session->save();

            (new JSONResult())->show();
        }
    }

    /**
     * Deletes a session
     *
     * @param Request $request
     * @param Session $session
     * @throws \Exception
     */
    public function delete(Request $request, Session $session) {
        // Fire event
        event(new UserSessionKilledEvent(Auth::id(), $session->id, 'Session killed manually by user.', $request['session_id']));

        // Delete the session
        $session->delete();

        (new JSONResult())->show();
    }

    /**
     * Displays session information
     *
     * @param Session $session
     */
    public function details(Session $session) {
        (new JSONResult())->setData([
            'session' => $session->formatForSessionDetails()
        ])->show();
    }
}
