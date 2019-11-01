<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Requests\SIWARegistration;
use App\Session;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use KuroAuthToken;

class SignInWithAppleController extends Controller
{
    /**
     * @param SIWARegistration $request
     * @return \Illuminate\Http\JsonResponse
     */
    function register(SIWARegistration $request) {
        $data = $request->validated();

        // Create the new (temporary) SIWA user
        $newUser = User::create([
            'username'              => 'siwa_temp_' . Str::random(30),
            'email'                 => $data['email'],
            'password'              => null,
            'email_confirmation_id' => null
        ]);

        // Create a temporary session
        $newSession = Session::create([
            'user_id'           => $newUser->id,
            'secret'            => Str::random(128),
            'expiration_date'   => date('Y-m-d H:i:s', strtotime('10 days')),
            'ip'                => $request->ip()
        ]);

        // Show a successful response
        return JSONResult::success([
            'user' => [
                'id'                => $newUser->id,
                'kuro_auth_token'   => KuroAuthToken::generate($newUser->id, $newSession->secret),
                'session_id'        => $newSession->id,
                'role'              => $newUser->role
            ]
        ]);
    }
}
