<main>
    <x-slot:title>
        {{ __('Create Theme') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Create a unique theme for your account only on Kurozora, the largest, free online anime, manga & music database in the world.') }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Create Theme') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Create a unique theme for your account only on Kurozora, the largest, free online anime, manga & music database in the world.') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('theme-store.create') }}">
    </x-slot:meta>

    {{-- Header --}}
    <section class="bg-gray-100 shadow">
        <article>
            <header class="flex max-w-7xl mx-auto py-6 px-4 sm:px-6">
                <h1 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Create Theme') }}</h1>
            </header>
        </article>
    </section>

    {{-- Content --}}
    <section class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <div class="text-center mt-6">
            <h1 class="text-2xl font-black">{{ __('Create your own theme') }}</h1>
            <p class="">{{ __('Take your time and make something great, we\'ll leave you to it.') }}</p>
        </div>

        <livewire:theme-store.theme-store-roller />
    </section>

    <section class="max-w-7xl mx-auto px-4 py-6 text-center sm:px-6">
        <h1 class="text-xl font-bold">{{ __('Finished designing your theme?') }}</h1>
        <p>{{ __ ('Use the button below to submit your theme to the Kurozora team for approval.') }}</p>

        <div class="mt-6">
            <x-button class="rounded-full font-bold">
                {{ __('Submit') }}
            </x-button>
        </div>
    </section>
</main>
