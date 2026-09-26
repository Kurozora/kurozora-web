<main>
    <x-slot:title>
        {{ __('People') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover the extensive list of people, voice actors, cast, and staff only on :x, the largest, free online anime, manga, game & music database in the world.', ['x' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('People') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover an extensive list of people, voice actors, cast, and staff on :x, the largest, free online anime, manga, game & music database in the world.', ['x' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('people.index') }}">
    </x-slot:meta>

    <x-slot:appArgument>
        people
    </x-slot:appArgument>

    <div class="pt-4 pb-6" wire:init="loadPage">
        <section class="mb-4 xl:safe-area-inset">
            <div>
                <div class="flex gap-1 pl-4 pr-4">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __('People') }}</h1>
                    </div>

                    <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                    </div>
                </div>

                <x-search-bar>
                    <x-slot:rightBarButtonItems>
                        <x-square-button wire:click="randomPerson">
                            @svg('dice', 'fill-current', ['aria-labelledby' => 'random person', 'width' => '28'])
                        </x-square-button>
                    </x-slot:rightBarButtonItems>
                </x-search-bar>
            </div>
        </section>

        @if ($this->searchResults->count())
            <section class="mt-4 xl:safe-area-inset">
                <x-rows.person-lockup :people="$this->searchResults" :is-row="false" />

                <div class="mt-4 pl-4 pr-4">
                    {{ $this->searchResults->links() }}
                </div>
            </section>
        @elseif (!$readyToLoad)
            <section class="mt-4 xl:safe-area-inset">
                <x-skeletons.lockup-row lockup="person" :is-row="false" />
            </section>
        @else
            <x-empty-state image="empty_anime_library.webp" alt="Empty People Index" :heading="__('People Not Found')" :description="__('No people found with the selected criteria.')" />
        @endif
    </div>
</main>
