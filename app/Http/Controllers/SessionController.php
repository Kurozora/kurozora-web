<?php

namespace App\Http\Controllers;

use App\Events\UserSessionKilledEvent;
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

    /**
     * Deletes a session
     *
     * @param Request $request
     * @param $sessionID
     */
    public function deleteSession(Request $request, $sessionID) {
        // Fetch the variables
        $delSessionID = $sessionID;

        // Find the session
        $foundSession = Session::where([
            ['id'       , '=', $sessionID],
            ['user_id'  , '=', $request->user_id]
        ])->first();

        if($foundSession === null)
            (new JSONResult())->setError('Unable to delete this session.')->show();

        // Fire event
        event(new UserSessionKilledEvent($request->user_id, $delSessionID, 'Session killed manually by user.', $request->session_id));

        // Delete the session
        $foundSession->delete();

        (new JSONResult())->show();
    }

    /**
     * Displays session information
     *
     * @param Request $request
     * @param $sessionID
     */
    public function getSessionDetails(Request $request, $sessionID) {
        // Find the session
        $foundSession = Session::where([
            ['id'       , '=', $sessionID],
            ['user_id'  , '=', $request->user_id]
        ])->first();

        // Session not found
        if($foundSession === null)
            (new JSONResult())->setError('The given session does not exist or does not belong to you.')->show();

        (new JSONResult())->setData([
            'session' => $foundSession->formatForSessionDetails()
        ])->show();
    }
}
