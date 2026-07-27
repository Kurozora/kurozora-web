<main>
    <x-slot:title>
        {{ __('Become a Subscriber') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Take your tracking to the next level with :x+. Get access to exclusive features like GIF profile images, premium app icons, customizable themes, and iCal reminders to never miss an episode. Upgrade to :x+ and get the ultimate experience.', ['x' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Become a Subscriber') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Take your tracking to the next level with :x+. Get access to exclusive features like GIF profile images, premium app icons, customizable themes, and iCal reminders to never miss an episode. Upgrade to :x+ and get the ultimate experience.', ['x' => config('app.name')]) }}" />
        <link rel="canonical" href="{{ route('kurozora-plus') }}">
    </x-slot:meta>

    <x-slot:appArgument>
        kurozora-plus
    </x-slot:appArgument>

    <div class="pt-4 pb-6">
        <section class="xl:safe-area-inset">
            <div class="pl-4 pr-4">
                <x-purchase.header
                    :primary="__('Elevate your tracking with :x+', ['x' => config('app.name')])"
                    :secondary="__('Take your tracking to the next level with :x+. Get access to exclusive features like GIF profile images, premium app icons, customizable themes, and iCal reminders to never miss an episode. Upgrade to :x+ and get the ultimate experience.', ['x' => config('app.name')])"
                />

                <p class="pb-6 text-sm text-secondary text-center max-w-2xl mx-auto">
                    {{ __('Purchases are made in the :x app.', ['x' => config('app.name')]) }}
                    <x-simple-link href="{{ config('app.ios.store_url') }}" target="_blank">{{ __('Get the app') }}</x-simple-link>
                </p>
            </div>
        </section>

        @if (!empty($this->currentSubscription))
            <section class="xl:safe-area-inset">
                <div class="pb-4 pl-4 pr-4">
                    <x-purchase.status-card
                        :image="$this->currentSubscription['image']"
                        :name="$this->currentSubscription['name']"
                        :price="$this->currentSubscription['price']"
                        :renewal-status="$this->currentSubscription['renewalStatus']"
                        :purchase-status="$this->currentSubscription['purchaseStatus']"
                    />
                </div>
            </section>
        @endif

        <section class="xl:safe-area-inset">
            <div class="flex flex-col gap-2 pl-4 pr-4">
                @foreach ($this->subscriptionRows as $subscriptionRow)
                    <x-purchase.button-row
                        :image="$subscriptionRow['image']"
                        :primary="$subscriptionRow['name']"
                        :secondary="$subscriptionRow['secondary']"
                    >
                        <x-link-button href="{{ config('app.ios.store_url') }}" target="_blank">{{ $subscriptionRow['buttonLabel'] }}</x-link-button>
                    </x-purchase.button-row>
                @endforeach
            </div>
        </section>

        <section class="pt-6 xl:safe-area-inset">
            <div class="flex flex-wrap gap-4 justify-between pl-4 pr-4">
                @foreach ($this->features as $productFeature)
                    <x-purchase.feature-card
                        class="w-64 md:w-80 flex-grow"
                        :image="$productFeature['image']"
                        :title="$productFeature['title']"
                        :description="$productFeature['description']"
                    />
                @endforeach

                <div class="w-64 md:w-80 flex-grow"></div>
                <div class="w-64 md:w-80 flex-grow"></div>
                <div class="w-64 md:w-80 flex-grow"></div>
                <div class="w-64 md:w-80 flex-grow"></div>
            </div>
        </section>

        <section class="xl:safe-area-inset">
            <div class="pl-4 pr-4">
                <x-purchase.footer>
                    <x-link-button class="whitespace-nowrap" href="{{ config('app.ios.store_url') }}" target="_blank">
                        {{ __('Restore Purchase') }}
                    </x-link-button>
                </x-purchase.footer>
            </div>
        </section>
    </div>
</main>
