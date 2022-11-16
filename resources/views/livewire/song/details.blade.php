<main>
    <x-slot:title>
        {!! $song->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Listen to anime songs for free.') }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ $song->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('app.description') }}" />
        <meta property="og:image" content="{{ $song->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? asset('images/static/placeholders/song_banner.webp') }}" />
        <meta property="og:type" content="music.song" />
        <meta property="og:url" content="{{ route('embed.songs', $song) }}">
        <meta property="twitter:title" content="{{ $song->title }} — {{ config('app.name') }}" />

        <link rel="canonical" href="{{ route('songs.details', $song) }}">
        <link rel="alternate" type="application/json+oembed" href="{{ route('oembed', ['format' => 'json', 'url' => route('songs.details', $song)]) }}">
        <link rel="alternate" type="application/json+oembed" href="{{ route('oembed', ['format' => 'xml', 'url' => route('songs.details', $song)]) }}">
    </x-slot:meta>

    <x-slot:styles>
    </x-slot:styles>

    <x-slot:appArgument>
        songs/{{ $song->id }}
    </x-slot:appArgument>

    <div
        class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6"
        x-data="{
            song: null,
            bgColor: '#A660B2',
            artworkURL: '{{ asset('images/static/placeholders/music_album.webp') }}',
            songURL: null,
            musicManager: null,
            async fetchSongData(songID) {
                if (!!songID) {
                    this.song = await musicManager.fetchSong(songID)
                    this.musicManager = window.musicManager
                    window.song = this.song
                    this.bgColor = '#' + this.song.attributes.artwork.bgColor
                    this.artworkURL = musicManager.getArtworkURL(song)
                    this.songURL = this.song.attributes.url
                }
            }
        }"
        x-on:musicmanagerloaded.window="await fetchSongData('{{ $song->am_id }}')"
    >
        <section class="flex flex-col items-center gap-4 pb-8">
            <div style="max-width: 300px">
                <x-picture class="aspect-square rounded-lg shadow-md overflow-hidden">
                    <img class="w-full h-full object-cover"
                         alt="{{ $song->title }} Banner" title="{{ $song->title }}"
                         width="320" height="320"
                         x-bind:src="artworkURL"
                         x-bind:style="{'background-color': bgColor}"
                    >

                    <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
                </x-picture>
            </div>

            <div class="flex flex-col items-center">
                <p class="font-semibold">{{ $song->title }}</p>
                <p>{{ $song->artist }}</p>
            </div>

            <div class="flex items-center gap-2">
                <template x-if="song">
                    <div>
                        <template x-if="musicManager.isPlaying && musicManager.currentMusicID === '{{ $song->am_id }}'">
                            <x-tinted-pill-button
                                title="{{ __('Pause :x by :y', ['x' => $song->title, 'y' => $song->artist]) }}"
                                x-on:click="await musicManager.playSong(song)"
                            >
                                @svg('pause_fill', 'fill-current', ['width' => 14])
                                {{ __('Stop') }}
                            </x-tinted-pill-button>
                        </template>

                        <template x-if="!(musicManager.isPlaying && musicManager.currentMusicID === '{{ $song->am_id }}')">
                            <x-tinted-pill-button
                                :color="'orange'"
                                title="{{ __('Preview :x by :y', ['x' => $song->title, 'y' => $song->artist]) }}"
                                x-on:click="await musicManager.playSong(song)"
                            >
                                @svg('play_fill', 'fill-current', ['width' => 14])
                                {{ __('Preview') }}
                            </x-tinted-pill-button>
                        </template>
                    </div>
                </template>

                <x-dropdown align="right" width="48">
                    <x-slot:trigger>
                        <x-circle-button
                            title="{{ __('More Settings') }}"
                        >
                            @svg('ellipsis', 'fill-current', ['width' => '28'])
                        </x-circle-button>
                    </x-slot:trigger>

                    <x-slot:content>
                        <button
                            class="block w-full pl-4 pr-4 pt-2 pb-2 bg-white text-gray-400 text-xs text-center font-semibold hover:bg-gray-50 focus:bg-gray-200"
                            wire:click="$toggle('showSharePopup')"
                        >
                            {{ __('Share') }}
                        </button>
                        @if ($song->am_id)
                            <a
                                class="block w-full pl-4 pr-4 pt-2 pb-2 bg-white text-gray-400 text-xs text-center font-semibold hover:bg-gray-50 focus:bg-gray-200"
                                x-bind:href="songURL"
                                target="_blank"
                                x-show="songURL"
                            >
                                {{ __('View on Apple Music') }}
                            </a>
                        @endif
                    </x-slot:content>
                </x-dropdown>
            </div>
        </section>

        @if ($this->anime?->count())
            <section class="pt-5 pb-8 pl-4 pr-4 border-t-2">
                <x-section-nav>
                    <x-slot:title>
                        {{ __('As Heard On') }}
                    </x-slot:title>
                </x-section-nav>

                <x-rows.small-lockup :animes="$this->anime" :is-row="false" />
            </section>
        @endif
    </div>

    <x-share-modal
        model="showSharePopup"
        :link="route('songs.details', $this->song)"
        :embed-link="route('embed.songs', $this->song)"
        :title="$this->song->title"
        :type="'song'"
    />
</main>
