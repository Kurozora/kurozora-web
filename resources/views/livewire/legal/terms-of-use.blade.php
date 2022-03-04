<main>
    <x-slot:title>
        {{ __('Terms of Use') }}
    </x-slot>

    <x-slot:description>
        {{ __('These Terms and Conditions of Use (the "Terms of Use") apply to the Kurozora web site located at www.kurozora.app, and all associated sites linked to www.kurozora.app by Redark.') }}
    </x-slot>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Terms of Use') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('These Terms and Conditions of Use (the "Terms of Use") apply to the Kurozora web site located at www.kurozora.app, and all associated sites linked to www.kurozora.app by Redark.') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('legal.terms-of-use') }}">
    </x-slot>

    <x-slot:appArgument>
        legal/terms-of-use
    </x-slot>

    {{-- Header --}}
    <section class="bg-gray-100 shadow">
        <article>
            <header class="flex max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <h1 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Legal') }}</h1>
            </header>
        </article>
    </section>

    {{-- Content --}}
    <section class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
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
