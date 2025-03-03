<main>
    <x-slot:title>
        {{ __('About Personalisation') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Find out everything about personalisation on Kurozora. How does Kurozora handle my information? What information is saved? Does anyone else have access to this information?') }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('About Personalisation') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Find out everything about personalisation on Kurozora. How does Kurozora handle my information? What information is saved? Does anyone else have access to this information?') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
    </x-slot:meta>

    <div class="max-w-7xl m-auto prose prose-theme pl-4 pr-4 sm:px-6 lg:prose-lg">
        <x-picture>
            <img class="h-32 w-full object-cover object-center rounded-lg shadow-lg sm:h-44" src="{{ asset('images/static/banners/personalisation.webp') }}"  alt="About Personalisation" />
        </x-picture>

        <section>
            <h1 class="text-2xl font-bold">{{ __('About Personalisation') }}</h1>

            <p>{{ __('We use your interactions with the app and website—such as the anime you track, purchases you make, downloads and what you’ve previously searched for and viewed—to help you discover anime and manga that are most relevant to you through personalised recommendations.') }}</p>

            <x-hr />

            <p>{{ __('Personalised information are never sold or made accessible to any third party services. We use personalised information only on our platform to provide you with the best experience ever.') }}</p>
        </section>
    </div>
</main>
