<?php

namespace App\Http\Controllers;

use App\Events\NewUserRegisteredEvent;
use App\Helpers\JSONResult;
use App\Http\Requests\Registration;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class RegistrationController extends Controller
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

        // Create the user
        $newUser = User::create([
            'username'              => $data['username'],
            'email'                 => $data['email'],
            'password'              => User::hashPass($data['password']),
            'email_confirmation_id' => Str::random(50),
            'avatar'                => $fileName
        ]);

        if( $request->hasFile('profileImage') &&
            $request->file('profileImage')->isValid()
        ) {
            // Save the uploaded avatar
            $newUser->addMediaFromRequest('profileImage')->toMediaCollection('avatar');
        }

        // Fire registration event
        event(new NewUserRegisteredEvent($newUser));

        // Show a successful response
        return JSONResult::success();
    }
}
