<?php

namespace App\Contracts\AppStore;

use App\Models\StoreProduct;
use App\Models\User;
use App\Models\UserReceipt;
use Imdhemy\AppStore\ValueObjects\JwsRenewalInfo;

interface HandlesSubscription
{
    /**
     * Handle the received purchase event.
     */
    public function handle($event);

    /**
     * Whether bill is in retrying period and grace period expiry date is in the future.
     */
    public function isInGracePeriod(JwsRenewalInfo $renewalInfo): bool;

    /**
     * Notify the user of the changes applied to the subscription.
     */
    public function notifyUserAboutUpdate(?User $user, $event, StoreProduct $product, ?UserReceipt $receipt = null): void;

    /**
     * Recompute the user's entitlements and derived flags.
     */
    public function recomputeUserEntitlements(User $user): void;
}
