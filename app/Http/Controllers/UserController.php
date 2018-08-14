<?php

namespace App\Http\Controllers;

use App\Session;
use App\User;
use Illuminate\Support\Facades\Hash;
use Validator;
use Illuminate\Http\Request;
use App\Helpers\JSONResult;

class UserController extends Controller
{
    /**
        /api/v1/register

        expects:
        - POST "username": the username that the user picked.
        - POST "email": the email address of the registering user.
        - POST "password": the user's new password.
    **/
    public function register(Request $request) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'username'  => 'bail|required|min:3|max:50|alpha_dash|unique:users,username',
            'email'     => 'bail|required|max:255|email|unique:users,email',
            'password'  => 'bail|required|min:5|max:255'
        ]);

        // Display an error if validation failed
        if($validator->fails())
            (new JSONResult())->setError($validator->errors()->first())->show();

        // Fetch the variables and sanitize them
        $username       = $request->input('username');
        $email          = $request->input('email');
        $rawPassword    = $request->input('password');

        // Create the user
        User::create([
            'username'  => $username,
            'email'     => $email,
            'password'  => Hash::make($rawPassword)
        ]);

        // Show a successful response
        (new JSONResult())->show();
    }

    /**
        /api/v1/login

        expects:
        - POST "username": the user's username used to authenticate.
        - POST "password": the user's password used to authenticate.
        - POST "device": the device name used to log in.
    **/
    public function login(Request $request) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'username'  => 'bail|required|exists:users,username',
            'password'  => 'bail|required',
            'device'    => 'bail|required|max:50'
        ]);

        // Display an error if validation failed
        if($validator->fails())
            (new JSONResult())->setError($validator->errors()->first())->show();

        // Fetch the variables and sanitize them
        $username       = $request->input('username');
        $rawPassword    = $request->input('password');
        $device         = $request->input('device');

        // Find the user
        $foundUser = User::where('username', $username)->first();

        // Compare the passwords
        if(!Hash::check($rawPassword, $foundUser->password))
            (new JSONResult())->setError('The entered password does not match.')->show();

        // Create a new session
        $newSession = Session::create([
            'user_id'   => $foundUser->id,
            'device'    => $device,
            'secret'    => str_random(128)
        ]);

        // Show a successful response
        (new JSONResult())->setData([
            'session_secret'    => $newSession->secret,
            'user_id'           => $foundUser->id
        ])->show();
    }
}
