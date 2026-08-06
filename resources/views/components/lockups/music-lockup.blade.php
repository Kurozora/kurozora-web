@props(['song', 'anime' => null, 'type' => null, 'position' => null, 'episodes' => null, 'showEpisodes' => true, 'showModel' => false, 'rank', 'isRanked' => false, 'isRow' => true])

@php
    $class = $isRow ? 'shrink-0' : 'flex-grow';
    $artworkURL = $song->getFirstMediaFullUrl(\App\Enums\MediaCollection::Artwork()) ?? asset('images/static/placeholders/music_album.webp');
@endphp

<div
    wire:key="{{ uniqid(more_entropy: true) }}"
    class="relative pb-2 w-64 snap-normal snap-center {{ $class }}"
    @if (!empty($song->am_id)) data-music-song data-am-id="{{ $song->am_id }}" @endif
>
    <div class="relative flex flex-col">
        <x-picture class="aspect-square rounded-lg shadow-md overflow-hidden">
            <img class="w-full h-full object-cover bg-[#A660B2]"
                 width="320" height="320"
                 src="{{ $artworkURL }}"
                 title="{{ $song->original_title }}"
                 alt="{{ $song->original_title }}"
            >

            <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
        </x-picture>

        <a class="absolute bottom-0 w-full h-full" href="{{ route('songs.details', $song) }}" wire:navigate></a>

        @if (!empty($song->am_id))
            <div class="absolute bottom-2 right-2">
                <div class="flex flex-col justify-center items-center h-full">
                    <button
                        type="button"
                        data-music-play
                        title="{{ __('Play') }}"
                        class="inline-flex items-center pt-2 pr-2 pb-2 pl-2 bg-blur backdrop-blur border border-transparent rounded-full font-semibold text-xs uppercase tracking-widest shadow-md hover:bg-tint-800 hover:btn-text-tinted active:bg-tint active:btn-text-tinted focus:outline-none transition ease-in-out duration-150"
                    >
                        <span data-music-icon="play">@svg('play_fill', 'fill-current', ['width' => '18'])</span>
                        <span data-music-icon="pause" class="hidden">@svg('pause_fill', 'fill-current', ['width' => '18'])</span>
                    </button>
                </div>
            </div>
        @endif
    </div>

    <div class="relative flex flex-grow mt-2">
        <div class="flex flex-col w-full gap-2 justify-between">
            <div>
            @if ($isRanked)
                <p class="text-sm leading-tight font-semibold" title="{{ __('Ranked #:x', ['x' => $rank]) }}">{{ __('#:x', ['x' => $rank]) }}</p>
            @endif

            <a class="flex gap-2 justify-between" href="{{ route('songs.details', $song) }}" wire:navigate>
                <p class="line-clamp-2" title="{{ $song->original_title }}">{{ $song->original_title }}</p>

                @if ($type && !$showModel || $type && $anime)
                    <span class="ml-1 pl-2 pr-2 pt-1 pb-1 h-full {{ $type->color() }} text-white text-xs font-semibold whitespace-nowrap rounded-full" title="{{ __(':x #:y', ['x' => $type->description, 'y' => $position]) }}">{{ __(':x #:y', ['x' => $type->abbreviated(), 'y' => $position]) }}</span>
                @endif
            </a>

            <p class="opacity-75 line-clamp-2" title="{{ $song->artist ?? __('Unknown') }}">{{ $song->artist ?? __('Unknown') }}</p>

            @if ($anime && $showModel)
                <x-simple-link class="text-sm" href="{{ route('anime.details', $anime) }}" wire:navigate>{{ $anime->title }}</x-simple-link>
            @endif

            @if (!empty($episodes) && $showEpisodes)
                <p class="text-sm opacity-75 line-clamp-2" title="{{ __('Episodes: :x', ['x' => $episodes]) }}">{{ __('Episodes: :x', ['x' => $episodes]) }}</p>
            @endif
            </div>
        </div>
    </div>
</div>
