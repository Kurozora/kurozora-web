<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Session;
use Validator;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    /**
        /api/v1/session/validate

        expects:
        - POST "session_secret": the session secret that needs to be validated.
        - POST "user_id": the user ID that of the user that created the session.
    **/
    public function validateSession(Request $request) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'session_secret'    => 'bail|required|exists:sessions,secret',
            'user_id'           => 'bail|required|numeric|exists:users,id'
        ]);

        // Display an error if validation failed
        if($validator->fails())
            (new JSONResult())->setError('Failed to validate the session. (1)')->show();

        // Fetch the variables
        $givenSecret    = $request->input('session_secret');
        $givenUserID    = $request->input('user_id');

        // Find the session
        $foundSession = Session::where([
            ['user_id', '=', $givenUserID],
            ['secret',  '=', $givenSecret]
        ])->first();

        // Check if any session was found
        if(!$foundSession)
            (new JSONResult())->setError('Failed to validate the session. (2)')->show();

        // Check if the session is not expired
        if($foundSession->isExpired()) {
            (new JSONResult())->setError('Session is expired.')->show();
            $foundSession->delete();
        }
        else
            (new JSONResult())->show();
    }
}
