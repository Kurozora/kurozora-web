<main>
    <x-slot:title>
        {{ __('Studios') }} | {!! $game->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('An extensive list of :x studios only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $game->title, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Studios') }} | {{ $game->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('An extensive list of :x studios only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $game->title, 'y' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ $game->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/game_poster.webp') }}" />
        <meta property="og:type" content="video.tv_show" />
        <meta property="video:duration" content="{{ $game->duration }}" />
        <meta property="video:release_date" content="{{ $game->published_at?->toIso8601String() }}" />
        <link rel="canonical" href="{{ route('games.studios', $game) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        games/{{ $game->id }}/studios
    </x-slot:appArgument>

    <div class="pt-4 pb-6" wire:init="loadPage">
        <section class="mb-4 xl:safe-area-inset">
            <div>
                <div class="flex gap-1 pl-4 pr-4">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __(':x’s Studios', ['x' => $game->title]) }}</h1>
                    </div>

                    <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                    </div>
                </div>
            </div>
        </section>

        @if ($this->studios->count())
            <section class="xl:safe-area-inset">
                <x-rows.studio-lockup :studios="$this->studios" :is-row="false" />

                <div class="mt-4 pl-4 pr-4">
                    {{ $this->studios->links() }}
                </div>
            </section>
        @elseif (!$readyToLoad)
            <section class="xl:safe-area-inset">
                <div class="flex flex-wrap gap-4 justify-between pl-4 pr-4">
                    @foreach (range(1,25) as $range)
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                    @endforeach
                    <div class="w-64 md:w-80 flex-grow"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                </div>
            </section>
        @endif
    </div>
</main>
