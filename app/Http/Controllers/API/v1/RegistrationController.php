<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\MediaCollection;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\SignUpRequest;
use App\Models\User;
use Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Random\RandomException;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
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
     * @throws FileCannotBeAdded
     * @throws RandomException
     */
    public function signUp(SignUpRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Create the user
        $newUser = User::create([
            'username' => $data['nickname'] ?? $data['username'] ?? bin2hex(random_bytes(20)),
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'can_change_username' => false,
            'tv_rating' => 4
        ]);

        if ($request->hasFile('profileImage') &&
            $request->file('profileImage')->isValid()) {
            // Save the uploaded profile image
            $newUser->updateImageMedia(MediaCollection::Profile(), $request->file('profileImage'));
        }

        event(new Registered($newUser));

        // Show a successful response
        return JSONResult::success();
    }
}
