<main>
    <x-slot:title>
        Games | {!! $person->full_name !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover the extensive list of games :x has worked on only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $person->full_name, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="Games | {{ $person->full_name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover the extensive list of games :x has worked on only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $person->full_name, 'y' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ $person->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/person_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $person->full_name }}" />
        <link rel="canonical" href="{{ route('people.games', $person) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        people/{{ $person->id }}/games
    </x-slot:appArgument>

    <div class="pb-6" wire:init="loadPage">
        <x-back-link
            :url="route('people.details', $person)"
            :label="$person->full_name"
            :title="__(':x’s Games', ['x' => $person->full_name])"
        />

        @if ($readyToLoad)
            <section class="xl:safe-area-inset">
                <x-rows.small-lockup :games="$this->titles" :is-row="false" />

                <div class="mt-4 pl-4 pr-4">
                    {{ $this->titles->links() }}
                </div>
            </section>
        @else
            <section class="mt-4 pt-4 pb-8 border-t border-primary">
                <x-skeletons.lockup-row :kind="\App\Enums\UserLibraryKind::Game" :is-row="false" />
            </section>
        @endif
    </div>
</main>
