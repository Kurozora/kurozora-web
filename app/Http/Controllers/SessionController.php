<?php

namespace App\Http\Controllers;

use App\Events\NewUserSessionEvent;
use App\Events\UserSessionKilledEvent;
use App\Helpers\JSONResult;
use App\LoginAttempt;
use App\Session;
use App\User;
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
            'secret'            => str_random(128),
            'expiration_date'   => date('Y-m-d H:i:s', strtotime('90 days')),
            'ip'                => $loginIPAddress
        ]);

        // Fire event
        event(new NewUserSessionEvent($newSession));

        // Show a successful response
        (new JSONResult())->setData([
            'kuro_auth_token'   => KuroAuthToken::generate($foundUser->id, $newSession->secret),
            'session_id'        => $newSession->id,
            'user_id'           => $foundUser->id,
            'role'              => $foundUser->role
        ])->show();
    }

    /**
     * Checks whether or not a session_secret/user_id combination is valid
     *
     * @param Request $request
     */
    public function validate(Request $request) {
        // Find the session
        $foundSession = Session::where([
            ['user_id', '=', $request->user_id],
            ['secret',  '=', $request->session_secret]
        ])->first();

        // Check if the session is not expired
        if($foundSession->isExpired()) {
            (new JSONResult())->setError('Session is expired.')->show();
            $foundSession->delete();
        }
        // Session is perfectly valid
        else {
            $foundSession->last_validated = date('Y-m-d H:i:s', time());
            $foundSession->save();

            (new JSONResult())->show();
        }
    }

    /**
     * Deletes a session
     *
     * @param Request $request
     * @param $sessionID
     */
    public function delete(Request $request, $sessionID) {
        // Fetch the variables
        $delSessionID = $sessionID;

        // Find the session
        $foundSession = Session::where([
            ['id'       , '=', $sessionID],
            ['user_id'  , '=', $request->user_id]
        ])->first();

        if($foundSession === null)
            (new JSONResult())->setError('Unable to delete this session.')->show();

        // Fire event
        event(new UserSessionKilledEvent($request->user_id, $delSessionID, 'Session killed manually by user.', $request->session_id));

        // Delete the session
        $foundSession->delete();

        (new JSONResult())->show();
    }

    /**
     * Displays session information
     *
     * @param Request $request
     * @param $sessionID
     */
    public function details(Request $request, $sessionID) {
        // Find the session
        $foundSession = Session::where([
            ['id'       , '=', $sessionID],
            ['user_id'  , '=', $request->user_id]
        ])->first();

        // Session not found
        if($foundSession === null)
            (new JSONResult())->setError('The given session does not exist or does not belong to you.')->show();

        (new JSONResult())->setData([
            'session' => $foundSession->formatForSessionDetails()
        ])->show();
    }
}
