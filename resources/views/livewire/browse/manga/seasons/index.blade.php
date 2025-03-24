<main>
    <x-slot:title>
        {{ $this->seasonOfYear->key . ' ' . $year }} | {{ __('Manga') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Browse the :x :y manga season. Join the :z community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => $this->seasonOfYear->key, 'y' => $year, 'z' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ $this->seasonOfYear->key . ' ' . $year }} | {{ __('Manga') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Browse the :x :y manga season. Join the :z community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => $this->seasonOfYear->key, 'y' => $year, 'z' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('manga.seasons.year.season', [$year, $season]) }}">
    </x-slot:meta>

    <div
        class="py-6"
        x-data="{
            selectedMediaType: null
        }"
    >
        <section class="flex gap-1 pl-4 pr-4">
            <div class="flex flex-wrap items-center w-full">
                <h1 class="text-2xl font-bold">{{ __('Seasonal Manga') }}</h1>
            </div>

            <div class="flex flex-wrap justify-end items-center w-full">
            </div>
        </section>

        <section id="mediaTypeHeader" class="bg-primary pt-4 pb-4 z-10">
            <x-season-pagination :type="App\Models\Manga::class" :season-of-year="$this->seasonOfYear" :year="$year" />

            <x-hr class="mt-4 mb-4 ml-4 mr-4" />

            @if ($this->mediaTypes->count())
                <div class="flex gap-2 pl-4 pr-4 overflow-x-scroll no-scrollbar">
                    <template x-if="selectedMediaType === null">
                        <x-button>{{ __('All') }}</x-button>
                    </template>

                    <template x-if="selectedMediaType !== null">
                        <x-outlined-button x-on:click="selectedMediaType = null">{{ __('All') }}</x-outlined-button>
                    </template>

                    @foreach ($this->mediaTypes as $mediaType)
                        <template x-if="selectedMediaType === '{{ $mediaType->name }}'">
                            <x-button class="whitespace-nowrap">{{ $mediaType->name }}</x-button>
                        </template>

                        <template x-if="selectedMediaType !== '{{ $mediaType->name }}'">
                            <x-outlined-button class="whitespace-nowrap" x-on:click="selectedMediaType = '{{ $mediaType->name }}'">{{ $mediaType->name }}</x-outlined-button>
                        </template>
                    @endforeach
                </div>
            @endif
        </section>

        @if ($this->mediaTypes->count())
            <section class="space-y-10">
                @foreach ($this->mediaTypes as $mediaType)
                    <div x-show="selectedMediaType === '{{ $mediaType->name }}' || selectedMediaType === null">
                        <livewire:components.browse.seasons-section :class="\App\Models\Manga::class" :media-type="$mediaType" :season-of-year="$this->seasonOfYear->value" :year="$year" />
                    </div>
                @endforeach
            </section>
        @else
            <section class="flex flex-col items-center mt-4 text-center">
                <x-picture>
                    <img class="w-full max-w-sm" src="{{ asset('images/static/placeholders/empty_manga_library.webp') }}" alt="Empty Manga Season" title="Empty Manga Season">
                </x-picture>

                <p class="font-bold">{{ __('No Manga') }}</p>

                <p class="text-sm text-secondary">{{ __('There are no manga publishing this season.') }}</p>
            </section>
        @endif
    </div>

    <script>
        // Get the header
        const header = document.getElementById('mediaTypeHeader')

        // Get the offset position of the navbar
        const sticky = header.offsetTop

        // When the user scrolls the page, execute stickyHeader
        window.onscroll = function() { stickyHeader() }

        // Add the sticky class to the header when you reach its scroll position. Remove "sticky" when you leave the scroll position
        function stickyHeader() {
            if (window.scrollY > sticky) {
                header.classList.add('sticky', 'top-0', 'border-b', 'border-primary')
            } else {
                header.classList.remove('sticky', 'top-0', 'border-b', 'border-primary')
            }
        }
    </script>
</main>
