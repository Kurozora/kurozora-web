<main>
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

    <div class="pt-4 pb-6" wire:init="loadPage">
        <section class="mb-4">
            <div>
                <div class="flex gap-1 pl-4 pr-4">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __(':x Top Charts', ['x' => ucfirst($chartKind)]) }}</h1>
                    </div>

                    <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                    </div>
                </div>
            </div>
        </section>

        @if ($readyToLoad)
            <section class="mt-4">
                @switch($chartKind)
                    @case(App\Enums\ChartKind::Anime)
                        <x-rows.small-lockup :animes="$this->chart" :page="$this->getPage()" :per-page="$perPage" :is-ranked="true" :is-row="false" />
                    @break
                    @case(App\Enums\ChartKind::Characters)
                        <x-rows.character-lockup :characters="$this->chart" :page="$this->getPage()" :per-page="$perPage" :is-ranked="true" :is-row="false" />
                    @break
                    @case(App\Enums\ChartKind::Episodes)
                        <x-rows.episode-lockup :episodes="$this->chart" :page="$this->getPage()" :per-page="$perPage" :is-ranked="true" :is-row="false" />
                    @break
                    @case(App\Enums\ChartKind::Games)
                        <x-rows.small-lockup :games="$this->chart" :page="$this->getPage()" :per-page="$perPage" :is-ranked="true" :is-row="false" />
                    @break
                    @case(App\Enums\ChartKind::Manga)
                        <x-rows.small-lockup :mangas="$this->chart" :page="$this->getPage()" :per-page="$perPage" :is-ranked="true" :is-row="false" />
                    @break
                    @case(App\Enums\ChartKind::People)
                        <x-rows.person-lockup :people="$this->chart" :page="$this->getPage()" :per-page="$perPage" :is-ranked="true" :is-row="false" />
                    @break
                    @case(App\Enums\ChartKind::Songs)
                        <x-rows.music-lockup :songs="$this->chart" :page="$this->getPage()" :per-page="$perPage" :is-ranked="true" :is-row="false" />
                    @break
                    @case(App\Enums\ChartKind::Studios)
                        <x-rows.studio-lockup :studios="$this->chart" :page="$this->getPage()" :per-page="$perPage" :is-ranked="true" :is-row="false" />
                    @break
                @endswitch
            </section>

            <div class="mt-4 pl-4 pr-4">
                {{ $this->chart->links() }}
            </div>
        @else
            <section>
                <div class="flex flex-wrap gap-4 justify-between pl-4 pr-4">
                    @foreach (range(1,25) as $range)
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                    @endforeach
                    <div class="w-64 md:w-80 flex-grow"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                </div>
            </section>
        @endif
    </div>
</main>
