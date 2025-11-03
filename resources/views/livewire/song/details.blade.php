<main>
    <x-slot:title>
        {!! $song->original_title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Listen to :x for free. Only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $song->original_title, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ $song->original_title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Listen to :x songs for free. Only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $song->original_title, 'y' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ $song->getFirstMediaFullUrl(\App\Enums\MediaCollection::Artwork()) ?? asset('images/static/placeholders/song_banner.webp') }}" />
        <meta property="og:type" content="music.song" />
        <meta property="og:url" content="{{ route('embed.songs', $song) }}">
        <meta property="twitter:title" content="{{ $song->original_title }} — {{ config('app.name') }}" />

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
        x-data="{
            song: null,
            bgColor: '#A660B2',
            songTitle: '{{ str($song->original_title)->replace('\'', '’') }}',
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
                    this.artworkURL = musicManager.getArtworkURL(song, 500, 500)
                    this.songURL = this.song.attributes.url
                }
            }
        }"
        x-on:musicmanagerloaded.window="await fetchSongData('{{ $song->am_id }}')"
    >
        <div class="pt-4 pb-6">
            <section class="flex flex-col items-center gap-4 pb-8 pl-4 pr-4">
                <div style="max-width: 320px">
                    <x-picture class="aspect-square rounded-lg natural-shadow overflow-hidden">
                        <img class="w-full h-full object-cover"
                             alt="{{ $song->original_title }} Artwork" title="{{ $song->original_title }}"
                             width="500" height="500"
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
                    <p class="font-semibold">{{ $song->original_title }}</p>
                    <p>{{ $song->artist }}</p>
                </div>

                <div class="flex items-center gap-2">
                    <template x-if="song">
                        <div>
                            <template x-if="musicManager.isPlaying && musicManager.currentMusicID === '{{ $song->am_id }}'">
                                <x-tinted-pill-button
                                    title="{{ __('Pause :x by :y', ['x' => $song->original_title, 'y' => $song->artist]) }}"
                                    x-on:click="await musicManager.playSong(song)"
                                >
                                    @svg('pause_fill', 'fill-current', ['width' => '14'])
                                    {{ __('Stop') }}
                                </x-tinted-pill-button>
                            </template>

                            <template x-if="!(musicManager.isPlaying && musicManager.currentMusicID === '{{ $song->am_id }}')">
                                <x-tinted-pill-button
                                    :color="'orange'"
                                    title="{{ __('Preview :x by :y', ['x' => $song->original_title, 'y' => $song->artist]) }}"
                                    x-on:click="await musicManager.playSong(song)"
                                >
                                    @svg('play_fill', 'fill-current', ['width' => '14'])
                                    {{ __('Preview') }}
                                </x-tinted-pill-button>
                            </template>
                        </div>
                    </template>

                    <x-nova-link :href="route('songs.edit', $song)">
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
                                class="block w-full pl-4 pr-4 pt-2 pb-2 text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary"
                                wire:click="$toggle('showSharePopup')"
                            >
                                {{ __('Share') }}
                            </button>
                            @if ($song->amazon_id)
                                <a
                                    class="block w-full pl-4 pr-4 pt-2 pb-2 text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary"
                                    href="{{ config('services.amazon.music.albums') . $song->amazon_id }}"
                                    target="_blank"
                                >
                                    {{ __('View on Amazon Music') }}
                                </a>
                            @endif
                            @if ($song->am_id)
                                <a
                                    class="block w-full pl-4 pr-4 pt-2 pb-2 text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary"
                                    x-bind:href="songURL"
                                    target="_blank"
                                    x-show="songURL"
                                >
                                    {{ __('View on Apple Music') }}
                                </a>
                            @endif
                            @if ($song->deezer_id)
                                <a
                                    class="block w-full pl-4 pr-4 pt-2 pb-2 text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary"
                                    href="{{ config('services.deezer.track') . $song->deezer_id }}"
                                    target="_blank"
                                >
                                    {{ __('View on Deezer') }}
                                </a>
                            @endif
                            @if ($song->spotify_id)
                                <a
                                    class="block w-full pl-4 pr-4 pt-2 pb-2 text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary"
                                    href="{{ config('services.spotify.track') . $song->spotify_id }}"
                                    target="_blank"
                                >
                                    {{ __('View on Spotify') }}
                                </a>
                            @endif
                            @if ($song->youtube_id)
                                <a
                                    class="block w-full pl-4 pr-4 pt-2 pb-2 text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary"
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

            @if (!empty($song->original_lyrics))
                <section class="pb-8">
                    <x-section-nav class="flex-nowrap pt-4">
                        <x-slot:title>
                            {{ __('Lyrics') }}
                        </x-slot:title>

{{--                        <x-slot:action>--}}
{{--                            <x-select>--}}
{{--                                <option value="1">English</option>--}}
{{--                            </x-select>--}}
{{--                        </x-slot:action>--}}
                    </x-section-nav>

                    <div
                        class="relative pl-2 pr-2 overflow-x-scroll no-scrollbar"
                        x-data="{
                            columns: [
                                { key: 'showJa', label: '{{ __('Japanese') }}' },
                                { key: 'showRomanized', label: '{{ __('Romanized') }}' },
                                { key: 'showUser', label: '{{ __($song->getTranslation(app()->getLocale())?->language->name ?? __('English')) }}' }
                            ],
                            showJa: true,
                            showRomanized: true,
                            showUser: true,
                            enabledCount() {
                                return [this.showJa, this.showRomanized, this.showUser].filter(Boolean).length
                            },
                            toggle(column) {
                                if (this.enabledCount() > 1) {
                                    this[column] = !this[column]
                                } else {
                                    this[column] = true
                                }
                            },
                            isToggled(column) {
                                return this[column]
                            },
                            isCollapsed: true,
                            headHeight: 0,
                            rowHeight: 0,
                            visibleRows: 5,
                            initCollapse() {
                                // Estimate row height from first row
                                let headRow = this.$refs.content.querySelector('thead > tr')
                                let bodyRow = this.$refs.content.querySelector('tbody > tr')

                                if (headRow) {
                                    this.headHeight = headRow.offsetHeight || 0
                                    this.headHeight -= 8
                                }
                                if (bodyRow) {
                                    this.rowHeight = bodyRow.offsetHeight || 0
                                }
                            },
                            handleExpand() {
                                this.isCollapsed = false
                            }
                        }"
                    >
                        {{-- Toggle Buttons --}}
                        <div class="flex gap-2 mb-5 pl-2 pr-2">
                            <template x-for="col in columns" :key="col.key">
                                <span>
                                    <template x-if="isToggled(col.key)">
                                        <x-button
                                            x-on:click="toggle(col.key)"
                                            x-bind:disabled="enabledCount() === 1 && isToggled(col.key)"
                                            x-text="col.label"
                                        ></x-button>
                                    </template>

                                    <template x-if="!isToggled(col.key)">
                                        <x-outlined-button
                                            x-on:click="toggle(col.key)"
                                            x-text="col.label"
                                        ></x-outlined-button>
                                    </template>
                                </span>
                            </template>
                        </div>

                        {{-- Lyrics Table --}}
                        <div
                            x-init="initCollapse()"
                            x-ref="content"
                            :style="isCollapsed && rowHeight
                                ? `max-height: ${headHeight + rowHeight * visibleRows}px; overflow: hidden; position: relative; mask: linear-gradient(0deg, rgba(0, 0, 0, 0) 0px, rgba(0, 0, 0, 0) 22px, rgb(0, 0, 0) 22px), linear-gradient(270deg, rgba(0, 0, 0, 0) 0px, rgba(0, 0, 0, 0) 40px, rgb(0, 0, 0) 70px);` : ''"
                        >
                            <table class="whitespace-nowrap">
                                <thead class="text-xs uppercase">
                                    <tr>
                                        <th x-show="showJa"
                                            scope="col"
                                            :class="{
                                                'pl-2 text-left border-e border-primary': showRomanized || showUser,
                                                'pl-2 text-left': !showRomanized && !showUser
                                            }">
                                            {{ __('Japanese') }}
                                        </th>

                                        <th x-show="showRomanized"
                                            scope="col"
                                            :class="{
                                                'pl-2 pr-2 text-left border-e border-primary': showUser,
                                                'pl-2 pr-2 text-left': !showUser
                                            }">
                                            {{ __('Romanized') }}
                                        </th>

                                        <th x-show="showUser"
                                            scope="col"
                                            class="pl-2 pr-2 text-left">
                                            {{ $song->getTranslation(app()->getLocale())?->language->name ?? __('English') }}
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @php
                                        $jaLines = preg_split('/\r\n|\r|\n/', $song->getTranslation('ja')?->lyrics ?? '');
                                        $romajiLines = preg_split('/\r\n|\r|\n/', $song->original_lyrics ?? '');
                                        $userLines = preg_split('/\r\n|\r|\n/', $song->getTranslation(app()->getLocale())?->lyrics
                                            ?? $song->getTranslation('en')?->lyrics
                                            ?? ''
                                        );

                                        $max = max(count($jaLines), count($romajiLines), count($userLines));
                                        $jaLines = array_pad($jaLines, $max, '');
                                        $romajiLines = array_pad($romajiLines, $max, '');
                                        $userLines = array_pad($userLines, $max, '');
                                    @endphp

                                    @foreach(range(0, $max-1) as $i)
                                        <tr
                                            :class="isCollapsed ? '' : 'hover:bg-tertiary'"
                                        >
                                            <td x-show="showJa"
                                                :class="{
                                                    'pt-2 pb-2 pl-2 pr-8': showRomanized || showUser,
                                                    'pt-2 pb-2 pl-2 pr-2': !showRomanized && !showUser
                                                }">
                                                {{ $jaLines[$i] === '' ? "\u{00A0}" : $jaLines[$i] }}
                                            </td>

                                            <td x-show="showRomanized"
                                                :class="{
                                                    'pt-2 pb-2 pl-2 pr-8': showUser,
                                                    'pt-2 pb-2 pl-2 pr-2': !showJa || !showUser
                                                }">
                                                {{ $romajiLines[$i] === '' ? "\u{00A0}" : $romajiLines[$i] }}
                                            </td>

                                            <td x-show="showUser"
                                                class="pt-2 pb-2 pl-2 pr-2"
                                            >
                                                {{ $userLines[$i] === '' ? "\u{00A0}" : $userLines[$i] }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <x-simple-button
                            @click="handleExpand()"
                            x-show="isCollapsed"
                            x-text="'{{ __('more') }}'"
                            class="absolute bottom-0 right-0 mr-4 text-base tracking-normal leading-snug"
                        />
                    </div>
                </section>
            @endif

            <section id="ratingsAndReviews" class="pb-8">
                <x-section-nav class="pt-4">
                    <x-slot:title>
                        {{ __('Ratings & Reviews') }}
                    </x-slot:title>

                    <x-slot:action>
                        <x-section-nav-link href="{{ route('songs.reviews', $song) }}">{{ __('See All') }}</x-section-nav-link>
                    </x-slot:action>
                </x-section-nav>

                <div class="flex flex-row flex-wrap justify-between gap-4 pl-4 pr-4">
                    <div class="flex flex-col justify-end text-center">
                        <p class="font-bold text-6xl">{{ number_format($song->mediaStat->rating_average, 1) }}</p>
                        <p class="font-bold text-sm text-secondary">{{ __('out of') }} 5</p>
                    </div>

                    <div class="flex flex-col justify-end items-center text-center">
                        @svg('star_fill', 'fill-current', ['width' => 32])
                        <p class="font-bold text-2xl">{{ number_format($song->mediaStat->highestRatingPercentage) }}%</p>
                        <p class="text-sm text-secondary">{{ $song->mediaStat->sentiment }}</p>
                    </div>

                    <div class="flex flex-col w-full justify-end text-right sm:w-auto">
                        <x-star-rating-bar :media-stat="$song->mediaStat" />

                        <p class="text-sm text-secondary">{{ trans_choice('[0,1] Not enough ratings|[2,*] :x Ratings', $song->mediaStat->rating_count, ['x' => number_format($song->mediaStat->rating_count)]) }}</p>
                    </div>
                </div>
            </section>

            <section id="writeAReview" class="pb-8">
                <x-hr class="ml-4 mr-4 pb-5" />

                <div class="flex flex-row flex-wrap gap-4 pl-4 pr-4">
                    <div class="flex justify-between items-center">
                        <p class="">{{ __('Click to Rate:') }}</p>

                        <livewire:components.star-rating :model-id="$song->id" :model-type="$song->getMorphClass()" :rating="$userRating->first()?->rating" :star-size="'md'" />
                    </div>

                    <div class="flex justify-between">
                        <x-simple-button class="flex gap-1" wire:click="$dispatch('show-review-box', { 'id': '{{ $this->reviewBoxID }}' })">
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

        <div class="bg-tinted">
            @if (!empty($song->copyright))
                <section class="pt-4 pb-4 pl-4 pr-4">
                    <p class="text-sm text-secondary">{!! nl2br(e($song->copyright)) !!}</p>
                </section>
            @endif
        </div>
    </div>

    <x-share-modal
        model="showSharePopup"
        :link="route('songs.details', $this->song)"
        :embed-link="route('embed.songs', $this->song)"
        :title="$this->song->original_title"
        :type="'song'"
    />

    <livewire:components.review-box :review-box-id="$reviewBoxID" :model-id="$song->id" :model-type="$song->getMorphClass()" :user-rating="$userRating?->first()" />
</main>
