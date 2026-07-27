<?php

namespace App\Livewire\KurozoraPlus;

use App\Enums\StoreProductType;
use App\Models\StoreProduct;
use App\Models\UserReceipt;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Index extends Component
{
    /**
     * The active Kurozora+ products, cheapest first.
     *
     * @return Collection
     */
    public function getSubscriptionProductsProperty(): Collection
    {
        return StoreProduct::where('is_active', true)
            ->where('type', StoreProductType::AutoRenewingSubscription)
            ->orderBy('price_usd_milliunits')
            ->get();
    }

    /**
     * The user’s active subscription receipt.
     *
     * @return ?UserReceipt
     */
    public function getCurrentReceiptProperty(): ?UserReceipt
    {
        return auth()->user()?->receipts()
            ->where('is_subscribed', true)
            ->orderByDesc('expires_at')
            ->first();
    }

    /**
     * The user’s current subscription ready for display.
     *
     * @return ?array
     */
    public function getCurrentSubscriptionProperty(): ?array
    {
        $userReceipt = $this->currentReceipt;

        if ($userReceipt === null) {
            return null;
        }

        $storeProduct = $this->subscriptionProducts->firstWhere('product_id', $userReceipt->product_id);

        if ($storeProduct === null) {
            return null;
        }

        return [
            'image' => $this->imageFor($storeProduct),
            'name' => $storeProduct->name,
            'price' => __(':x per :y', ['x' => $this->priceFor($storeProduct), 'y' => $this->displayUnitFor($storeProduct)]),
            'renewalStatus' => $this->renewalStatusFor($userReceipt),
            'purchaseStatus' => $this->purchaseStatusFor($userReceipt, $storeProduct),
        ];
    }

    /**
     * The subscription rows ready for display, cheapest first.
     *
     * @return array
     */
    public function getSubscriptionRowsProperty(): array
    {
        $currentProductID = $this->currentReceipt?->product_id;
        $isTrialEligible = $this->isEligibleForTrial();

        return $this->subscriptionProducts
            ->reject(fn(StoreProduct $storeProduct) => $storeProduct->product_id === $currentProductID)
            ->map(fn(StoreProduct $storeProduct) => [
                'image' => $this->imageFor($storeProduct),
                'name' => $storeProduct->name,
                'secondary' => $this->secondaryFor($storeProduct, $isTrialEligible),
                'buttonLabel' => $this->buttonLabelFor($storeProduct),
            ])
            ->values()
            ->all();
    }

    /**
     * The Kurozora+ features ready for display.
     *
     * @return array
     */
    public function getFeaturesProperty(): array
    {
        return [
            [
                'image' => asset('images/static/in-app_purchases/unified_anime_linking.png'),
                'title' => __('Unified Anime Linking'),
                'description' => __('Seamlessly transition from other services to :x. Add ‘:site’ to any URL and let us bring all your anime data in one place.', ['x' => config('app.name'), 'site' => config('app.domain')]),
            ],
            [
                'image' => asset('images/static/in-app_purchases/reminders.png'),
                'title' => __('Integrate with Calendar'),
                'description' => __('Integrate your anime schedule into your calendar. Never miss an episode again with reminders for new airings.'),
            ],
            [
                'image' => asset('images/static/in-app_purchases/themes.png'),
                'title' => __('Dynamic Themes'),
                'description' => __('Choose from a range of themes to create a look that reflects your personality and style.'),
            ],
            [
                'image' => asset('images/static/in-app_purchases/icons.png'),
                'title' => __('Stylish App Icons'),
                'description' => __('Make your home screen stand out with premium and limited time app icons.'),
            ],
            [
                'image' => asset('images/static/in-app_purchases/chimes.jpg'),
                'title' => __('Startup Chimes'),
                'description' => __('Immerse yourself in the world of anime from the very start with serene chimes and iconic anime sounds.'),
            ],
            [
                'image' => asset('images/static/in-app_purchases/gifs.png'),
                'title' => __('Get Animated'),
                'description' => __('Upgrade your profile with a gif image that captures your unique style.'),
            ],
            [
                'image' => asset('images/static/in-app_purchases/username.jpg'),
                'title' => __('Change Your Identity'),
                'description' => __('Switch things up every now an then with a fresh username that truly represents you.'),
            ],
            [
                'image' => asset('images/static/in-app_purchases/character_count_500.jpg'),
                'title' => __('Up to :x Characters', ['x' => 1000]),
                'description' => __('Dive even deeper into discussions with an extended 1000 character limit for your feed messages.'),
            ],
            [
                'image' => asset('images/static/in-app_purchases/subscriber_badge.jpg'),
                'title' => __('Unlock Subscriber Badge'),
                'description' => __('Stand out in the community with an exclusive subscription badge that evolves over time as you continue to support :x!', ['x' => config('app.name')]),
            ],
            [
                'image' => asset('images/static/in-app_purchases/support.jpg'),
                'title' => __('Support the Community'),
                'description' => __('Your contribution helps with maintaining the servers, paying for software licenses, and fund events and activities.'),
            ],
        ];
    }

    /**
     * The subscription length of the product in months.
     *
     * @param StoreProduct $storeProduct
     *
     * @return int
     */
    protected function monthsFor(StoreProduct $storeProduct): int
    {
        return (int) round($storeProduct->duration_days / 30.44);
    }

    /**
     * The display price of the product in USD.
     *
     * @param StoreProduct $storeProduct
     *
     * @return string
     */
    protected function priceFor(StoreProduct $storeProduct): string
    {
        return '$' . number_format($storeProduct->price_usd_milliunits / 1000, 2);
    }

    /**
     * The tier artwork of the product.
     *
     * @param StoreProduct $storeProduct
     *
     * @return string
     */
    protected function imageFor(StoreProduct $storeProduct): string
    {
        $fileName = match ($this->monthsFor($storeProduct)) {
            6 => 'six_months.jpg',
            12 => 'twelve_months.jpg',
            default => 'one_month.jpg',
        };

        return asset('images/static/in-app_purchases/' . $fileName);
    }

    /**
     * The subscription period of the product spelled out.
     *
     * @param StoreProduct $storeProduct
     *
     * @return string
     */
    protected function displayUnitFor(StoreProduct $storeProduct): string
    {
        $months = $this->monthsFor($storeProduct);

        return $months === 1
            ? __('a month')
            : __(':x months', ['x' => $months]);
    }

    /**
     * The price button label of the product.
     *
     * @param StoreProduct $storeProduct
     *
     * @return string
     */
    protected function buttonLabelFor(StoreProduct $storeProduct): string
    {
        $months = $this->monthsFor($storeProduct);
        $shortDisplayUnit = match ($months) {
            12 => '1y',
            default => $months . 'm',
        };

        return $this->priceFor($storeProduct) . '/' . $shortDisplayUnit;
    }

    /**
     * The secondary label of the product, pairing the free trial with the savings.
     *
     * @param StoreProduct $storeProduct
     * @param bool         $isTrialEligible
     *
     * @return ?string
     */
    protected function secondaryFor(StoreProduct $storeProduct, bool $isTrialEligible): ?string
    {
        $trialLine = $isTrialEligible ? $this->trialLineFor($storeProduct) : null;
        $savings = $this->savingsFor($storeProduct);

        if ($trialLine !== null && $savings !== null) {
            return $trialLine . "\n" . $savings;
        }

        return $trialLine ?? $savings;
    }

    /**
     * Whether the user is eligible for the introductory free trial.
     *
     * @return bool
     */
    protected function isEligibleForTrial(): bool
    {
        $user = auth()->user();

        if ($user === null) {
            return true;
        }

        return !$user->receipts()->exists();
    }

    /**
     * The free trial line of the product.
     *
     * @param StoreProduct $storeProduct
     *
     * @return ?string
     */
    protected function trialLineFor(StoreProduct $storeProduct): ?string
    {
        $trialPeriod = $this->trialPeriodFor($storeProduct);

        if ($trialPeriod === null) {
            return null;
        }

        return __('Includes :x free trial!', ['x' => $trialPeriod]);
    }

    /**
     * The introductory free trial period of the product spelled out.
     *
     * @param StoreProduct $storeProduct
     *
     * @return ?string
     */
    protected function trialPeriodFor(StoreProduct $storeProduct): ?string
    {
        return match ($this->monthsFor($storeProduct)) {
            1 => __('a week'),
            6 => __(':x weeks', ['x' => 2]),
            12 => __('a month'),
            default => null,
        };
    }

    /**
     * The savings of the product compared to the monthly tier.
     *
     * @param StoreProduct $storeProduct
     *
     * @return ?string
     */
    protected function savingsFor(StoreProduct $storeProduct): ?string
    {
        $months = $this->monthsFor($storeProduct);

        if ($months <= 1) {
            return null;
        }

        $monthlyProduct = $this->subscriptionProducts->first(fn(StoreProduct $subscriptionProduct) => $this->monthsFor($subscriptionProduct) === 1);

        if ($monthlyProduct === null) {
            return null;
        }

        $pricePerMonth = $storeProduct->price_usd_milliunits / $months;
        $savedPercentage = round((1 - $pricePerMonth / $monthlyProduct->price_usd_milliunits) * 100) . '%';

        return __('(:x months at :ymo. Save :z)', [
            'x' => $months,
            'y' => '$' . number_format($pricePerMonth / 1000, 2),
            'z' => $savedPercentage,
        ]);
    }

    /**
     * The renewal status line of the receipt.
     *
     * @param UserReceipt $userReceipt
     *
     * @return string
     */
    protected function renewalStatusFor(UserReceipt $userReceipt): string
    {
        return $userReceipt->expires_at !== null
            ? __('Renews :x', ['x' => $userReceipt->expires_at->toFormattedDateString()])
            : __('Date unknown.');
    }

    /**
     * The purchase status line of the receipt.
     *
     * @param UserReceipt  $userReceipt
     * @param StoreProduct $storeProduct
     *
     * @return ?string
     */
    protected function purchaseStatusFor(UserReceipt $userReceipt, StoreProduct $storeProduct): ?string
    {
        if ($userReceipt->revoked_at !== null) {
            return __('The App Store refunded your subscription to :x on :y.', ['x' => $storeProduct->name, 'y' => $userReceipt->revoked_at->toFormattedDateString()]);
        }

        if ($userReceipt->grace_period_expires_date?->isFuture()) {
            return __('The App Store could not confirm your billing information for :x. Please verify your billing information to continue service after :y', ['x' => $storeProduct->name, 'y' => $userReceipt->grace_period_expires_date->toFormattedDateString()]);
        }

        if (!$userReceipt->will_auto_renew && $userReceipt->expires_at?->isFuture()) {
            return __('Your subscription to :x will expire on :y.', ['x' => $storeProduct->name, 'y' => $userReceipt->expires_at->toFormattedDateString()]);
        }

        if ($userReceipt->expires_at?->isPast()) {
            return __('Your subscription to :x expired on :y.', ['x' => $storeProduct->name, 'y' => $userReceipt->expires_at->toFormattedDateString()]);
        }

        return null;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.kurozora-plus.index');
    }
}
