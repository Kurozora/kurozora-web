<main>
    <x-slot:title>
        {{ __('Up-Next Episodes') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Find what episodes to watch next on :x, the largest, free online anime, manga, game & music database in the world.', ['x' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Up-Next Episodes') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Find what episodes to watch next on :x, the largest, free online anime, manga, game & music database in the world.', ['x' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/placeholders/anime_poster.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('up-next.episodes') }}">
    </x-slot:meta>

    <x-slot:appArgument>
        up-next/episodes
    </x-slot:appArgument>

    <div class="pt-4 pb-6" wire:init="loadPage">
        <section class="mb-4">
            <div>
                <div class="flex gap-1 pl-4 pr-4">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __('Up-Next Episodes') }}</h1>
                    </div>

                    <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                    </div>
                </div>
            </div>
        </section>

        @if ($this->episodes->count())
            <section id="up-next" class="mb-16">
                <x-rows.episode-lockup :episodes="$this->episodes" :is-row="false" />

                <div class="mt-4 pl-4 pr-4">
                    {{ $this->episodes->links() }}
                </div>
            </section>
        @elseif (!$readyToLoad)
            <section id="up-next-skeleton" class="mb-16">
                <div class="flex flex-wrap gap-4 justify-between pl-4 pr-4">
                    @foreach (range(1,25) as $range)
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                    @endforeach
                    <div class="w-64 md:w-80 flex-grow"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                </div>
            </section>
        @else
            <section id="up-next-empty" class="mb-16">
                <div class="flex flex-col items-center justify-center mt-4 text-center">
                    <x-picture>
                        <img class="w-full max-w-sm" src="{{ asset('images/static/placeholders/empty_anime_library.webp') }}" alt="Empty Up-Next" title="Empty Up-Next">
                    </x-picture>

                    <p class="font-bold">{{ __('You watched them all!') }}</p>

                    <p class="text-sm text-secondary">{{ __('You’re up to date with every episode. Start a new anime to see episodes appear here!') }}</p>
                </div>
            </section>
        @endif

        <livewire:components.episode.past-episodes-section />
    </div>
</main>
