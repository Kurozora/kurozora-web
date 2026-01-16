<main>
    <x-slot:title>
        {{ __('About In-App Purchases') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Find out everything about in-app purchases on :x. What does :x+ cost? How to manage or cancel subscription? How is privacy managed?', ['x' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('About In-App Purchases') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Find out everything about in-app purchases on :x. What does :x+ cost? How to manage or cancel subscription? How is privacy managed?', ['x' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
    </x-slot:meta>

    <div class="pt-4 pb-6 max-w-full prose prose-theme lg:prose-lg">
        <x-picture class="mb-8 ml-4 mr-4 not-prose">
            <img class="h-32 w-full object-cover object-center rounded-lg shadow-lg sm:h-60" src="{{ asset('images/static/banners/in-app_purchases.webp') }}"  alt="About Personalisation" />
        </x-picture>

        {{-- Header --}}
        <section class="xl:safe-area-inset">
            <div class="pr-4 pl-4 ">
                <h1 class="text-2xl font-bold">{{ __('About In-App Purchases') }}</h1>

                <p>{{ __(':x is free to download and use. However, some supplemental features can be unlocked using in-app purchases. The cost depends on which offer you choose. You can find in-app purchases in the settings of the :x app.', ['x' => config('app.name')]) }}</p>

                <x-hr />
            </div>
        </section>

        {{-- Types of IAP --}}
        <section id="what-types-of-in-app-purchases-can-you-make" class="xl:safe-area-inset">
            <div class="pr-4 pl-4 ">
                <h2 class="text-xl font-bold">
                    <a href="#what-types-of-in-app-purchases-can-you-make">{{ __('What types of in-app purchases can you make?') }}</a>
                </h2>

                <p>{{ __('There are three types of in-app purchases:') }}</p>

                <ul class="list-disc">
                    <li>
                        <p>{!! __('<strong>:x+ —</strong> a membership that unlocks all features within :x.', ['x' => config('app.name')]) !!}</p>
                        <p>{{ __('There are 3 options to choose from. All offers unlock the same features.') }}</p>
                        <p>{{ __('(1) Free to try for 1 week and $3.99 per month after that.') }}</p>
                        <p>{{ __('(2) Free to try for 14 days and $11.99 per 6 months after that. Save up to 50%.') }}</p>
                        <p>{{ __('(3) Free to try for 1 month and $18.99 per 12 months after that. Save up to 60%.') }}</p>
                    </li>
                    <li>
                        <p>{!! __('<strong>:x One —</strong> a one time purchase for the price of $34.99 which unlocks most features forever.', ['x' => config('app.name')]) !!}</p>
                    </li>
                    <li>
                        <p>{!! __('<strong>Donations —</strong> a small amount of your choice to help with the development of the app. No commitments or recurring fees.') !!}</p>
                    </li>
                </ul>

                <x-hr />
            </div>
        </section>

        {{-- Manage your subscriptions --}}
        <section id="manage-your-subscriptions" class="xl:safe-area-inset">
            <div class="pr-4 pl-4 ">
                <h2 class="text-xl font-bold">
                    <a href="#manage-your-subscriptions">{{ __('Manage your subscriptions') }}</a>
                </h2>

                <p>{{ __('Unlike donations and :x One, :x+ renews until you choose to end it. If you wish to cancel your subscription, you can easily change renewal settings from the :x app. On the :x app, go to settings and scroll down to the Support Us section. Next, tap Manage Subscriptions.', ['x' => config('app.name')]) }}</p>

                <x-picture style="max-width: 320px;">
                    <img class="rounded-lg shadow-lg" src="{{ asset('images/static/screenshots/manage_subscriptions.webp') }}" alt="{{ __('Manage :x Subscriptions', ['x' => config('app.name')]) }}">
                </x-picture>

                <p>{{ __('Use the presented options to manage your subscription. You can choose a different subscription offering or tap Cancel Subscription to cancel your subscription. After you cancel, your subscription will stop at the end of the current billing cycle.') }}</p>

                <p>{{ __('You can also manage subscriptions through your Apple ID. Please refer to') }} <a target="_blank" href="https://support.apple.com/en-us/HT202039">{{ __('this') }}</a> {{ __('Apple support page for more information.') }}</p>

                <x-hr />
            </div>
        </section>

        {{-- Subscriptions and privacy --}}
        <section id="subscriptions-and-privacy" class="xl:safe-area-inset">
            <div class="pr-4 pl-4 ">
                <h2 class="text-xl font-bold">
                    <a href="#subscriptions-and-privacy">{{ __('About subscriptions and privacy') }}</a>
                </h2>

                <p>{{ __('When you purchase :x+, Apple manages the payment process according to their privacy policy. The only thing we do is create a randomly generated Subscriber ID that is unique to you. We use this Subscriber ID to provide you with the features you paid for across all platforms where :x can be accessed.', ['x' => config('app.name')]) }}</p>
            </div>
        </section>
    </div>
</main>
