<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Requests\Web\SignUpRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class RegistrationController extends Controller
{
    /**
     * Signup a new user
     *
     * @param SignUpRequest $request
     *
     * @return JsonResponse
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function signUp(SignUpRequest $request): JsonResponse
    {
        $data = $request->only(['username', 'email', 'password']);

        // Create the user
        $newUser = User::create([
            'username'  => $data['username'],
            'email'     => $data['email'],
            'password'  => User::hashPass($data['password']),
            'settings'  => [
                'can_change_username'   => false,
                'tv_rating'             => -1,
            ],
        ]);

        if ($request->hasFile('profileImage') &&
            $request->file('profileImage')->isValid()) {
            // Save the uploaded profile image
            $newUser->updateProfileImage($request->file('profileImage'));
        }

        event(new Registered($newUser));

        // Show a successful response
        return JSONResult::success();
    }
}
