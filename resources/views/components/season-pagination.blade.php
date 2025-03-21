@props(['type', 'seasonOfYear' => season_of_year(), 'year' => now()->year, 'onEachSide' => 2])

@php
    $seasonIndexRouteName = match ($type) {
        App\Models\Game::class => 'games.seasons.index',
        App\Models\Manga::class => 'manga.seasons.index',
        default => 'anime.seasons.index'
    };
    $seasonYearRouteName = match ($type) {
        App\Models\Game::class => 'games.seasons.year.season',
        App\Models\Manga::class => 'manga.seasons.year.season',
        default => 'anime.seasons.year.season'
    };
    $seasonScheduleRouteName = match ($type) {
        App\Models\Game::class => 'schedule',
        App\Models\Manga::class => 'schedule',
        default => 'schedule'
    };
    $seasonArchiveRouteName = match ($type) {
        App\Models\Game::class => 'games.seasons.archive',
        App\Models\Manga::class => 'manga.seasons.archive',
        default => 'anime.seasons.archive'
    };
@endphp

<div class="flex flex-wrap gap-4 justify-between items-baseline">
    <div class="flex gap-2 pl-4 pr-4 overflow-x-scroll no-scrollbar">
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
            <a class="pl-4 pr-4 pb-2 whitespace-nowrap border-b border-primary hover:border-tint" href="{{ route($seasonIndexRouteName) }}" wire:navigate>
                {{ __('Current') }}
            </a>
        @else
            <a class="pl-4 pr-4 pb-2 whitespace-nowrap border-b border-primary hover:border-tint" href="{{ route($seasonYearRouteName, [$nextYear, $nextSeasonOfYear->key]) }}" wire:navigate>
                {{ $nextSeasonOfYear->key . ' ' . $nextYear }}
            </a>
        @endif

        {{-- Current position --}}
        @php
            $active = request()->routeIs($seasonYearRouteName, [$year, $seasonOfYear->key]);
        @endphp
        <a class="pl-4 pr-4 pb-2 whitespace-nowrap border-b {{ $active ? 'border-tint' : 'border-primary hover:border-tint' }}" href="{{ route($seasonYearRouteName, [$year, $seasonOfYear->key]) }}" wire:navigate>
            {{ $seasonOfYear->key . ' ' . $year }}
        </a>

        {{-- Next position --}}
        @php
            // Capture current values
            $previousSeasonOfYear = $seasonOfYear;
            $previousYear = $year;
        @endphp

        @foreach (range(0, $onEachSide - 1) as $ignored)
            @php
                // Determine next season and year
                $nextSeasonOfYear = $previousSeasonOfYear->next();
                $nextYear = $previousSeasonOfYear->value === \App\Enums\SeasonOfYear::Fall ? $previousYear + 1 : $previousYear;

                // Update backing values
                $previousYear = $nextYear;
                $previousSeasonOfYear = $nextSeasonOfYear;
            @endphp
            <a class="pl-4 pr-4 pb-2 whitespace-nowrap border-b border-primary hover:border-tint" href="{{ route($seasonYearRouteName, [$nextYear, $nextSeasonOfYear->key]) }}" wire:navigate>
                {{ $nextSeasonOfYear->key . ' ' . $nextYear }}
            </a>
        @endforeach

        {{-- Schedule --}}
        @php
            $active = request()->routeIs($seasonScheduleRouteName, [$year, $seasonOfYear->key]);
        @endphp
        <a class="pl-4 pr-4 pb-2 whitespace-nowrap border-b {{ $active ? 'border-tint' : 'border-primary hover:border-tint' }}" href="{{ route($seasonScheduleRouteName) }}" wire:navigate>
            {{ __('Schedule') }}
        </a>

        {{-- Archive --}}
        @php
            $active = request()->routeIs($seasonArchiveRouteName, [$year, $seasonOfYear->key]);
        @endphp
        <a class="pl-4 pr-4 pb-2 whitespace-nowrap border-b {{ $active ? 'border-tint' : 'border-primary hover:border-tint' }}" href="{{ route($seasonArchiveRouteName) }}" wire:navigate>
            {{ __('Archive') }}
        </a>
    </div>

    <div
        class="flex flex-wrap gap-1 pl-4 pr-4"
        x-data="{
            year: null,
            season: '{{ $seasonOfYear->key }}',
            goToSeason() {
                let year = this.year;
                let season = this.season;

                if (year && typeof parseInt(year) === 'number' && typeof season === 'string') {
                    window.location = '{{ route($seasonIndexRouteName) }}/' + year  + '/' + season
                }
            }
        }"
    >
        <p class="m-auto">{{ __('Jump to') }}</p>

        <x-label>
            <x-select x-model="season">
                @foreach (\App\Enums\SeasonOfYear::asSelectArray() as $seasonOfYearValue)
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
