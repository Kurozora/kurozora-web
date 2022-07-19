@props(['seasonOfYear' => season_of_year(), 'year' => now()->year, 'onEachSide' => 2])

<div class="flex flex-wrap gap-4 justify-between">
    <div class="flex gap-2 overflow-x-scroll no-scrollbar">
        {{-- Previous page --}}
        @php
            // Capture current values
            $previousSeasonOfYear = $seasonOfYear;
            $previousYear = $year;

            // Determine next season and year
            $nextSeasonOfYear = $previousSeasonOfYear->previous();
            $nextYear = $previousSeasonOfYear->value === \App\Enums\SeasonOfYear::Winter ? $previousYear - 1 : $previousYear;

            // Update backing values
            $previousYear = $nextYear;
            $previousSeasonOfYear = $nextSeasonOfYear;
        @endphp

        @if ($nextYear < 1917 && $nextSeasonOfYear->value === \App\Enums\SeasonOfYear::Fall)
            <a class="px-4 pb-2 border-b-2 hover:border-orange-500" href="{{ route('anime.seasons.index') }}">
                {{ __('Current') }}
            </a>
        @else
            <a class="px-4 pb-2 border-b-2 hover:border-orange-500" href="{{ route('anime.seasons.year.season', [$nextYear, $nextSeasonOfYear->key]) }}">
                {{ $nextSeasonOfYear->key . ' ' . $nextYear }}
            </a>
        @endif

        {{-- Current position --}}
        @php
            $active = request()->routeIs('anime.seasons.year.season', [$year, $seasonOfYear->key]);
        @endphp
        <a class="px-4 pb-2 border-b-2 {{ $active ? 'border-orange-500' : 'hover:border-orange-500' }}" href="{{ route('anime.seasons.year.season', [$year, $seasonOfYear->key]) }}">
            {{ $seasonOfYear->key . ' ' . $year }}
        </a>

        {{-- Next position --}}
        @php
            // Capture current values
            $previousSeasonOfYear = $seasonOfYear;
            $previousYear = $year;
        @endphp

        @foreach(range(0, $onEachSide - 1) as $ignored)
            @php
                // Determine next season and year
                $nextSeasonOfYear = $previousSeasonOfYear->next();
                $nextYear = $previousSeasonOfYear->value === \App\Enums\SeasonOfYear::Fall ? $previousYear + 1 : $previousYear;

                // Update backing values
                $previousYear = $nextYear;
                $previousSeasonOfYear = $nextSeasonOfYear;
            @endphp
            <a class="px-4 pb-2 border-b-2 hover:border-orange-500" href="{{ route('anime.seasons.year.season', [$nextYear, $nextSeasonOfYear->key]) }}">
                {{ $nextSeasonOfYear->key . ' ' . $nextYear }}
            </a>
        @endforeach

        {{-- archive --}}
        @php
            $active = request()->routeIs('anime.seasons.archive', [$year, $seasonOfYear->key]);
        @endphp
        <a class="px-4 pb-2 border-b-2 {{ $active ? 'border-orange-500' : 'hover:border-orange-500' }}" href="{{ route('anime.seasons.archive') }}">
            {{ __('Archive') }}
        </a>
    </div>

    <div
        class="flex flex-wrap gap-1"
        x-data="{
                    year: null,
                    season: '{{ $seasonOfYear->key }}',
                    goToSeason() {
                        let year = this.year;
                        let season = this.season;

                        if (year && typeof parseInt(year) === 'number' && typeof season === 'string') {
                            window.location = '{{ route('anime.seasons.index') }}/' + year  + '/' + season
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
</div>
