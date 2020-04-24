<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Requests\ResetPassword;
use App\Http\Requests\UpdateProfile;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\SessionResource;
use App\Http\Resources\UserResourceLarge;
use App\Http\Resources\UserResourceSmall;
use App\Http\Responses\LoginResponse;
use App\Jobs\SendNewPasswordMail;
use App\Jobs\SendPasswordResetMail;
use App\PasswordReset;
use App\Session;
use App\User;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Returns the profile details for a user
     *
     * @param User $user
     * @return JsonResponse
     */
    public function profile(User $user) {
        // Show profile response
        return JSONResult::success([
            'user' => UserResourceLarge::make($user)
        ]);
    }

    /**
     * Returns the profile details for the current user
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return JsonResponse
     */
    public function me(Request $request) {
        // Get authenticated user
        $user = Auth::user();

        // Get current session
        $session = Session::find($request->session_id);

        // Show profile response
        return LoginResponse::make($user, $session);
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
                $createdReset = factory(PasswordReset::class)->create([
                    'user_id'   => $user->id,
                    'ip'        => $request->ip()
                ]);

                // Dispatch job to send reset mail
                SendPasswordResetMail::dispatch($user, $createdReset);
            }
        }

        // Show successful response
        return JSONResult::success();
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
     * @param string $token
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Throwable
     */
    public function resetPasswordPage($token) {
        // Try to find a reset with this reset token
        $foundReset = PasswordReset::where('token', $token)->first();

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
        return JSONResult::success([
            'notifications' => NotificationResource::collection($user->notifications)
        ]);
    }

    /**
     * Retrieves User search results
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
        $users = User::kuroSearch($searchQuery, [
            'limit' => User::MAX_SEARCH_RESULTS
        ]);

        // Show response
        return JSONResult::success([
            'max_search_results'    => User::MAX_SEARCH_RESULTS,
            'results'               => UserResourceSmall::collection($users)
        ]);
    }

    /**
     * Update a user's profile information
     *
     * @param UpdateProfile $request
     * @param User $user
     * @return JsonResponse
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig
     */
    public function updateProfile(UpdateProfile $request, User $user) {
        $data = $request->validated();

        // Track if anything changed
        $changedFields = [];

        // Update biography
        if($request->has('biography')) {
            $newBiography = $data['biography'];

            if($newBiography !== $user->biography) {
                $user->biography = $newBiography;
                $changedFields[] = 'biography';
            }
        }

        // Update avatar
        if($request->has('profileImage')) {
            // Remove previous avatar
            $user->clearMediaCollection('avatar');

            // Upload a new avatar, if one was uploaded
            if($request->hasFile('profileImage') && $request->file('profileImage')->isValid())
            {
                $user->addMediaFromRequest('profileImage')->toMediaCollection('avatar');
            }

            $changedFields[] = 'avatar';
        }

        // Update banner
        if($request->has('bannerImage')) {
            // Remove previous banner
            $user->clearMediaCollection('banner');

            // Save the uploaded banner, if one was uploaded
            if($request->hasFile('bannerImage') && $request->file('bannerImage')->isValid())
            {
                $user->addMediaFromRequest('bannerImage')->toMediaCollection('banner');
            }

            $changedFields[] = 'banner image';
        }

        // Successful response
        $displayMessage = 'Your settings were saved. ';

        if(count($changedFields)) {
            $displayMessage .= 'You have updated your ' . join(', ', $changedFields) . '.';
            $user->save();
        }
        else $displayMessage .= 'No information was updated.';

        return JSONResult::success([
            'message' => $displayMessage,
            'user' => UserResourceLarge::make($user)
        ]);
    }
}
