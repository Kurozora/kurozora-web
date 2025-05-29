<main>
    <x-slot:title>
        {{ __('App Icon') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover the extensive list of app icons only on :x, the largest, free online anime, manga, game & music database in the world.', ['x' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('App Icon') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover the extensive list of app icons only on :x, the largest, free online anime, manga, game & music database in the world.', ['x' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('app-icons.index') }}">
    </x-slot:meta>

    <x-slot:appArgument>
        app-icon
    </x-slot:appArgument>

    <div class="pt-4 pb-6">
        <section class="mb-4">
            <div>
                <div class="flex gap-1 pl-4 pr-4">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __('App Icon') }}</h1>
                    </div>

                    <div class="flex flex-wrap justify-end items-center w-full">
                    </div>
                </div>
            </div>
        </section>

        <div class="space-y-4">
            @if ($this->searchResults->count())
                @foreach($this->searchResults as $categoryName => $appIcons)
                    <section id="{{ str($categoryName)->slug() }}" class="space-y-4">
                        <div class="pl-4 pr-4">
                            <h2 class="text-lg font-bold uppercase">{{ __($categoryName) }}</h2>
                        </div>

                        <div
                            class="flex flex-wrap gap-4 pl-4 pr-4"
                        >
                            @foreach ($appIcons as $appIcon)
                                <button
                                    class="relative flex flex-col items-center gap-2 pt-2 pb-2 pl-2 pr-2 rounded-xl hover:bg-tertiary"
                                    :class="{'bg-secondary': currentAppIconName.toLowerCase() === '{{ strtolower($appIcon->name) }}'}"
                                    style="width: 128px"
                                    title="{{ $appIcon->name }}"
                                    wire:key="{{ uniqid(md5($appIcon->name), true) }}"
                                    wire:click="setAppIcon('{{ $appIcon->name }}')"
                                    x-data="{
                                        currentAppIconName: $persist(@entangle('currentAppIconName').live).as('currentAppIconName')
                                    }"
                                >
                                    <x-app-icon size="64px" :image-url="$appIcon->getImage()" />

                                    <p class="text-center leading-tight line-clamp-2" title="{{ $appIcon->name }}">{{ $appIcon->name }}</p>
                                </button>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            @else
                <section class="flex flex-col items-center justify-center mt-4 text-center" style="min-height: 50vh;">
                    <x-picture>
                        <img class="w-full max-w-sm" src="{{ asset('images/static/placeholders/empty_anime_library.webp') }}" alt="Empty App Icon" title="Empty App Icon">
                    </x-picture>

                    <p class="font-bold">{{ __('App Icons Not Found') }}</p>

                    <p class="text-sm text-secondary">{{ __('No app icons found with the selected criteria.') }}</p>
                </section>
            @endif
        </div>
    </div>
</main>
