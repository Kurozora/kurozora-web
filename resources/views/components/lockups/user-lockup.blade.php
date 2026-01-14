@props(['user', 'isRow' => true])

@php
    $class = $isRow ? 'pb-2 shrink-0 snap-normal snap-center' : '';
    $isFollowed = (bool) $user->isFollowed;
    $followersCount = $user->followers_count;
@endphp

<div {{ $attributes->merge(['class' => 'relative flex-grow w-64 md:w-80 ' . $class]) }}>
    <div class="flex flex-nowrap justify-between">
        <div class="flex items-center">
            <picture
                class="relative shrink-0 w-16 aspect-square rounded-full overflow-hidden"
                style="background-color: {{ $user->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['background_color'] ?? 'var(--bg-secondary-color)' }};"
            >
                <img
                    class="w-full h-full object-cover lazyload"
                    data-sizes="auto"
                    data-src="{{ $user->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) }}"
                    alt="{{ $user->username }} Profile Image"
                    title="{{ $user->username }}"
                    width="{{ $user->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['width'] ?? 96 }}"
                    height="{{ $user->getFirstMedia(\App\Enums\MediaCollection::Profile)?->custom_properties['height'] ?? 96 }}"
                >

                <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-full"></div>
            </picture>

            <div class="pl-2 pr-2">
                <p class="leading-tight line-clamp-2" title="{{ $user->username }}">{{ $user->username }}</p>
                <p class="text-xs leading-tight opacity-75">
                    @auth
                        @if ($followersCount === 0)
                            @if ($user->id === auth()->user()->id)
                                {{ __('You, followed by you!') }}
                            @else
                                {{ __('Be the first to follow!') }}
                            @endif
                        @elseif ($followersCount === 1)
                            @if ($user->id == auth()->user()->id)
                                {{ __('Followed by you… and one fan!') }}
                            @else
                                {{ $isFollowed ? __('Followed by you.') : __('Followed by one fan.') }}
                            @endif
                        @elseif ($followersCount >= 2 && $followersCount <= 999)
                            @if ($user->id == auth()->user()->id)
                                {{ __('Followed by you and :x fans.', ['x' => $followersCount]) }}
                            @else
                                {{
                                    $isFollowed ?
                                    trans_choice('{1}Followed by you and one fan.|[2,*] Followed by you and :x fans.', $followersCount - 1, ['x' => $followersCount - 1]) :
                                    __('Followed by :x fans.', ['x' => $followersCount])
                                }}
                            @endif
                        @else
                            @if ($user->id == auth()->user()->id)
                                {{ __('Followed by :x fans.', ['x' => number_shorten($followersCount)]) }}
                            @else
                                {{
                                    $isFollowed ?
                                    trans_choice('{1}Followed by you and one fan.|[2,*] Followed by you and :x fans.',$followersCount - 1, ['x' => number_shorten($followersCount - 1)]) :
                                    __('Followed by :x fans.', ['x' => number_shorten($followersCount)])
                                }}
                            @endif
                        @endif
                    @else
                        {{ __('Be the first to follow!') }}
                    @endauth
                </p>
            </div>
        </div>

        <a class="absolute w-full h-full" href="{{ route('profile.details', $user) }}" wire:navigate></a>

        <div class="flex flex-row flex-nowrap items-center justify-between z-10 whitespace-nowrap">
            @if ($user->id != auth()->user()?->id)
                <livewire:components.follow-button :user="$user" :is-followed="$isFollowed" wire:key="{{ uniqid(more_entropy: true) }}" />
            @endif
        </div>
    </div>
</div>
