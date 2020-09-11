<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Requests\GetFeedMessagesRequest;
use App\Http\Requests\ResetPassword;
use App\Http\Requests\SearchUserRequest;
use App\Http\Resources\FeedMessageResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserResourceBasic;
use App\Jobs\SendNewPasswordMail;
use App\Jobs\SendPasswordResetMail;
use App\PasswordReset;
use App\Session;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Returns the profile details for a user
     *
     * @param User $user
     * @return JsonResponse
     */
    public function profile(User $user): JsonResponse
    {
        // Show profile response
        return JSONResult::success([
            'data' => UserResource::collection([$user])
        ]);
    }

    /**
     * Returns the feed messages for a user.
     *
     * @param GetFeedMessagesRequest $request
     * @param User $user
     * @return JsonResponse
     */
    public function getFeedMessages(GetFeedMessagesRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        // Get the feed messages
        $feedMessages = $user->feedMessages()
            ->orderByDesc('created_at')
            ->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $feedMessages->nextPageUrl());

        return JSONResult::success([
            'data' => FeedMessageResource::collection($feedMessages),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Requests a password reset link to be sent to the email address
     *
     * @param ResetPassword $request
     * @return JsonResponse
     */
    public function resetPassword(ResetPassword $request): JsonResponse
    {
        $data = $request->validated();

        // Try to find the user with this email
        /** @var User $user */
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
     * Email confirmation page
     *
     * @param $confirmationID
     * @return Application|Factory|View
     */
    public function confirmEmail($confirmationID)
    {
        // Try to find a user with this confirmation ID
        /** @var User $foundUser */
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
     *
     * @return Application|Factory|View
     * @throws Exception
     */
    public function resetPasswordPage($token)
    {
        // Try to find a reset with this reset token
        /** @var PasswordReset $foundReset */
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
     * Retrieves User search results
     *
     * @param SearchUserRequest $request
     * @return JsonResponse
     */
    public function search(SearchUserRequest $request): JsonResponse
    {
        $searchQuery = $request->input('query');

        // Search for the users
        $users = User::kuroSearch($searchQuery, [
            'limit' => User::MAX_SEARCH_RESULTS
        ]);

        // Show response
        return JSONResult::success([
            'data' => UserResourceBasic::collection($users)
        ]);
    }
}
