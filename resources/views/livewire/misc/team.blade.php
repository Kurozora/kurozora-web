<main>
    <x-slot:title>
        {{ __('Team') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('The amazing people behind :x — the largest, free online anime, manga, game & music database.', ['x' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Team') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('The amazing people behind :x — the largest, free online anime, manga, game & music database.', ['x' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('misc.team') }}">
    </x-slot:meta>

    <div class="pt-4 pb-6">
        <x-picture class="ml-4 mr-4">
            <img class="h-32 w-full object-cover object-center rounded-lg shadow-lg sm:h-60" src="{{ asset('images/static/banners/made_with_love.webp') }}" alt="Made with love by an amazing community." />
        </x-picture>

        {{-- Staff --}}
        <section class="flex flex-col mt-36">
            <h2 class="mx-auto pl-4 pr-4 max-w-2xl text-4xl text-center font-semibold">{{ __(':x is built by a passionate team…', ['x' => config('app.name')]) }}</h2>

            <div class="flex flex-row flex-wrap justify-between gap-4 mt-36 pl-4 pr-4">
                @foreach ($this->staff as $user)
                    @php
                        switch ($user->id) {
                        case 2:
                            $backgroundColor = '#1F2937';
                            $usernameColor = '#F9F9F9';
                            $text = 'Owner/Developer';
                            break;
                        case 461:
                            $backgroundColor = '#C63E10';
                            $usernameColor = '#002855';
                            $text = 'Artisan Ace';
                            break;
                        case 668:
                            $backgroundColor = '#597B79';
                            $usernameColor = '#730303';
                            $text = 'Queen of Kurozora (VTuber)';
                            break;
                        case 380:
                            $backgroundColor = '#F1C9BF';
                            $usernameColor = '#E8288F';
                            $text = 'Event Manager';
                            break;
                        case 1110:
                            $backgroundColor = '#6229CC';
                            $usernameColor = '#C9EEFF';
                            $text = 'Linux/Windows Developer';
                            break;
                        default:
                            $backgroundColor = '#FFFFFF';
                            $usernameColor = '#1F2937';
                            $text = '';
                        }
                    @endphp

                    <div class="relative flex-grow w-64 md:w-80">
                        <a class="block pt-6 pb-6 pl-6 pr-6 text-center rounded-lg shadow-lg" style="background: url({{ asset('images/static/patterns/genre_pattern.svg') }}) no-repeat center center / cover, url({{ asset('images/static/patterns/grain.svg') }}), {{ $backgroundColor }};" href="{{ route('profile.details', $user) }}">
                            <div class="flex justify-center mb-3">
                                <picture class="relative w-40 h-40 rounded-full shadow-lg overflow-hidden">
                                    <img class="w-full h-full object-cover" width="160" height="160" src="{{ $user->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) }}" alt="{{ $user->username }} Profile Image" title="{{ $user->username }}">

                                    <div class="absolute top-0 left-0 h-full w-full border-4 border-solid border-black/20 rounded-full"></div>
                                </picture>
                            </div>

                            <h2 class="text-xl font-medium" style="color: {{ $usernameColor }};">{{ $user->username }}</h2>

                            <span class="block mb-5" style="color: {{ $usernameColor }};">{{ $text }}</span>
                        </a>
                    </div>
                @endforeach

                <div class="w-64 md:w-80 flex-grow"></div>
                <div class="w-64 md:w-80 flex-grow"></div>
                <div class="w-64 md:w-80 flex-grow"></div>
            </div>
        </section>

        {{-- Ex-Staff --}}
        <section class="flex flex-col mt-36">
            <h2 class="mx-auto pl-4 pr-4 max-w-2xl text-4xl text-center font-semibold">{{ __('Shaped by past contributors…') }}</h2>

            <div class="flex flex-row flex-wrap justify-between gap-4 mt-36 pl-4 pr-4">
                @foreach ($this->exStaff as $user)
                    @php
                        switch ($user->id) {
                        case 1:
                            $backgroundColor = '#C1C1C1';
                            $usernameColor = '#1F2937';
                            $text = 'Co-Developer';
                            break;
                        default:
                            $backgroundColor = '#FFFFFF';
                            $usernameColor = '#1F2937';
                            $text = '';
                        }
                    @endphp

                    <div class="relative flex-grow w-64 md:w-80">
                        <a class="block pt-6 pb-6 pl-6 pr-6 text-center rounded-lg shadow-lg" style="background: url({{ asset('images/static/patterns/genre_pattern.svg') }}) no-repeat center center / cover, url({{ asset('images/static/patterns/grain.svg') }}), {{ $backgroundColor }};" href="{{ route('profile.details', $user) }}">
                            <div class="flex justify-center mb-3">
                                <picture class="relative w-40 h-40 rounded-full shadow-lg overflow-hidden">
                                    <img class="w-full h-full object-cover" width="160" height="160" src="{{ $user->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) }}" alt="{{ $user->username }} Profile Image" title="{{ $user->username }}">

                                    <div class="absolute top-0 left-0 h-full w-full border-4 border-solid border-black/20 rounded-full"></div>
                                </picture>
                            </div>

                            <h2 class="text-xl font-medium" style="color: {{ $usernameColor }};">{{ $user->username }}</h2>

                            <span class="block mb-5" style="color: {{ $usernameColor }};">{{ $text }}</span>
                        </a>
                    </div>
                @endforeach

                <div class="w-64 md:w-80 flex-grow"></div>
                <div class="w-64 md:w-80 flex-grow"></div>
                <div class="w-64 md:w-80 flex-grow"></div>
            </div>
        </section>

        {{-- You --}}
        <section class="flex flex-col mt-36">
            <h2 class="mx-auto pl-4 pr-4 max-w-2xl text-4xl text-center font-semibold">{{ __('And growing with :x amazing members—including you ❤️', ['x' => $this->userCount]) }}</h2>

            <div class="mt-36 pl-4 pr-4">
                @php
                    $user = auth()->user();
                    $href = $user ? route('profile.details', $user) : route('sign-up');
                @endphp

                <a class="block pt-6 pb-6 pl-6 pr-6 text-center rounded-lg shadow-lg"
                     style="background: url({{ asset('images/static/patterns/genre_pattern.svg') }}) no-repeat center center / cover, url({{ asset('images/static/patterns/grain.svg') }}), var(--tint-color);" href="{{ $href }}">
                    <div class="flex justify-center mb-3">
                        <picture class="relative w-40 h-40 rounded-full shadow-lg overflow-hidden">
                            <img class="w-full h-full object-cover" width="160" height="160" src="{{ $user?->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/user_profile.webp') }}" alt="{{ $user?->username ?? __('Guest') }} Profile Image" title="{{ $user?->username ?? __('Guest Profile') }}">

                            <div class="absolute top-0 left-0 h-full w-full border-4 border-solid border-black/20 rounded-full"></div>
                        </picture>
                    </div>

                    <h2 class="text-xl btn-text-tinted font-medium">{{ $user?->username ?? __('You') }}</h2>

                    <span class="block mb-5 btn-text-tinted">{{ __('Community Member') }}</span>

                    @guest
                        <x-button>
                            {{ __('Create your List') }} 🤗
                        </x-button>
                    @endguest
                </a>
            </div>
        </section>
    </div>
</main>
