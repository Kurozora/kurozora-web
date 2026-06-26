<main
    data-museum
    data-museum-item-height="{{ $itemHeight }}"
    data-museum-endpoint="/museum/{{ $slug }}"
    data-museum-title="{{ __('Museum :year') }} — {{ config('app.name') }}"
    data-museum-itunes="app-id={{ config('app.ios.id') }}, app-argument={{ config('app.ios.protocol') }}museum/{{ $slug }}/:year"
    data-museum-deeplink="{{ config('app.ios.protocol') }}museum/{{ $slug }}/:year"
>
    <x-slot:title>
        {{ __('Museum') }}
    </x-slot:title>

    <x-slot:description>
        {{ $this->ogDescription }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Museum') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $this->ogDescription }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ $this->canonicalUrl }}">
    </x-slot:meta>

    <x-slot:scripts>
        @vite(['resources/js/museum.js'])
    </x-slot:scripts>

    <x-slot:appArgument>
        museum/{{ $slug }}
    </x-slot:appArgument>

    <div class="flex items-center gap-2 pl-4 pr-4 pt-2 xl:safe-area-inset-scroll">
        <div class="flex flex-col">
            <div class="flex items-center gap-1">
                <h1 class="text-2xl font-bold">{{ __('Museum') }}</h1>

                <x-select-button
                    data-museum-select
                    aria-label="{{ __('Jump to year') }}"
                    chevronClass="w-5 h-5 text-secondary"
                    class="pl-1 pr-6 pt-0 pb-0 bg-transparent text-secondary text-2xl font-bold tabular-nums shadow-none"
                >
                    @foreach ($decades as $decade => $decadeYears)
                        <optgroup label="{{ $decade }}s">
                            @foreach ($decadeYears as $year)
                                <option value="{{ $year['year'] }}" @selected($year['year'] === $currentYear)>{{ $year['year'] }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </x-select-button>
            </div>

            @if ($startYear)
                <p
                    data-museum-scale
                    data-year-label="{{ __(':count works · :year') }}"
                    class="text-xs text-secondary font-bold"
                    style="transition: opacity 150ms ease-in-out;"
                >{{ __(':count works · :start–:end', ['count' => number_format($totalCount), 'start' => $startYear, 'end' => $endYear]) }}</p>
            @endif
        </div>

        <div class="ml-auto flex items-center gap-2">
            @auth
                <x-toggle-button data-museum-library-toggle>{{ __('Dim library') }}</x-toggle-button>
            @endauth

            <x-button variant="secondary" data-museum-recenter>{{ __('Now') }}</x-button>
        </div>
    </div>

    <div
        data-museum-scroller
        data-museum-current-year="{{ $currentYear }}"
        tabindex="0"
        class="flex gap-10 overflow-x-auto overflow-y-hidden pt-4 pl-4 pr-4 pb-4 no-scrollbar focus:outline-none xl:safe-area-inset-scroll"
        style="height: calc(100dvh - 12rem);"
    >
        @forelse ($years as $year)
            <div
                data-museum-year="{{ $year['year'] }}"
                data-museum-count="{{ $year['count'] }}"
                class="grid shrink-0 grid-flow-col content-start gap-2"
                style="grid-template-rows: repeat(auto-fill, {{ $itemHeightRem }}); grid-auto-columns: 7rem;"
            >
                @for ($index = 0; $index < min($year['count'], 10); $index++)
                    <div class="{{ $skeletonClass }} bg-secondary" aria-hidden="true"></div>
                @endfor
            </div>
        @empty
            <p class="pl-4 pr-4 text-secondary xl:safe-area-inset-scroll">{{ __('There is nothing here yet.') }}</p>
        @endforelse
    </div>

    <div data-museum-timeline class="pl-4 pr-4 pb-3 xl:safe-area-inset-scroll">
        <div data-museum-track class="relative flex h-8 items-end gap-px" role="group" aria-label="{{ __('Jump to year') }}">
            @foreach ($years as $year)
                <button
                    type="button"
                    data-museum-tick
                    data-museum-year="{{ $year['year'] }}"
                    class="min-w-0 flex-1 rounded-sm bg-secondary transition-colors hover:bg-tint"
                    style="height: {{ max(8, (int) round($year['count'] / $maxCount * 100)) }}%;"
                    aria-label="{{ $year['year'] }}"
                ></button>
            @endforeach

            <div
                data-museum-chip
                data-label="{{ __(':year · :count titles') }}"
                class="pointer-events-none absolute bottom-full left-0 z-20 mb-1 hidden -translate-x-1/2 whitespace-nowrap rounded-md border-primary bg-blur pl-2 pr-2 pt-1 pb-1 text-xs tabular-nums shadow-lg backdrop-blur"
            ></div>
        </div>

        <div data-museum-decades class="mt-1 hidden gap-px text-xs leading-none text-secondary sm:flex">
            @foreach ($decades as $decade => $decadeYears)
                <span
                    data-label="{{ $decade }}s"
                    class="min-w-0 overflow-hidden whitespace-nowrap border-l border-primary pl-1"
                    style="flex: {{ count($decadeYears) }} 1 0;"
                >
                    {{ __(':years', ['year' => $decade]) }}
                </span>
            @endforeach
        </div>
    </div>

    <template data-museum-poster>
        <x-museum.poster :kind="$kind" />
    </template>
</main>
