<div wire:init="loadSection">
    @if ($this->library->count())
        <section class="relative max-w-7xl mx-auto pl-4 pr-4 pb-6 mb-8 z-10 sm:px-6">
            <x-section-nav class="flex flex-nowrap justify-between mb-5">
                <x-slot:title>
                    {{ $title }}
                </x-slot:title>

                <x-slot:action>
                    @hasrole('superAdmin')
                        <x-button wire:click="$refresh">{{ __('Refresh') }}</x-button>
                    @endhasrole

                    <x-section-nav-link class="whitespace-nowrap" href="{{ $seeAllURL }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot:action>
            </x-section-nav>

            @switch($type)
                @case(\App\Models\Anime::class)
                    <x-rows.small-lockup :animes="$this->library" />
                    @break
                @case(\App\Models\Game::class)
                    <x-rows.small-lockup :games="$this->library" />
                    @break
                @case(\App\Models\Manga::class)
                    <x-rows.small-lockup :mangas="$this->library" />
                    @break
            @endswitch
        </section>
    @endif
</div>
