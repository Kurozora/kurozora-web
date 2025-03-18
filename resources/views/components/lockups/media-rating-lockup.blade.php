@props(['mediaRating', 'isRow' => true])

@php
    $class = $isRow ? 'pb-2 shrink-0 snap-normal snap-center' : '';
@endphp

<div {{ $attributes->merge(['class' => 'relative flex-grow w-64 md:w-80 ' . $class]) }}>
    @switch($mediaRating->model_type)
        @case(\App\Models\Anime::class)
            <div class="flex flex-nowrap gap-2">
                <picture
                    class="relative shrink-0 w-28 h-40 rounded-lg overflow-hidden"
                    style="background-color: {{ $mediaRating->model->getFirstMedia(\App\Enums\MediaCollection::Poster)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
                >
                    <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $mediaRating->model->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $mediaRating->model->title }} Poster" title="{{ $mediaRating->model->title }}">

                    <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
                </picture>

                <a class="absolute w-full h-full" href="{{ route('anime.details', $mediaRating->model) }}"></a>

                <div class="flex flex-col items-baseline w-full">
                    <div class="flex justify-between gap-2 w-full">
                        <p class="inline-flex items-center text-sm font-semibold break-all overflow-hidden">{{ $mediaRating->model->title }}</p>

                        <p class="text-sm text-secondary whitespace-nowrap" title="{{ $mediaRating->created_at->toFormattedDateString() }}">{{ $mediaRating->created_at->toFormattedDateString() }}</p>
                    </div>

                    <div>
                        <livewire:components.star-rating :rating="$mediaRating->rating" :star-size="'sm'" :disabled="true" wire:key="{{ uniqid('rating-', true) }}" />
                    </div>

                    <div class="mt-1 w-full">
                        <x-truncated-text class="ml-4 mr-4">
                            <x-slot:text>
                                {!! nl2br(e($mediaRating->description)) !!}
                            </x-slot:text>
                        </x-truncated-text>
                    </div>
                </div>
            </div>
            @break
        @case(\App\Models\Character::class)
            <div class="flex flex-nowrap gap-2">
                <picture
                    class="relative shrink-0 w-28 aspect-square rounded-full overflow-hidden"
                    style="background-color: {{ $mediaRating->model->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
                >
                    <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $mediaRating->model->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/person_poster.webp') }}" alt="{{ $mediaRating->model->name }} Profile Image" title="{{ $mediaRating->model->name }}">

                    <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-full"></div>
                </picture>

                <a class="absolute w-full h-full" href="{{ route('characters.details', $mediaRating->model) }}"></a>

                <div class="flex flex-col items-baseline w-full">
                    <div class="flex justify-between gap-2 w-full">
                        <p class="inline-flex items-center text-sm font-semibold break-all overflow-hidden">{{ $mediaRating->model->name }}</p>

                        <p class="text-sm text-secondary whitespace-nowrap" title="{{ $mediaRating->created_at->toFormattedDateString() }}">{{ $mediaRating->created_at->toFormattedDateString() }}</p>
                    </div>

                    <div>
                        <livewire:components.star-rating :rating="$mediaRating->rating" :star-size="'sm'" :disabled="true" wire:key="{{ uniqid('rating-', true) }}" />
                    </div>

                    <div class="mt-1 w-full">
                        <x-truncated-text class="ml-4 mr-4">
                            <x-slot:text>
                                {!! nl2br(e($mediaRating->description)) !!}
                            </x-slot:text>
                        </x-truncated-text>
                    </div>
                </div>
            </div>
            @break
        @case(\App\Models\Episode::class)
            <div class="flex flex-nowrap gap-2">
                <picture
                    class="relative shrink-0 w-28 aspect-video rounded-lg overflow-hidden"
                    style="background-color: {{ $mediaRating->model->getFirstMedia(\App\Enums\MediaCollection::Banner)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
                >
                    <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $mediaRating->model->getFirstMediaFullUrl(\App\Enums\MediaCollection::Banner()) ?? asset('images/static/placeholders/episode_banner.webp') }}" alt="{{ $mediaRating->model->title }} Banner" title="{{ $mediaRating->model->title }}">

                    <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
                </picture>

                <a class="absolute w-full h-full" href="{{ route('episodes.details', $mediaRating->model) }}"></a>

                <div class="flex flex-col items-baseline w-full">
                    <div class="flex justify-between gap-2 w-full">
                        <p class="inline-flex items-center text-sm font-semibold break-all overflow-hidden">{{ $mediaRating->model->title }}</p>

                        <p class="text-sm text-secondary whitespace-nowrap" title="{{ $mediaRating->created_at->toFormattedDateString() }}">{{ $mediaRating->created_at->toFormattedDateString() }}</p>
                    </div>

                    <div>
                        <livewire:components.star-rating :rating="$mediaRating->rating" :star-size="'sm'" :disabled="true" wire:key="{{ uniqid('rating-', true) }}" />
                    </div>

                    <div class="mt-1 w-full">
                        <x-truncated-text class="ml-4 mr-4">
                            <x-slot:text>
                                {!! nl2br(e($mediaRating->description)) !!}
                            </x-slot:text>
                        </x-truncated-text>
                    </div>
                </div>
            </div>
            @break
        @case(\App\Models\Game::class)
            <div class="flex flex-nowrap gap-2">
                <picture
                    class="relative shrink-0 w-28 h-28 rounded-3xl overflow-hidden"
                    style="background-color: {{ $mediaRating->model->getFirstMedia(\App\Enums\MediaCollection::Poster)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
                >
                    <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $mediaRating->model->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $mediaRating->model->title }} Poster" title="{{ $mediaRating->model->title }}">

                    <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-3xl"></div>
                </picture>

                <a class="absolute w-full h-full" href="{{ route('games.details', $mediaRating->model) }}"></a>

                <div class="flex flex-col items-baseline w-full">
                    <div class="flex justify-between gap-2 w-full">
                        <p class="inline-flex items-center text-sm font-semibold break-all overflow-hidden">{{ $mediaRating->model->title }}</p>

                        <p class="text-sm text-secondary whitespace-nowrap" title="{{ $mediaRating->created_at->toFormattedDateString() }}">{{ $mediaRating->created_at->toFormattedDateString() }}</p>
                    </div>

                    <div>
                        <livewire:components.star-rating :rating="$mediaRating->rating" :star-size="'sm'" :disabled="true" wire:key="{{ uniqid('rating-', true) }}" />
                    </div>

                    <div class="mt-1 w-full">
                        <x-truncated-text class="ml-4 mr-4">
                            <x-slot:text>
                                {!! nl2br(e($mediaRating->description)) !!}
                            </x-slot:text>
                        </x-truncated-text>
                    </div>
                </div>
            </div>
            @break
        @case(\App\Models\Manga::class)
            <div class="flex flex-nowrap gap-2">
                <svg
                    class="relative shrink-0 w-28 h-40 overflow-hidden"
                    style="background-color: {{ $mediaRating->model->getFirstMedia(\App\Enums\MediaCollection::Poster)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
                >
                    <foreignObject width="112" height="160" mask="url(#svg-mask-book-cover)">
                        <img class="h-full w-full object-cover lazyload" data-sizes="auto" data-src="{{ $mediaRating->model->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $mediaRating->model->title }} Poster" title="{{ $mediaRating->model->title }}" />
                    </foreignObject>

                    <g opacity="0.40">
                        <use fill-opacity="0.03" fill="url(#svg-pattern-book-cover-1)" fill-rule="evenodd" xlink:href="#svg-rect-book-cover" />
                        <use fill-opacity="1" fill="url(#svg-linearGradient-book-cover-1)" fill-rule="evenodd" style="mix-blend-mode: lighten;" xlink:href="#svg-rect-book-cover" />
                        <use fill-opacity="1" fill="black" filter="url(#svg-filter-book-cover-1)" xlink:href="#svg-rect-book-cover" />
                    </g>
                </svg>

                <a class="absolute w-full h-full" href="{{ route('manga.details', $mediaRating->model) }}"></a>

                <div class="flex flex-col items-baseline w-full">
                    <div class="flex justify-between gap-2 w-full">
                        <p class="inline-flex items-center text-sm font-semibold break-all overflow-hidden">{{ $mediaRating->model->title }}</p>

                        <p class="text-sm text-secondary whitespace-nowrap" title="{{ $mediaRating->created_at->toFormattedDateString() }}">{{ $mediaRating->created_at->toFormattedDateString() }}</p>
                    </div>

                    <div>
                        <livewire:components.star-rating :rating="$mediaRating->rating" :star-size="'sm'" :disabled="true" wire:key="{{ uniqid('rating-', true) }}" />
                    </div>

                    <div class="mt-1 w-full">
                        <x-truncated-text class="ml-4 mr-4">
                            <x-slot:text>
                                {!! nl2br(e($mediaRating->description)) !!}
                            </x-slot:text>
                        </x-truncated-text>
                    </div>
                </div>
            </div>
            @break
        @case(\App\Models\Person::class)
            <div class="flex flex-nowrap gap-2">
                <picture
                    class="relative shrink-0 w-28 aspect-square rounded-full overflow-hidden"
                    style="background-color: {{ $mediaRating->model->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
                >
                    <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $mediaRating->model->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/person_poster.webp') }}" alt="{{ $mediaRating->model->full_name }} Profile Image" title="{{ $mediaRating->model->full_name }}">

                    <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-full"></div>
                </picture>

                <a class="absolute w-full h-full" href="{{ route('people.details', $mediaRating->model) }}"></a>

                <div class="flex flex-col items-baseline w-full">
                    <div class="flex justify-between gap-2 w-full">
                        <p class="inline-flex items-center text-sm font-semibold break-all overflow-hidden">{{ $mediaRating->model->full_name }}</p>

                        <p class="text-sm text-secondary whitespace-nowrap" title="{{ $mediaRating->created_at->toFormattedDateString() }}">{{ $mediaRating->created_at->toFormattedDateString() }}</p>
                    </div>

                    <div>
                        <livewire:components.star-rating :rating="$mediaRating->rating" :star-size="'sm'" :disabled="true" wire:key="{{ uniqid('rating-', true) }}" />
                    </div>

                    <div class="mt-1 w-full">
                        <x-truncated-text class="ml-4 mr-4">
                            <x-slot:text>
                                {!! nl2br(e($mediaRating->description)) !!}
                            </x-slot:text>
                        </x-truncated-text>
                    </div>
                </div>
            </div>
            @break
        @case(\App\Models\Song::class)
            <div
                class="flex flex-nowrap gap-2"
                x-data="{
                    song: null,
                    musicManager: null,
                    bgColor: '#A660B2',
                    songTitle: '{{ str($mediaRating->model->title)->replace('\'', '’') }}',
                    artistName: '{{ str($mediaRating->model->artist ?? 'Unknown')->replace('\'', '’') }}',
                    artworkURL: '{{ $mediaRating->model->getFirstMediaFullUrl(\App\Enums\MediaCollection::Artwork()) ?? asset('images/static/placeholders/music_album.webp') }}',
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
                x-on:musicmanagerloaded.window="await fetchSongData('{{ $mediaRating->model->am_id }}')"
                x-init="window.musicManager !== undefined ? await fetchSongData('{{ $mediaRating->model->am_id }}') : null"
            >
                <x-picture class="relative shrink-0 w-28 aspect-square rounded-lg overflow-hidden">
                    <img class="w-full h-full object-cover"
                         width="320" height="320"
                         src="{{ $mediaRating->model->getFirstMediaFullUrl(\App\Enums\MediaCollection::Artwork()) ?? asset('images/static/placeholders/music_album.webp') }}"
                         x-bind:title="songTitle"
                         x-bind:alt="songTitle + ' Artwork'"
                         x-bind:src="artworkURL"
                         x-bind:style="{'background-color': bgColor}"
                    >

                    <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>

                    <div class="absolute top-0 bottom-0 left-0 right-0">
                        <div class="flex flex-col justify-center items-center h-full">
                            @if (!empty($mediaRating->model->am_id))
                                <button
                                    class="inline-flex items-center pt-3 pr-3 pb-3 pl-3 bg-blur backdrop-blur border border-transparent rounded-full font-semibold text-xs uppercase tracking-widest shadow-md hover:bg-tint-800 hover:btn-text-tinted active:bg-tint active:btn-text-tinted focus:outline-none disabled:bg-gray-100 disabled:text-gray-300 disabled:cursor-default disabled:opacity-100 transition ease-in-out duration-150"
                                    x-on:click="await musicManager?.playSong(song)"
                                >
                                    <template x-if="musicManager?.isPlaying && musicManager?.currentMusicID === '{{ $mediaRating->model->am_id }}'">
                                        @svg('pause_fill', 'fill-current', ['width' => '18'])
                                    </template>

                                    <template x-if="!(musicManager?.isPlaying && musicManager?.currentMusicID === '{{ $mediaRating->model->am_id }}')">
                                        @svg('play_fill', 'fill-current', ['width' => '18'])
                                    </template>
                                </button>
                            @endif
                        </div>
                    </div>
                </x-picture>

                <div class="relative flex flex-col items-baseline w-full">
                    <a class="absolute w-full h-full" href="{{ route('songs.details', $mediaRating->model) }}"></a>

                    <div class="flex justify-between gap-2 w-full">
                        <p class="inline-flex items-center text-sm font-semibold break-all overflow-hidden" x-text="songTitle">{{ $mediaRating->model->title }}</p>

                        <p class="text-sm text-secondary whitespace-nowrap" title="{{ $mediaRating->created_at->toFormattedDateString() }}">{{ $mediaRating->created_at->toFormattedDateString() }}</p>
                    </div>

                    <div>
                        <livewire:components.star-rating :rating="$mediaRating->rating" :star-size="'sm'" :disabled="true" wire:key="{{ uniqid('rating-', true) }}" />
                    </div>

                    <div class="mt-1 w-full">
                        <x-truncated-text class="ml-4 mr-4">
                            <x-slot:text>
                                {!! nl2br(e($mediaRating->description)) !!}
                            </x-slot:text>
                        </x-truncated-text>
                    </div>
                </div>
            </div>
            @break
        @case(\App\Models\Studio::class)
            <div class="flex flex-nowrap gap-2">
                <picture
                    class="relative shrink-0 w-28 rounded-full overflow-hidden"
                    style="background-color: {{ $mediaRating->model->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
                >
                    <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $mediaRating->model->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/studio_profile.webp') }}" alt="{{ $mediaRating->model->name }} Logo" title="{{ $mediaRating->model->name }}">

                    <div class="absolute top-0 left-0 h-full w-full border-2 border-solid border-black/20 rounded-full"></div>
                </picture>

                <a class="absolute w-full h-full" href="{{ route('studios.details', $mediaRating->model) }}"></a>

                <div class="flex flex-col items-baseline w-full">
                    <div class="flex justify-between gap-2 w-full">
                        <p class="inline-flex items-center text-sm font-semibold break-all overflow-hidden">{{ $mediaRating->model->name }}</p>

                        <p class="text-sm text-secondary whitespace-nowrap" title="{{ $mediaRating->created_at->toFormattedDateString() }}">{{ $mediaRating->created_at->toFormattedDateString() }}</p>
                    </div>

                    <div>
                        <livewire:components.star-rating :rating="$mediaRating->rating" :star-size="'sm'" :disabled="true" wire:key="{{ uniqid('rating-', true) }}" />
                    </div>

                    <div class="mt-1 w-full">
                        <x-truncated-text class="ml-4 mr-4">
                            <x-slot:text>
                                {!! nl2br(e($mediaRating->description)) !!}
                            </x-slot:text>
                        </x-truncated-text>
                    </div>
                </div>
            </div>
        @break
        @default
            @if (app()->isLocal())
                {{ 'Unhandled type: ' . $mediaRating->model_type }}
            @endif
    @endswitch
</div>
