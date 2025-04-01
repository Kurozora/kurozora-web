<main>
    <x-slot:title>
        {{ __('Terms of Use') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('These Terms and Conditions of Use (the "Terms of Use") apply to the :x web site located at :y, and all associated sites linked to :y by :x.', ['x' => config('app.name'), 'y' => config('app.domain')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Terms of Use') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('These Terms and Conditions of Use (the "Terms of Use") apply to the :x web site located at :y, and all associated sites linked to :y by :x.', ['x' => config('app.name'), 'y' => config('app.domain')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('legal.terms-of-use') }}">
    </x-slot:meta>

    <x-slot:appArgument>
        legal/terms-of-use
    </x-slot:appArgument>

    {{-- Header --}}
    <section class="bg-secondary shadow">
        <article>
            <header class="flex pt-4 pb-6 pl-4 pr-4">
                <h2 class="text-2xl font-bold">{{ __('Legal') }}</h2>
            </header>
        </article>
    </section>

    {{-- Content --}}
    <section class="pt-4 pb-6 pl-4 pr-4">
        <article>
            <hedaer class="text-center mt-16">
                <h1 class="text-xl font-bold">{{ __('Terms of Use') }}</h1>
            </hedaer>
        </article>

        <article>
            {!! $termsOfUse !!}
        </article>
    </section>
</main>
