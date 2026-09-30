<x-base-layout>
    <x-slot:title>
        {{ __(':x Top Charts on :y', ['x' => ucfirst($chartKind), 'y' => config('app.name')]) }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Browse the top free anime on :x, like One Piece, Attack on Titan, Demon Slayer, My Hero Academia, Bleach and more!', ['x' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __(':x Top Charts on :y', ['x' => ucfirst($chartKind), 'y' => config('app.name')]) }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Browse the top free anime on :x, like One Piece, Attack on Titan, Demon Slayer, My Hero Academia, Bleach and more!', ['x' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('charts.details', ['chart' => $chartKind]) }}">
    </x-slot:meta>

    <x-slot:scripts>
        @vite(['resources/js/charts.js'])
    </x-slot:scripts>

    <main data-charts>
        <div class="pt-4 pb-6">
            <section class="mb-4 xl:safe-area-inset">
                <div>
                    <div class="flex gap-1 pl-4 pr-4">
                        <div class="flex flex-wrap items-center w-full">
                            <h1 class="text-2xl font-bold">{{ __(':x Top Charts', ['x' => ucfirst($chartKind)]) }}</h1>
                        </div>

                        <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                            @auth
                                @if (in_array($chartKind, [App\Enums\ChartKind::Anime, App\Enums\ChartKind::Games, App\Enums\ChartKind::Manga]))
                                    <x-toggle-button data-charts-library-toggle title="{{ __('Dim library') }}" aria-label="{{ __('Dim library') }}">
                                        <span data-charts-library-icon-off>@svg('rectangle_stack_fill', 'fill-current', ['width' => '16'])</span>
                                        <span data-charts-library-icon-on class="hidden">@svg('rectangle_stack_slash_fill', 'fill-current', ['width' => '16'])</span>
                                    </x-toggle-button>
                                @endif
                            @endauth
                        </div>
                    </div>
                </div>
            </section>

            <section class="mt-4 xl:safe-area-inset" data-paginated="chart">
                @switch($chartKind)
                    @case(App\Enums\ChartKind::Anime)
                        <x-rows.small-lockup :animes="$chart" :page="$chart->currentPage()" :per-page="$chart->perPage()" :is-ranked="true" :is-row="false" :marks-library="auth()->check()" />
                    @break
                    @case(App\Enums\ChartKind::Characters)
                        <x-rows.character-lockup :characters="$chart" :page="$chart->currentPage()" :per-page="$chart->perPage()" :is-ranked="true" :is-row="false" />
                    @break
                    @case(App\Enums\ChartKind::Episodes)
                        <x-rows.episode-lockup :episodes="$chart" :page="$chart->currentPage()" :per-page="$chart->perPage()" :is-ranked="true" :is-row="false" />
                    @break
                    @case(App\Enums\ChartKind::Games)
                        <x-rows.small-lockup :games="$chart" :page="$chart->currentPage()" :per-page="$chart->perPage()" :is-ranked="true" :is-row="false" :marks-library="auth()->check()" />
                    @break
                    @case(App\Enums\ChartKind::Manga)
                        <x-rows.small-lockup :mangas="$chart" :page="$chart->currentPage()" :per-page="$chart->perPage()" :is-ranked="true" :is-row="false" :marks-library="auth()->check()" />
                    @break
                    @case(App\Enums\ChartKind::People)
                        <x-rows.person-lockup :people="$chart" :page="$chart->currentPage()" :per-page="$chart->perPage()" :is-ranked="true" :is-row="false" />
                    @break
                    @case(App\Enums\ChartKind::Songs)
                        <x-rows.music-lockup :songs="$chart" :page="$chart->currentPage()" :per-page="$chart->perPage()" :is-ranked="true" :is-row="false" />
                    @break
                    @case(App\Enums\ChartKind::Studios)
                        <x-rows.studio-lockup :studios="$chart" :page="$chart->currentPage()" :per-page="$chart->perPage()" :is-ranked="true" :is-row="false" />
                    @break
                @endswitch

                <div class="mt-4 pl-4 pr-4">
                    {{ $chart->links() }}
                </div>
            </section>
        </div>
    </main>
</x-base-layout>
