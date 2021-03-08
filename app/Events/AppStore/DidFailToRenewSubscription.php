<?php

namespace App\Events\AppStore;

use App\Contracts\AppStore\HandlesSubscription;
use App\Models\User;
use App\Models\UserReceipt;
use App\Notifications\SubscriptionStatus;
use Imdhemy\Purchases\Events\AppStore\DidFailToRenew;

class DidFailToRenewSubscription implements HandlesSubscription
{
    /**
     * Handle the received Cancel subscription event.
     *
     * @param DidFailToRenew $event
     */
    public function handle($event)
    {
        // Retrieve the necessary data from the event
        $notification = $event->getServerNotification();
        $subscription = $notification->getSubscription();
        $uniqueIdentifier = $subscription->getUniqueIdentifier();
        $pendingRenewalInfo = $subscription->getProviderRepresentation()->getPendingRenewalInfo();
        $gracePeriodExpiresDate = $pendingRenewalInfo[0]->getGracePeriodExpiresDate();

        // Find the user and update their receipt.
        $user = $this->findUserBySubscriptionId($uniqueIdentifier);
        if ($this->isInGracePeriod($event)) {
            $user->receipt->is_subscribed = true;
            $user->receipt->latest_expires_date = $gracePeriodExpiresDate->toDateTime();
        } else {
            $user->receipt->is_subscribed = false;
            $user->receipt->latest_expires_date = null;
        }
        $user->save();

        // Notify the user about the subscription update.
        $this->notifyUserAboutUpdate($user, $event);
    }

    /** Finds the user to which the subscription belongs.
     *
     * @param string $uniqueIdentifier
     * @return User
     */
    public function findUserBySubscriptionId(string $uniqueIdentifier): User
    {
        return UserReceipt::whereOriginalTransactionId($uniqueIdentifier)->first()->user;
    }

    /**
     * Notify the user of the changes applied to the subscription.
     *
     * @param User $user
     * @param DidFailToRenew $event
     */
    public function notifyUserAboutUpdate(User $user, $event)
    {
        // Get server notification.
        $notification = $event->getServerNotification();

        // Notify the user about the subscription update.
        $user->notify(new SubscriptionStatus($notification->getType()));
    }

    /**
     * Billing retrying and grace period expires date is in the future.
     *
     * @param DidFailToRenew $event
     *
     * @return bool
     */
    public function isInGracePeriod($event): bool
    {
        $pendingRenewalInfo = $event->getSubscription()->getProviderRepresentation()->getPendingRenewalInfo()[0];
        return $pendingRenewalInfo->isInBillingRetryPeriod() &&
            $pendingRenewalInfo->getGracePeriodExpiresDate() !== null &&
            $pendingRenewalInfo->getGracePeriodExpiresDate()->isFuture();
    }
}
