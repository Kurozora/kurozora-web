<div wire:init="loadSection">
    <section id="#{{ $mediaType->name }}" class="pb-8">
        @if ($this->models->count())
            <x-section-nav class="pt-4">
                <x-slot:title>
                    {{ $mediaType->name . ' (' . $this->models->count() . ')' }}
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
                    <x-rows.small-lockup :animes="$this->models" :is-row="false" />
                    @break
                @case(\App\Models\Game::class)
                    <x-rows.small-lockup :games="$this->models" :is-row="false" />
                    @break
                @case(\App\Models\Manga::class)
                    <x-rows.small-lockup :mangas="$this->models" :is-row="false" />
                    @break
            @endswitch
        @elseif (!$readyToLoad)
            <x-section-nav class="pt-4">
                <x-slot:title>
                    {{ $mediaType->name }}
                </x-slot:title>

                <x-slot:action>
                    <x-spinner />

                    @hasrole('superAdmin')
                        <x-button wire:click="$refresh">{{ __('Refresh') }}</x-button>
                    @endhasrole
                </x-slot:action>
            </x-section-nav>

            <x-skeletons.lockup-row :is-row="false" :count="9" />
        @endif
    </section>
</div>
