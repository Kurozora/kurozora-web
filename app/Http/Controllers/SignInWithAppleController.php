<?php

namespace App\Http\Controllers;

use App\Helpers\AppleAuthKeys;
use App\Helpers\JSONResult;
use App\Http\Requests\SIWALoginRequest;
use App\Http\Requests\SIWARegistration;
use App\Http\Resources\SessionResource;
use App\User;
use Exception;
use Illuminate\Http\JsonResponse;
use musa11971\JWTDecoder\JWTDecoder;

class SignInWithAppleController extends Controller
{
    /**
     * Registers an account for the user via SIWA.
     *
     * @param SIWARegistration $request
     * @return JsonResponse
     */
    function register(SIWARegistration $request): JsonResponse
    {
        $data = $request->validated();

        // Create the new user
        $user = User::create([
            'email'                     => $data['email'],
            'siwa_id'                   => $data['siwa_id'],
            'username_change_available' => true
        ]);

        // Create a session for the user
        $session = $user->createSession([
            'platform'          => $data['platform'],
            'platform_version'  => $data['platform_version'],
            'device_vendor'     => $data['device_vendor'],
            'device_model'      => $data['device_model'],
        ]);

        return JSONResult::success([
            'data' => [
                SessionResource::make($session)->includesAuthKey()
            ]
        ]);
    }

    /**
     * Logs the user in via SIWA.
     *
     * @param SIWALoginRequest $request
     * @return JsonResponse
     */
    function login(SIWALoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get Apple's public keys
        $keys = AppleAuthKeys::get();

        // If there are no keys, show an error
        if(!count($keys))
            return JSONResult::error('Sorry, "Sign in with Apple" is not available at this moment!', [
                'error_code' => 340056
            ]);

        // Attempt to decode the JWT
        $payload = null;

        try {
            $payload = JWTDecoder::token($data['identity_token'])
                ->withKeys($keys)
                ->ignoreExpiry()
                ->decode();
        }
        catch(Exception $e)
        {
            return JSONResult::error('Your credentials are invalid.', [
                'error_code' => 220028
            ]);
        }

        // Find the user
        /** @var User $user */
        $user = User::findSIWA($payload->get('sub'), $payload->get('email'))->first();

        if(!$user)
            return JSONResult::error('Your account could not be located in the database.', [
                'error_code' => 273782
            ]);

        // Create a session for the user
        $session = $user->createSession([
            'platform'          => $data['platform'],
            'platform_version'  => $data['platform_version'],
            'device_vendor'     => $data['device_vendor'],
            'device_model'      => $data['device_model'],
        ]);

        return JSONResult::success([
            'data' => [
                SessionResource::make($session)->includesAuthKey()
            ]
        ]);
    }
}
