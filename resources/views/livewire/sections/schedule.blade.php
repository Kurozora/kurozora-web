<div wire:init="loadSection">
    <section id="#{{ $date->toDateString() }}" class="pt-5 pb-8 pl-4 pr-4 border-t-2">
        @if ($this->models->count())
            <x-section-nav>
                <x-slot:title>
                    {{ $date->format('l') . ' ' . $date->format('M d') . ' (' . $this->models->count() . ')' }}
                </x-slot:title>

                <x-slot:action>
                    <x-spinner />

                    @hasrole('superAdmin')
                        <x-button wire:click="$refresh">{{ __('Refresh') }}</x-button>
                    @endhasrole
                </x-slot:action>
            </x-section-nav>

            @switch($class)
                @case(\App\Models\Anime::class)
                    <x-rows.small-lockup :animes="$this->models" :shows-schedule="true" :is-row="false" />
                    @break
                @case(\App\Models\Game::class)
                    <x-rows.small-lockup :games="$this->models" :shows-schedule="true" :is-row="false" />
                    @break
                @case(\App\Models\Manga::class)
                    <x-rows.small-lockup :mangas="$this->models" :shows-schedule="true" :is-row="false" />
                    @break
            @endswitch
        @elseif (!$readyToLoad)
            <x-section-nav>
                <x-slot:title>
                    {{ $date->format('l') . ' ' . $date->format('M d') }}
                </x-slot:title>

                <x-slot:action>
                    <x-spinner />

                    @hasrole('superAdmin')
                        <x-button wire:click="$refresh">{{ __('Refresh') }}</x-button>
                    @endhasrole
                </x-slot:action>
            </x-section-nav>

            <div class="flex gap-4 justify-between flex-wrap">
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                <div class="w-64 md:w-80 flex-grow"></div>
                <div class="w-64 md:w-80 flex-grow"></div>
            </div>
        @endif
    </section>
</div>
