<div wire:init="loadSection">
    @if ($this->favorites->count())
        <section class="relative pb-6 mb-8 z-10">
            <x-section-nav class="flex flex-nowrap justify-between mb-5">
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
                    <x-rows.small-lockup :animes="$this->favorites" />
                    @break
                @case(\App\Models\Game::class)
                    <x-rows.small-lockup :games="$this->favorites" />
                    @break
                @case(\App\Models\Manga::class)
                    <x-rows.small-lockup :mangas="$this->favorites" />
                    @break
            @endswitch
        </section>
    @endif
</div>
