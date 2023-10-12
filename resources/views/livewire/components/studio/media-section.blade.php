<div wire:init="loadSection">
    @if (!empty($this->models->count()))
        <section class="pt-5 pb-8 border-t-2">
            <x-section-nav>
                <x-slot:title>
                    {{ $title }}
                </x-slot:title>

                <x-slot:action>
                    @hasrole('superAdmin')
                        <x-button wire:click="$refresh">{{ __('Refresh') }}</x-button>
                    @endhasrole
                    <x-section-nav-link href="{{ $seeAllURL }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot:action>
            </x-section-nav>

            @switch($type)
                @case(\App\Models\Anime::class)
                    <x-rows.small-lockup :animes="$this->models" />
                    @break
                @case(\App\Models\Manga::class)
                    <x-rows.small-lockup :mangas="$this->models" />
                    @break
                @case(\App\Models\Game::class)
                    <x-rows.small-lockup :games="$this->models" />
                    @break
            @endswitch
        </section>
    @elseif (!$readyToLoad)
        <x-skeletons.small-lockup />
    @endif
</div>
