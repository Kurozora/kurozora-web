<main>
    <x-slot:title>
        {{ __('Privacy Policy') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Your privacy is important to :x, so we have developed a Privacy Policy that covers how we collect, use, disclose, transfer, and store your personal information.', ['x' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Privacy Policy') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Your privacy is important to :x, so we have developed a Privacy Policy that covers how we collect, use, disclose, transfer, and store your personal information.', ['x' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('legal.privacy-policy') }}">
    </x-slot:meta>

    <x-slot:appArgument>
        legal/privacy-policy
    </x-slot:appArgument>

    {{-- Header --}}
    <section class="bg-secondary shadow">
        <article>
            <header class="flex pl-4 pr-4 py-6">
                <h1 class="font-semibold text-xl text-primary leading-tight">{{ __('Legal') }}</h1>
            </header>
        </article>
    </section>

    {{-- Content --}}
    <section class="pl-4 pr-4 py-6">
        <article>
            <hedaer class="text-center mt-16">
                <h2 class="text-xl font-bold">{{ __('Privacy Policy') }}</h2>
            </hedaer>
        </article>

        <article>
            {!! $privacyPolicy !!}
        </article>
    </section>
</main>
