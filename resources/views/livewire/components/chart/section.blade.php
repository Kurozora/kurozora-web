<section class="pt-5 pb-8 border-t-2" wire:init="loadSection">
    <x-section-nav class="flex flex-nowrap justify-between mb-5">
        <x-slot:title>
            {{ __(':x Top Charts', ['x' => ucfirst($chartKind)]) }}
        </x-slot:title>

        <x-slot:action>
            @hasrole('superAdmin')
            <x-button wire:click="$refresh">{{ __('Refresh') }}</x-button>
            @endhasrole
            <x-section-nav-link class="whitespace-nowrap" href="{{ route('charts.details', $chartKind) }}">{{ __('See All') }}</x-section-nav-link>
        </x-slot:action>
    </x-section-nav>

    <div class="flex justify-center">
        <x-spinner />
    </div>

    @if($isInit)
        <div class="flex flex-nowrap gap-4 snap-x overflow-x-scroll no-scrollbar">
            @switch($chartKind)
                @case(App\Enums\ChartKind::Anime)
                    <x-rows.small-lockup :animes="$this->chart" :is-ranked="true" :is-row="true" />
                    @break
                @case(App\Enums\ChartKind::Episodes)
                    <x-rows.episode-lockup :episodes="$this->chart" :is-ranked="true" :is-row="true" />
                    @break
                @case(App\Enums\ChartKind::Games)
                    <x-rows.small-lockup :games="$this->chart" :is-ranked="true" :is-row="true" />
                    @break
                @case(App\Enums\ChartKind::Manga)
                    <x-rows.small-lockup :mangas="$this->chart" :is-ranked="true" :is-row="true" />
                    @break
                @case(App\Enums\ChartKind::Songs)
                    <x-rows.music-lockup :songs="$this->chart" :is-ranked="true" :is-row="true" />
                    @break
            @endswitch
        </div>
    @endif
</section>
