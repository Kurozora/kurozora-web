<main>
    <x-slot:title>
        {{ __('About Personalisation') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Find out everything about personalisation on :x. How does :x handle my information? What information is saved? Does anyone else have access to this information?', ['x' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('About Personalisation') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Find out everything about personalisation on :x. How does :x handle my information? What information is saved? Does anyone else have access to this information?', ['x' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
    </x-slot:meta>

    <div class="pt-4 pb-6 max-w-full prose prose-theme lg:prose-lg">
        <x-picture class="mb-8 ml-4 mr-4 not-prose">
            <img class="h-32 w-full object-cover object-center rounded-lg shadow-lg sm:h-60" src="{{ asset('images/static/banners/personalisation.webp') }}"  alt="About Personalisation" />
        </x-picture>

        <section class="xl:safe-area-inset">
            <div class="pr-4 pl-4 ">
                <h1 class="text-2xl font-bold">{{ __('About Personalisation') }}</h1>

                <p>{{ __('We use your interactions with the app and website—such as the anime you track, purchases you make, downloads and what you’ve previously searched for and viewed—to help you discover anime and manga that are most relevant to you through personalised recommendations.') }}</p>

                <x-hr />

                <p>{{ __('Personalised information are never sold nor made accessible to any third party services. We use personalised information only on our platform to provide you with the best experience ever.') }}</p>
            </div>
        </section>
    </div>
</main>
