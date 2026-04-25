<?php

namespace App\Notifications;

use App\Models\StoreProduct;
use App\Models\User;
use App\Models\UserReceipt;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Apn\ApnChannel;
use NotificationChannels\Apn\ApnMessage;

class SubscriptionStatus extends Notification
{
    use Queueable;

    /**
     * The App Store Server notification type.
     *
     * @var string $notificationType
     */
    private readonly string $notificationType;

    /**
     * The notification subtype, when provided by the App Store.
     *
     * @var string|null $subtype
     */
    private readonly ?string $subtype;

    /**
     * The store product the notification refers to.
     *
     * @var StoreProduct $product
     */
    private readonly StoreProduct $product;

    /**
     * The user receipt associated with the notification, if any.
     *
     * @var UserReceipt|null $receipt
     */
    private readonly ?UserReceipt $receipt;

    /**
     * Create a new notification instance.
     *
     * @param string $notificationType
     * @param string|null $subtype
     * @param StoreProduct $product
     * @param UserReceipt|null $receipt
     */
    public function __construct(string $notificationType, ?string $subtype, StoreProduct $product, ?UserReceipt $receipt = null)
    {
        $this->notificationType = $notificationType;
        $this->subtype = $subtype;
        $this->product = $product;
        $this->receipt = $receipt;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(mixed $notifiable): array
    {
        return ['database', ApnChannel::class];
    }

    /**
     * Get the database representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase(mixed $notifiable): array
    {
        return [
            'message' => $this->getDescription(),
        ];
    }

    public function toApn(User $notifiable): ApnMessage
    {
        return ApnMessage::create()
            ->title($this->getTitle())
            ->body($this->getDescription())
            ->badge($notifiable->unreadNotifications()->count());
    }

    /**
     * Get the title for the current event.
     *
     * @return string
     */
    private function getTitle(): string
    {
        return match ($this->notificationType) {
            'SUBSCRIBED', 'INITIAL_BUY' => __('Welcome'),
            'DID_RENEW' => __('Subscription Renewed'),
            'DID_FAIL_TO_RENEW' => __('Payment Failed'),
            'EXPIRED', 'GRACE_PERIOD_EXPIRED' => __('Subscription Ended'),
            'DID_CHANGE_RENEWAL_STATUS', 'DID_CHANGE_RENEWAL_PREF' => __('Subscription Update'),
            'OFFER_REDEEMED' => __('Plan Updated'),
            'REFUND' => __('Refund Processed'),
            'REVOKE' => __('Access Ended'),
            'RENEWAL_EXTENDED' => __('Subscription Extended'),
            'PRICE_INCREASE' => __('Price Change'),
            'ONE_TIME_CHARGE' => __('Thank You'),
            'INTERACTIVE_RENEWAL' => __('Welcome Back'),
            'DID_RECOVER' => __('Payment Recovered'),
            'CANCEL' => __('Subscription Cancelled'),
            default => __('Subscription Update'),
        };
    }

    /**
     * Get the description for the current event.
     *
     * @return string
     */
    public function getDescription(): string
    {
        $productName = $this->product->name;
        $expiresDate = $this->expiresDate();

        return match ($this->notificationType) {
            'SUBSCRIBED', 'INITIAL_BUY' => $expiresDate !== null
                ? __('Welcome to :productName! Active until :expiresDate.', ['productName' => $productName, 'expiresDate' => $expiresDate])
                : __('Welcome to :productName!', ['productName' => $productName]),
            'DID_RENEW' => $expiresDate !== null
                ? __(':productName renewed. Active until :expiresDate.', ['productName' => $productName, 'expiresDate' => $expiresDate])
                : __(':productName renewed.', ['productName' => $productName]),
            'DID_FAIL_TO_RENEW' => $this->renewalFailedDescription($productName),
            'EXPIRED' => match ($this->subtype) {
                'BILLING_RETRY' => __(':productName ended after billing failed.', ['productName' => $productName]),
                'PRICE_INCREASE' => __(":productName ended because the price change wasn't accepted.", ['productName' => $productName]),
                'PRODUCT_NOT_FOR_SALE' => __(':productName ended because the plan is no longer offered.', ['productName' => $productName]),
                default => __(':productName has ended.', ['productName' => $productName]),
            },
            'GRACE_PERIOD_EXPIRED' => __(":productName couldn't be renewed and has now ended.", ['productName' => $productName]),
            'DID_CHANGE_RENEWAL_STATUS' => match ($this->subtype) {
                'AUTO_RENEW_DISABLED' => $expiresDate !== null
                    ? __('Auto-renew is off. :productName ends on :expiresDate.', ['productName' => $productName, 'expiresDate' => $expiresDate])
                    : __('Auto-renew is off for :productName.', ['productName' => $productName]),
                'AUTO_RENEW_ENABLED' => __('Auto-renew is back on for :productName.', ['productName' => $productName]),
                default => __('Renewal settings updated for :productName.', ['productName' => $productName]),
            },
            'DID_CHANGE_RENEWAL_PREF' => match ($this->subtype) {
                'DOWNGRADE', 'UPGRADE' => $this->upcomingPlanChangeDescription($expiresDate),
                default => __('Scheduled plan change cancelled. Staying on :productName.', ['productName' => $productName]),
            },
            'OFFER_REDEEMED' => match ($this->subtype) {
                'UPGRADE' => $expiresDate !== null
                    ? __('Upgraded to :productName. Active until :expiresDate.', ['productName' => $productName, 'expiresDate' => $expiresDate])
                    : __('Upgraded to :productName.', ['productName' => $productName]),
                default => __('Offer applied to :productName.', ['productName' => $productName]),
            },
            'REFUND' => __(':productName was refunded.', ['productName' => $productName]),
            'REVOKE' => __('Family Sharing access to :productName has ended.', ['productName' => $productName]),
            'RENEWAL_EXTENDED' => $expiresDate !== null
                ? __(':productName extended. Active until :expiresDate.', ['productName' => $productName, 'expiresDate' => $expiresDate])
                : __(':productName extended.', ['productName' => $productName]),
            'PRICE_INCREASE' => __('A price change is coming to :productName. Open Kurozora to review.', ['productName' => $productName]),
            'ONE_TIME_CHARGE' => __('Thanks for the tip! Kurozora Pro has been added to your account.'),
            'INTERACTIVE_RENEWAL' => $expiresDate !== null
                ? __('Welcome back! :productName is active until :expiresDate.', ['productName' => $productName, 'expiresDate' => $expiresDate])
                : __('Welcome back! :productName is active.', ['productName' => $productName]),
            'DID_RECOVER' => $expiresDate !== null
                ? __('Payment went through. :productName is active until :expiresDate.', ['productName' => $productName, 'expiresDate' => $expiresDate])
                : __('Payment went through. :productName is active.', ['productName' => $productName]),
            'CANCEL' => __(':productName was cancelled.', ['productName' => $productName]),
            default => __('Your transaction has been completed.'),
        };
    }

    /**
     * Get the description for a failed renewal event.
     *
     * @param string $productName
     *
     * @return string
     */
    private function renewalFailedDescription(string $productName): string
    {
        if ($this->subtype !== 'GRACE_PERIOD') {
            return __("We couldn't renew :productName.", ['productName' => $productName]);
        }

        $gracePeriodDate = $this->gracePeriodDate();

        if ($gracePeriodDate !== null) {
            return __("We couldn't renew :productName. Update your payment method by :gracePeriodDate to keep access.", ['productName' => $productName, 'gracePeriodDate' => $gracePeriodDate]);
        }

        return __("We couldn't renew :productName. Update your payment method to keep access.", ['productName' => $productName]);
    }

    /**
     * Get the description for an upcoming plan change.
     *
     * @param string|null $expiresDate
     *
     * @return string
     */
    private function upcomingPlanChangeDescription(?string $expiresDate): string
    {
        $autoRenewProductName = $this->autoRenewProductName();

        if ($autoRenewProductName !== null && $expiresDate !== null) {
            return __("You'll switch to :autoRenewProductName on :expiresDate.", ['autoRenewProductName' => $autoRenewProductName, 'expiresDate' => $expiresDate]);
        }

        if ($autoRenewProductName !== null) {
            return __("You'll switch to :autoRenewProductName at the end of the current period.", ['autoRenewProductName' => $autoRenewProductName]);
        }

        if ($expiresDate !== null) {
            return __('Your plan will change on :expiresDate.', ['expiresDate' => $expiresDate]);
        }

        return __('Your plan will change at the end of the current period.');
    }

    /**
     * Get the display name of the auto-renew product.
     */
    private function autoRenewProductName(): ?string
    {
        $autoRenewProductId = $this->receipt?->auto_renew_product_id;

        if ($autoRenewProductId === null || $autoRenewProductId === $this->product->product_id) {
            return null;
        }

        return StoreProduct::where('product_id', $autoRenewProductId)->value('name');
    }

    /**
     * Get the formatted current period expiry date.
     */
    private function expiresDate(): ?string
    {
        return $this->receipt?->expires_at?->format('F j');
    }

    /**
     * Get the formatted grace period expiry date.
     */
    private function gracePeriodDate(): ?string
    {
        return $this->receipt?->grace_period_expires_date?->format('F j');
    }
}
