<main>
    <x-slot name="title">
        {!! $character->name !!}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="{{ $character->name }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $character->synopsis }}" />
        <meta property="og:image" content="{{ $character->profile_image_url ?? asset('images/static/placeholders/person_poster.webp') }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $character->name }}" />
    </x-slot>

    <x-slot name="appArgument">
        character/{{ $character->id }}
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6">
        <section class="pt-5 pb-8">
            <div class="relative pb-2">
                <div class="flex flex-col flex-wrap text-center items-center">
                    <picture class="relative min-w-[128px] max-w-[128px] min-h-[128px] max-h-[128px] rounded-full overflow-hidden">
                        <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $character->profile_image_url ?? asset('images/static/placeholders/character_profile.webp') }}" alt="{{ $character->name }} Profile" title="{{ $character->name }}">

                        <div class="absolute top-0 left-0 h-full w-full border-2 border-solid border-black/20 rounded-full"></div>
                    </picture>
                    <p class="text-3xl font-bold">{{ $character->name }}</p>
                </div>
            </div>
        </section>

        <section class="pt-5 pb-8 border-t-2">
            <x-section-nav>
                <x-slot name="title">
                    {{ __('About') }}
                </x-slot>
            </x-section-nav>

            <x-truncated-text>
                <x-slot name="text">
                    {!! nl2br($character->about) !!}
                </x-slot>
            </x-truncated-text>
        </section>

        <section class="pt-5 pb-8 border-t-2">
            <x-section-nav>
                <x-slot name="title">
                    {{ __('Information') }}
                </x-slot>
            </x-section-nav>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-x-4 gap-y-4">
                <x-information-list id="debut" title="{{ __('Debut') }}" icon="{{ asset('images/symbols/star.svg') }}">
                    <x-slot name="information">
                        {{ $character->debut ?? '-' }}
                    </x-slot>

                    <x-slot name="footer">
                        {{ __('The character is :x.', ['x' => $character->status]) }}
                    </x-slot>
                </x-information-list>

                <x-information-list id="age" title="{{ __('Age') }}" icon="{{ asset('images/symbols/calendar.svg') }}">
                    <x-slot name="information">
                        {{ $character->age_string ?? '-' }}
                    </x-slot>

                    <x-slot name="footer">
                        {{ $character->birthdate . ($character->astrological_sign_string ? ', ' . $character->astrological_sign_string : '') }}
                    </x-slot>
                </x-information-list>

                <x-information-list id="measurements" title="{{ __('Measurements') }}" icon="{{ asset('images/symbols/ruler.svg') }}">
                    <x-slot name="information">
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
                    </x-slot>
                </x-information-list>

                <x-information-list id="characteristics" title="{{ __('Characteristics') }}" icon="{{ asset('images/symbols/list_bullet_rectangle.svg') }}">
                    <x-slot name="information">
                        @if ($character->blood_type)
                            {{ __('Blood Type: :x', ['x' => $character->blood_type]) }} <br/>
                        @endif

                        @if ($character->favorite_food)
                            {{ __('Favorite Food: :x', ['x' => $character->favorite_food]) }} <br/>
                        @endif
                    </x-slot>
                </x-information-list>
            </div>
        </section>

        @if (!empty($characterAnime->total()))
            <section class="pt-5 pb-8 border-t-2">
                <x-section-nav>
                    <x-slot name="title">
                        {{ __('Shows') }}
                    </x-slot>

                    <x-slot name="action">
                        <x-simple-link href="{{ route('characters.anime', $character) }}">{{ __('See All') }}</x-simple-link>
                    </x-slot>
                </x-section-nav>

                <div class="flex mt-5 overflow-x-scroll no-scrollbar">
                    <div class="flex flex-row flex-nowrap gap-4">
                        @foreach($characterAnime as $anime)
                            <x-lockups.small-lockup :anime="$anime" />
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        @if (!empty($characterPeople->total()))
            <section class="pt-5 pb-8 border-t-2">
                <x-section-nav>
                    <x-slot name="title">
                        {{ __('People') }}
                    </x-slot>

                    <x-slot name="action">
                        <x-simple-link href="{{ route('characters.people', $character) }}">{{ __('See All') }}</x-simple-link>
                    </x-slot>
                </x-section-nav>

                <div class="flex mt-5 overflow-x-scroll no-scrollbar">
                    <div class="flex flex-row flex-nowrap gap-4">
                        @foreach($characterPeople as $person)
                            <x-lockups.person-lockup :person="$person" />
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </div>
</main>