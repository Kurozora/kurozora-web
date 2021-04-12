<?php

namespace App\Http\Livewire\Misc;

use App\Jobs\SendNewPasswordMail;
use App\Models\PasswordReset;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ResetPassword extends Component
{
    /**
     * Prepare the component.
     *
     * @param string $token
     *
     * @return void
     * @throws Exception
     */
    public function mount(string $token)
    {
        // Try to find a reset with this reset token
        /** @var PasswordReset $foundReset */
        $foundReset = PasswordReset::where('token', $token)->firstOrFail();

        // Get the user
        $user = User::findOrFail($foundReset->user_id);

        // Reset their password to a temporary one
        $newPass = PasswordReset::genTempPassword();

        $user->password = User::hashPass($newPass);
        $user->save();

        // Delete all their sessions
        $user->sessions()->delete();

        // Dispatch job to send them the new password
        SendNewPasswordMail::dispatch($user, $newPass);

        // Delete the password reset
        $foundReset->delete();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.misc.reset-password');
    }
}
