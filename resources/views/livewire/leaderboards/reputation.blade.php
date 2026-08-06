<main>
    <x-slot:title>
        {{ __('Leaderboard') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Browse the highest-reputation users on :y. Join the :y community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Leaderboard') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Browse the highest-reputation users on :y. Join the :y community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['y' => config('app.name')]) }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('leaderboards.reputation') }}">
    </x-slot:meta>

    <x-slot:appArgument>
        users?sort=reputation(most)
    </x-slot:appArgument>

    @php
        $users = $this->users;
        $isFirstPage = $users instanceof \Illuminate\Contracts\Pagination\Paginator
            ? $users->currentPage() === 1
            : false;
        $podiumUsers = $isFirstPage ? $users->take(3) : collect();
        $rowUsers = $isFirstPage ? $users->slice(3) : $users;
        $rowStartRank = $users instanceof \Illuminate\Contracts\Pagination\Paginator
            ? (($users->currentPage() - 1) * $users->perPage()) + ($isFirstPage ? 4 : 1)
            : 1;
    @endphp

    <div class="pt-4 pb-6" wire:init="loadPage">
        <section class="mb-4 xl:safe-area-inset">
            <div class="flex gap-1 pl-4 pr-4">
                <div class="flex flex-wrap items-center w-full">
                    <h1 class="text-2xl font-bold">{{ __('Leaderboard') }}</h1>
                </div>

                <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                </div>
            </div>
        </section>

        @if ($users->count())
            @if ($podiumUsers->count() === 3)
                <section class="mb-6 xl:safe-area-inset">
                    <div class="flex items-end justify-center gap-4 sm:gap-8 pl-4 pr-4">
                        @php
                            $podiumOrder = [
                                ['user' => $podiumUsers[1], 'rank' => 2, 'imageClass' => 'w-24 h-24 sm:w-28 sm:h-28'],
                                ['user' => $podiumUsers[0], 'rank' => 1, 'imageClass' => 'w-32 h-32 sm:w-40 sm:h-40'],
                                ['user' => $podiumUsers[2], 'rank' => 3, 'imageClass' => 'w-20 h-20 sm:w-24 sm:h-24'],
                            ];
                        @endphp

                        @foreach ($podiumOrder as $tile)
                            @php
                                $podiumUser = $tile['user'];
                                $rank = $tile['rank'];
                            @endphp

                            <a
                                class="flex flex-col items-center text-center"
                                href="{{ route('profile.details', $podiumUser) }}"
                                wire:navigate
                            >
                                <picture
                                    class="relative shrink-0 {{ $tile['imageClass'] }} aspect-square rounded-full overflow-hidden"
                                    style="background-color: {{ $podiumUser->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
                                >
                                    <img
                                        class="w-full h-full object-cover lazyload"
                                        data-sizes="auto"
                                        data-src="{{ $podiumUser->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) }}"
                                        alt="{{ $podiumUser->username }} Profile Image"
                                        title="{{ $podiumUser->username }}"
                                        width="{{ $podiumUser->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['width'] ?? 96 }}"
                                        height="{{ $podiumUser->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['height'] ?? 96 }}"
                                    >

                                    <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-full"></div>
                                </picture>

                                <p class="mt-2 font-bold">{{ '#' . $rank }}</p>

                                <p class="text-sm leading-tight line-clamp-2 max-w-[8rem]" title="{{ $podiumUser->username }}">{{ $podiumUser->username }}</p>

                                <p class="text-xs leading-tight text-secondary">{{ number_shorten($podiumUser->reputation_count, 0, true) }}</p>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($rowUsers->count())
                <section class="xl:safe-area-inset">
                    <div class="flex flex-wrap gap-4 justify-between pl-4 pr-4">
                        @foreach ($rowUsers as $rowUser)
                            @php
                                $rank = $rowStartRank + $loop->index;
                            @endphp

                            <x-lockups.user-lockup
                                :user="$rowUser"
                                :is-row="false"
                                :show-follow-button="false"
                            >
                                <x-slot:leadingAccessory>
                                    <p class="font-semibold text-secondary">{{ '#' . number_format($rank) }}</p>
                                </x-slot:leadingAccessory>

                                <x-slot:subtitle>
                                    {{ number_shorten($rowUser->reputation_count, 0, true) }}
                                </x-slot:subtitle>
                            </x-lockups.user-lockup>
                        @endforeach

                        <div class="w-64 md:w-80 flex-grow"></div>
                        <div class="w-64 md:w-80 flex-grow"></div>
                    </div>

                    <div class="mt-4 pl-4 pr-4">
                        {{ $users->links() }}
                    </div>
                </section>
            @elseif ($users->hasPages())
                <div class="mt-4 pl-4 pr-4">
                    {{ $users->links() }}
                </div>
            @endif
        @elseif (!$readyToLoad)
            <section class="mt-4 xl:safe-area-inset">
                <div class="flex items-end justify-center gap-4 sm:gap-8 pl-4 pr-4 mb-6">
                    <div class="bg-secondary rounded-full w-24 h-24 sm:w-28 sm:h-28"></div>
                    <div class="bg-secondary rounded-full w-32 h-32 sm:w-40 sm:h-40"></div>
                    <div class="bg-secondary rounded-full w-20 h-20 sm:w-24 sm:h-24"></div>
                </div>

                <div class="flex flex-wrap gap-4 justify-between pl-4 pr-4">
                    @foreach (range(1, 25) as $range)
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                    @endforeach

                    <div class="w-64 md:w-80 flex-grow"></div>
                    <div class="w-64 md:w-80 flex-grow"></div>
                </div>
            </section>
        @else
            <section class="flex flex-col items-center justify-center mt-4 text-center xl:safe-area-inset" style="min-height: 50vh;">
                <x-picture>
                    <img class="w-full max-w-sm" src="{{ asset('images/static/placeholders/empty_anime_library.webp') }}" alt="No leaderboard yet" title="No leaderboard yet">
                </x-picture>

                <p class="font-bold">{{ __('No Users') }}</p>

                <p class="text-sm text-secondary">{{ __('The leaderboard is empty. Check back later!') }}</p>
            </section>
        @endif
    </div>
</main>
