@props(['animeSong', 'showEpisodes' => true, 'showAnime' => false, 'onMusicKitLoad' => false, 'isRow' => true])

@php
    /** @var \App\Models\AnimeSong $animeSong */
@endphp

<div
    class="relative pb-2 {{ $isRow ? 'w-64' : '' }}"
    x-data="{
        song: null,
        bgColor: '#A660B2',
        artworkURL: '{{ asset('images/static/placeholders/music_album.webp') }}',
        async fetchSongData(songID) {
            if (!!songID) {
                this.song = await MusicKit.getInstance().api.song(songID);
                this.artworkURL = MusicKit.formatArtworkURL(this.song.attributes.artwork, 200, 200);
                this.bgColor = '#'+this.song.attributes.artwork.bgColor;
            }
        }
    }"
    @if($onMusicKitLoad)
        x-on:musickitloaded.window="await fetchSongData('{{ $animeSong->song->am_id }}')"
    @else
        x-init="await fetchSongData('{{ $animeSong->song->am_id }}')"
    @endif
>
    <div class="flex flex-col">
        <x-picture class="aspect-square rounded-lg shadow-md overflow-hidden">
            <img class="w-full h-full object-cover"
                 alt="{{ $animeSong->song->title }} Banner" title="{{ $animeSong->song->title }}"
                 width="320" height="320"
                 x-bind:src="artworkURL"
                 x-bind:style="{'background-color': bgColor}"
            >

            <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>

            <div class="absolute top-0 bottom-0 left-0 right-0">
                <div class="flex flex-col justify-center items-center h-full">
                    @if(!empty($animeSong->song->am_id))
                        <button
                            class="inline-flex items-center p-5 bg-white/60 backdrop-blur border border-transparent rounded-full font-semibold text-xs text-gray-500 uppercase tracking-widest shadow-md hover:opacity-75 active:opacity-50 focus:outline-none disabled:bg-gray-100 disabled:text-gray-300 disabled:cursor-default disabled:opacity-100 transition ease-in-out duration-150"
                            x-on:click="await playSong(song)"
                        >
                            <template x-if="musicIsPlaying && currentMusicID === '{{ $animeSong->song->am_id }}'">
                                @svg('pause_fill', 'fill-current', ['width' => '24'])
                            </template>

                            <template x-if="!(musicIsPlaying && currentMusicID === '{{ $animeSong->song->am_id }}')">
                                @svg('play_fill', 'fill-current', ['width' => '24'])
                            </template>
                        </button>
                    @endif
                </div>
            </div>
        </x-picture>
    </div>

    <div class="relative mt-4">
        <div class="flex flex-col gap-1 justify-between">
            <span class="flex gap-2 justify-between">
                <p class="leading-tight line-clamp-2">{{ $animeSong->song->title }}</p>
                <span class="ml-1 px-2 py-1 h-full {{ $animeSong->type->color() }} text-white text-xs font-semibold whitespace-nowrap rounded-full">{{ $animeSong->type->abbreviated() . ' #' . $animeSong->position }}</span>
            </span>

            <p class="text-xs leading-tight text-black/60 line-clamp-2">{{ $animeSong->song->artist ?? 'Unknown' }}</p>
            @if($showAnime)
                <x-simple-link class="text-xs" href="{{ route('anime.details', $animeSong->anime) }}">{{ $animeSong->anime->title }}</x-simple-link>
            @endif
            @if(!empty($animeSong->episodes) && $showEpisodes)
                <p class="text-xs leading-tight text-black/60 line-clamp-2">{{ __('Episodes: :x', ['x' => $animeSong->episodes]) }}</p>
            @endif
        </div>
    </div>
</div>
