<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Helpers\KuroMail;
use App\Session;
use App\User;
use App\LoginAttempt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Validator;
use Illuminate\Http\Request;

class UserController extends Controller
{

    /**
     * Registers a new user
     *
     * @param Request $request
     * @throws \Throwable
     */
    public function register(Request $request) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'username'  => 'bail|required|min:3|max:50|alpha_dash|unique:users,username',
            'email'     => 'bail|required|max:255|email|unique:users,email',
            'password'  => 'bail|required|min:5|max:255'
        ]);

        // Display an error if validation failed
        if($validator->fails())
            (new JSONResult())->setError($validator->errors()->first())->show();

        $fileName = null;

        // Check if a valid avatar was uploaded
        $imgValidator = Validator::make($request->all(), [
            'profileImage' => 'required|mimes:jpeg,jpg,png|max:700',
        ]);

        if( $request->hasFile('profileImage') &&
            $request->file('profileImage')->isValid() &&
            !$imgValidator->fails()
        ) {
            // Save the uploaded avatar
            $fileName = 'avatar_' . str_random('30') . '.' . $request->file('profileImage')->extension();;

            $request->file('profileImage')->storeAs(User::USER_UPLOADS_PATH, $fileName);
        }

        // Fetch the variables and sanitize them
        $username       = $request->input('username');
        $email          = $request->input('email');
        $rawPassword    = $request->input('password');

        // Create the user
        $newUser = User::create([
            'username'              => $username,
            'email'                 => $email,
            'password'              => Hash::make($rawPassword),
            'email_confirmation_id' => str_random(50),
            'avatar'                => $fileName
        ]);

        // Send the user an email
        $emailData = [
            'username'          => $username,
            'confirmation_id'   => $newUser->email_confirmation_id
        ];

        (new KuroMail())
            ->setTo($newUser->email)
            ->setSubject('Your Kurozora account registration')
            ->setContent(view('email.confirmation_email', $emailData)->render())
            ->send();

        // Show a successful response
        (new JSONResult())->show();
    }

    /**
     * Logs the user in (create session)
     *
     * @param Request $request
     */
    public function login(Request $request) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'username'  => 'bail|required|exists:users,username',
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
        if(!Hash::check($rawPassword, $foundUser->password)) {
            // Register the login attempt
            LoginAttempt::registerFailedLoginAttempt($request->ip());

            // Show error message
            (new JSONResult())->setError('The entered password does not match.')->show();
        }

        // Check if email is confirmed
        if(!$foundUser->hasConfirmedEmail())
            (new JSONResult())->setError('You have not confirmed your email address yet. Please check your email inbox or spam folder.')->show();

        // Create a new session
        $newSession = Session::create([
            'user_id'           => $foundUser->id,
            'device'            => $device,
            'secret'            => str_random(128),
            'expiration_date'   => date('Y-m-d H:i:s', strtotime('90 days')),
            'ip'                => $request->ip()
        ]);

        // Show a successful response
        (new JSONResult())->setData([
            'session_secret'    => $newSession->secret,
            'user_id'           => $foundUser->id,
            'role'              => $foundUser->role
        ])->show();
    }

    /**
     * Logs the user out (destroys the session)
     *
     * @param Request $request
     */
    public function logout(Request $request) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'session_secret'    => 'bail|required|exists:sessions,secret',
            'user_id'           => 'bail|required|numeric|exists:users,id'
        ]);

        // Display an error if validation failed
        if($validator->fails())
            (new JSONResult())->setError('Failed to log out. Please restart the app.')->show();

        // Fetch the variables
        $givenSecret    = $request->input('session_secret');
        $givenUserID    = $request->input('user_id');

        // Find the session
        $foundSession = Session::where([
            ['user_id', '=', $givenUserID],
            ['secret',  '=', $givenSecret]
        ])->first();

        // Check if any session was found
        if(!$foundSession)
            (new JSONResult())->setError('An error occurred. Please reach out to an administrator.')->show();

        // Delete the session
        $foundSession->delete();

        // Show a successful response
        (new JSONResult())->show();
    }

    /**
     * Returns the profile details for a user
     *
     * @param Request $request
     * @param $id
     */
    public function profile(Request $request, $id) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'session_secret'        => 'bail|required|exists:sessions,secret',
            'user_id'               => 'bail|required|numeric|exists:users,id'
        ]);

        // Fetch the variables
        $givenSecret        = $request->input('session_secret');
        $givenUserID        = $request->input('user_id');

        // Check authentication
        if($validator->fails() || !User::authenticateSession($givenUserID, $givenSecret))
            (new JSONResult())->setError(JSONResult::ERROR_SESSION_REJECTED)->show();

        // Check if this user profile exists
        $profileUser = User::find($id);

        if(!$profileUser)
            (new JSONResult())->setError('This user does not exist.')->show();

        // Show profile response
        (new JSONResult())->setData([
            'profile' => [
                'username'          => $profileUser->username,
                'biography'         => $profileUser->biography,
                'avatar_url'        => $profileUser->getAvatarURL(),
                'banner_url'        => $profileUser->banner,
                'follower_count'    => $profileUser->follower_count,
                'following_count'   => $profileUser->following_count,
                'reputation_count'  => $profileUser->reputation_count
            ]
        ])->show();
    }


    /**
     * Returns the current active sessions for a user
     *
     * @param Request $request
     */
    public function getSessions(Request $request) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'session_secret'        => 'bail|required|exists:sessions,secret',
            'user_id'               => 'bail|required|numeric|exists:users,id'
        ]);

        // Fetch the variables
        $givenSecret        = $request->input('session_secret');
        $givenUserID        = $request->input('user_id');

        // Check authentication
        if($validator->fails() || !User::authenticateSession($givenUserID, $givenSecret))
            (new JSONResult())->setError(JSONResult::ERROR_SESSION_REJECTED)->show();

        // Get the other sessions and put them in an array
        $otherSessions = [];
        $sessions = Session::where([
            ['user_id', '=',    $givenUserID],
            ['secret',  '!=',   $givenSecret]
        ])->get();

        foreach($sessions as $session)
            $otherSessions[] = $session->formatForSessionList();

        // Get the current session
        $curSession = Session::where([
            ['user_id', '=',    $givenUserID],
            ['secret',  '=',    $givenSecret]
        ])->first();

        $curSession = $curSession->formatForSessionList();

        (new JSONResult())->setData([
            'current_session'   => $curSession,
            'other_sessions'    => $otherSessions
        ])->show();
    }

    /**
     * Deletes a user session
     *
     * @param Request $request
     */
    public function deleteSession(Request $request) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'session_secret' => 'bail|required|exists:sessions,secret',
            'user_id' => 'bail|required|numeric|exists:users,id',
            'del_session_id' => 'bail|required|numeric|exists:sessions,id'
        ]);

        // Fetch the variables
        $givenSecret = $request->input('session_secret');
        $givenUserID = $request->input('user_id');
        $delSessionID = $request->input('del_session_id');

        // Check authentication
        if ($validator->fails() || !User::authenticateSession($givenUserID, $givenSecret))
            (new JSONResult())->setError('The server rejected your credentials. Please restart the app.')->show();

        // Delete the session
        Session::destroy($delSessionID);

        (new JSONResult())->show();
    }

    /**
     * Email confirmation page
     *
     * @param $confirmationID
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function confirmEmail($confirmationID, Request $request) {
        // Try to find a user with this confirmation ID
        $foundUser = User::where('email_confirmation_id', $confirmationID)->first();

        // No user found
        if(!$foundUser)
            return view('website.email_confirm_page', ['success' => false]);

        // Confirm their email and show the page
        $foundUser->email_confirmation_id = null;
        $foundUser->save();

        return view('website.email_confirm_page', ['success' => true]);
    }
}
