<main>
    <x-slot:title>
        {!! $song->original_title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Listen to anime songs for free.') }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ $song->original_title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('app.description') }}" />
        <meta property="og:image" content="{{ $song->getFirstMediaFullUrl(\App\Enums\MediaCollection::Artwork()) ?? asset('images/static/placeholders/song_banner.webp') }}" />
        <meta property="og:type" content="music.song" />
        <meta property="og:url" content="{{ route('embed.songs', $song) }}">
        <meta property="twitter:title" content="{{ $song->original_title }} — {{ config('app.name') }}" />

        <link rel="canonical" href="{{ route('embed.songs', $song) }}">
        <link rel="alternate" type="application/json+oembed" href="{{ route('oembed', ['format' => 'json', 'url' => route('songs.details', $song)]) }}">
        <link rel="alternate" type="application/json+oembed" href="{{ route('oembed', ['format' => 'xml', 'url' => route('songs.details', $song)]) }}">
    </x-slot:meta>

    <x-slot:styles>
    </x-slot:styles>

    <x-slot:appArgument>
        songs/{{ $song->id }}
    </x-slot:appArgument>

    <x-slot:scripts>
        @vite(['resources/js/listen.js'])
    </x-slot:scripts>

    <div
        class="flex flex-col gap-4"
        @if (!empty($song->am_id)) data-music-song data-am-id="{{ $song->am_id }}" data-music-detail data-music-colors @endif
    >
        <div
            class="flex gap-4 pt-4 pr-5 pb-5 pl-5 rounded-lg"
            style="background-color: var(--am-bg, #A660B2);"
        >
            <div>
                <x-picture
                    data-music-play
                    class="aspect-square rounded-lg natural-shadow overflow-hidden cursor-pointer"
                    style="height: calc(100vh - 2.50rem);max-height: 200px;min-height: 164px;"
                >
                    <img class="object-cover"
                         data-music-artwork
                         alt="{{ $song->original_title }}" title="{{ $song->original_title }}"
                         width="500" height="500"
                         src="{{ $song->getFirstMediaFullUrl(\App\Enums\MediaCollection::Artwork()) ?? asset('images/static/placeholders/music_album.webp') }}"
                         style="background-color: var(--am-bg, #A660B2);"
                    >

                    <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg cursor-pointer"></div>
                </x-picture>
            </div>

            <div class="flex flex-col gap-4 justify-between w-full">
                <div class="flex gap-4 justify-between items-start">
                    <div class="flex flex-col gap-4">
                        <div class="flex flex-col gap-1">
                            <p class="leading-tight line-clamp-2 font-bold" style="color: var(--am-text-1, inherit);">{{ $song->original_title }}</p>
                            <p class="text-sm leading-tight opacity-75 line-clamp-2" style="color: var(--am-text-2, inherit);">{{ $song->artist ?? 'Unknown' }}</p>
                            <p class="text-sm leading-tight opacity-75 line-clamp-2" style="color: var(--am-text-3, inherit);" data-music-album></p>
                        </div>

                        <div class="flex gap-2">
                            <span
                                data-music-preview-badge
                                class="pt-1 pr-1 pb-1 pl-1 uppercase text-xs font-bold border rounded-md cursor-default"
                                style="color: var(--am-bg, #A660B2); background-color: var(--am-text-1, #ffffff); border-color: var(--am-text-1, #ffffff);"
                            >{{ __('Preview') }}</span>

                            <span
                                data-music-explicit
                                class="hidden pt-1 pr-1 pb-1 pl-1 uppercase text-xs font-bold border rounded-md cursor-default"
                                style="color: var(--am-bg, #A660B2); background-color: var(--am-text-1, #ffffff); border-color: var(--am-text-1, #ffffff);"
                            >{{ __('E') }}</span>
                        </div>
                    </div>

                    <a class="flex items-center gap-1" href="{{ route('home') }}">
                        <x-logo class="block h-5 w-auto" style="color: var(--am-text-2, inherit);" />
                        <p class="font-semibold" style="color: var(--am-text-2, inherit);">{{ __('Music') }}</p>
                    </a>
                </div>

                <div class="flex items-center gap-2">
                    @if (!empty($song->am_id))
                        <button
                            type="button"
                            data-music-play
                            title="{{ __('Play') }}"
                            class="inline-flex items-center pt-2 pr-2 pb-2 pl-2 border border-transparent rounded-full font-semibold text-xs uppercase tracking-widest shadow-md hover:bg-tint-800 hover:btn-text-tinted active:bg-tint active:btn-text-tinted focus:outline-none transition ease-in-out duration-150"
                            style="color: var(--am-bg, #A660B2); background-color: var(--am-text-4, #ffffff);"
                        >
                            <span data-music-icon="play">@svg('play_fill', 'fill-current', ['width' => '24'])</span>
                            <span data-music-icon="pause" class="hidden">@svg('pause_fill', 'fill-current', ['width' => '24'])</span>
                        </button>
                    @endif

                    <input
                        class="w-full"
                        type="range"
                        min="0"
                        max="30"
                        step="0.000001"
                        value="0"
                        data-music-seek
                        style="accent-color: var(--am-text-4, #ffffff);"
                    />

                    <div class="text-xs" style="width: 42px; color: var(--am-text-4, inherit);" data-music-duration>00:30</div>
                </div>
            </div>
        </div>
    </div>
</main>
