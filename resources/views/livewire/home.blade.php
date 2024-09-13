<main>
    <x-slot:title>
        {{ __('Explore') }}
    </x-slot:title>

    <x-slot:meta>
        <meta property="og:title" content="{{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('app.description') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('home') }}">
    </x-slot:meta>

    <x-slot:appArgument>
        explore
    </x-slot:appArgument>

    <div>
{{--        <section>--}}
{{--            <a href="{{ route('recap.index') }}" wire:navigate>--}}
{{--                <x-picture>--}}
{{--                    <img class="w-full object-cover h-40 rounded-lg shadow md:h-80" src="{{ asset('images/static/banners/kurozora_recap_2023.webp') }}" alt="Kurozora Recap 2023">--}}
{{--                </x-picture>--}}
{{--            </a>--}}
{{--        </section>--}}

{{--        <section>--}}
{{--            <x-picture>--}}
{{--                <img class="w-full object-cover h-32 rounded-lg shadow sm:h-40 md:h-80" src="{{ asset('images/static/banners/games_now_on_kurozora.webp') }}" alt="Games and Manga tracking now available on Kurozora">--}}
{{--            </x-picture>--}}
{{--        </section>--}}

{{--        <section class="relative mb-8">--}}
{{--            <a href="{{ config('social.discord.url') }}" target="_blank" class="after:absolute after:inset-0">--}}
{{--                <x-picture>--}}
{{--                    <img class="h-32 w-full object-cover object-center rounded-lg shadow-lg sm:h-44" src="{{ asset('images/static/banners/kurozora_art_challenge_2022.webp') }}"  alt="Kurozora Art Challenge 2022" />--}}
{{--                </x-picture>--}}
{{--            </a>--}}
{{--        </section>--}}

{{--        <section class="relative mb-8">--}}
{{--            <x-section-nav class="flex flex-nowrap justify-between mb-5">--}}
{{--                <x-slot:title>--}}
{{--                    {{ __('KAIROS ’22 Winners') }}--}}
{{--                </x-slot:title>--}}

{{--                <x-slot:description>--}}
{{--                    {{ __('Congrats to the winners 🎉') }}--}}
{{--                </x-slot:description>--}}

{{--                <x-slot:action>--}}
{{--                    <x-section-nav-link class="whitespace-nowrap" href="{{ config('social.discord.url') }}">{{ __('Join KAIROS ’23') }}</x-section-nav-link>--}}
{{--                </x-slot:action>--}}
{{--            </x-section-nav>--}}

{{--            <div class="flex flex-nowrap gap-4 snap-mandatory snap-x overflow-x-scroll no-scrollbar">--}}
{{--                @foreach ($this->users as $user)--}}
{{--                    @php--}}
{{--                        switch ($user->id) {--}}
{{--                        case 363:--}}
{{--                            $backgroundColor = '#FFE15D';--}}
{{--                            $textColor = '#EA5C2B';--}}
{{--                            $imageURL = 'https://cdn.discordapp.com/attachments/1027527900896956416/1061404084860620851/ChrismasThemedWallpaper.png';--}}
{{--                            $text = '1st Place';--}}
{{--                            break;--}}
{{--                        case 765:--}}
{{--                            $backgroundColor = '#ECECEC';--}}
{{--                            $textColor = '#FF8B13';--}}
{{--                            $imageURL = 'https://cdn.discordapp.com/attachments/1027527900896956416/1062558851662102538/Happy_New_Years_.jpg';--}}
{{--                            $text = '2nd Place';--}}
{{--                            break;--}}
{{--                        default:--}}
{{--                            $backgroundColor = '#FFFFFF';--}}
{{--                            $textColor = '#000000';--}}
{{--                            $imageURL = 'https://cdn.discordapp.com/attachments/1027527900896956416/1062558851662102538/Happy_New_Years_.jpg';--}}
{{--                            $text = '';--}}
{{--                        }--}}
{{--                    @endphp--}}

{{--                    <a class="relative pb-2 snap-normal snap-center min-w-[18rem] md:min-w-[30rem]" href="{{ route('profile.details', $user) }}" wire:navigate>--}}
{{--                        <div class="rounded-lg shadow-sm overflow-hidden" style="background-color: {{ $backgroundColor }};">--}}
{{--                            <div class="relative flex justify-center bg-gray-800">--}}
{{--                                <picture class="relative overflow-hidden">--}}
{{--                                    <img class="w-full h-full object-cover" style="max-height: 16rem;" width="300" height="168" src="{{ $imageURL }}" alt="{{ $user->username }} KAIOS ’22 submission" title="{{ $user->username }} KAIOS ’22 submission">--}}

{{--                                    <div class="absolute top-0 left-0 h-full w-full border-4 border-solid border-black/20 rounded-lg"></div>--}}
{{--                                </picture>--}}
{{--                            </div>--}}

{{--                            <div class="flex flex-row px-6 py-6">--}}
{{--                                <div class="flex justify-center mb-3 mr-2" style="max-height: 6rem;">--}}
{{--                                    <picture class="relative w-16 h-16 rounded-full shadow-lg overflow-hidden">--}}
{{--                                        <img class="w-full h-full object-cover" width="160" height="160" src="{{ $user->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) }}" alt="{{ $user->username }} Profile Image" title="{{ $user->username }}">--}}

{{--                                        <div class="absolute top-0 left-0 h-full w-full border-2 border-solid border-black/20 rounded-full"></div>--}}
{{--                                    </picture>--}}
{{--                                </div>--}}

{{--                                <div class="flex flex-col">--}}
{{--                                    <h2 class="text-xl font-medium">{{ $user->username }}</h2>--}}

{{--                                    <span class="block" style="color: {{ $textColor }};">{{ $text }}</span>--}}

{{--                                    @if ($user->id == 363)--}}
{{--                                        <ul class="list-disc block">--}}
{{--                                            <li>1 Year Discord Nitro</li>--}}
{{--                                            <li>1 Year Kurozora+</li>--}}
{{--                                            <li>1 Year Kurozora Early Access</li>--}}
{{--                                            <li>Kurozora PRO</li>--}}
{{--                                            <li>KAIROS ’22 Event Badge</li>--}}
{{--                                            <li>$75 Gift Card of Choice</li>--}}
{{--                                            <li>50 Raffle Tickets for Upcoming Events</li>--}}
{{--                                        </ul>--}}
{{--                                    @elseif ($user->id == 765)--}}
{{--                                        <ul class="list-disc block">--}}
{{--                                            <li>6 Months Discord Nitro</li>--}}
{{--                                            <li>1 Year Kurozora+</li>--}}
{{--                                            <li>1 Year Kurozora Early Access</li>--}}
{{--                                            <li>Kurozora PRO</li>--}}
{{--                                            <li>KAIROS ’22 Event Badge</li>--}}
{{--                                            <li>$50 Gift Card of Choice</li>--}}
{{--                                            <li>25 Raffle Tickets for Upcoming Events</li>--}}
{{--                                        </ul>--}}
{{--                                    @endif--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </a>--}}
{{--                @endforeach--}}
{{--            </div>--}}
{{--        </section>--}}

        <section wire:init="loadPage">
            @foreach ($this->exploreCategories as $index => $exploreCategory)
                @switch($exploreCategory->type)
                @case(\App\Enums\ExploreCategoryTypes::MostPopularShows)
                    <section class="mx-auto max-w-7xl">
                        <div class="no-scrollbar flex snap-mandatory snap-x flex-nowrap overflow-x-scroll xl:rounded-b-2xl">
                            @foreach ($exploreCategory->mostPopular(\App\Models\Anime::class)->exploreCategoryItems as $index => $categoryItem)
                                <x-lockups.banner-lockup :anime="$categoryItem->model" />
                            @endforeach
                        </div>
                    </section>
                    @break
                @default
                    <article class="mx-auto max-w-7xl {{ $index != 1 ? 'border-t-2' : '' }}">
                        <livewire:components.explore-category-section :exploreCategory="$exploreCategory" />
                    </article>
                @endswitch
            @endforeach
        </section>

        @if (!$readyToLoad)
            <section class="mx-auto max-w-7xl pb-6 pl-4 pr-4 sm:px-6">
                <x-skeletons.small-lockup />
                <x-skeletons.small-lockup />
                <x-skeletons.small-lockup />
                <x-skeletons.small-lockup />
            </section>
        @endif

        @guest
            <section class="mx-auto max-w-7xl">
                <a href="{{ route('recap.index') }}" wire:navigate>
                    <x-picture>
                        <img class="h-40 w-full object-cover shadow md:h-80 xl:rounded-3xl" src="{{ asset('images/static/banners/kurozora_recap.webp') }}" alt="Kurozora Recap 2023">
                    </x-picture>
                </a>
            </section>
        @endguest

        <section class="mx-auto max-w-7xl pb-8 pl-4 pr-4 pt-5 sm:px-6">
            <x-section-nav class="mb-5 flex flex-nowrap justify-between">
                <x-slot:title>
                    {{ __('More to Explore') }}
                </x-slot>
            </x-section-nav>

            <div class="grid gap-4 md:grid-cols-3">
                <x-simple-link href="{{ route('anime.seasons.index') }}" wire:navigate class="w-full justify-between rounded-lg bg-gray-100 pb-4 pl-4 pr-4 pt-4 text-sm" :hover-underline-enabled="false">
                    <span>
                        {{ __('Browse by Season') }}
                    </span>

                    @svg('chevron_forward', 'fill-current', ['width' => 12])
                </x-simple-link>

                <x-simple-link href="{{ route('genres.index') }}" wire:navigate class="w-full justify-between rounded-lg bg-gray-100 pb-4 pl-4 pr-4 pt-4 text-sm" :hover-underline-enabled="false">
                    <span>
                        {{ __('Browse by Genre') }}
                    </span>

                    @svg('chevron_forward', 'fill-current', ['width' => 12])
                </x-simple-link>

                <x-simple-link href="{{ route('themes.index') }}" wire:navigate class="w-full justify-between rounded-lg bg-gray-100 pb-4 pl-4 pr-4 pt-4 text-sm" :hover-underline-enabled="false">
                    <span>
                        {{ __('Browse by Theme') }}
                    </span>

                    @svg('chevron_forward', 'fill-current', ['width' => 12])
                </x-simple-link>

                <x-simple-link href="{{ route('schedule') }}" wire:navigate class="w-full justify-between rounded-lg bg-gray-100 pb-4 pl-4 pr-4 pt-4 text-sm" :hover-underline-enabled="false">
                    <span>
                        {{ __('Broadcast Schedule') }}
                    </span>

                    @svg('chevron_forward', 'fill-current', ['width' => 12])
                </x-simple-link>

                <x-simple-link href="{{ route('charts.index') }}" wire:navigate class="w-full justify-between rounded-lg bg-gray-100 pb-4 pl-4 pr-4 pt-4 text-sm" :hover-underline-enabled="false">
                    <span>
                        {{ __('Charts') }}
                    </span>

                    @svg('chevron_forward', 'fill-current', ['width' => 12])
                </x-simple-link>
            </div>
        </section>
    </div>
</main>
