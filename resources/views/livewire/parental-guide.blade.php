<main>
    <x-slot:title>
        {{ __('Parents Guide') }} | {!! $this->parent->title !!}
    </x-slot:title>

    <x-slot:description>
        {{ __(':x parental guide on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $this->parent->title, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Parents Guide') }} | {{ $this->parent->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __(':x parental guide on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $this->parent->title, 'y' => config('app.name')]) }}" />
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
                <meta property="video:release_date" content="{{ $this->parent->started_at?->toIso8601String() }}" />
                @break
        @endswitch
        <link rel="canonical" href="{{ $this->canonicalUrl }}">
    </x-slot:meta>

    <x-slot:appArgument>
        {{ $this->appArgumentSegment }}/{{ $this->parent->id }}/parentalguide
    </x-slot:appArgument>

    <div class="pb-6">
        <section class="sticky top-0 mb-4 pt-4 pb-4 backdrop-blur bg-blur z-10 xl:safe-area-inset">
            <div>
                <div class="flex gap-1 pl-4 pr-4">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __(':x’s Parents Guide', ['x' => $this->parent->title]) }}</h1>
                    </div>

                    <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                        <x-button wire:click="openSubmitForm">{{ __('Add') }}</x-button>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-16 xl:safe-area-inset">
            <div class="flex flex-col gap-4 pb-6 pl-4 pr-4">
                <h3 class="text-xl font-bold">{{ __('Summary') }}</h3>

                <div class="w-full max-w-prose bg-secondary rounded-md pt-4 pb-4 pl-4 pr-4">
                    <ul class="m-0 space-y-4 list-none">
                        <li>
                            <div class="flex gap-1 items-center">
                                <h4 class="font-bold">{{ __('Rating') }}:</h4>

                                <p class="text-secondary">{{ $this->parent->tv_rating->name }} ({{ $this->parent->tv_rating->description }})</p>
                            </div>
                        </li>

                        @foreach (App\Enums\ParentalGuideCategory::getInstances() as $category)
                            <li>
                                <a href="#{{ $category->urlSlug() }}" class="flex gap-1 items-center">
                                    <h4 class="font-bold">{{ $category->description }}:</h4>
                                    <p class="text-secondary">{{ $this->parent->parental_guide_stat->getAverageRating($category)->description }}</p>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </section>

        <section class="mb-4 xl:safe-area-inset">
            <div class="flex flex-col gap-6">
                @foreach (App\Enums\ParentalGuideCategory::getInstances() as $category)
                    @php
                        $categorySlug = $category->urlSlug();
                        $averageRating = $this->parent->parental_guide_stat->getAverageRating($category);
                        [$averageRatingCount, $totalRatingCount] = $this->parent->parental_guide_stat->getAverageRatingCount($category);
                    @endphp

                    <div id="{{ $categorySlug }}" class="flex flex-col gap-4 pb-6" style="scroll-margin-top: 4rem;">
                        <x-section-nav class="!mb-0">
                            <x-slot:title>
                                <a href="#{{ $categorySlug }}">{{ $category->description }}</a>
                            </x-slot:title>

                            @if($averageRatingCount !== 0 && $totalRatingCount !== 0)
                                <x-slot:description>
                                    {{ trans_choice('{0} :x of :y found this to have :z|[1,*] :x of :y found this :z', $averageRating->value, ['x' => $averageRatingCount, 'y' => $totalRatingCount, 'z' => strtolower($averageRating->description)]) }}
                                </x-slot:description>
                            @endif

                            <x-slot:action>
                                <x-section-nav-link href="{{ $this->categoryRoute($categorySlug) }}">{{ __('See All') }}</x-section-nav-link>
                            </x-slot:action>
                        </x-section-nav>

                        @if ($this->parentalGuideEntries->has($category->value))
                            <div class="flex flex-wrap gap-4 pl-4 pr-4">
                                @foreach ($this->parentalGuideEntries->get($category->value)->take(5) as $entry)
                                    @include('livewire.components.parental-guide.entry-lockup', [
                                        'entry' => $entry,
                                    ])
                                @endforeach
                            </div>
                        @else
                            <button type="button" class="bg-secondary rounded-md p-4 ml-4 mr-4 text-left w-full max-w-prose" wire:click="openSubmitForm({{ $category->value }})">
                                <span class="text-tint underline">{{ __('It looks like we don’t have an evaluation for this category yet.') }}</span>
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    @include('livewire.components.parental-guide.submit-form')
    @include('livewire.components.parental-guide.report-form')

</main>
