<div>
    <section id="#{{ $mediaType->name }}" class="pt-5 pb-8 px-4 border-t-2" wire:init="getAnimeForMediaType">
        <x-section-nav>
            <x-slot:title>
                {{ $mediaType->name . ' (' . $animes->count() . ')' }}
                </x-slot>
        </x-section-nav>

        <div class="flex justify-center">
            <x-spinner />
        </div>

        <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
            @foreach($animes as $anime)
                <x-lockups.small-lockup :anime="$anime" :is-row="false" />
            @endforeach
        </div>
    </section>
</div>
