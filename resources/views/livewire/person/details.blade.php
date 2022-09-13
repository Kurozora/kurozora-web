<main>
    <x-slot:title>
        {!! $person->full_name !!}
    </x-slot:title>

    <x-slot:description>
        {{ $person->about }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ $person->full_name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $person->about ?? __('app.description') }}" />
        <meta property="og:image" content="{{ $person->profile_image_url ?? asset('images/static/placeholders/person_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $person->full_name }}" />
        <link rel="canonical" href="{{ route('people.details', $person) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        person/{{ $person->id }}
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
        <section class="pt-5 pb-8">
            <div class="relative pb-2">
                <div class="flex flex-col flex-wrap text-center items-center">
                    <picture class="relative min-w-[128px] max-w-[128px] min-h-[128px] max-h-[128px] rounded-full overflow-hidden">
                        <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $person->profile_image_url ?? asset('images/static/placeholders/person_poster_square.webp') }}" alt="{{ $person->full_name }} Profile Image" title="{{ $person->full_name }}">

                        <div class="absolute top-0 left-0 h-full w-full border-2 border-solid border-black/20 rounded-full"></div>
                    </picture>

                    <div class="flex gap-2">
                        <p class="text-3xl font-bold">{{ $person->full_name }}</p>

                        <x-nova-link :resource="\App\Nova\Person::class" :model="$person">
                            @svg('pencil', 'fill-current', ['width' => '44'])
                        </x-nova-link>
                    </div>
                </div>
            </div>
        </section>

        @if ($person->about)
            <section class="pt-5 pb-8 border-t-2">
                <x-section-nav>
                    <x-slot:title>
                        {{ __('About') }}
                    </x-slot:title>
                </x-section-nav>

                <x-truncated-text>
                    <x-slot:text>
                        {!! nl2br(e($person->about)) !!}
                    </x-slot:text>
                </x-truncated-text>
            </section>
        @endif

        <section class="pt-5 pb-8 border-t-2">
            <x-section-nav>
                <x-slot:title>
                    {{ __('Information') }}
                </x-slot:title>
            </x-section-nav>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4">
                <x-information-list id="aliases" title="{{ __('Aliases') }}" icon="{{ asset('images/symbols/person.svg') }}">
                    <x-slot:information>
                        @if (!empty($person->full_name))
                            {{ __('Name: :x', ['x' => $person->full_name]) }} <br />
                        @endif

                        @if (!empty($person->full_given_name))
                        {{ __('Given name: :x', ['x' => $person->full_given_name]) }} <br />
                        @endif

                        @if (count(array_filter((array)$person->alternative_names)))
                            {{ __('Nicknames: :x', ['x' => collect(array_filter((array)$person->alternative_names))->join(',  ', ' and ')]) }} <br />
                        @endif
                    </x-slot:information>
                </x-information-list>

                <x-information-list id="age" title="{{ __('Age') }}" icon="{{ asset('images/symbols/calendar.svg') }}">
                    <x-slot:information>
                        {{ $person->age_string ?? '-' }}
                    </x-slot:information>

                    <x-slot:footer>
                        {{ $person->birthdate?->format('d F Y') }} {{ $person->astrological_sign_string }}
                    </x-slot:footer>
                </x-information-list>

                <x-information-list id="website" title="{{ __('Websites') }}" icon="{{ asset('images/symbols/safari.svg') }}">
                    <x-slot:information>
                        @if (!empty($person->website_urls))
                            <ul class="list-disc">
                                @foreach($person->website_urls as $website_url)
                                    <li>
                                        <x-link href="{{ $website_url }}" target="_blank">
                                            {{ str_ireplace('www.', '', parse_url($website_url, PHP_URL_HOST)) ?? $website_url }}
                                        </x-link>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            -
                        @endif
                    </x-slot:information>
                </x-information-list>
            </div>
        </section>

        @if (!empty($personAnime->total()))
            <section id="personAnime" class="pt-5 pb-8 border-t-2">
                <x-section-nav>
                    <x-slot:title>
                        {{ __('Shows') }}
                    </x-slot:title>

                    <x-slot:action>
                        <x-section-nav-link href="{{ route('people.anime', $person) }}">{{ __('See All') }}</x-section-nav-link>
                    </x-slot:action>
                </x-section-nav>

                <x-rows.small-lockup :animes="$personAnime" />
            </section>
        @endif

        @if (!empty($personCharacters->total()))
            <section id="personCharacters" class="pt-5 pb-8 border-t-2">
                <x-section-nav>
                    <x-slot:title>
                        {{ __('Characters') }}
                    </x-slot:title>

                    <x-slot:action>
                        <x-section-nav-link href="{{ route('people.characters', $person) }}">{{ __('See All') }}</x-section-nav-link>
                    </x-slot:action>
                </x-section-nav>

                <x-rows.character-lockup :characters="$personCharacters" />
            </section>
        @endif
    </div>
</main>
