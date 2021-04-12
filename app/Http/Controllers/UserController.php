<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Requests\GetFeedMessagesRequest;
use App\Http\Requests\ResetPassword;
use App\Http\Requests\SearchUserRequest;
use App\Http\Resources\FeedMessageResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserResourceBasic;
use App\Jobs\SendPasswordResetMail;
use App\Models\PasswordReset;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

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
                $createdReset = PasswordReset::factory()->create([
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
