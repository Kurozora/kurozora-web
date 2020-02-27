<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Requests\SIWALoginRequest;
use App\Http\Requests\SIWARegistration;
use App\Http\Responses\LoginResponse;
use App\Session;
use App\User;
use CoderCat\JWKToPEM\JWKConverter;
use Illuminate\Support\Str;
use musa11971\JWTDecoder\JWTDecoder;

class SignInWithAppleController extends Controller
{
    /**
     * Registers an account for the user via SIWA.
     *
     * @param SIWARegistration $request
     * @return \Illuminate\Http\JsonResponse
     */
    function register(SIWARegistration $request)
    {
        $data = $request->validated();

        // Create the new user
        $user = User::create([
            'email'                     => $data['email'],
            'siwa_id'                   => $data['siwa_id'],
            'username_change_available' => true
        ]);

        // Create a temporary session
        $session = Session::create([
            'user_id'           => $user->id,
            'secret'            => Str::random(128),
            'expiration_date'   => date('Y-m-d H:i:s', strtotime('10 days')),
            'ip'                => $request->ip()
        ]);

        return LoginResponse::make($user, $session);
    }

    /**
     * Logs the user in via SIWA.
     *
     * @param SIWALoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    function login(SIWALoginRequest $request)
    {
        $data = $request->validated();

        // Request apple keys
        $jwk = json_decode(file_get_contents('https://appleid.apple.com/auth/keys'));

        // Convert apple keys to PEM
        $jwkConverter = new JWKConverter();

        $keys = [];
        foreach($jwk->keys as $appleKey) {
            try {
                $keys[] = $jwkConverter->toPem((array) $appleKey);
            }
            catch(\Exception $e) {
                print_r($appleKey);
                die;
            }
        }

        if(!count($keys))
            return JSONResult::error('Sorry, "Sign in with Apple" is not available at this moment!', 340056);

        // Decode the JWT
        $payload = null;

        try {
            $payload = JWTDecoder::token($data['identity_token'])
                ->withKeys($keys)
                ->ignoreExpiry()
                ->decode();
        }
        catch(\Exception $e)
        {
            return JSONResult::error('Your credentials could not be verified.', 220028);
        }

        // Find the user
        /** @var User $user */
        $user = User::where('email', $payload->get('email'))
            ->where('siwa_id', $payload->get('sub'))
            ->first();

        if(!$user)
            return JSONResult::error('Your account could not be located in the database.', 273782);

        // Create a session
        $ip = $request->ip();

        $session = Session::create([
            'user_id'           => $user->id,
            'device'            => 'SIWA TEMP DEVICE',
            'secret'            => Str::random(128),
            'expiration_date'   => date('Y-m-d H:i:s', strtotime('90 days')),
            'ip'                => $ip
        ]);

        return LoginResponse::make($user, $session);
    }
}
