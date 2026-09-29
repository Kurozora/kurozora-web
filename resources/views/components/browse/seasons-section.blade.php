<section id="#{{ $mediaType->name }}" class="pb-8">
    <x-section-nav class="pt-4">
        <x-slot:title>
            {{ $mediaType->name . ' (' . $models->count() . ')' }}
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
            <x-rows.small-lockup :animes="$models" :is-row="false" />
            @break
        @case(\App\Models\Game::class)
            <x-rows.small-lockup :games="$models" :is-row="false" />
            @break
        @case(\App\Models\Manga::class)
            <x-rows.small-lockup :mangas="$models" :is-row="false" />
            @break
    @endswitch
</section>
