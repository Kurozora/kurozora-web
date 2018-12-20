<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Session;
use App\User;
use Validator;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    /**
     * Checks whether or not a session_secret/user_id combination is valid
     *
     * @param Request $request
     */
    public function validateSession(Request $request) {
        // Find the session
        $foundSession = Session::where([
            ['user_id', '=', $request->user_id],
            ['secret',  '=', $request->session_secret]
        ])->first();

        // Check if the session is not expired
        if($foundSession->isExpired()) {
            (new JSONResult())->setError('Session is expired.')->show();
            $foundSession->delete();
        }
        // Session is perfectly valid
        else {
            $foundSession->last_validated = date('Y-m-d H:i:s', time());
            $foundSession->save();

            (new JSONResult())->show();
        }
    }
}
