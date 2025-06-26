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
        <script src="{{ url(mix('js/listen.js')) }}"></script>
    </x-slot:scripts>

    <div
        class="flex flex-col gap-4"
        x-data="{
            song: null,
            musicManager: null,
            async fetchSongData(songID) {
                if (!!songID) {
                    this.song = await musicManager.fetchSong(songID)
                    this.musicManager = window.musicManager
                    window.song = this.song
                }
            }
        }"
        x-on:musicmanagerloaded.window="await fetchSongData('{{ $song->am_id }}')"
    >
        <template x-if="song">
            <div
                class="flex gap-4 pt-4 pr-5 pb-5 pl-5 rounded-lg"
                x-bind:style="{'background-color': '#' + song.attributes.artwork.bgColor}"
            >
                <div>
                    <x-picture
                        class="aspect-square rounded-lg natural-shadow overflow-hidden"
                        style="height: calc(100vh - 2.50rem);max-height: 200px;min-height: 164px;"
                        x-on:click="await musicManager.playSong(song)"
                    >
                        <img class="object-cover"
                             alt="{{ $song->original_title }} Artwork" title="{{ $song->original_title }}"
                             width="500" height="500"
                             x-bind:src="musicManager.getArtworkURL(song, 500, 500)"
                             x-bind:style="{'background-color': '#' + song.attributes.artwork.bgColor}"
                        >

                        <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg cursor-pointer"></div>
                    </x-picture>
                </div>

                <div class="flex flex-col gap-4 justify-between w-full">
                    <div class="flex gap-4 justify-between items-start">
                        <div class="flex flex-col gap-4">
                            <div class="flex flex-col gap-1">
                                <p
                                    class="leading-tight line-clamp-2 font-bold"
                                    x-bind:style="{color: '#' + song.attributes.artwork.textColor1}"
                                    x-text="song.attributes.name"
                                >{{ $song->original_title }}</p>
                                <p
                                    class="text-sm leading-tight opacity-75 line-clamp-2"
                                    x-bind:style="{color: '#' + song.attributes.artwork.textColor2}"
                                    x-text="song.attributes.artistName"
                                >{{ $song->artist ?? 'Unknown' }}</p>
                                <p
                                    class="text-sm leading-tight opacity-75 line-clamp-2"
                                    x-bind:style="{color: '#' + song.attributes.artwork.textColor3}"
                                    x-text="song.attributes.albumName"
                                ></p>
                            </div>

                            <div
                                class="flex gap-2"
                                x-show="song !== null"
                            >
                                <span
                                    class="pt-1 pr-1 pb-1 pl-1 uppercase text-xs font-bold border rounded-md cursor-default"
                                    x-bind:style="{color: '#' + song.attributes.artwork.bgColor, 'background-color': '#' + song.attributes.artwork.textColor1, 'border-color': '#' + song.attributes.artwork.textColor1}"
                                >{{ __('Preview') }}</span>

                                <template x-if="song.attributes.isExplicit">
                                    <span
                                        class="pt-1 pr-1 pb-1 pl-1 uppercase text-xs font-bold border rounded-md cursor-default"
                                        x-bind:style="{color: '#' + song.attributes.artwork.bgColor, 'background-color': '#' + song.attributes.artwork.textColor1, 'border-color': '#' + song.attributes.artwork.textColor1}"
                                    >E</span>
                                </template>
                            </div>
                        </div>

                        <a class="flex items-center gap-1" href="{{ route('home') }}">
                            <x-logo
                                class="block h-5 w-auto"
                                x-bind:style="{color: '#' + song.attributes.artwork.textColor2}"
                            />
                            <p
                                class="font-semibold"
                                x-bind:style="{color: '#' + song.attributes.artwork.textColor2}"
                            >{{ __('Music') }}</p>
                        </a>
                    </div>

                    <div class="flex items-center gap-2">
                        @if (!empty($song->am_id))
                            <button
                                class="inline-flex items-center pt-2 pr-2 pb-2 pl-2 border border-transparent rounded-full font-semibold text-xs uppercase tracking-widest shadow-md hover:bg-tint-800 hover:btn-text-tinted active:bg-tint active:btn-text-tinted focus:outline-none disabled:bg-gray-100 disabled:text-gray-300 disabled:cursor-default disabled:opacity-100 transition ease-in-out duration-150"
                                x-on:click="await musicManager.playSong(song)"
                                x-bind:style="{color: '#' + song.attributes.artwork.bgColor, 'background-color': '#' + song.attributes.artwork.textColor4}"
                            >
                                <template x-if="musicManager.isPlaying && musicManager.currentMusicID === '{{ $song->am_id }}'">
                                    @svg('pause_fill', 'fill-current', ['width' => '24'])
                                </template>

                                <template x-if="!(musicManager.isPlaying && musicManager.currentMusicID === '{{ $song->am_id }}')">
                                    @svg('play_fill', 'fill-current', ['width' => '24'])
                                </template>
                            </button>
                        @endif

                        <input
                            class="w-full"
                            type="range"
                            min="0"
                            max="30"
                            step="0.000001"
                            x-bind:value="musicManager.progress"
                            onchange="musicManager.seekTo(this.value)"
                            x-bind:style="{'accent-color': '#' + song.attributes.artwork.textColor4 }"
                        />

                        <div
                            class="text-xs"
                            style="width: 42px;"
                            x-bind:style="{color: '#' + song.attributes.artwork.textColor4}"
                            x-text="musicManager.currentPlaybackDuration"
                        >00:30</div>
                    </div>
                </div>
            </div>
        </template>

        <template x-if="!song">
            <div class="flex justify-center items-center w-screen h-screen bg-secondary">
                @svg('music_note_fill', 'current-fill opacity-25', ['width' => '128'])
            </div>
        </template>
    </div>
</main>
