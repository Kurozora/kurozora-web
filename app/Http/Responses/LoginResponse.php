<?php

namespace App\Http\Responses;

use App\Helpers\JSONResult;
use App\Helpers\KuroAuthToken;
use App\Http\Resources\SessionResource;
use App\Http\Resources\UserResourceSmall;
use App\Session;
use App\User;
use Illuminate\Http\JsonResponse;

class LoginResponse
{
    /**
     * @param User $user
     * @param Session $session
     *
     * @return JSONResponse
     */
    public static function make(User $user, Session $session)
    {
        return JSONResult::success([
            'data' => [
                'kuro_auth_token'   => KuroAuthToken::generate($user->id, $session->secret),
                'user'              => UserResourceSmall::make($user)->includePrivateDetails(),
                'session'           => SessionResource::make($session)
            ]
        ]);
    }
}
