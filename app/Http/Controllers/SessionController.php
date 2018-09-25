<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Session;
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
        // Session is perfectly valid
        else {
            $foundSession->last_validated = date('Y-m-d H:i:s', time());
            $foundSession->save();

            (new JSONResult())->show();
        }
    }
}
