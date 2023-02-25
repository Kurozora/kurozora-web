<div>
    <section id="#{{ $mediaType->name }}" class="pt-5 pb-8 pl-4 pr-4 border-t-2" wire:init="getGameForMediaType">
        <x-section-nav>
            <x-slot:title>
                {{ $mediaType->name . ' (' . $games->count() . ')' }}
            </x-slot:title>
        </x-section-nav>

        <div class="flex justify-center">
            <x-spinner />
        </div>

        <x-rows.small-lockup :games="$games" :is-row="false" />
    </section>
</div>
