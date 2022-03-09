@props(['seasonOfYear' => season_of_year(), 'year' => now(), 'onEachSide' => 2])

<div class="flex flex-wrap gap-1">
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
    <a class="px-4 pb-2 border-b-2 border-gray-500 hover:border-orange-500" href="{{ route('anime.index.seasons.year.season', [$nextYear, $nextSeasonOfYear->key]) }}">
        {{ $nextSeasonOfYear->key . ' ' . $nextYear }}
    </a>

    {{-- Current position --}}
    <a class="px-4 pb-2 border-b-2 border-orange-500" href="{{ route('anime.index.seasons.year.season', [$year, $seasonOfYear->key]) }}">
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
        <a class="px-4 pb-2 border-b-2 border-gray-500 hover:border-orange-500" href="{{ route('anime.index.seasons.year.season', [$nextYear, $nextSeasonOfYear->key]) }}">
            {{ $nextSeasonOfYear->key . ' ' . $nextYear }}
        </a>
    @endforeach
</div>
