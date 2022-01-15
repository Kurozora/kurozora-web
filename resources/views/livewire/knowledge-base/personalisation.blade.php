<main>
    <x-slot name="title">
        {{ __('About Personalisation') }}
    </x-slot>

    <x-slot name="description">
        {{ __('') }}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="{{ __('About Personalisation') }} — {{ config('app.name') }}" />
        <meta property="og:og:description" content="{{ __('') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <img class="w-full h-32 object-cover object-center rounded-lg shadow-lg sm:h-44" src="{{ asset('images/static/banners/personalisation.webp') }}"  alt="About Personalisation" />

        <section class="my-4">
            <p class="text-2xl font-bold">{{ __('About Personalisation') }}</p>
        </section>

        <section>
            <p>{{ __('We use your interactions with the app and website—such as the anime you track, purchases you make, downloads and what you’ve previously searched for and viewed—to help you discover anime and manga that are most relevant to you through personalised recommendations.') }}</p>

            <x-hr class="my-4" />

            <p>{{ __('Personalised information are never sold or made accessible to any third party services. We use personalised information only on our platform to provide you with the best experience ever.') }}</p>
        </section>
    </div>
</main>
