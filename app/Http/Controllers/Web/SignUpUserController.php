<?php

namespace App\Http\Controllers\Web;

use App\Events\NewUserRegisteredEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\SignUpRequest;
use App\Models\User;
use Auth;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Str;
use Throwable;

class SignUpUserController extends Controller
{
    /**
     * Show the registration view.
     *
     * @param Request $request
     *
     * @return Application|Factory|View
     */
    public function create(Request $request): Application|Factory|View
    {
        return view('auth.sign-up');
    }

    /**
     * Create a new registered user.
     *
     * @param SignUpRequest $request
     *
     * @return Application|RedirectResponse|Redirector
     * @throws Throwable
     */
    public function store(SignUpRequest $request): Application|RedirectResponse|Redirector
    {
        $data = $request->only(['username', 'email', 'password']);

        // Create the user
        $newUser = User::create([
            'username'              => $data['username'],
            'email'                 => $data['email'],
            'password'              => User::hashPass($data['password']),
            'email_confirmation_id' => Str::random(50)
        ]);

        event(new NewUserRegisteredEvent($newUser));

        Auth::login($newUser);

        return redirect('/');
    }
}
