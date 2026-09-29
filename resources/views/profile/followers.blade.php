<x-base-layout>
    <x-slot:title>
        {{ __(':x’s Followers', ['x' => $user->username]) }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Browse :x’s followers on :y. Join the :y community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => $user->username, 'y' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __(':x’s Followers', ['x' => $user->username]) }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Browse :x’s followers on :y. Join the :y community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => $user->username, 'y' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ $user->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('profile.followers', $user) }}">
    </x-slot:meta>

    <x-slot:appArgument>
        users/{{ $user->id }}/followers
    </x-slot:appArgument>

    <main>
        <div class="pt-4 pb-6">
            <section class="mb-4 xl:safe-area-inset">
                <div>
                    <div class="flex gap-1 pl-4 pr-4">
                        <div class="flex flex-wrap items-center w-full">
                            <h1 class="text-2xl font-bold">{{ __(':x’s Followers', ['x' => $user->username]) }}</h1>
                        </div>

                        <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                        </div>
                    </div>
                </div>
            </section>

            @if ($followers->count())
                <section class="xl:safe-area-inset">
                    <x-rows.user-lockup :users="$followers" :is-row="false" />

                    <div class="mt-4 pl-4 pr-4">
                        {{ $followers->links() }}
                    </div>
                </section>
            @else
                <x-empty-state image="empty_anime_library.webp" alt="No followers" :heading="__('No Followers')">
                    @if ($user->id != auth()->user()?->id)
                        <p class="text-sm text-secondary">{{ __('Be the first to follow :x!', ['x' => $user->username]) }}</p>
                        <x-follow-button :user="$user" :is-followed="$isFollowed" />
                    @else
                        <p class="text-sm text-secondary">{{ __('When someone follows you, they will show up here!') }}</p>
                    @endif
                </x-empty-state>
            @endif
        </div>
    </main>
</x-base-layout>
