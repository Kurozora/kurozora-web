<x-base-layout>
    <x-slot:title>
        {{ __(':x’s Achievements', ['x' => $user->username]) }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Browse :x’s achievements on :y. Join the :y community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => $user->username, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __(':x’s Achievements', ['x' => $user->username]) }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Browse :x’s achievements on :y. Join the :y community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => $user->username, 'y' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ $user->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('profile.achievements', $user) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        users/{{ $user->id }}/achievements
    </x-slot:appArgument>

    <main>
        <div class="pt-4 pb-6">
            <section class="mb-4 xl:safe-area-inset">
                <div>
                    <div class="flex gap-1 pl-4 pr-4">
                        <div class="flex flex-wrap items-center w-full">
                            <h1 class="text-2xl font-bold">{{ __(':x’s Achievements', ['x' => $user->username]) }}</h1>
                        </div>

                        <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                        </div>
                    </div>
                </div>
            </section>

            @if ($achievements->count())
                <section class="xl:safe-area-inset">
                    <div class="flex flex-wrap gap-4 justify-between pl-4 pr-4">
                        @foreach ($achievements as $achievement)
                           <x-lockups.achievement-lockup :achievement="$achievement" />
                        @endforeach

                        <div class="w-[98%] sm:w-72 flex-grow"></div>
                        <div class="w-[98%] sm:w-72 flex-grow"></div>
                        <div class="w-[98%] sm:w-72 flex-grow"></div>
                        <div class="w-[98%] sm:w-72 flex-grow"></div>
                    </div>

                    <div class="mt-4 pl-4 pr-4">
                        {{ $achievements->links() }}
                    </div>
                </section>
            @else
                <x-empty-state image="empty_anime_library.webp" alt="No achievements" :heading="__('No Achievements')">
                    @if ($user->id == auth()->user()?->id)
                        <p class="text-sm text-secondary">{{ __('Your unlocked achievements will show up here!') }}</p>
                    @else
                        <p class="text-sm text-secondary">{{ __(':x has no achievements unlocked yet.', ['x' => $user->username]) }}</p>
                    @endif
                </x-empty-state>
            @endif
        </div>
    </main>
</x-base-layout>
