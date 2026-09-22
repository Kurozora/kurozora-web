<section class="pb-8">
    <x-section-nav class="flex flex-nowrap justify-between mb-5 pt-4 xl:safe-area-inset-scroll">
        <x-slot:title>
            {{ __(':x Top Charts', ['x' => ucfirst($chartKind)]) }}
        </x-slot:title>

        <x-slot:action>
            @hasrole('superAdmin')
                <x-button wire:click="$refresh">{{ __('Refresh') }}</x-button>
            @endhasrole
            <x-section-nav-link href="{{ route('charts.details', $chartKind) }}">{{ __('See All') }}</x-section-nav-link>
        </x-slot:action>
    </x-section-nav>

    <section wire:init="loadSection">
        @if ($readyToLoad)
            @switch($chartKind)
                @case(App\Enums\ChartKind::Anime)
                    <x-rows.small-lockup :animes="$this->chart" :is-ranked="true" :is-row="true" />
                    @break
                @case(App\Enums\ChartKind::Characters)
                    <x-rows.character-lockup :characters="$this->chart" :is-ranked="true" :is-row="true" />
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
                @case(App\Enums\ChartKind::People)
                    <x-rows.person-lockup :people="$this->chart" :is-ranked="true" :is-row="true" />
                    @break
                @case(App\Enums\ChartKind::Songs)
                    <x-rows.music-lockup :songs="$this->chart" :is-ranked="true" :is-row="true" />
                    @break
                @case(App\Enums\ChartKind::Studios)
                    <x-rows.studio-lockup :studios="$this->chart" :is-ranked="true" :is-row="true" />
                    @break
            @endswitch
        @else
            <x-skeletons.lockup-row :lockup="\App\Enums\ChartKind::lockup($chartKind)" :kind="\App\Enums\ChartKind::libraryKind($chartKind)" />
        @endif
    </section>
</section>
