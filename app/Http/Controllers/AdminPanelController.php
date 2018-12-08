<?php

namespace App\Http\Controllers;

use App\LoginAttempt;
use App\Session;
use App\User;
use Illuminate\Http\Request;
use Validator;

class AdminPanelController extends Controller
{
    public function login(Request $request) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'email'     => 'bail|required|email',
            'password'  => 'bail|required'
        ]);

        // Display an error if validation failed
        if($validator->fails())
            return AdminPanelController::toLoginPage($request, $validator->errors()->first());

        // Check if the request IP is not banned from logging in
        if(!LoginAttempt::isIPAllowedToLogin($request->ip()))
            return AdminPanelController::toLoginPage($request, 'Oops. You have failed to login too many times. Please grab yourself a snack and try again in a bit.');

        // Fetch the variables and sanitize them
        $email          = $request->input('email');
        $rawPassword    = $request->input('password');

        // Find the user
        $foundUser = User::where('email', $email)->first();

        // User not found
        if($foundUser === null)
            return AdminPanelController::toLoginPage($request, 'The email you entered does not belong to a user.');

        // Compare the passwords
        if(!User::checkPassHash($rawPassword, $foundUser->password)) {
            // Register the login attempt
            LoginAttempt::registerFailedLoginAttempt($request->ip());

            // Show error message
            return AdminPanelController::toLoginPage($request, 'The password you entered does not match.');
        }

        // Check if allowed to use admin panel
        if(!$foundUser->canUseAdminPanel())
            return AdminPanelController::toLoginPage($request, 'You do not have access to the admin panel.');

        // Create new session
        $loginIPAddress = $request->ip();

        $newSession = Session::create([
            'user_id'           => $foundUser->id,
            'device'            => 'Kurozora Admin Panel',
            'secret'            => str_random(128),
            'expiration_date'   => date('Y-m-d H:i:s', strtotime('30 days')),
            'ip'                => $loginIPAddress
        ]);

        // Set session variables
        session(['user_id' => $foundUser->id]);
        session(['session_secret' => $newSession->secret]);

        // Redirect to dashboard
        return redirect()->route('admin_panel.dashboard');
    }

    /**
     * Logs the user out
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request) {
        // Forget session
        $request->session()->forget('user_id');
        $request->session()->forget('session_secret');

        // Redirect to login page
        return AdminPanelController::toLoginPage($request, 'You have been logged out.');
    }

    /**
     * Redirect to login page with an additional message
     *
     * @param $request
     * @param null $message
     * @return \Illuminate\Http\RedirectResponse
     */
    public static function toLoginPage($request, $message = null) {
        if($message != null)
            $request->session()->flash('admin_login_msg', $message);

        return redirect()->route('admin_panel.login');
    }
}
