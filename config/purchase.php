<?php

use App\Events\AppStore\CancelSubscription;
use App\Events\AppStore\DidChangeRenewalPrefSubscription;
use App\Events\AppStore\DidChangeRenewalStatusSubscription;
use App\Events\AppStore\DidFailToRenewSubscription;
use App\Events\AppStore\DidRecoverSubscription;
use App\Events\AppStore\DidRenewSubscription;
use App\Events\AppStore\InitialBuySubscription;
use App\Events\AppStore\InteractiveRenewalSubscription;
use App\Events\AppStore\PriceIncreaseConsentSubscription;
use App\Events\AppStore\RefundSubscription;
use Imdhemy\Purchases\Events\AppStore\Cancel;
use Imdhemy\Purchases\Events\AppStore\DidChangeRenewalPref;
use Imdhemy\Purchases\Events\AppStore\DidChangeRenewalStatus;
use Imdhemy\Purchases\Events\AppStore\DidFailToRenew;
use Imdhemy\Purchases\Events\AppStore\DidRecover;
use Imdhemy\Purchases\Events\AppStore\DidRenew;
use Imdhemy\Purchases\Events\AppStore\InitialBuy;
use Imdhemy\Purchases\Events\AppStore\InteractiveRenewal;
use Imdhemy\Purchases\Events\AppStore\PriceIncreaseConsent;
use Imdhemy\Purchases\Events\AppStore\Refund;
use Imdhemy\Purchases\Events\GooglePlay\SubscriptionCanceled;
use Imdhemy\Purchases\Events\GooglePlay\SubscriptionDeferred;
use Imdhemy\Purchases\Events\GooglePlay\SubscriptionExpired;
use Imdhemy\Purchases\Events\GooglePlay\SubscriptionInGracePeriod;
use Imdhemy\Purchases\Events\GooglePlay\SubscriptionOnHold;
use Imdhemy\Purchases\Events\GooglePlay\SubscriptionPaused;
use Imdhemy\Purchases\Events\GooglePlay\SubscriptionPauseScheduleChanged;
use Imdhemy\Purchases\Events\GooglePlay\SubscriptionPriceChangeConfirmed;
use Imdhemy\Purchases\Events\GooglePlay\SubscriptionPurchased;
use Imdhemy\Purchases\Events\GooglePlay\SubscriptionRecovered;
use Imdhemy\Purchases\Events\GooglePlay\SubscriptionRenewed;
use Imdhemy\Purchases\Events\GooglePlay\SubscriptionRestarted;
use Imdhemy\Purchases\Events\GooglePlay\SubscriptionRevoked;

return [
    'routing' => [
        'prefix' => 'api/v1'
    ],

    'google_play_package_name' => env('GOOGLE_PLAY_PACKAGE_NAME', 'com.example.name'),

    'appstore_password' => env('SK_APP_PASSWORD', ''),

    'eventListeners' => [
        /**
         * --------------------------------------------------------
         * Google Play Events
         * --------------------------------------------------------
         */
        SubscriptionPurchased::class => [],
        SubscriptionRenewed::class => [],
        SubscriptionInGracePeriod::class => [],
        SubscriptionExpired::class => [],
        SubscriptionCanceled::class => [],
        SubscriptionPaused::class => [],
        SubscriptionRestarted::class => [],
        SubscriptionDeferred::class => [],
        SubscriptionRevoked::class => [],
        SubscriptionOnHold::class => [],
        SubscriptionRecovered::class => [],
        SubscriptionPauseScheduleChanged::class => [],
        SubscriptionPriceChangeConfirmed::class => [],

        /**
         * --------------------------------------------------------
         * Appstore Events
         * --------------------------------------------------------
         */
        Cancel::class => [CancelSubscription::class],
        DidChangeRenewalPref::class => [DidChangeRenewalPrefSubscription::class],
        DidChangeRenewalStatus::class => [DidChangeRenewalStatusSubscription::class],
        DidFailToRenew::class => [DidFailToRenewSubscription::class],
        DidRecover::class => [DidRecoverSubscription::class],
        DidRenew::class => [DidRenewSubscription::class],
        InitialBuy::class => [InitialBuySubscription::class],
        InteractiveRenewal::class => [InteractiveRenewalSubscription::class],
        PriceIncreaseConsent::class => [PriceIncreaseConsentSubscription::class],
        Refund::class => [RefundSubscription::class],
    ],
];
