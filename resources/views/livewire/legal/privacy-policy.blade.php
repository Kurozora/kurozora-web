<main>
    <x-slot:title>
        {{ __('Privacy Policy') }}
    </x-slot>

    <x-slot:description>
        {{ __('Your privacy is important to Kurozora, so we have developed a Privacy Policy that covers how we collect, use, disclose, transfer, and store your personal information.') }}
    </x-slot>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Privacy Policy') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Your privacy is important to Redark, so we have developed a Privacy Policy that covers how we collect, use, disclose, transfer, and store your personal information.') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('legal.privacy-policy') }}">
    </x-slot>

    <x-slot:appArgument>
        legal/privacy-policy
    </x-slot>

    {{-- Header --}}
    <section class="bg-gray-100 shadow">
        <article>
            <header class="flex max-w-7xl mx-auto px-4 py-6 sm:px-6">
                <h1 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Legal') }}</h1>
            </header>
        </article>
    </section>

    {{-- Content --}}
    <section class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <article>
            <hedaer class="text-center mt-16">
                <h1 class="text-xl font-bold">{{ __('Privacy Policy') }}</h1>
            </hedaer>
        </article>

        <article>
            {!! $privacyPolicy !!}
        </article>
    </section>
</main>
