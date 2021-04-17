<?php

namespace App\Http\Controllers;

use App\Events\NewUserRegisteredEvent;
use App\Helpers\JSONResult;
use App\Http\Requests\RegistrationRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Throwable;

class RegistrationController extends Controller
{
    /**
     * Signup a new user
     *
     * @param RegistrationRequest $request
     * @return JsonResponse
     * @throws Throwable
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function signUp(RegistrationRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Create the user
        $newUser = User::create([
            'username'  => $data['username'],
            'email'     => $data['email'],
            'password'  => User::hashPass($data['password'])
        ]);

        if ($request->hasFile('profileImage') &&
            $request->file('profileImage')->isValid()) {
            // Save the uploaded profile image
            $newUser->updateProfileImage($request->file('profileImage'));
        }

        // Fire registration event
        event(new NewUserRegisteredEvent($newUser));

        // Show a successful response
        return JSONResult::success();
    }
}
