<x-base-layout>
    <x-slot:title>
        {{ $user->username }}
    </x-slot:title>

    <x-slot:description>
        {{ $user->biography }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __(':x on :y', ['x' => $user->username, 'y' => config('app.name')]) }}" />
        <meta property="og:description" content="{{ $user->biography ?? __('A community for anime fans with an extensive library of anime, manga, music, games, movies, specials, OVA, and ONA. Only on :x, the largest, free online anime, manga, game & music database in the world. Track, share and discover anime with friends.', ['x' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ $user->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) }}" />
        <meta property="og:type" content="profile" />
        <meta property="og:profile:username" content="{{ $user->username }}" />
        <link rel="canonical" href="{{ route('profile.details', $user) }}">
    </x-slot:meta>

    <x-slot:styles>
        @vite(['resources/css/watch.css'])
    </x-slot:styles>

    <x-slot:scripts>
        @vite(['resources/js/gif.js', 'resources/js/markdown.js', 'resources/js/watch.js'])
    </x-slot:scripts>

    <x-slot:appArgument>
        users/{{ $user->id }}
    </x-slot:appArgument>

    <main
        x-data="profileActions({{ Js::from(['id' => $user->id, 'blocked' => $isBlocked, 'followersCount' => $user->followers_count]) }})"
        x-on:user-blocked.window="syncBlock($event.detail)"
        x-on:followers-badge-refresh.window="syncFollowers($event.detail)"
        x-on:user-actions-failed.window="busy = false"
    >
        <div>
            <section>
                <x-user.banner-image :user="$user" :on-profile="true" />
            </section>

            <section class="relative z-10 xl:safe-area-inset">
                <div class="pt-4 pb-6 pl-4 pr-4">
                    <div class="flex items-end justify-between -mt-14 sm:-mt-20">
                        <div class="flex items-end">
                            <div class="relative">
                                <x-user.profile-image :user="$user" :on-profile="true" />
                            </div>

                            <div class="flex flex-col gap-1 sm:flex-row">
                                <p class="ml-2 text-xl font-bold">{{ $user->username }}</p>

                                <x-user.badge-shelf :user="$user" />
                            </div>
                        </div>

                        <div class="flex gap-2 items-end">
                            @if ($isOwner)
                                <x-button x-on:click="$dispatch('open-modal', { id: 'edit-profile' })">{{ __('Edit') }}</x-button>
                            @else
                                <x-button
                                    class="!bg-red-500 hover:!bg-red-600"
                                    x-on:click="$dispatch('open-modal', { id: 'block-user' })"
                                    x-bind:disabled="busy"
                                    x-show="blocked"
                                    :style="$isBlocked ? null : 'display: none;'"
                                >
                                    {{ __('Blocked') }}
                                </x-button>

                                @if (!$isBlockedBy)
                                    <div x-show="!blocked" @if ($isBlocked) style="display: none;" @endif>
                                        <x-follow-button :user="$user" :is-followed="$isFollowed" />
                                    </div>
                                @endif
                            @endif

                            {{-- More Options --}}
                            <x-dropdown align="right" width="48">
                                <x-slot:trigger>
                                    <x-circle-button
                                        title="{{ __('More') }}"
                                    >
                                        @svg('ellipsis', 'fill-current', ['width' => '28'])
                                    </x-circle-button>
                                </x-slot:trigger>

                                <x-slot:content>
                                    <button
                                        class="block w-full pl-4 pr-4 pt-2 pb-2 text-primary text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary"
                                        x-on:click="$dispatch('open-modal', { id: 'share' })"
                                    >
                                        {{ __('Share') }}
                                    </button>

                                    @auth
                                        @if (!$isOwner)
                                            <button
                                                class="block w-full pl-4 pr-4 pt-2 pb-2 text-red-500 text-xs text-center font-semibold hover:bg-tertiary focus:bg-secondary"
                                                x-on:click="$dispatch('open-modal', { id: 'block-user' })"
                                                x-text="blocked ? {{ Js::from(__('Blocked')) }} : {{ Js::from(__('Block')) }}"
                                            >
                                                {{ $isBlocked ? __('Blocked') : __('Block') }}
                                            </button>
                                        @endif
                                    @endauth
                                </x-slot:content>
                            </x-dropdown>
                        </div>
                    </div>

                    <div class="mt-2 pt-2 pb-2 px-3">{!! $user->biography_html !!}</div>

                    <div class="flex justify-between">
                        <x-profile-information-badge href="{{ route('leaderboards.reputation') }}" wire:navigate>
                            <x-slot:title>{{ __('Reputation') }}</x-slot:title>
                            <x-slot:description>{{ number_shorten($user->reputation_count, 0, true) }}</x-slot:description>
                        </x-profile-information-badge>

                        <x-profile-information-badge href="{{ route('profile.achievements', $user) }}" wire:navigate>
                            <x-slot:title>{{ __('Achievements') }}</x-slot:title>
                            <x-slot:description>{{ number_shorten($user->achievements_count, 0, true) }}</x-slot:description>
                        </x-profile-information-badge>

                        <x-profile-information-badge href="{{ route('profile.following', $user) }}" wire:navigate>
                            <x-slot:title>{{ __('Following') }}</x-slot:title>
                            <x-slot:description>{{ number_shorten($user->following_count, 0, true) }}</x-slot:description>
                        </x-profile-information-badge>

                        <x-profile-information-badge href="{{ route('profile.followers', $user) }}" wire:navigate>
                            <x-slot:title>{{ __('Followers') }}</x-slot:title>
                            <x-slot:description><span x-text="followersLabel">{{ number_shorten($user->followers_count, 0, true) }}</span></x-slot:description>
                        </x-profile-information-badge>

                        <x-profile-information-badge href="{{ route('profile.ratings', $user) }}" wire:navigate>
                            <x-slot:title>{{ __('Reviews') }}</x-slot:title>
                            <x-slot:description>{{ number_shorten($user->media_ratings_count, 0, true) }}</x-slot:description>
                        </x-profile-information-badge>
                    </div>

                    <x-hr class="mt-2" />
                </div>
            </section>

            <x-user.library-section :user="$user" :type="\App\Models\Anime::class" />

            <x-user.library-section :user="$user" :type="\App\Models\Manga::class" />

            <x-user.library-section :user="$user" :type="\App\Models\Game::class" />

            <x-user.favorites-section :user="$user" :type="\App\Models\Anime::class" />

            <x-user.favorites-section :user="$user" :type="\App\Models\Manga::class" />

            <x-user.favorites-section :user="$user" :type="\App\Models\Game::class" />

            <section
                class="pb-6 pl-4 pr-4 mb-8 xl:safe-area-inset-scroll"
                x-show="blocked && !showBlockedPosts"
                @if (!$isBlocked) style="display: none;" @endif
            >
                <div class="max-w-2xl mx-auto">
                    <h2 class="text-2xl font-bold">{{ __('@:x is blocked', ['x' => $user->username]) }}</h2>

                    <p class="mt-2 text-sm text-secondary">{{ __('Are you sure you want to view these posts? Viewing posts won’t unblock @:x.', ['x' => $user->username]) }}</p>

                    <x-button class="mt-4" x-on:click="showBlockedPosts = true">{{ __('View posts') }}</x-button>
                </div>
            </section>

            <div
                x-show="!blocked || showBlockedPosts"
                @if ($isBlocked) style="display: none;" @endif
            >
                @if ($isBlockedBy)
                    <section class="pb-6 pl-4 pr-4 mb-8 xl:safe-area-inset-scroll">
                        <div class="max-w-2xl mx-auto">
                            <h2 class="text-2xl font-bold">{{ __('@:x has blocked you', ['x' => $user->username]) }}</h2>

                            <p class="mt-2 text-sm text-secondary">{{ __('You can view public posts from @:x, but you are blocked from engaging with them. You also cannot follow or message @:x.', ['x' => $user->username]) }}</p>
                        </div>
                    </section>
                @endif

                <x-user.feed-messages-section :user="$user" />
            </div>
        </div>

        <x-feed.message-modals />

        <x-share-modal
            id="share"
            :link="route('profile.details', $user)"
            :title="$user->username"
            :image-url="$user->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile())"
            :type="'user'"
        />

        @if ($isOwner)
            <x-modal-form-section id="edit-profile" submit="">
                <x-slot:title>
                    {{ __('Edit Profile') }}
                </x-slot:title>

                <livewire:profile.update-profile-information-form lazy />
            </x-modal-form-section>
        @endif

        @auth
            @if (!$isOwner)
                <x-dialog-modal id="block-user">
                    <x-slot:title>
                        <span x-text="blocked ? {{ Js::from(__('Unblock :x', ['x' => $user->username])) }} : {{ Js::from(__('Block :x', ['x' => $user->username])) }}">{{ $isBlocked ? __('Unblock :x', ['x' => $user->username]) : __('Block :x', ['x' => $user->username]) }}</span>
                    </x-slot:title>

                    <x-slot:content>
                        <div class="pt-4 pb-4 pl-4 pr-4">
                            <p x-text="blocked ? {{ Js::from(__('They will be able to follow you and view your messages.')) }} : {{ Js::from(__('They will be able to see your public messages, but will no longer be able to engage with them. They will also not be able to follow or message you, and you will not see notifications from them.')) }}">{{ $isBlocked ? __('They will be able to follow you and view your messages.') : __('They will be able to see your public messages, but will no longer be able to engage with them. They will also not be able to follow or message you, and you will not see notifications from them.') }}</p>
                        </div>
                    </x-slot:content>

                    <x-slot:footer>
                        <x-outlined-button x-on:click="$dispatch('close')" x-bind:disabled="busy">
                            {{ __('Cancel') }}
                        </x-outlined-button>

                        <x-button class="ml-2" x-on:click="toggleBlock()" x-bind:disabled="busy">
                            <span x-text="blocked ? {{ Js::from(__('Unblock')) }} : {{ Js::from(__('Block')) }}">{{ $isBlocked ? __('Unblock') : __('Block') }}</span>
                        </x-button>
                    </x-slot:footer>
                </x-dialog-modal>
            @endif
        @endauth
    </main>
</x-base-layout>
