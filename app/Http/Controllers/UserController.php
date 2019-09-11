<?php

namespace App\Http\Controllers;

use App\Events\NewUserRegisteredEvent;
use App\Events\UserSessionKilledEvent;
use App\Helpers\JSONResult;
use App\Http\Requests\Registration;
use App\Http\Requests\ResetPassword;
use App\Http\Resources\BadgeResource;
use App\Http\Resources\SessionResource;
use App\Http\Resources\UserNotificationResource;
use App\Jobs\SendNewPasswordMail;
use App\Jobs\SendPasswordResetMail;
use App\PasswordReset;
use App\Session;
use App\User;
use App\UserFollow;
use App\UserNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PusherHelper;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Registers a new user
     *
     * @param Registration $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function register(Registration $request) {
        $data = $request->validated();

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
            $fileName = 'avatar_' . Str::random(30) . '.' . $request->file('profileImage')->extension();;

            $request->file('profileImage')->storeAs(User::USER_UPLOADS_PATH, $fileName);
        }

        // Create the user
        $newUser = User::create([
            'username'              => $data['username'],
            'email'                 => $data['email'],
            'password'              => User::hashPass($data['password']),
            'email_confirmation_id' => Str::random(50),
            'avatar'                => $fileName
        ]);

        // Fire registration event
        event(new NewUserRegisteredEvent($newUser));

        // Show a successful response
        return JSONResult::success();
    }

    /**
     * Logs the user out (destroys the session)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request) {
        // Find the session
        $foundSession = Session::where([
            ['user_id', '=', Auth::id()],
            ['secret',  '=', $request['session_secret']]
        ])->first();

        // Check if any session was found
        if(!$foundSession)
            return JSONResult::error('An error occurred. Please reach out to an administrator.');

        // Fire event
        event(new UserSessionKilledEvent(Auth::id(), $foundSession->id, 'Session logged out.', $request['session_id']));

        // Delete the session
        $foundSession->delete();

        // Show a successful response
        return JSONResult::success();
    }

    /**
     * Returns the profile details for a user
     *
     * @param User $user
     * @return JsonResponse
     */
    public function profile(User $user) {
        // Get the current user
        $currentUser = Auth::user();

        // Get their badges
        $badges = $user->getBadges();

        // Show profile response
        return JSONResult::success([
            'user' => [
                'username'          => $user->username,
                'biography'         => $user->biography,
                'avatar_url'        => $user->getAvatarURL(),
                'banner_url'        => $user->banner,
                'follower_count'    => $user->getFollowerCount(),
                'following_count'   => $user->getFollowingCount(),
                'reputation_count'  => $user->getReputationCount(),
                'badges'            => BadgeResource::collection($badges)
            ],
            'currently_following' => $currentUser->isFollowing($user)
        ]);
    }

    /**
     * Requests a password reset link to be sent to the email address
     *
     * @param ResetPassword $request
     * @return JsonResponse
     */
    public function resetPassword(ResetPassword $request) {
        $data = $request->validated();

        // Try to find the user with this email
        $user = User::where('email', $data['email'])->first();

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

                // Dispatch job to send reset mail
                SendPasswordResetMail::dispatch($user, $createdReset);
            }
        }

        // Show successful response
        return JSONResult::success();
    }

    /**
     * Matches the given details and checks whether or not the user has
     * access to a private user channel for Pusher
     *
     * @param Request $request
     * @param User $user
     * @return string
     */
    public function authenticateChannel(Request $request, User $user) {
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
        if($channelUserID != $user->id) {
            return abort(403, 'Not the appropriate channel.');
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
     * @param User $user
     * @return JsonResponse
     */
    public function getSessions(Request $request, User $user) {
        // Get the other sessions
        $otherSessions = Session::where([
            ['user_id', '=',    $user->id],
            ['secret',  '!=',   $request['session_secret']]
        ])->get();

        // Get the current session
        $curSession = Session::where([
            ['user_id', '=',    $user->id],
            ['secret',  '=',    $request['session_secret']]
        ])->first();

        return JSONResult::success([
            'current_session'   => SessionResource::make($curSession),
            'other_sessions'    => SessionResource::collection($otherSessions)
        ]);
    }

    /**
     * Email confirmation page
     *
     * @param $confirmationID
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function confirmEmail($confirmationID) {
        // Try to find a user with this confirmation ID
        $foundUser = User::where('email_confirmation_id', $confirmationID)->first();

        // No user found
        if(!$foundUser)
            return view('website.email_confirm_page', [
                'success' => false,
                'page' => [
                    'no_index' => true
                ]
            ]);

        // Confirm their email and show the page
        $foundUser->email_confirmation_id = null;
        $foundUser->save();

        return view('website.email_confirm_page', [
            'success' => true,
            'page' => [
                'no_index' => true
            ]
        ]);
    }

    /**
     * Password reset page
     *
     * @param $resetToken
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Throwable
     */
    public function resetPasswordPage($resetToken) {
        // Try to find a reset with this reset token
        $foundReset = PasswordReset::where('token', $resetToken)->first();

        // No reset found
        if(!$foundReset)
            return view('website.password_reset_page', [
                'success' => false,
                'page' => [
                    'no_index' => true
                ]
            ]);

        $user = User::find($foundReset->user_id);

        if($user) {
            // Reset their password to a temporary one
            $newPass = PasswordReset::genTempPassword();

            $user->password = User::hashPass($newPass);
            $user->save();

            // Delete all their sessions
            Session::where('user_id', $user->id)->delete();

            // Dispatch job to send them the new password
            SendNewPasswordMail::dispatch($user, $newPass);

            // Delete the password reset
            $foundReset->delete();
        }

        // Show successful response
        return view('website.password_reset_page', [
            'success' => true,
            'page' => [
                'no_index' => true
            ]
        ]);
    }

    /**
     * Returns the notifications for the user
     *
     * @param User $user
     * @return JsonResponse
     */
    public function getNotifications(User $user) {
        // Get their notifications
        $notifications = UserNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'DESC')
            ->get();

        return JSONResult::success([
            'notifications' => UserNotificationResource::collection($notifications)
        ]);
    }

    /**
     * Retrieves Anime search results
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'query' => 'bail|required|string|min:1'
        ]);

        // Check validator
        if($validator->fails())
            return JSONResult::error($validator->errors()->first());

        $searchQuery = $request->input('query');

        // Search for the users
        $resultArr = User::kuroSearch($searchQuery, [
            'limit' => User::MAX_SEARCH_RESULTS
        ]);

        // Format the results
        $displayResults = [];

        foreach($resultArr as $user) {
            $displayResults[] = [
                'id'                => $user->id,
                'username'          => $user->username,
                'reputation_count'  => $user->getReputationCount(),
                'avatar'            => $user->getAvatarURL()
            ];
        }

        // Show response
        return JSONResult::success([
            'max_search_results'    => User::MAX_SEARCH_RESULTS,
            'results'               => $displayResults
        ]);
    }

    /**
     * Update a user's profile information
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function updateProfile(Request $request, User $user) {
        // Track if anything changed
        $changedFields = [];

        // Update biography
        $newBio = $request->input('biography');

        if( $newBio !== null &&
            $newBio !== $user->biography
        ) {
            // Check if the bio has a correct length
            if(strlen($newBio) > User::BIOGRAPHY_LIMIT)
                return JSONResult::success('Your biography contains more than ' . User::BIOGRAPHY_LIMIT . ' characters.');

            $user->biography = $newBio;
            $changedFields[] = 'biography';
        }

        // Update avatar
        if($request->hasFile('profileImage')) {
            // Check if the uploaded avatar is valid
            $imgValidator = Validator::make($request->all(), [
                'profileImage' => 'required|mimes:jpeg,jpg,png|max:700',
            ]);

            // Avatar is not valid
            if(!$request->file('profileImage')->isValid() || $imgValidator->fails())
                return JSONResult::error('The uploaded avatar is not valid.');

            // Create a name for the new avatar
            $newAvatarName = 'avatar_' . Str::random(30);

            if($user->hasAvatar()) {
                // Delete the old avatar
                $avatarPath = $user->getAvatarPath();

                if(Storage::exists($avatarPath))
                    Storage::delete($avatarPath);
            }

            // Save the uploaded avatar
            $fileName = $newAvatarName . '.' . $request->file('profileImage')->extension();
            $user->avatar = $fileName;
            $request->file('profileImage')->storeAs(User::USER_UPLOADS_PATH, $fileName);

            $changedFields[] = 'avatar';
        }

        // Successful response
        $displayMessage = 'Your settings were saved. ';

        if(count($changedFields)) {
            $displayMessage .= 'You have updated your ' . join(', ', $changedFields) . '.';
            $user->save();
        }
        else $displayMessage .= 'No information was updated.';

        return JSONResult::success([
            'message' => $displayMessage
        ]);
    }
}
