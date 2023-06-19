<main>
    <x-slot:title>
        {{ __('Theme Store') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover the extensive list of platform themes only on Kurozora, the largest, free online anime, manga, game & music database in the world.') }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Theme Store') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover the extensive list of platform themes only on Kurozora, the largest, free online anime, manga, game & music database in the world.') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('theme-store.index') }}">
    </x-slot:meta>

    <x-slot:appArgument>
        theme-store
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
        <section class="mb-4">
            <div>
                <div class="flex gap-1">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __('Theme Store') }}</h1>
                    </div>

                    <div class="flex flex-wrap justify-end items-center w-full">
                        <x-link-button href="{{ route('theme-store.create') }}">{{ __('Create') }}</x-link-button>
                    </div>
                </div>

                <x-search-bar />
            </div>
        </section>

        <section class="grid gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
            @foreach($this->searchResults as $platformTheme)
                <x-lockups.platform-theme-lockup :theme="$platformTheme" />
            @endforeach
        </section>

        <section class="mt-4">
            {{ $this->searchResults->links() }}
        </section>
    </div>
</main>
