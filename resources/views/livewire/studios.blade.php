<main>
    <x-slot:title>
        {{ __('Studios') }} | {!! $this->parent->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __('An extensive list of :x studios only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $this->parent->title, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Studios') }} | {{ $this->parent->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('An extensive list of :x studios only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $this->parent->title, 'y' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ $this->parent->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/' . $this->ogImagePoster) }}" />
        <meta property="og:type" content="{{ $this->ogType }}" />
        @switch ($kind)
            @case (\App\Enums\UserLibraryKind::Anime)
                <meta property="video:duration" content="{{ $this->parent->duration }}" />
                <meta property="video:release_date" content="{{ $this->parent->started_at?->toIso8601String() }}" />
                @break
            @case (\App\Enums\UserLibraryKind::Manga)
                <meta property="book:release_date" content="{{ $this->parent->started_at?->toIso8601String() }}" />
                @foreach ($this->parent->tags() as $tag)
                    <meta property="book:tag" content="{{ $tag->name }}" />
                @endforeach
                @break
            @case (\App\Enums\UserLibraryKind::Game)
                <meta property="video:duration" content="{{ $this->parent->duration }}" />
                <meta property="video:release_date" content="{{ $this->parent->published_at?->toIso8601String() }}" />
                @break
        @endswitch
        <link rel="canonical" href="{{ $this->canonicalUrl }}">
    </x-slot:meta>

    <x-slot:appArgument>
        {{ $this->appArgumentSegment }}/{{ $this->parent->id }}/studios
    </x-slot:appArgument>

    <div class="pt-4 pb-6" wire:init="loadPage">
        <section class="mb-4 xl:safe-area-inset">
            <div>
                <div class="flex gap-1 pl-4 pr-4">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __(':x’s Studios', ['x' => $this->parent->title]) }}</h1>
                    </div>

                    <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                    </div>
                </div>
            </div>
        </section>

        @if ($this->studios->count())
            <section class="xl:safe-area-inset">
                <x-rows.studio-lockup :studios="$this->studios" :is-row="false" />

                <div class="mt-4 pl-4 pr-4">
                    {{ $this->studios->links() }}
                </div>
            </section>
        @elseif (!$readyToLoad)
            <section class="xl:safe-area-inset">
                <div class="flex flex-wrap gap-4 justify-between pl-4 pr-4">
                    @foreach (range(1,25) as $range)
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                    @endforeach
                    <div class="w-64 md:w-80 flex-grow"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                </div>
            </section>
        @endif
    </div>
</main>
