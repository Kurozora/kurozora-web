<main>
    <x-slot:title>
        Games | {!! $studio->name !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover all of the latest anime, movies, specials, OVA and ONA by :x only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $studio->name, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="Games | {{ $studio->name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover all of the latest anime, movies, specials, OVA and ONA by :x on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $studio->name, 'y' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ $studio->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/person_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $studio->name }}" />
        <link rel="canonical" href="{{ route('studios.games', $studio) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        studios/{{ $studio->id }}/games
    </x-slot:appArgument>

    <div class="pb-6" wire:init="loadPage">
        <x-back-link
            :url="route('studios.details', $studio)"
            :label="$studio->name"
            :title="__(':x’s Games', ['x' => $studio->name])"
        />

        @if ($readyToLoad)
            <section class="xl:safe-area-inset">
                <x-rows.small-lockup :games="$this->titles" :is-row="false" />

                <div class="mt-4 pl-4 pr-4">
                    {{ $this->titles->links() }}
                </div>
            </section>
        @else
            <section class="xl:safe-area-inset">
                <x-skeletons.lockup-row :kind="\App\Enums\UserLibraryKind::Game" :is-row="false" />
            </section>
        @endif
    </div>
</main>
