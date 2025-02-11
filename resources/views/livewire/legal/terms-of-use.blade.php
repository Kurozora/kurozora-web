<main>
    <x-slot:title>
        {{ __('Terms of Use') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('These Terms and Conditions of Use (the "Terms of Use") apply to the Kurozora web site located at www.kurozora.app, and all associated sites linked to www.kurozora.app by Kurozora.') }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Terms of Use') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('These Terms and Conditions of Use (the "Terms of Use") apply to the Kurozora web site located at www.kurozora.app, and all associated sites linked to www.kurozora.app by Kurozora.') }}" />
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
            <header class="flex max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
                <h1 class="font-semibold text-xl text-primary leading-tight">{{ __('Legal') }}</h1>
            </header>
        </article>
    </section>

    {{-- Content --}}
    <section class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
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
