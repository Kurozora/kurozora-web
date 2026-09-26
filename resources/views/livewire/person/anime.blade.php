<main>
    <x-slot:title>
       Anime | {!! $person->full_name !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('Discover the extensive list of anime :x has worked on only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $person->full_name, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="Anime | {{ $person->full_name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Discover the extensive list of anime :x has worked on only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $person->full_name, 'y' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ $person->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/person_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $person->full_name }}" />
        <link rel="canonical" href="{{ route('people.anime', $person) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        people/{{ $person->id }}/shows
    </x-slot:appArgument>

    <div class="pb-6">
        <x-back-link
            :url="route('people.details', $person)"
            :label="$person->full_name"
            :title="__(':x’s Anime', ['x' => $person->full_name])"
        />

        <section class="xl:safe-area-inset">
            <x-rows.small-lockup :animes="$this->titles" :is-row="false" />

            <div class="mt-4 pl-4 pr-4">
                {{ $this->titles->links() }}
            </div>
        </section>
    </div>
</main>
