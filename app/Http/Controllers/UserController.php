<?php

namespace App\Http\Controllers;

use App\Events\UserSessionKilledEvent;
use App\Helpers\JSONResult;
use App\Helpers\KuroMail;
use App\PasswordReset;
use App\Session;
use App\User;
use App\UserNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use PusherHelper;
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
            'confirmation_url'  => url('/confirmation/' . $newUser->email_confirmation_id)
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
     * Logs the user out (destroys the session)
     *
     * @param Request $request
     */
    public function logout(Request $request) {
        // Find the session
        $foundSession = Session::where([
            ['user_id', '=', $request->user_id],
            ['secret',  '=', $request->session_secret]
        ])->first();

        // Check if any session was found
        if(!$foundSession)
            (new JSONResult())->setError('An error occurred. Please reach out to an administrator.')->show();

        // Fire event
        event(new UserSessionKilledEvent($request->user_id, $foundSession->id, 'Session logged out.', $request->session_id));

        // Delete the session
        $foundSession->delete();

        // Show a successful response
        (new JSONResult())->show();
    }

    /**
     * Returns the profile details for a user
     *
     * @param Request $request
     * @param $userID
     */
    public function profile(Request $request, $userID) {
        // Check if this user profile exists
        $profileUser = User::find($userID);

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
     * @param $userID
     * @return string
     */
    public function authenticateChannel(Request $request, $userID) {
        // Check if we can do this for this user
        if($request->user_id != $userID)
            (new JSONResult())->setError('You are not permitted to do this.')->show();

        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'channel_name'          => 'bail|required',
            'socket_id'             => 'bail|required'
        ]);

        // Fetch the variables
        $givenChannel   = $request->input('channel_name');
        $givenSocket    = $request->input('socket_id');

        // Check validator
        if($validator->fails()) {
            return abort(403, $validator->errors()->first());
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
        if($channelUserID != $userID) {
            return abort(403, 'Not your channel.');
        }

        // Create pusher instance
        $pusher = new PusherHelper();

        // Authenticate
        $authentication = $pusher->socketAuth($givenChannel, $givenSocket);

        if($authentication == null) {
            return abort(403, 'Pusher failed to authenticate.');
        }
        else return $authentication;
    }

    /**
     * Returns the current active sessions for a user
     *
     * @param Request $request
     * @param $userID
     */
    public function getSessions(Request $request, $userID) {
        // Check if we can retrieve the sessions of this user
        if($request->user_id != $userID)
            (new JSONResult())->setError('You are not permitted to view this.')->show();

        // Get the other sessions and put them in an array
        $otherSessions = [];

        $sessions = Session::where([
            ['user_id', '=',    $userID],
            ['secret',  '!=',   $request->session_secret]
        ])->get();

        foreach($sessions as $session)
            $otherSessions[] = $session->formatForSessionList();

        // Get the current session
        $curSession = Session::where([
            ['user_id', '=',    $userID],
            ['secret',  '=',    $request->session_secret]
        ])->first();

        $curSession = $curSession->formatForSessionList();

        (new JSONResult())->setData([
            'current_session'   => $curSession,
            'other_sessions'    => $otherSessions
        ])->show();
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
    public function getNotifications(Request $request, $userID) {
        // Check if we can do this for this user
        if($request->user_id != $userID)
            (new JSONResult())->setError('You are not permitted to view this.')->show();

        // Get their notifications
        $rawNotifications = UserNotification::where('user_id', $userID)
            ->orderBy('created_at', 'DESC')
            ->get();

        $notifications = [];

        foreach($rawNotifications as $rawNotification)
            $notifications[] = $rawNotification->formatForResponse();

        (new JSONResult())->setData(['notifications' => $notifications])->show();
    }

    /**
     * Retrieves Anime search results
     *
     * @param Request $request
     */
    public function search(Request $request) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'query' => 'bail|required|string|min:1'
        ]);

        // Check validator
        if($validator->fails())
            (new JSONResult())->setError($validator->errors()->first())->show();

        $searchQuery = $request->input('query');

        // Search for the user
        $rawSearchResults = User::search($searchQuery)->limit(User::MAX_SEARCH_RESULTS)->get();

        // Format the results
        $displayResults = [];

        foreach($rawSearchResults as $user) {
            $displayResults[] = [
                'id'                => $user->id,
                'username'          => $user->username,
                'reputation_count'  => $user->getReputationCount(),
                'avatar'            => $user->getAvatarURL()
            ];
        }

        // Show response
        (new JSONResult())->setData([
            'max_search_results'    => User::MAX_SEARCH_RESULTS,
            'results'               => $displayResults
        ])->show();
    }

    /**
     * Update a user's information
     *
     * @param Request $request
     * @param $userID
     */
    public function update(Request $request, $userID) {
        // Check if we can do this for this user
        if($request->user_id != $userID)
            (new JSONResult())->setError('You are not permitted to do this.')->show();

        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'email'         => 'bail|email',
            'biography'     => 'bail|max:' . User::BIOGRAPHY_LIMIT,
            'profileImage'  => 'bail|mimes:jpeg,jpg,png|max:700',
        ]);

        // Check validator
        if($validator->fails())
            (new JSONResult())->setError($validator->errors()->first())->show();

        // Get the user
        $anyChanges = false;
        $user = User::find($request->user_id);

        // Update email
        $newEmail = $request->input('email');

        if($newEmail !== null)
            $user->email = $newEmail;

        // Update biography
        $newBio = $request->input('biography');

        if($newBio !== null)
            $user->biography = $newBio;

        // Save the user
        $user->save();

        // Successful response
        (new JSONResult())->show();
    }
}
