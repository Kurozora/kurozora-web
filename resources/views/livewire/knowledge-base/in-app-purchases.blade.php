<main>
    <x-slot name="title">
        {{ __('About In-App Purchases') }}
    </x-slot>

    <x-slot name="description">
        {{ __('') }}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="{{ __('About In-App Purchases') }} — {{ config('app.name') }}" />
        <meta property="og:og:description" content="{{ __('') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <img class="w-full h-32 object-cover object-center rounded-lg shadow-lg sm:h-44" src="{{ asset('images/static/banners/in-app_purchases.webp') }}"  alt="About Personalisation" />

        <section class="my-4">
            <p class="text-2xl font-bold">{{ __('About In-App Purchases') }}</p>
        </section>

        {{-- Header --}}
        <section>
            <p>{{ __('Kurozora is free to download and use. However, some supplemental features can be unlocked using in-app purchases. The cost depends on which offer you choose. You can find in-app purchases in the settings of the Kurozora app.') }}</p>

            <x-hr class="my-4" />
        </section>

        {{-- Types of IAP --}}
        <section>
            <p class="text-xl font-bold">{{ __('What types of in-app purchases can you make?') }}</p>
            <p>{{ __('There are three types of in-app purchases:') }}</p>

            <ul class="list-disc">
                <li>
                    <p><strong>{{ __('Kurozora+') }}—</strong> {{ __('a membership that unlocks all features within kurozora.') }}</p>
                    <p>{{ __('There are 3 options to choose from. All offers unlock the same features.') }}</p>
                    <p>{{ __('(1) Free to try for 1 week and $3.99 per month after that.') }}</p>
                    <p>{{ __('(2) Free to try for 14 days and $11.99 per 6 months after that. Save up to 50%.') }}</p>
                    <p>{{ __('(3) Free to try for 1 month and $18.99 per 12 months after that. Save up to 60%.') }}</p>
                </li>
                <li>
                    <p><strong>{{ __('Kurozora One') }}—</strong> {{ __('a one time purchase for the price of $34.99 which unlocks most features forever.') }}</p>
                </li>
                <li>
                    <p><strong>{{ __('Donations') }}—</strong> {{ __('a small amount of your choice to help with the development of the app. No commitments or recurring fees.') }}</p>
                </li>
            </ul>

            <x-hr class="my-4" />
        </section>

        {{-- Manage your subscriptions --}}
        <section>
            <p class="text-xl font-bold">{{ __('Manage your subscriptions') }}</p>
            <p>{{ __('Unlike donations and Kurozora One, Kurozora+ renews until you choose to end it. If you wish to cancel your subscription, you can easily change renewal settings from the Kurozora app. On the Kurozora app, go to settings and scroll down to the Support Us section. Next, tap Manage Subscriptions.') }}</p>
            <br />

            <img class="w-full rounded-lg shadow-lg" src="{{ asset('images/static/screenshots/manage_subscriptions.webp') }}" alt="Manage Kurozora Subscriptions" style="max-width: 320px;">
            <br />

            <p>{{ __('Use the presented options to manage your subscription. You can choose a different subscription offering or tap Cancel Subscription to cancel your subscription. After you cancel, your subscription will stop at the end of the current billing cycle.') }}</p>
            <br />

            <p>{{ __('You can also manage subscriptions through your Apple ID. Please refer to') }} <x-link target="_blank" href="https://support.apple.com/en-us/HT202039">{{ __('this') }}</x-link> {{ __('Apple support page for more information.') }}</p>

            <x-hr class="my-4" />
        </section>

        {{-- Subscriptions and privacy --}}
        <section>
            <p class="text-xl font-bold">{{ __('About subscriptions and privacy') }}</p>
            <p>{{ __('When you purchase Kurozora+, Apple manages the payment process according to their privacy policy. The only thing we do is create a randomly generated Subscriber ID that is unique to you. We use this Subscriber ID to provide you with the features you paid for across all platforms where Kurozora can be accessed.') }}</p>
        </section>
    </div>
</main>