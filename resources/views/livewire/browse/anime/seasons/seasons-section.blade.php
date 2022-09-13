<div>
    <section id="#{{ $mediaType->name }}" class="pt-5 pb-8 pl-4 pr-4 border-t-2" wire:init="getAnimeForMediaType">
        <x-section-nav>
            <x-slot:title>
                {{ $mediaType->name . ' (' . $animes->count() . ')' }}
            </x-slot:title>
        </x-section-nav>

        <div class="flex justify-center">
            <x-spinner />
        </div>

        <x-rows.small-lockup :animes="$animes" :is-row="false" />
    </section>
</div>
