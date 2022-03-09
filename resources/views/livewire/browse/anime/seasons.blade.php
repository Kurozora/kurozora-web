<main>
    <x-slot:title>
        {{ $seasonOfYear->key . ' ' . $year }} | {{ __('Anime') }}
    </x-slot>

    <x-slot:description>
        {{ __('Browse the :x :y anime season. Join the Kurozora community and create your anime and manga list. Discover songs, games and read reviews and news!', ['x' => $seasonOfYear->key, 'y' => $year]) }}
    </x-slot>

    <x-slot:meta>
        <meta property="og:title" content="{{ $seasonOfYear->key . ' ' . $year }} | {{ __('Anime') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Browse the :x :y anime season. Join the Kurozora community and create your anime and manga list. Discover songs, games and read reviews and news!', ['x' => $seasonOfYear->key, 'y' => $year]) }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('anime.index.seasons.year.season', [$year, $season]) }}">
    </x-slot>

    <div
        class="max-w-7xl mx-auto px-4 py-6 sm:px-6"
        x-data="{
            selectedMediaType: null
        }"
    >
        <p class="text-2xl font-bold">{{ __('Seasonal Anime') }}</p>

        <section class="flex flex-wrap gap-4 justify-between mt-4 pb-5">
            <x-season-pagination :season-of-year="$seasonOfYear" :year="$year" :on-each-side="2" />

            <div
                class="flex flex-wrap gap-1"
                x-data="{
                    year: null,
                    season: '{{ $season }}',
                    goToSeason() {
                        let year = this.year;
                        let season = this.season;

                        if (year && typeof parseInt(year) === 'number' && typeof season === 'string') {
                            window.location = '../' + year  + '/' + season
                        }
                    }
                }"
            >
                <p class="m-auto">{{ __('Jump to') }}</p>

                <x-label>
                    <x-select x-model="season">
                        @foreach(\App\Enums\SeasonOfYear::asSelectArray() as $seasonOfYearValue)
                            <option value="{{ $seasonOfYearValue }}">{{ __($seasonOfYearValue) }}</option>
                        @endforeach
                    </x-select>
                    <x-input-error for="season"></x-input-error>
                </x-label>

                <x-label>
                    <x-input class="w-24" name="year" type="number" placeholder="{{ __('Year') }}" x-model="year" />
                    <x-input-error for="year"></x-input-error>
                </x-label>

                <x-button x-on:click="goToSeason()">{{ __('Go') }}</x-button>
            </div>
        </section>

        <section id="mediaTypeHeader" class="flex flex-wrap gap-1 py-5 border-t-2 bg-white z-10">
            <template x-if="selectedMediaType === null">
                <x-button>{{ __('All') }}</x-button>
            </template>
            <template x-if="selectedMediaType !== null">
                <x-outlined-button x-on:click="selectedMediaType = null">{{ __('All') }}</x-outlined-button>
            </template>
            @foreach($mediaTypes as $mediaType)
                <template x-if="selectedMediaType === '{{ $mediaType->name }}'">
                    <x-button>{{ $mediaType->name }}</x-button>
                </template>
                <template x-if="selectedMediaType !== '{{ $mediaType->name }}'">
                    <x-outlined-button x-on:click="selectedMediaType = '{{ $mediaType->name }}'">{{ $mediaType->name }}</x-outlined-button>
                </template>
            @endforeach
        </section>

        <section class="space-y-10">
            @foreach($mediaTypes as $mediaType)
                <div
                    x-show="selectedMediaType === '{{ $mediaType->name }}' || selectedMediaType === null"
                >
                    <livewire:browse.anime.seasons-section :media-type="$mediaType" :season-of-year="$seasonOfYear->value" :year="$year" />
                </div>
            @endforeach
        </section>
    </div>

    <script>
        // When the user scrolls the page, execute myFunction
        window.onscroll = function() { myFunction() };

        // Get the header
        const header = document.getElementById('mediaTypeHeader');

        // Get the offset position of the navbar
        const sticky = header.offsetTop;

        // Add the sticky class to the header when you reach its scroll position. Remove "sticky" when you leave the scroll position
        function myFunction() {
            if (window.scrollY > sticky) {
                header.classList.add('sticky', 'top-0', 'border-b-2');
            } else {
                header.classList.remove('sticky', 'top-0', 'border-b-2');
            }
        }
    </script>
</main>
