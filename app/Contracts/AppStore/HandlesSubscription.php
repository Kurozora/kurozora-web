<?php

namespace App\Contracts\AppStore;

use App\Models\User;

interface HandlesSubscription
{
    /**
     * Handle the received purchase event.
     *
     * @param $event
     */
    public function handle($event);

    /**
     * Finds the user to which the subscription belongs.
     *
     * @param string $uniqueIdentifier
     * @return User
     */
    public function findUserBySubscriptionId(string $uniqueIdentifier): User;

    /**
     * Notify the user of the changes applied to the subscription.
     *
     * @param User $user
     * @param $event
     */
    public function notifyUserAboutUpdate(User $user, $event);
}
