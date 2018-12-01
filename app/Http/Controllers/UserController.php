<?php

namespace App\Http\Controllers;

use App\Anime;
use App\Events\NewUserSession;
use App\Helpers\JSONResult;
use App\Helpers\KuroMail;
use App\PasswordReset;
use App\Session;
use App\User;
use App\LoginAttempt;
use App\UserLibrary;
use App\UserNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Pusher\Pusher;
use Pusher\PusherException;
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
            'username'  => 'bail|required|min:3|max:50|alpha_dash|unique:user,username',
            'email'     => 'bail|required|max:255|email|unique:user,email',
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
            'password'              => User::hashPass($rawPassword),
            'email_confirmation_id' => str_random(50),
            'avatar'                => $fileName
        ]);

        // Send the user an email
        $emailData = [
            'title'             => 'Email confirmation',
            'username'          => $username,
            'confirmation_url'  => env('APP_URL', '') . '/confirmation/' . $newUser->email_confirmation_id
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
            'username'  => 'bail|required|exists:user,username',
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
        event(new NewUserSession($foundUser->id, $newSession->id, $loginIPAddress));

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
            'session_secret'    => 'bail|required|exists:' . Session::TABLE_NAME . ',secret',
            'user_id'           => 'bail|required|numeric|exists:user,id'
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
            'session_secret'        => 'bail|required|exists:' . Session::TABLE_NAME . ',secret',
            'user_id'               => 'bail|required|numeric|exists:user,id'
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

        // Get their badges
        $badges = [];
        $rawBadges = $profileUser->getBadges();

        foreach($rawBadges as $rawBadge)
            $badges[] = $rawBadge->formatForResponse();

        // Show profile response
        (new JSONResult())->setData([
            'profile' => [
                'username'          => $profileUser->username,
                'biography'         => $profileUser->biography,
                'avatar_url'        => $profileUser->getAvatarURL(),
                'banner_url'        => $profileUser->banner,
                'follower_count'    => $profileUser->getFollowerCount(),
                'following_count'   => $profileUser->getFollowingCount(),
                'reputation_count'  => $profileUser->getReputationCount(),
                'badges'            => $badges
            ]
        ])->show();
    }

    /**
     * Make (severe) changes to the User account
     *
     * @param Request $request
     */
    public function updateAccount(Request $request) {
        // @todo FINISH THIS
        // Validate the inputs
        /*$validator = Validator::make($request->all(), [
            'current_password'  => 'bail|required',
            'email'             => 'bail|required|email'
        ]);

        // Display an error if validation failed
        if($validator->fails())
            (new JSONResult())->setError($validator->errors()->first())->show();

        */
    }

    /**
     * Requests a password reset link to be sent to the email address
     *
     * @param Request $request
     */
    public function resetPassword(Request $request) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'email' => 'bail|required|email'
        ]);

        // Display an error if validation failed
        if($validator->fails())
            (new JSONResult())->setError($validator->errors()->first())->show();

        $enteredEmail = $request->input('email');

        // Try to find the user with this email
        $user = User::where('email', $enteredEmail)->first();

        // There is a user with this email
        if($user && $user->hasConfirmedEmail()) {
            $compareTime = Carbon::now()->subHours(PasswordReset::VALID_HOURS);

            // Check if a password reset was requested recently
            $pReset = PasswordReset::where([
                ['user_id',     '=',    $user->id],
                ['created_at',  '>=',   $compareTime]
            ])->first();

            // No password reset has been requested recently
            if(!$pReset) {
                // Create password reset
                $createdReset = PasswordReset::create([
                    'user_id'   => $user->id,
                    'ip'        => $request->ip(),
                    'token'     => PasswordReset::genToken()
                ]);

                // Send notification email
                $createdReset->sendEmailNotification();
            }
        }

        // Show successful response
        (new JSONResult())->show();
    }

    /**
     * Matches the given details and checks whether or not the user has
     * access to a private user channel for Pusher
     *
     * @param Request $request
     * @return string
     * @throws PusherException
     */
    public function authenticateChannel(Request $request) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'session_secret'        => 'bail|required|exists:' . Session::TABLE_NAME . ',secret',
            'user_id'               => 'bail|required|numeric|exists:' . User::TABLE_NAME . ',id',
            'channel_name'          => 'bail|required',
            'socket_id'             => 'bail|required'
        ]);

        // Fetch the variables
        $givenSecret    = $request->input('session_secret');
        $givenUserID    = $request->input('user_id');
        $givenChannel   = $request->input('channel_name');
        $givenSocket    = $request->input('socket_id');

        // Check validator
        if($validator->fails()) {
            return abort(403, 'Insufficient parameters: ' . $validator->errors()->first());
        }

        // Check authentication
        if(!User::authenticateSession($givenUserID, $givenSecret)) {
            return abort(403, 'Unauthorized action.');
        }

        // Extract the user ID from the channel
        $regexText = preg_match('/private-user.([0-9]*)/', $givenChannel, $matches);

        // Invalid channel name
        if(!$regexText || count($matches) < 2) {
            return abort(403, 'Invalid channel.');
        }

        // Get the user ID in the channel
        $channelUserID = (int) $matches[1];

        // Check if this is the channel of the authenticated user
        if($channelUserID != $givenUserID) {
            return abort(403, 'Not your channel.');
        }

        // Create pusher instance
        $pusher = new Pusher(
            config('broadcasting.connections.pusher.key'),
            config('broadcasting.connections.pusher.secret'),
            config('broadcasting.connections.pusher.app_id'),
            config('broadcasting.connections.pusher.options')
        );

        // Authenticate
        try {
            return $pusher->socket_auth($givenChannel, $givenSocket);
        }
        catch(PusherException $p) {
            return abort(403, 'Pusher failed to authenticate.');
        }
    }

    /**
     * Returns the current active sessions for a user
     *
     * @param Request $request
     */
    public function getSessions(Request $request) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'session_secret'        => 'bail|required|exists:' . Session::TABLE_NAME . ',secret',
            'user_id'               => 'bail|required|numeric|exists:' . User::TABLE_NAME . ',id'
        ]);

        // Fetch the variables
        $givenSecret = $request->input('session_secret');
        $givenUserID = $request->input('user_id');

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
     * Gets the user's library depending on the status
     *
     * @param Request $request
     */
    public function getLibrary(Request $request) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'session_secret'    => 'bail|required|exists:' . Session::TABLE_NAME . ',secret',
            'user_id'           => 'bail|required|numeric|exists:' . User::TABLE_NAME . ',id',
            'status'            => 'bail|required|string'
        ]);

        // Fetch the variables
        $givenSecret = $request->input('session_secret');
        $givenUserID = $request->input('user_id');

        // Check authentication
        if($validator->fails() || !User::authenticateSession($givenUserID, $givenSecret))
            (new JSONResult())->setError(JSONResult::ERROR_SESSION_REJECTED)->show();

        $givenStatus = $request->input('status');

        // Check the status
        $foundStatus = UserLibrary::getStatusFromString($givenStatus);

        if($foundStatus == null)
            (new JSONResult())->setError('The given status is not a valid one.')->show();

        /*
         * Selects the necessary data from the Anime that are ..
         * .. in the user's library, that match the given status
         */
        $columnsToSelect = [
            Anime::TABLE_NAME . '.id',
            Anime::TABLE_NAME . '.title',
            Anime::TABLE_NAME . '.episode_count',
            Anime::TABLE_NAME . '.average_rating',
            Anime::TABLE_NAME . '.cached_poster_thumbnail AS poster_thumbnail',
            Anime::TABLE_NAME . '.cached_background_thumbnail AS background_thumbnail'
        ];

        $animeInfo = DB::table(Anime::TABLE_NAME)
            ->join(UserLibrary::TABLE_NAME, function ($join) {
                $join->on(Anime::TABLE_NAME . '.id', '=', UserLibrary::TABLE_NAME . '.anime_id');
            })
            ->where([
                [UserLibrary::TABLE_NAME . '.user_id', '=', $givenUserID],
                [UserLibrary::TABLE_NAME . '.status',  '=', $foundStatus]
            ])
            ->get($columnsToSelect);

        (new JSONResult())->setData(['anime' => $animeInfo])->show();
    }

    /**
     * Adds an Anime to the user's library
     *
     * @param Request $request
     */
    public function addLibrary(Request $request) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'session_secret'    => 'bail|required|exists:' . Session::TABLE_NAME . ',secret',
            'user_id'           => 'bail|required|numeric|exists:user,id',
            'anime_id'          => 'bail|required|numeric|exists:anime,id',
            'status'            => 'bail|required|string'
        ]);

        // Fetch the variables
        $givenSecret = $request->input('session_secret');
        $givenUserID = $request->input('user_id');

        // Check authentication
        if($validator->fails() || !User::authenticateSession($givenUserID, $givenSecret))
            (new JSONResult())->setError(JSONResult::ERROR_SESSION_REJECTED)->show();

        $givenAnimeID = $request->input('anime_id');
        $givenStatus = $request->input('status');

        // Check the status
        $foundStatus = UserLibrary::getStatusFromString($givenStatus);

        if($foundStatus == null)
            (new JSONResult())->setError('The given status is not a valid one.')->show();

        // Check if this user already has the Anime in their library
        $oldLibraryItem = UserLibrary::where([
            ['user_id',     '=',    $givenUserID],
            ['anime_id',    '=',    $givenAnimeID]
        ])->first();

        // The user already had the anime in their library, update the status
        if($oldLibraryItem != null) {
            if($oldLibraryItem->status != $foundStatus) {
                $oldLibraryItem->status = $foundStatus;
                $oldLibraryItem->save();
            }
        }
        // Add a new library item
        else {
            UserLibrary::create([
                'user_id'   => $givenUserID,
                'anime_id'  => $givenAnimeID,
                'status'    => $foundStatus
            ]);
        }

        // Successful response
        (new JSONResult())->show();
    }

    /**
     * Removes an Anime from the user's library
     *
     * @param Request $request
     */
    public function removeLibrary(Request $request) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'session_secret'    => 'bail|required|exists:' . Session::TABLE_NAME . ',secret',
            'user_id'           => 'bail|required|numeric|exists:user,id',
            'anime_id'          => 'bail|required|numeric|exists:anime,id'
        ]);

        // Fetch the variables
        $givenSecret = $request->input('session_secret');
        $givenUserID = $request->input('user_id');

        // Check authentication
        if($validator->fails() || !User::authenticateSession($givenUserID, $givenSecret))
            (new JSONResult())->setError(JSONResult::ERROR_SESSION_REJECTED)->show();

        $givenAnimeID = $request->input('anime_id');

        // Find the Anime in their library
        $foundAnime = UserLibrary::where([
            ['user_id',     '=',    $givenUserID],
            ['anime_id',    '=',    $givenAnimeID]
        ])->first();

        // Remove this Anime from their library
        if($foundAnime) {
            $foundAnime->delete();

            // Successful response
            (new JSONResult())->show();
        }

        // Unsuccessful response
        (new JSONResult())->setError('This item is not in your library.')->show();
    }

    /**
     * Deletes a user session
     *
     * @param Request $request
     */
    public function deleteSession(Request $request) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'session_secret' => 'bail|required|exists:' . Session::TABLE_NAME . ',secret',
            'user_id' => 'bail|required|numeric|exists:user,id',
            'del_session_id' => 'bail|required|numeric|exists:' . Session::TABLE_NAME . ',id'
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

    /**
     * Password reset page
     *
     * @param $resetToken
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Throwable
     */
    public function resetPasswordPage($resetToken, Request $request) {
        // Try to find a reset with this reset token
        $foundReset = PasswordReset::where('token', $resetToken)->first();

        // No reset found
        if(!$foundReset)
            return view('website.password_reset_page', ['success' => false]);

        $user = User::find($foundReset->user_id);

        if($user) {
            // Reset their password to a temporary one
            $newPass = PasswordReset::genTempPassword();

            $user->password = User::hashPass($newPass);
            $user->save();

            // Delete all their sessions
            Session::where('user_id', $user->id)->delete();

            // Send the user an email with their new password
            $emailData = [
                'title'     => 'Your new password',
                'username'  => $user->username,
                'newPass'   => $newPass
            ];

            (new KuroMail())
                ->setTo($user->email)
                ->setSubject('Your new password')
                ->setContent(view('email.password_reset_new_pass', $emailData)->render())
                ->send();

            // Delete the password reset
            $foundReset->delete();
        }

        // Show successful response
        return view('website.password_reset_page', ['success' => true]);
    }

    /**
     * Returns the notifications for the user
     *
     * @param Request $request
     */
    public function getNotifications(Request $request) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'session_secret'    => 'bail|required|exists:' . Session::TABLE_NAME . ',secret',
            'user_id'           => 'bail|required|numeric|exists:user,id'
        ]);

        // Fetch the variables
        $givenSecret    = $request->input('session_secret');
        $givenUserID    = $request->input('user_id');

        // Check authentication
        if($validator->fails() || !User::authenticateSession($givenUserID, $givenSecret))
            (new JSONResult())->setError('The server rejected your credentials. Please restart the app.')->show();

        // Get their notifications
        $rawNotifications = UserNotification::where('user_id', $givenUserID)
            ->orderBy('created_at', 'DESC')
            ->get();

        $notifications = [];

        foreach($rawNotifications as $rawNotification)
            $notifications[] = $rawNotification->formatForResponse();

        (new JSONResult())->setData(['notifications' => $notifications])->show();
    }
}
