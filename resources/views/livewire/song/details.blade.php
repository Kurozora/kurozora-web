<main>
    <x-slot:title>
        {!! $song->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Listen to :x for free. Only on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x' => $song->title]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ $song->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Listen to :x songs for free. Only on Kurozora, the largest, free online anime, manga, game & music database in the world.', ['x' => $song->title]) }}" />
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
            songTitle: '{{ str($song->title)->replace('\'', '’') }}',
            artworkURL: '{{ $song->getFirstMediaFullUrl(\App\Enums\MediaCollection::Artwork()) ?? asset('images/static/placeholders/music_album.webp') }}',
            songURL: null,
            musicManager: null,
            async fetchSongData(songID) {
                if (!!songID) {
                    this.song = await musicManager.fetchSong(songID)
                    this.musicManager = window.musicManager
                    window.song = this.song
                    this.bgColor = '#' + this.song.attributes.artwork.bgColor
                    this.songTitle = this.song.attributes.name
                    this.artworkURL = musicManager.getArtworkURL(song, 320, 320)
                    this.songURL = this.song.attributes.url
                }
            }
        }"
        x-on:musicmanagerloaded.window="await fetchSongData('{{ $song->am_id }}')"
    >
        <section class="flex flex-col items-center gap-4 pb-8">
            <div style="max-width: 320px">
                <x-picture class="aspect-square rounded-lg shadow-md overflow-hidden">
                    <img class="w-full h-full object-cover"
                         alt="{{ $song->title }} Artwork" title="{{ $song->title }}"
                         width="320" height="320"
                         x-bind:title="songTitle"
                         x-bind:alt="songTitle + ' Artwork'"
                         x-bind:src="artworkURL"
                         x-bind:style="{'background-color': bgColor}"
                         style="width: 320px; height: 320px;"
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

                <x-nova-link :resource="\App\Nova\Song::class" :model="$song">
                    @svg('pencil', 'fill-current', ['width' => '44'])
                </x-nova-link>

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
                        @if ($song->amazon_id)
                            <a
                                class="block w-full pl-4 pr-4 pt-2 pb-2 bg-white text-gray-400 text-xs text-center font-semibold hover:bg-gray-50 focus:bg-gray-200"
                                href="{{ config('services.amazon.music.albums') . $song->amazon_id }}"
                                target="_blank"
                            >
                                {{ __('View on Amazon Music') }}
                            </a>
                        @endif
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
                        @if ($song->deezer_id)
                            <a
                                class="block w-full pl-4 pr-4 pt-2 pb-2 bg-white text-gray-400 text-xs text-center font-semibold hover:bg-gray-50 focus:bg-gray-200"
                                href="{{ config('services.deezer.track') . $song->deezer_id }}"
                                target="_blank"
                            >
                                {{ __('View on Deezer') }}
                            </a>
                        @endif
                        @if ($song->spotify_id)
                            <a
                                class="block w-full pl-4 pr-4 pt-2 pb-2 bg-white text-gray-400 text-xs text-center font-semibold hover:bg-gray-50 focus:bg-gray-200"
                                href="{{ config('services.spotify.track') . $song->spotify_id }}"
                                target="_blank"
                            >
                                {{ __('View on Spotify') }}
                            </a>
                        @endif
                        @if ($song->youtube_id)
                            <a
                                class="block w-full pl-4 pr-4 pt-2 pb-2 bg-white text-gray-400 text-xs text-center font-semibold hover:bg-gray-50 focus:bg-gray-200"
                                href="{{ config('services.youtube.music.watch') . $song->youtube_id }}"
                                target="_blank"
                            >
                                {{ __('View on Youtube Music') }}
                            </a>
                        @endif
                    </x-slot:content>
                </x-dropdown>
            </div>
        </section>

        <section id="ratingsAndReviews" class="pt-5 pb-8 pl-4 pr-4 border-t-2">
            <x-section-nav>
                <x-slot:title>
                    {{ __('Ratings & Reviews') }}
                </x-slot:title>

                <x-slot:action>
                    <x-section-nav-link class="whitespace-nowrap" href="{{ route('songs.reviews', $song) }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot:action>
            </x-section-nav>

            <div class="flex flex-row flex-wrap justify-between gap-4">
                <div class="flex flex-col justify-end text-center">
                    <p class="font-bold text-6xl">{{ number_format($song->mediaStat->rating_average, 1) }}</p>
                    <p class="font-bold text-sm text-gray-500">{{ __('out of') }} 5</p>
                </div>

                <div class="flex flex-col justify-end items-center text-center">
                    @svg('star_fill', 'fill-current', ['width' => 32])
                    <p class="font-bold text-2xl">{{ number_format($song->mediaStat->highestRatingPercentage) }}%</p>
                    <p class="text-sm text-gray-500">{{ $song->mediaStat->sentiment }}</p>
                </div>

                <div class="flex flex-col w-full justify-end text-right sm:w-auto">
                    <x-star-rating-bar :media-stat="$song->mediaStat" />

                    <p class="text-sm text-gray-500">{{ trans_choice('[0,1] Not enough ratings|[2,*] :x Ratings', $song->mediaStat->rating_count, ['x' => number_format($song->mediaStat->rating_count)]) }}</p>
                </div>
            </div>
        </section>

        <section id="writeAReview" class="pt-5 pb-8 pl-4 pr-4 border-t-2">
            <div class="flex flex-row flex-wrap gap-4">
                <div class="flex justify-between items-center">
                    <p class="">{{ __('Click to Rate:') }}</p>

                    <livewire:components.star-rating :model-id="$song->id" :model-type="$song->getMorphClass()" :rating="$userRating->first()?->rating" :star-size="'md'" />
                </div>

                <div class="flex justify-between">
                    <x-simple-button class="flex gap-1" wire:click="$emit('show-review-box', '{{  $this->reviewBoxID }}')">
                        @svg('pencil', 'fill-current', ['width' => 18])
                        {{ __('Write a Review') }}
                    </x-simple-button>
                </div>

                <div></div>
            </div>

            <div class="mt-5">
                <livewire:sections.reviews :model="$song" />
            </div>
        </section>

        <livewire:components.song.media-section :song="$song" :type="\App\Models\Anime::class" />

        <livewire:components.song.media-section :song="$song" :type="\App\Models\Game::class" />
    </div>

    <x-share-modal
        model="showSharePopup"
        :link="route('songs.details', $this->song)"
        :embed-link="route('embed.songs', $this->song)"
        :title="$this->song->title"
        :type="'song'"
    />

    <livewire:components.review-box :review-box-id="$reviewBoxID" :model-id="$song->id" :model-type="$song->getMorphClass()" :user-rating="$userRating?->first()" />
</main>
