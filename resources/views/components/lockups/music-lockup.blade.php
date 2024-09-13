@props(['song', 'anime' => null, 'type' => null, 'position' => null, 'episodes' => null, 'showEpisodes' => true, 'showModel' => false, 'rank', 'isRanked' => false, 'isRow' => true])

@php
    $class = $isRow ? 'shrink-0' : 'flex-grow';
@endphp

<div
    wire:key="{{ uniqid(more_entropy: true) }}"
    class="relative pb-2 w-64 snap-normal snap-center {{ $class }}"
    x-data="{
        song: null,
        musicManager: null,
        bgColor: '#A660B2',
        songTitle: '{{ str($song->original_title)->replace('\'', '’') }}',
        artistName: '{{ str($song->artist ?? 'Unknown')->replace('\'', '’') }}',
        artworkURL: '{{ $song->getFirstMediaFullUrl(\App\Enums\MediaCollection::Artwork()) ?? asset('images/static/placeholders/music_album.webp') }}',
        async fetchSongData(songID) {
            if (!!songID) {
                this.song = await musicManager.fetchSong(songID)
                this.musicManager = window.musicManager
                this.bgColor = '#' + this.song.attributes.artwork.bgColor
                this.songTitle = this.song.attributes.name
                this.artistName = this.song.attributes.artistName
                this.artworkURL = musicManager.getArtworkURL(this.song, 500, 500)
            }
        }
    }"
    x-on:musicmanagerloaded.window="await fetchSongData('{{ $song->am_id }}')"
    x-init="window.musicManager !== undefined ? await fetchSongData('{{ $song->am_id }}') : null"
>
    <div class="flex flex-col">
        <x-picture class="aspect-square rounded-lg shadow-md overflow-hidden">
            <img class="w-full h-full object-cover"
                 width="320" height="320"
                 src="{{ $song->getFirstMediaFullUrl(\App\Enums\MediaCollection::Artwork()) ?? asset('images/static/placeholders/music_album.webp') }}"
                 x-bind:title="songTitle"
                 x-bind:alt="songTitle + ' Artwork'"
                 x-bind:src="artworkURL"
                 x-bind:style="{'background-color': bgColor}"
            >

            <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>

            <div class="absolute top-0 bottom-0 left-0 right-0">
                <div class="flex flex-col justify-center items-center h-full">
                    @if (!empty($song->am_id))
                        <button
                            class="inline-flex items-center pt-5 pr-5 pb-5 pl-5 bg-white/60 backdrop-blur border border-transparent rounded-full font-semibold text-xs text-gray-500 uppercase tracking-widest shadow-md hover:opacity-75 active:opacity-50 focus:outline-none disabled:bg-gray-100 disabled:text-gray-300 disabled:cursor-default disabled:opacity-100 transition ease-in-out duration-150"
                            x-on:click="await musicManager?.playSong(song)"
                        >
                            <template x-if="musicManager?.isPlaying && musicManager?.currentMusicID === '{{ $song->am_id }}'">
                                @svg('pause_fill', 'fill-current', ['width' => '24'])
                            </template>

                            <template x-if="!(musicManager?.isPlaying && musicManager?.currentMusicID === '{{ $song->am_id }}')">
                                @svg('play_fill', 'fill-current', ['width' => '24'])
                            </template>
                        </button>
                    @endif
                </div>
            </div>
        </x-picture>
    </div>

    <div class="relative flex flex-grow mt-2">
        <div class="flex flex-col w-full gap-2 justify-between">
            <div>
            @if ($isRanked)
                <p class="text-sm leading-tight font-semibold">#{{ $rank }}</p>
            @endif

            <a class="flex gap-2 justify-between" href="{{ route('songs.details', $song) }}" wire:navigate>
                <p class="line-clamp-2" x-text="songTitle">{{ $song->original_title }}</p>

                @if ($type && !$showModel || $type && $anime)
                    <span class="ml-1 pl-2 pr-2 pt-1 pb-1 h-full {{ $type->color() }} text-white text-xs font-semibold whitespace-nowrap rounded-full">{{ $type->abbreviated() . ' #' . $position }}</span>
                @endif
            </a>

            <p class="opacity-75 line-clamp-2" x-text="artistName">{{ $song->artist }}</p>

            @if ($anime && $showModel)
                <x-simple-link class="text-sm" href="{{ route('anime.details', $anime) }}" wire:navigate>{{ $anime->title }}</x-simple-link>
            @endif

            @if (!empty($episodes) && $showEpisodes)
                <p class="text-sm opacity-75 line-clamp-2">{{ __('Episodes: :x', ['x' => $episodes]) }}</p>
            @endif
            </div>
        </div>
    </div>
</div>
