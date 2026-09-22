<main>
    <x-slot:title>
        {{ __('People') }} | {!! $character->name !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover the list of voice actors that played :x only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $character->name, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('People') }} | {{ $character->name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover the list of voice actors that played :x only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $character->name, 'y' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ $character->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/character_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $character->name }}" />
        <link rel="canonical" href="{{ route('characters.people', $character) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        characters/{{ $character->id }}/people
    </x-slot:appArgument>

    <div class="pb-6" wire:init="loadPage">
        <x-back-link
            :url="route('characters.details', $character)"
            :label="$character->name"
            :title="__(':x’s Voice Actors', ['x' => $character->name])"
        />

        @if ($readyToLoad)
            <section class="xl:safe-area-inset">
                <x-rows.person-lockup :people="$this->people" :is-row="false" />

                <div class="mt-4 pl-4 pr-4">
                    {{ $this->people->links() }}
                </div>
            </section>
        @else
            <section class="xl:safe-area-inset">
                <x-skeletons.lockup-row lockup="person" :is-row="false" />
            </section>
        @endif
    </div>
</main>
