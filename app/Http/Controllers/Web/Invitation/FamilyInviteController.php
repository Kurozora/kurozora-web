<?php

namespace App\Http\Controllers\Web\Invitation;

use App\Events\Invited;
use App\Http\Requests\InviteUserRequest;
use Illuminate\Http\RedirectResponse;

class FamilyInviteController
{
    /**
     * Handle the incoming request to accept a family invitation.
     *
     * @param InviteUserRequest $request
     *
     * @return RedirectResponse
     */
    public function __invoke(InviteUserRequest $request): RedirectResponse
    {
        if ($request->user()->parent_id !== null) {
            return redirect()->intended('/?invited=1');
        }

        if ($this->acceptInvitation($request)) {
            event(new Invited($request->user()));
        }

        session()->flash('success', __('You’ve joined :x’s family!', ['x' => $request->user()->username]));

        return redirect()->intended('/?invited=1');
    }

    /**
     * Accept the invitation to join a family.
     *
     * @param InviteUserRequest $request
     *
     * @return bool
     */
    private function acceptInvitation(InviteUserRequest $request)
    {
        return $request->user()->forceFill([
            'parent_id' => $request->route('id'),
        ])->save();
    }
}
