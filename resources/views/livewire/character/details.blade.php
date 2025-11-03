<main>
    <x-slot:title>
        {!! $character->name !!}
    </x-slot:title>

    <x-slot:description>
        {{ $character->synopsis }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ $character->name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $character->synopsis ?? __('app.description') }}" />
        <meta property="og:image" content="{{ $character->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/person_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $character->name }}" />
        <link rel="canonical" href="{{ route('characters.details', $character) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        characters/{{ $character->id }}
    </x-slot:appArgument>

    <div class="pt-4 pb-6" wire:init="loadPage">
        <section class="pt-4 pb-8 pl-4 pr-4">
            <div class="relative">
                <div class="flex flex-col flex-wrap text-center items-center">
                    <picture
                        class="relative aspect-square rounded-full overflow-hidden"
                        style="height: 128px; background-color: {{ $character->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
                    >
                        <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $character->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/person_poster.webp') }}" alt="{{ $character->name }} Profile Image" title="{{ $character->name }}">

                        <div class="absolute top-0 left-0 h-full w-full border-2 border-solid border-black/20 rounded-full"></div>
                    </picture>

                    <div class="flex gap-2">
                        <p class="text-3xl font-bold">{{ $character->name }}</p>

                        <x-nova-link :href="route('characters.edit', $character)">
                            @svg('pencil', 'fill-current', ['width' => '44'])
                        </x-nova-link>
                    </div>
                </div>
            </div>
        </section>

        @if (!empty($character->about))
            <section class="pb-8">
                <x-section-nav class="pt-4">
                    <x-slot:title>
                        {{ __('About') }}
                    </x-slot:title>
                </x-section-nav>

                <x-truncated-text class="max-w-7xl ml-4 mr-4">
                    <x-slot:text>
                        {!! nl2br(e($character->about)) !!}
                    </x-slot:text>
                </x-truncated-text>
            </section>
        @endif

        <section id="ratingsAndReviews" class="pb-8">
            <x-section-nav class="pt-4">
                <x-slot:title>
                    {{ __('Ratings & Reviews') }}
                </x-slot:title>

                <x-slot:action>
                    <x-section-nav-link href="{{ route('characters.reviews', $character) }}">{{ __('See All') }}</x-section-nav-link>
                </x-slot:action>
            </x-section-nav>

            <div class="flex flex-row flex-wrap justify-between gap-4 pl-4 pr-4">
                <div class="flex flex-col justify-end text-center">
                    <p class="font-bold text-6xl">{{ number_format($character->mediaStat->rating_average, 1) }}</p>
                    <p class="font-bold text-sm text-secondary">{{ __('out of') }} 5</p>
                </div>

                <div class="flex flex-col justify-end items-center text-center">
                    @svg('star_fill', 'fill-current', ['width' => 32])
                    <p class="font-bold text-2xl">{{ number_format($character->mediaStat->highestRatingPercentage) }}%</p>
                    <p class="text-sm text-secondary">{{ $character->mediaStat->sentiment }}</p>
                </div>

                <div class="flex flex-col w-full justify-end text-right sm:w-auto">
                    <x-star-rating-bar :media-stat="$character->mediaStat" />

                    <p class="text-sm text-secondary">{{ trans_choice('[0,1] Not enough ratings|[2,*] :x Ratings', $character->mediaStat->rating_count, ['x' => number_format($character->mediaStat->rating_count)]) }}</p>
                </div>
            </div>
        </section>

        <section id="writeAReview" class="pb-8">
            <x-hr class="ml-4 mr-4 pb-5" />

            <div class="flex flex-row flex-wrap gap-4 pl-4 pr-4">
                <div class="flex justify-between items-center">
                    <p class="">{{ __('Click to Rate:') }}</p>

                    <livewire:components.star-rating :model-id="$character->id" :model-type="$character->getMorphClass()" :rating="$userRating->first()?->rating" :star-size="'md'" />
                </div>

                <div class="flex justify-between">
                    <x-simple-button class="flex gap-1" wire:click="$dispatch('show-review-box', { 'id': '{{ $this->reviewBoxID }}' })">
                        @svg('pencil', 'fill-current', ['width' => 18])
                        {{ __('Write a Review') }}
                    </x-simple-button>
                </div>

                <div></div>
            </div>

            <div class="mt-5">
                <livewire:sections.reviews :model="$character" />
            </div>
        </section>

        <section class="pb-8">
            <x-section-nav class="pt-4">
                <x-slot:title>
                    {{ __('Information') }}
                </x-slot:title>
            </x-section-nav>

            <div class="grid grid-cols-2 gap-4 pl-4 pr-4 sm:grid-cols-3 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                <x-information-list id="debut" title="{{ __('Debut') }}" icon="{{ asset('images/symbols/star.svg') }}">
                    <x-slot:information>
                        {{ $character->debut ?? '-' }}
                    </x-slot:information>

                    @if ($character->status)
                        <x-slot:footer>
                            @if ($character->status != \App\Enums\CharacterStatus::Unknown())
                                {{ __('The character is :x.', ['x' => strtolower($character->status->description)]) }}
                            @endif
                        </x-slot:footer>
                    @endif
                </x-information-list>

                <x-information-list id="age" title="{{ __('Age') }}" icon="{{ asset('images/symbols/calendar.svg') }}">
                    <x-slot:information>
                        {{ $character->age_string ?? '-' }}
                    </x-slot:information>

                    <x-slot:footer>
                        {{ $character->birthdate . ($character->astrological_sign?->description ? ', ' . $character->astrological_sign?->description : '') }}
                    </x-slot:footer>
                </x-information-list>

                <x-information-list id="measurements" title="{{ __('Measurements') }}" icon="{{ asset('images/symbols/ruler.svg') }}">
                    <x-slot:information>
                        @if ($character->height_string )
                        {{ __('Height: :x', ['x' => $character->height_string]) }} <br />
                        @endif

                        @if ($character->weight_string)
                            {{ __('Weight: :x', ['x' => $character->weight_string]) }} <br />
                        @endif

                        @if ($character->bust)
                            {{ __('Bust: :x', ['x' => $character->bust]) }} <br />
                        @endif

                        @if ($character->waist)
                            {{ __('Waist: :x', ['x' => $character->waist]) }} <br />
                        @endif

                        @if ($character->hip)
                            {{ __('Hip: :x', ['x' => $character->hip]) }}
                        @endif
                    </x-slot:information>
                </x-information-list>

                <x-information-list id="characteristics" title="{{ __('Characteristics') }}" icon="{{ asset('images/symbols/list_bullet_rectangle.svg') }}">
                    <x-slot:information>
                        @if ($character->blood_type)
                            {{ __('Blood Type: :x', ['x' => $character->blood_type]) }} <br/>
                        @endif

                        @if ($character->favorite_food)
                            {{ __('Favorite Food: :x', ['x' => $character->favorite_food]) }} <br/>
                        @endif
                    </x-slot:information>
                </x-information-list>
            </div>
        </section>

        @if ($readyToLoad)
            <livewire:components.character.media-section :character="$character" :type="\App\Models\Anime::class" />

            <livewire:components.character.media-section :character="$character" :type="\App\Models\Manga::class" />

            <livewire:components.character.media-section :character="$character" :type="\App\Models\Game::class" />

            <livewire:components.character.media-section :character="$character" :type="\App\Models\Person::class" />
        @else
            <x-skeletons.small-lockup />
            <x-skeletons.small-lockup />
            <x-skeletons.small-lockup />
            <x-skeletons.small-lockup />
        @endif
    </div>

    <livewire:components.review-box :review-box-id="$reviewBoxID" :model-id="$character->id" :model-type="$character->getMorphClass()" :user-rating="$userRating?->first()" />
</main>
