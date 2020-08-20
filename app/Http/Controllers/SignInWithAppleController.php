<?php

namespace App\Http\Controllers;

use App\Helpers\AppleAuthKeys;
use App\Helpers\JSONResult;
use App\Helpers\KuroAuthToken;
use App\Http\Requests\SIWALoginRequest;
use App\Http\Requests\SIWARegistration;
use App\Http\Resources\UserResource;
use App\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Laravel\Nova\Exceptions\AuthenticationException;
use musa11971\JWTDecoder\JWTDecoder;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class SignInWithAppleController extends Controller
{
    /**
     * Signup an account for the user via SIWA.
     *
     * @param SIWARegistration $request
     * @return JsonResponse
     */
    function signup(SIWARegistration $request): JsonResponse
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
            'data'      => [
                UserResource::make($user)->includingSession($session)
            ],
            'authToken' => KuroAuthToken::generate($user->id, $session->secret)
        ]);
    }

    /**
     * Sign in the user in via SIWA.
     *
     * @param SIWALoginRequest $request
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws ServiceUnavailableHttpException
     */
    function signin(SIWALoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get Apple's public keys
        $keys = AppleAuthKeys::get();

        // If there are no keys, show an error
        if(!count($keys))
            throw new ServiceUnavailableHttpException('Sorry, "Sign in with Apple" is not available at this moment!');

        // Attempt to decode the JWT
        $payload = null;

        try
        {
            $payload = JWTDecoder::token($data['identity_token'])
                ->withKeys($keys)
                ->ignoreExpiry()
                ->decode();
        }
        catch(Exception $e)
        {
            throw new AuthenticationException('Your credentials are incorrect.');
        }

        // Find the user
        /** @var User $user */
        $user = User::findSIWA($payload->get('sub'), $payload->get('email'))->firstOrFail();

        // Create a session for the user
        $session = $user->createSession([
            'platform'          => $data['platform'],
            'platform_version'  => $data['platform_version'],
            'device_vendor'     => $data['device_vendor'],
            'device_model'      => $data['device_model'],
        ]);

        return JSONResult::success([
            'data'      => [
                UserResource::make($user)->includingSession($session)
            ],
            'authToken' => KuroAuthToken::generate($user->id, $session->secret)
        ]);
    }
}
